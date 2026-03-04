jQuery(document).ready(function($) {
	function updateWeightInputs() {
		$('#related_content-table tbody input[type="text"]').each(function() {
			if ($(this).val().trim() === '') {
				$(this).removeClass('active-weight');
			} else {
				$(this).addClass('active-weight');
			}
		});
	}

	updateWeightInputs();

	$('#related_content-table input[type="text"]').on('keyup change', updateWeightInputs);
});
