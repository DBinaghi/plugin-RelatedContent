<?php
/**
 * RelatedContent
 *
 * Suggests Items related to the one currently shown

 *
 * @copyright Daniele Binaghi, 2021-2026
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package RelatedContent
 */

/**
 * The RelatedContent plugin.
 * @package Omeka\Plugins\RelatedContent
 */
 
class RelatedContentPlugin extends Omeka_Plugin_AbstractPlugin
{
	protected $_criteria;
	
	protected $_hooks = array(
		'install',
		'uninstall',
		'initialize',
		'upgrade',
		'config',
		'config_form',
		'admin_head',
		'public_head',
		'public_items_show'
	);
			
	public function hookInstall()
	{
		set_option('related_content_limit', 6);
		set_option('related_content_square_thumbnails', 1);
		set_option('related_content_short_date', 0);
		set_option('related_content_show_title', 1);
		set_option('related_content_exclude_no_image', 0);

		$criteria = array();
		$criteria['elements'][49]['weight'] = 2; 	// Subject
		$criteria['elements'][40]['weight'] = 1.2; 	// Date
		$criteria['elements'][39]['weight'] = 1.2; 	// Creator
		$criteria['elements'][37]['weight'] = 1; 	// Contributor
		$criteria['elements'][51]['weight'] = 1; 	// Type
		$criteria['collection']['weight'] = 0.5; 	// Collection
		$criteria['item type']['weight'] = 0.5; 	// Item Type
		$criteria['tags']['weight'] = 0.5; 			// Tags
		set_option('related_content_criteria', json_encode($criteria));
	}

	public function hookUninstall()
	{
		delete_option('related_content_limit');
		delete_option('related_content_square_thumbnails');
		delete_option('related_content_short_date');
		delete_option('related_content_show_title');
		delete_option('related_content_exclude_no_image');
		delete_option('related_content_criteria');
	 }

	public function hookInitialize()
	{
		add_translation_source(dirname(__FILE__) . '/languages');

		// Initialize new options if missing (e.g. after upgrade from older version)
		if (get_option('related_content_show_title') === null) {
			set_option('related_content_show_title', 1);
		}
		if (get_option('related_content_exclude_no_image') === null) {
			set_option('related_content_exclude_no_image', 0);
		}

		$criteria = json_decode(get_option('related_content_criteria'), true);
		$this->_criteria = $criteria;
	}
	
	public function hookUpgrade($args)
	{
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];

        if (version_compare($oldVersion, '1.3', '<')) {
			$criteria = json_decode(get_option('related_content_criteria'), true);
			$criteria['Item Type'] = array("weight" => '', "constraint" => 0);
			set_option('related_content_criteria', json_encode($criteria));
		}

