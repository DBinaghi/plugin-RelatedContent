<?php
	$view = get_view();
	$dateFields = array('Date', 'Date Available', 'Date Created', 'Date Accepted', 'Date Copyrighted', 'Date Submitted', 'Date Issued', 'Date Modified', 'Date Valid');
	$docsUrl = 'https://github.com/DBinaghi/plugin-RelatedContent';
?>
<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_limit', __('Items Limit')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('The maximum amount of related Items to be suggested.'); ?>
		</p>
		<?php echo $view->formText('related_content_limit', get_option('related_content_limit')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_square_thumbnails', __('Use Square Thumbnails')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('If checked, cropped square thumbnails are displayed.'); ?>
		</p>
		<?php echo $view->formCheckbox('related_content_square_thumbnails', get_option('related_content_square_thumbnails'), null, array('1', '0')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_short_date', __('Use Short Date')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('If checked, just the year (first 4 digits) will be considered for date similarity.'); ?>
		</p>
		<?php echo $view->formCheckbox('related_content_short_date', get_option('related_content_short_date'), null, array('1', '0')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_show_title', __('Show Item Title')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('If checked, a truncated title is displayed below each thumbnail.'); ?>
		</p>
		<?php echo $view->formCheckbox('related_content_show_title', get_option('related_content_show_title'), null, array('1', '0')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_exclude_no_image', __('Exclude Items Without Image')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('If checked, Items with no associated files will not be suggested.'); ?>
		</p>
		<?php echo $view->formCheckbox('related_content_exclude_no_image', get_option('related_content_exclude_no_image'), null, array('1', '0')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_criteria', __('Criteria')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo sprintf(__('The search criteria\'s relative weights (if blank, criteria will not be considered) and constraint rule. See the <a href="%s" target="_blank">plugin\'s documentation</a> for detailed instructions.'), $docsUrl); ?>
		</p>
		<table id="related_content-table">
			<thead>
				<tr>
					<th class="boxes-nowrap"><?php echo __('Criterion'); ?></th>
					<th class="boxes" title="<?php echo __('A positive number. Higher values increase the importance of this criterion. Leave blank to ignore it.'); ?>"><?php echo __('Weight'); ?></th>
					<th class="boxes" title="<?php echo __('If checked, only items matching this criterion will be suggested, regardless of other criteria.'); ?>"><?php echo __('Constraint'); ?></th>
					<th class="boxes-nowrap" title="<?php echo __('If checked, only the first 4 characters (the year) will be used for matching. Requires \"Use Short Date\" to be enabled above.'); ?>"><?php echo __('Is Date'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$current_element_set = null;
					foreach ($elements as $element):
						if ($element->set_name != $current_element_set):
							$current_element_set = $element->set_name;
				?>
				<tr>
					<th colspan="4">
						<strong><?php echo __($current_element_set); ?></strong>
					</th>
				</tr>
				<?php 	endif; ?>
				<tr>
					<td class="boxes-left"><?php echo __($element->name); ?></td>
					<td class="boxes">
						<?php echo $view->formText(
							"criteria[elements][{$element->id}][weight]",
							isset($criteria['elements'][$element->id]['weight']) ? $criteria['elements'][$element->id]['weight'] : ''
						); ?>
					</td>
					<td class="boxes">
						<?php echo $view->formCheckbox(
							"criteria[elements][{$element->id}][constraint]",
							'1', 
							array(
								'disableHidden' => true,
								'checked' => isset($criteria['elements'][$element->id]['constraint'])
							)
						); ?>
					</td>
					<td class="boxes">
						<?php echo $view->formCheckbox(
							"criteria[elements][{$element->id}][isDate]",
							'1', 
							array(
								'disableHidden' => true,
								'checked' => (($element->set_name == "Dublin Core" && in_array($element->name, $dateFields)) || isset($criteria['elements'][$element->id]['isDate'])),
								'disable' => ($element->set_name == "Dublin Core" && in_array($element->name, $dateFields))
							)
						); ?>
					</td>
				</tr>
			<?php endforeach; ?>
				<tr>
					<th colspan="4">
						<strong><?php echo __('Other criteria'); ?></strong>
					</th>
				</tr>
				<tr>
					<td class="boxes-left"><?php echo __('Item Type'); ?></td>
					<td class="boxes">
						<?php echo $view->formText(
							"criteria[item type][weight]",
							isset($criteria['item type']['weight']) ? htmlspecialchars($criteria['item type']['weight'], ENT_QUOTES, 'UTF-8') : ''
						); ?>
					</td>
					<td class="boxes">
						<?php echo $view->formCheckbox(
							"criteria[item type][constraint]",
							'1', 
							array(
								'disableHidden' => true,
								'checked' => isset($criteria['item type']['constraint'])
							)
						); ?>
					</td>
					<td class="boxes">
						<?php echo $view->formCheckbox(
							"criteria[item type][isDate]",
							'1', 
							array(
								'disableHidden' => true,
								'checked' => (isset($criteria['item type']['isDate'])),
								'disable' => true
							)
						); ?>
					</td>
				</tr>
				<tr>
					<td class="boxes-left"><?php echo __('Collection'); ?></td>
					<td class="boxes">
						<?php echo $view->formText(
							"criteria[collection][weight]",
							isset($criteria['collection']['weight']) ? htmlspecialchars($criteria['collection']['weight'], ENT_QUOTES, 'UTF-8') : ''
						); ?>
					</td>
					<td class="boxes">
						<?php echo $view->formCheckbox(
							"criteria[collection][constraint]",
							'1', 
							array(
								'disableHidden' => true,
								'checked' => isset($criteria['collection']['constraint'])
							)
						); ?>
					</td>
					<td class="boxes">
						<?php echo $view->formCheckbox(
							"criteria[collection][isDate]",
							'1', 
							array(
								'disableHidden' => true,
								'checked' => (isset($criteria['collection']['isDate'])),
								'disable' => true
							)
						); ?>
					</td>
				</tr>
				<tr>
					<td class="boxes-left"><?php echo __('Tags'); ?></td>
					<td class="boxes">
						<?php echo $view->formText(
							"criteria[tags][weight]",
							isset($criteria['tags']['weight']) ? htmlspecialchars($criteria['tags']['weight'], ENT_QUOTES, 'UTF-8') : ''
						); ?>
					</td>
					<td class="boxes">
						<?php echo $view->formCheckbox(
							"criteria[tags][constraint]",
							'1', 
							array(
								'disableHidden' => true,
								'checked' => isset($criteria['tags']['constraint'])
							)
						); ?>
					</td>
					<td class="boxes">
						<?php echo $view->formCheckbox(
							"criteria[tags][isDate]",
							'1', 
							array(
								'disableHidden' => true,
								'checked' => (isset($criteria['tags']['isDate'])),
								'disable' => true
							)
						); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
