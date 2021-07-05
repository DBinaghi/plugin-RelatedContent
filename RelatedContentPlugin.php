<?php
/**
 * RelatedContent
 *
 * Suggests Items related to the one currently shown

 *
 * @copyright Daniele Binaghi, 2021
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Reference
 */

/**
 * The RelatedContent plugin.
 * @package Omeka\Plugins\Reference
 */
 
class RelatedContentPlugin extends Omeka_Plugin_AbstractPlugin
{
	protected $_hooks = array(
		'install',
		'uninstall',
		'initialize',
		'upgrade',
		'config',
		'config_form',
		'public_head',
		'public_items_show'
	);
			
	public function hookInstall()
	{
		set_option('related_content_limit', 6);
		set_option('related_content_square_thumbnails', 1);
		set_option('related_content_short_date', 0);
		
		$criteria = array(
			"Subject" => array("weight" => 2, "constraint" => 0),
			"Tag" => array("weight" => 2, "constraint" => 0),
			"Date" => array("weight" => 1.5, "constraint" => 0),
			"Creator" => array("weight" => 1.2, "constraint" => 0),
			"Contributor" => array("weight" => 1, "constraint" => 0),
			"Type" => array("weight" => 0.5, "constraint" => 0),
			"Collection" => array("weight" => 0.5, "constraint" => 0),
			"Item Type" => array("weight" => 0.5, "constraint" => 0)
		);
		set_option('related_content_criteria', json_encode($criteria));
	}

	public function hookUninstall()
	{
		delete_option('related_content_limit');
		delete_option('related_content_square_thumbnails');
		delete_option('related_content_short_date');
		delete_option('related_content_criteria');
	 }

	public function hookInitialize()
	{
		add_translation_source(dirname(__FILE__) . '/languages');

		$criteria = json_decode(get_option('related_content_criteria'), true);
		
		// Adds/removes elements if DublinCoreExtended plugin is/is not active
		// Please note: here one can add other DCE:Elements, if needed
		if (plugin_is_active('DublinCoreExtended')) {
			if (!isset($criteria['Spatial Coverage'])) {
				$criteria['Spatial Coverage'] = array("weight" => '', "constraint" => 0);
			}
			if (!isset($criteria['Temporal Coverage'])) {
				$criteria['Temporal Coverage'] = array("weight" => '', "constraint" => 0);
			}
		} else {
			unset($criteria['Spatial Coverage']);
			unset($criteria['Temporal Coverage']);
		}
		
		$this->_criteria = $criteria;
	}
	
	public function hookUpgrade()
	{
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];

