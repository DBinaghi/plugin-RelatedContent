<?php	
	$view = get_view();
?>

<h2><?php echo __('General settings'); ?></h2>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_limit', __('Items Limit')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('The maximum amount of related Items to be suggested (default: 6).'); ?>
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

<h2><?php echo __('Weights'); ?></h2>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_weight_subject', __('Subject')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('The relative weight of similarity by Subject (if blank, will not be considered).'); ?>
		</p>
		<?php echo $view->formText('related_content_weight_subject', get_option('related_content_weight_subject')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_weight_tags', __('Tags')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('The relative weight of similarity by Tag (if blank, will not be considered).'); ?>
		</p>
		<?php echo $view->formText('related_content_weight_tags', get_option('related_content_weight_tags')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_weight_date', __('Date')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('The relative weight of similarity by Date (if blank, will not be considered).'); ?>
		</p>
		<?php echo $view->formText('related_content_weight_date', get_option('related_content_weight_date')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_weight_creator', __('Creator')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('The relative weight of similarity by Creator (if blank, will not be considered).'); ?>
		</p>
		<?php echo $view->formText('related_content_weight_creator', get_option('related_content_weight_creator')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_weight_contributor', __('Contributor')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('The relative weight of similarity by Contributor (if blank, will not be considered).'); ?>
		</p>
		<?php echo $view->formText('related_content_weight_contributor', get_option('related_content_weight_contributor')); ?>
	</div>
</div>

<div class="field">
	<div class="two columns alpha">
		<?php echo $view->formLabel('related_content_weight_type', __('Type')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php echo __('The relative weight of similarity by Type (if blank, will not be considered).'); ?>
		</p>
		<?php echo $view->formText('related_content_weight_type', get_option('related_content_weight_type')); ?>
	</div>
</div>