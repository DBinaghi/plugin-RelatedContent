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
			'Subject' => 2,
			'Tag' => 2,
			'Date' => 1.5,
			'Creator' => 1.2,
			'Contributor' => 1,
			'Type' => 0.5,
			'Collection' => 0.5
		);
		set_option('related_content_weights', json_encode($criteria));
	}

	public function hookUninstall()
	{
		delete_option('related_content_limit');
		delete_option('related_content_square_thumbnails');
		delete_option('related_content_short_date');
		delete_option('related_content_weights');
	 }

	public function hookInitialize()
	{
		add_translation_source(dirname(__FILE__) . '/languages');

		$criteria = json_decode(get_option('related_content_weights'), true);
		$this->_criteria = $criteria;
	}
	
	public function hookConfig($args)
	{
		$post = $args['post'];
		set_option('related_content_limit', $post['related_content_limit']);
		set_option('related_content_square_thumbnails', $post['related_content_square_thumbnails']);
		set_option('related_content_short_date', $post['related_content_short_date']);

		$criteria = isset($post['related_content-weights']) ? $post['related_content-weights'] : array();
		$this->_criteria = $criteria;
		set_option('related_content_weights', json_encode($criteria));
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
		$view = $args['view'];
		$item = $args['item'];
		$db = get_db();
		$limit = (int)get_option('related_content_limit');
		$thumbnailType = ((bool)get_option('related_content_square_thumbnails') ? 'square_thumbnail' : 'thumbnail');
		$results = array();
		
		if ($weight = $this->_criteria['Subject'] && $subjects = metadata($item, array('Dublin Core', 'Subject'), array('all' => true, 'no_filter' => true))) {
			// retrieve subject results
			$results_subjects = self::getResultsByElement(49, $subjects, $weight);

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_subjects);
		}
		
		if ($weight = $this->_criteria['Tag'] && metadata($item, 'has tags')) {
			// retrieve tag results
			$tags = get_current_record('Item')->Tags;
			$results_tags = get_records('Item', array('tags'=>$tags));
			$results_tags = array_column($results_tags, 'id');
		
			// multiply by weight, according to importance of element
			$results_tags = self::countAndMultiply($results_tags, $weight);

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_tags);
		}

		if ($weight = $this->_criteria['Date'] && $date = metadata($item, array('Dublin Core', 'Date'), array('no_filter' => true))) {
			if ((bool)get_option('related_content_short_date')) {
				$date = substr($date, 0, 4);
			}
			
			// retrieve date results
			$results_date = self::getResultsByDateElement(40, $date, $weight);

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_date);
		}

		if ($weight = $this->_criteria['Creator'] && $creators = metadata($item, array('Dublin Core', 'Creator'), array('all' => true, 'no_filter' => true))) {
			// retrieve creator results
			$results_creators = self::getResultsByElement(39, $creators, $weight);

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_creators);
		}

		if ($weight = $this->_criteria['Contributor'] && $contributors = metadata($item, array('Dublin Core', 'Contributor'), array('all' => true, 'no_filter' => true))) {
			// retrieve contributor results
			$results_contributors = self::getResultsByElement(37, $contributors, $weight);

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_contributors);
		}

		if ($weight = $this->_criteria['Type'] && $types = metadata($item, array('Dublin Core', 'Type'), array('all' => true, 'no_filter' => true))) {
			// retrieve type results
			$results_types = self::getResultsByElement(51, $types, $weight);

			// adds values to $results
			$results = self::addAndMergeArrays($results, $results_types);
		}

		// filter out actual item
		unset($results[$item->id]);
		
		if (count($results) > 0) {
			$i = 1;
			
			// order results by frequency
			arsort($results);

			// displays thumbnails
			echo "<div id='related_content'>\n";
			echo "<h3><strong>" . __('Related items you might want to check out') . "...</strong></h3>";

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
	
	public function getResultsByElement($element_id, $element_array, $element_weight=1) {
		$db = get_db();
		$joinCondition = '_advanced_0.record_id = items.id AND _advanced_0.record_type = \'Item\' AND _advanced_0.element_id = ';

		$select = $db
			->select()
			->from(array('items' => $db->Item), 'id')
			->joinLeft(array('_advanced_0' => $db->ElementText), $joinCondition . $element_id, array())
			->where("_advanced_0.text IN ('" . implode("','", $element_array) . "')");
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
			->where("_advanced_0.text LIKE '" . $date . "%'");
		$results = $db->fetchCol($select);
		
		// multiply by weight, according to importance of element
		return self::countAndMultiply($results, $element_weight);
	}
}
