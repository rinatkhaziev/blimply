jQuery(function($) {
	$('input[type="radio"]').click( function() {
		$('#blimply_push').removeAttr( 'disabled' );
	});
});