        if (version_compare($oldVersion, '1.4', '<')) {
			$criteria = json_decode(get_option('related_content_criteria'), true);
			// Subject
			if (isset($criteria['Subject'])) {
				$criteria['elements'][49] = $criteria['Subject'];
				unset($criteria['Subject']);
			}
			// Date
			if (isset($criteria['Date'])) {
				$criteria['elements'][40] = $criteria['Date'];
				unset($criteria['Date']);
			}
			$criteria['elements'][40]['isDate'] = true;
			// Creator
			if (isset($criteria['Creator'])) {
				$criteria['elements'][39] = $criteria['Creator'];
				unset($criteria['Creator']);
			}
			// Contributor
			if (isset($criteria['Contributor'])) {
				$criteria['elements'][37] = $criteria['Contributor'];
				unset($criteria['Contributor']);
			}
			// Type
			if (isset($criteria['Type'])) {
				$criteria['elements'][51] = $criteria['Type'];
				unset($criteria['Type']);
			}
			// Collection
			if (isset($criteria['Collection'])) {
				$criteria['collection'] = $criteria['Collection'];
				unset($criteria['Collection']);
			}
			// Item Type
			if (isset($criteria['Item Type'])) {
				$criteria['item type'] = $criteria['Item Type'];
				unset($criteria['Item Type']);
			}
			// Tags
			if (isset($criteria['Tag'])) {
				$criteria['tags'] = $criteria['Tag'];
				unset($criteria['Tag']);
			}

			set_option('related_content_criteria', json_encode($criteria));
		}
	}
		
	public function hookConfig($args)
	{
		$post = $args['post'];

		// Validate and sanitize Items Limit
		$limit = (int)$post['related_content_limit'];
		if ($limit < 1) $limit = 1;
		set_option('related_content_limit', $limit);

		set_option('related_content_square_thumbnails', $post['related_content_square_thumbnails']);
		set_option('related_content_short_date', $post['related_content_short_date']);
		set_option('related_content_show_title', isset($post['related_content_show_title']) ? 1 : 0);
		set_option('related_content_exclude_no_image', isset($post['related_content_exclude_no_image']) ? 1 : 0);

		// Validate weights: must be numeric and positive, or empty
		$criteria = isset($post['criteria']) ? $post['criteria'] : array();
		if (isset($criteria['elements'])) {
			foreach ($criteria['elements'] as $id => $element) {
				if (isset($element['weight']) && $element['weight'] !== '') {
					$criteria['elements'][$id]['weight'] = is_numeric($element['weight']) && $element['weight'] > 0
						? (float)$element['weight']
						: '';
				}
			}
		}
		foreach (array('tags', 'collection', 'item type') as $key) {
			if (isset($criteria[$key]['weight']) && $criteria[$key]['weight'] !== '') {
				$criteria[$key]['weight'] = is_numeric($criteria[$key]['weight']) && $criteria[$key]['weight'] > 0
					? (float)$criteria[$key]['weight']
					: '';
			}
		}

		$this->_criteria = $criteria;
		set_option('related_content_criteria', json_encode($criteria));
	}
	
	public function hookConfigForm()
	{
		$criteria = $this->_criteria;

		$table = get_db()->getTable('Element');
		$select = $table->getSelect()
			->order('elements.element_set_id')
			->order('ISNULL(elements.order)')
			->order('elements.order');
		$elements = $table->fetchObjects($select);

		include 'config_form.php';
	}

	public function hookAdminHead($args)
	{
		// Load CSS and JS only on the plugin's own config page
		$request = Zend_Controller_Front::getInstance()->getRequest();
		if ($request->getControllerName() === 'plugins' && $request->getActionName() === 'config') {
			queue_css_file('related_content');
			queue_js_file('related_content');
		}
	}

	public function hookPublicHead($args)
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		if ($request->getControllerName() === 'items' && $request->getActionName() === 'show') {
			queue_css_file('related_content');
		}
	}

	public function hookPublicItemsShow($args)
	{
		$criteria = $this->_criteria;
		$item = $args['item'];
		$limit = (int)get_option('related_content_limit');
		$thumbnailType = ((bool)get_option('related_content_square_thumbnails') ? 'square_thumbnail' : 'thumbnail');
		$showTitle = (bool)get_option('related_content_show_title');
		$excludeNoImage = (bool)get_option('related_content_exclude_no_image');
		$results = array();
		$constraints = array();
		
		// Elements
		if (isset($criteria['elements']) && is_array($criteria['elements'])) {
			foreach ($criteria['elements'] as $key => $value) {
				if (isset($value['weight']) && $weight = $value['weight']) {
					$element = self::findElementById($key);
					if ($metadata = metadata($item, array($element->set_name, $element->name), array('all' => true, 'no_filter' => true))) {
						//shorten date, if required
						if (isset($value['isDate']) && (bool)$value['isDate'] && (bool)get_option('related_content_short_date')) {
							$metadata = array_map(function($m) { return substr($m, 0, 4); }, $metadata);
							// retrieve results using date-specific method (LIKE match)
							$results_element = self::getResultsByDateElement($key, $metadata, $weight);
						} else {
							// retrieve results
							$results_element = self::getResultsByElement($key, $metadata, $weight);
						}

						// filter constraints array if needed
						if (isset($value['constraint']) && (bool)$value['constraint']) {
							$constraints =  self::updateConstraints($constraints, array_keys($results_element));
						}
						
						// adds values to $results
						$results = self::addAndMergeArrays($results, $results_element);
					}
				}
			}
		}
		
		// Tags
		if (($weight = $criteria['tags']['weight']) && metadata($item, 'has tags')) {
			// retrieve tag results
			$tags = get_current_record('Item')->Tags;
			$results_tags = get_records('Item', array('tags'=>$tags));
			$results_tags = array_column($results_tags, 'id');
		
			// multiply by weight, according to importance of element
			$results_tags = self::countAndMultiply($results_tags, $weight);

			// filter constraints array if needed
			if (isset($criteria['tags']['constraint']) && (bool)$criteria['tags']['constraint']) {
				$constraints =  self::updateConstraints($constraints, array_keys($results_tags));
			}

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_tags);
		}

		// Collection
		if (($weight = $criteria['collection']['weight']) && ($collection = get_collection_for_item($item))) {
			// retrieve collection results
			$results_collection = self::getResultsByCollection($collection, $weight);

			// filter constraints array if needed
			if (isset($criteria['collection']['constraint']) && (bool)$criteria['collection']['constraint']) {
				$constraints =  self::updateConstraints($constraints, array_keys($results_collection));
			}

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_collection);
		}

		// Item Type
		if (($weight = $criteria['item type']['weight']) && ($itemTypeID = $item->item_type_id)) {
			// retrieve item type results
			$results_item_type = self::getResultsByItemType($itemTypeID, $weight);

			// filter constraints array if needed
			if (isset($criteria['item type']['constraint']) && (bool)$criteria['item type']['constraint']) {
				$constraints =  self::updateConstraints($constraints, array_keys($results_item_type));
			}

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_item_type);
		}
		
		// filter out current item
		unset($results[$item->id]);

		// applies constraints
		if (count($constraints) > 0) {
			foreach ($results as $key => $value) {
				// searches for $results keys in $constraints, if not found removes $results elements
				if (!in_array($key, $constraints)) unset($results[$key]);
			}			
		}
		
		if (count($results) > 0) {
			// order results by score, then shuffle items with equal score for variety
			arsort($results);
			$grouped = array();
			foreach ($results as $key => $value) {
				$grouped[(string)$value][] = $key;
			}
			$sorted = array();
			foreach ($grouped as $score => $ids) {
				shuffle($ids);
				foreach ($ids as $id) {
					$sorted[$id] = $score;
				}
			}

			// displays thumbnails
			echo "<div id='related_content'" . ($showTitle ? " class='show-title'" : "") . ">\n";
			echo "<h3><strong>" . __('Related Items you might want to check out') . "...</strong></h3>";
			echo "<div class='related-items" . ($showTitle ? ' show-titles' : '') . "'>\n";

			$i = 1;
			foreach ($sorted as $key => $value) {
				$item = get_record_by_id('Item', $key);

				// skip items without image if required
				if ($excludeNoImage && !metadata($item, 'has files')) {
					continue;
				}

				$title = html_entity_decode(metadata($item, array('Dublin Core', 'Title')), ENT_QUOTES, 'UTF-8');
				$truncatedTitle = mb_strlen($title) > 20 ? mb_substr($title, 0, 20) . '...' : $title;

				$content = item_image($thumbnailType, array('alt' => $title), 0, $item);
				if ($showTitle) {
					$content .= '<div class="related-title">' . html_escape($truncatedTitle) . '</div>';
				}

				echo link_to_item($content, array('class' => 'image', 'title' => $title), 'show', $item);

				$i++;
				if ($i > $limit) break;
			}
			
			echo "</div>\n";
			echo "</div>";
		}
	}

	public function countAndMultiply($items, $multiplier=1) {
		// add count field to array
		$items_counted = array_count_values($items);

		// multiply value if needed (handles weights both > 1 and < 1)
		if ($multiplier != 1) {
			foreach ($items_counted as $key=>&$value) {
				$value = $value * $multiplier;
			}
		}
		
		return $items_counted;
	}
	
	public function addAndMergeArrays($array_1, $array_2) {
		$results = array();
		foreach (array_keys($array_1 + $array_2) as $key) {
			$results[$key] = (isset($array_1[$key]) ? $array_1[$key] : 0) + (isset($array_2[$key]) ? $array_2[$key] : 0);
		}
		return $results;
	}
	
	public function updateConstraints($array_1, $array_2) {
		if (count($array_1) == 0) {
			return $array_2;
		} else {
			return array_intersect($array_1, $array_2);
		}
	}
	
	public function getResultsByElement($element_id, $element_array, $element_weight=1) {
		$db = get_db();
		$joinCondition = '_advanced_0.record_id = items.id AND _advanced_0.record_type = \'Item\' AND _advanced_0.element_id = ';

		$select = $db
			->select()
			->from(array('items' => $db->Item), 'id')
			->joinLeft(array('_advanced_0' => $db->ElementText), $joinCondition . $element_id, array())
			->where("public = 1");

		if (!empty($element_array)) {
			$placeholders = implode(',', array_fill(0, count($element_array), '?'));
			$select->where("_advanced_0.text IN ($placeholders)", $element_array);
		}		

		$results = $db->fetchCol($select);
		
		// multiply by weight, according to importance of element
		return self::countAndMultiply($results, $element_weight);
	}
	
	public function getResultsByDateElement($element_id, $dates, $element_weight=1) {
		$db = get_db();
		$joinCondition = '_advanced_0.record_id = items.id AND _advanced_0.record_type = \'Item\' AND _advanced_0.element_id = ';

		// $dates can be a single string or an array (when 'all' => true)
		if (!is_array($dates)) {
			$dates = array($dates);
		}

		$select = $db
			->select()
			->from(array('items' => $db->Item), 'id')
			->joinLeft(array('_advanced_0' => $db->ElementText), $joinCondition . $element_id, array())
			->where("public = 1");

		$orConditions = array();
		foreach ($dates as $date) {
			$orConditions[] = $db->quoteInto("_advanced_0.text LIKE ?", $date . '%');
		}
		if (!empty($orConditions)) {
			$select->where(implode(' OR ', $orConditions));
		}

		$results = $db->fetchCol($select);
		
		// multiply by weight, according to importance of element
		return self::countAndMultiply($results, $element_weight);
	}
	
	public function getResultsByCollection($collection, $element_weight=1) {
		$db = get_db();

		$select = $db
			->select()
			->from(array('items' => $db->Item), 'id')
			->where("collection_id = ?", (int)$collection->id)
			->where("public = 1");
		$results = $db->fetchCol($select);
		
		// multiply by weight, according to importance of element
		return self::countAndMultiply($results, $element_weight);
	}
	
	public function getResultsByItemType($itemTypeID, $element_weight=1) {
		$db = get_db();

		$select = $db
			->select()
			->from(array('items' => $db->Item), 'id')
			->where("item_type_id = ?", (int)$itemTypeID)
			->where("public = 1");
		$results = $db->fetchCol($select);
		
		// multiply by weight, according to importance of element
		return self::countAndMultiply($results, $element_weight);
	}
	
	public function findElementById($elementID)
    {
		$db = get_db();

		return $db->getTable('Element')->find($elementID);
    }
}