        if (version_compare($oldVersion, '1.3', '<')) {
			$criteria = json_decode(get_option('related_content_criteria'), true);
			$criteria['Item Type'] = array("weight" => '', "constraint" => 0);
			set_option('related_content_criteria', json_encode($criteria));
		}
	}
	
	public function hookConfig($args)
	{
		$post = $args['post'];
		set_option('related_content_limit', $post['related_content_limit']);
		set_option('related_content_square_thumbnails', $post['related_content_square_thumbnails']);
		set_option('related_content_short_date', $post['related_content_short_date']);

		$criteria = isset($post['related_content_criteria']) ? $post['related_content_criteria'] : array();
		$this->_criteria = $criteria;
		set_option('related_content_criteria', json_encode($criteria));
	}
	
	public function hookConfigForm()
	{
		$criteria = $this->_criteria;

		include 'config_form.php';
	}

	public function hookPublicHead($args)
	{
		queue_css_file('related_content');
	}
	
	public function hookPublicItemsShow($args)
	{
		$criteria = $this->_criteria;
		$item = $args['item'];
		$limit = (int)get_option('related_content_limit');
		$thumbnailType = ((bool)get_option('related_content_square_thumbnails') ? 'square_thumbnail' : 'thumbnail');
		$results = array();
		$constraints = array();
		
		// Dublin Core: Subject
		if ($weight = $criteria['Subject']['weight'] && $subjects = metadata($item, array('Dublin Core', 'Subject'), array('all' => true, 'no_filter' => true))) {
			// retrieve subject results
			$results_subjects = self::getResultsByElement(49, $subjects, $weight);

			// filter constraints array if needed
			if ((bool)$criteria['Subject']['constraint']) $constraints =  self::updateConstraints($constraints, array_keys($results_subjects, 1));

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_subjects);
		}
		
		// Tags
		if ($weight = $criteria['Tag']['weight'] && metadata($item, 'has tags')) {
			// retrieve tag results
			$tags = get_current_record('Item')->Tags;
			$results_tags = get_records('Item', array('tags'=>$tags));
			$results_tags = array_column($results_tags, 'id');
		
			// multiply by weight, according to importance of element
			$results_tags = self::countAndMultiply($results_tags, $weight);

			// filter constraints array if needed
			if ((bool)$criteria['Tag']['constraint']) $constraints =  self::updateConstraints($constraints, array_keys($results_tags, 1));

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_tags);
		}

		// Dublin Core: Date
		if ($weight = $criteria['Date']['weight'] && $date = metadata($item, array('Dublin Core', 'Date'), array('no_filter' => true))) {
			if ((bool)get_option('related_content_short_date')) {
				$date = substr($date, 0, 4);
			}
			
			// retrieve date results
			$results_date = self::getResultsByDateElement(40, $date, $weight);

			// filter constraints array if needed
			if ((bool)$criteria['Date']['constraint']) $constraints =  self::updateConstraints($constraints, array_keys($results_date, 1));

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_date);
		}

		// Dublin Core: Creator
		if ($weight = $criteria['Creator']['weight'] && $creators = metadata($item, array('Dublin Core', 'Creator'), array('all' => true, 'no_filter' => true))) {
			// retrieve creator results
			$results_creators = self::getResultsByElement(39, $creators, $weight);

			// filter constraints array if needed
			if ((bool)$criteria['Creator']['constraint']) $constraints =  self::updateConstraints($constraints, array_keys($results_creators, 1));

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_creators);
		}

		// Dublin Core: Contributor
		if ($weight = $criteria['Contributor']['weight'] && $contributors = metadata($item, array('Dublin Core', 'Contributor'), array('all' => true, 'no_filter' => true))) {
			// retrieve contributor results
			$results_contributors = self::getResultsByElement(37, $contributors, $weight);

			// filter constraints array if needed
			if ((bool)$criteria['Contributor']['constraint']) $constraints =  self::updateConstraints($constraints, array_keys($results_contributors, 1));

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_contributors);
		}

		// Dublin Core: Type
		if ($weight = $criteria['Type']['weight'] && $types = metadata($item, array('Dublin Core', 'Type'), array('all' => true, 'no_filter' => true))) {
			// retrieve type results
			$results_types = self::getResultsByElement(51, $types, $weight);

			// filter constraints array if needed
			if ((bool)$criteria['Type']['constraint']) $constraints =  self::updateConstraints($constraints, array_keys($results_types, 1));

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_types);
		}

		// Collection
		if ($weight = $criteria['Collection']['weight'] && $collection = get_collection_for_item($item)) {
			// retrieve collection results
			$results_collection = self::getResultsByCollection($collection, $weight);

			// filter constraints array if needed
			if ((bool)$criteria['Collection']['constraint']) $constraints =  self::updateConstraints($constraints, array_keys($results_collection, 1));

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_collection);
		}

		// Item Type
		if ($weight = $criteria['Item Type']['weight'] && $itemTypeID = $item->item_type_id) {
			// retrieve item type results
			$results_item_type = self::getResultsByItemType($itemTypeID, $weight);

			// filter constraints array if needed
			if ((bool)$criteria['Item Type']['constraint']) $constraints =  self::updateConstraints($constraints, array_keys($results_item_type, 1));

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_item_type);
		}
		
		// Dublin Core Extended elements
		if (plugin_is_active('DublinCoreExtended')) {
			// Spatial Coverage
			if ($weight = $criteria['Spatial Coverage']['weight'] && $spatialCoverage = metadata($item, array('Dublin Core', 'Spatial Coverage'), array('all' => true, 'no_filter' => true))) {
				// retrieve item type results
				$results_spatial_coverage = self::getResultsByElement(112, $spatialCoverage, $weight);

				// filter constraints array if needed
				if ((bool)$criteria['Spatial Coverage']['constraint']) $constraints =  self::updateConstraints($constraints, array_keys($results_spatial_coverage, 1));

				// adds values to $results
				$results = self::addAndMergeArrays($results, $results_spatial_coverage);
			}
			// Temporal Coverage
			// Note: other elements can be used, just add them similarly to this ones
			if ($weight = $criteria['Temporal Coverage']['weight'] && $temporalCoverage = metadata($item, array('Dublin Core', 'Temporal Coverage'), array('no_filter' => true))) {
				if ((bool)get_option('related_content_short_date')) {
					$temporalCoverage = substr($temporalCoverage, 0, 4);
				}

				// retrieve item type results
				$results_temporal_coverage = self::getResultsByElement(113, $temporalCoverage, $weight);

				// filter constraints array if needed
				if ((bool)$criteria['Temporal Coverage']['constraint']) $constraints =  self::updateConstraints($constraints, array_keys($results_temporal_coverage, 1));

				// adds values to $results
				$results = self::addAndMergeArrays($results, $results_temporal_coverage);
			}
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
			$i = 1;
			
			// order results by frequency
			arsort($results);

			// displays thumbnails
			echo "<div id='related_content'>\n";
			echo "<h3><strong>" . __('Related Items you might want to check out') . "...</strong></h3>";

			foreach ($results as $key => $value) {
				$item = get_record_by_id('Item', $key);
				
				echo link_to_item(
					item_image($thumbnailType, array('alt' => str_replace("&#039;", "'", metadata($item, array('Dublin Core','Title')))), 0, $item),
					array('class' => 'image'), 
					'show', 
					$item
				);
				
				$i++;
				if ($i > $limit) break;
			}
			
			echo "</div>";
		}
	}

	public function countAndMultiply($items, $multiplier=1) {
		// add count field to array
		$items_counted = array_count_values($items);

		// multiply value if needed
		if ($multiplier > 1) {
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
			->where("_advanced_0.text IN ('" . implode("','", $element_array) . "')")
			->where("public = 1")
			->order("rand()");
		$results = $db->fetchCol($select);
		
		// multiply by weight, according to importance of element
		return self::countAndMultiply($results, $element_weight);
	}
	
	public function getResultsByDateElement($element_id, $date, $element_weight=1) {
		$db = get_db();
		$joinCondition = '_advanced_0.record_id = items.id AND _advanced_0.record_type = \'Item\' AND _advanced_0.element_id = ';

		$select = $db
			->select()
			->from(array('items' => $db->Item), 'id')
			->joinLeft(array('_advanced_0' => $db->ElementText), $joinCondition . $element_id, array())
			->where("_advanced_0.text LIKE '" . $date . "%'")
			->where("public = 1")
			->order("rand()");
		$results = $db->fetchCol($select);
		
		// multiply by weight, according to importance of element
		return self::countAndMultiply($results, $element_weight);
	}
	
	public function getResultsByCollection($collection, $element_weight=1) {
		$db = get_db();

		$select = $db
			->select()
			->from(array('items' => $db->Item), 'id')
			->where("collection_id = " . $collection->id)
			->where("public = 1")
			->order("rand()");
		$results = $db->fetchCol($select);
		
		// multiply by weight, according to importance of element
		return self::countAndMultiply($results, $element_weight);
	}
	
	public function getResultsByItemType($itemTypeID, $element_weight=1) {
		$db = get_db();

		$select = $db
			->select()
			->from(array('items' => $db->Item), 'id')
			->where("item_type_id = " . $itemTypeID)
			->where("public = 1")
			->order("rand()");
		$results = $db->fetchCol($select);
		
		// multiply by weight, according to importance of element
		return self::countAndMultiply($results, $element_weight);
	}
}
