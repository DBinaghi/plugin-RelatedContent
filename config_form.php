<?php	
	$view = get_view();
?>
<style type = "text/css">
	.boxes, .boxes-left {
		vertical-align: middle;
	}
	.boxes {
		text-align: center;
	}
	.boxes input {
		margin-bottom: 0;
	}
</style>

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
			<?php echo __('If checked, just the year (first 4 digits) will be considered for Date similarity.'); ?>
		</p>
		<?php echo $view->formCheckbox('related_content_short_date', get_option('related_content_short_date'), null, array('1', '0')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_criteria', __('Criteria')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('The search criterias\'s relative weights (if blank, criteria will not be considered).'); ?>
		</p>
		<table id="related_content-weights">
			<thead>
				<tr>
					<th class="boxes-left"><?php echo __('Criterion'); ?></th>
					<th class="boxes"><?php echo __('Weight'); ?></th>
					<th class="boxes"><?php echo __('Constraint'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($criteria as $name => $values): ?>
				<tr>
					<td class="boxes-left">
						<?php echo __($name); ?>
					</td>
					<td class="boxes">
						<?php echo $view->formText("related_content_criteria[{$name}][weight]", $values['weight']); ?>
					</td>
					<td class="boxes">
						<?php echo $view->formCheckbox("related_content_criteria[{$name}][constraint]", $values['constraint'], null, array('1', '0')); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
