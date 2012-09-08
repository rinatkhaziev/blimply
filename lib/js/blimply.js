jQuery(function($) {
	jQuery.fn.reset = function () {
	  $(this).each (function() { this.reset(); });
	}

	$('input[type="radio"]').click( function() {
	$('#blimply_push, #blimply_push_send').removeAttr( 'disabled' );
	});
	
	$('#blimply-dashboard-widget').submit( function(e) {
		e.preventDefault();
		var $this = $(this);
		$.post( $this.attr('action'), $this.serialize(), function (data) {
			if ( 'ok' == data ) {
				$this.reset();
				var updated = $this.children( '.updated' );
				var val = $this.children( 'textarea' ).val();
				if ( updated.length == 0 )
					$this.prepend( '<div class="updated"><p>' + Blimply.push_sent + ': ' + val + '</p></div>' )
				else
					updated.hide( 'slow' );
					updated.replaceWith( '<div class="updated"><p>' + Blimply.push_sent + ': ' + val + '</p></div>' );
					updated.show( 'slow' );
			} else {
				$this.prepend( '<div class="error"><p>' + Blimply.push_error + '</p></div>' );
			}
		}	
			
			);
	})
});