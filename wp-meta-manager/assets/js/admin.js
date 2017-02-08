(function($) {
	$(document).ready( function() {
		$('.wp-meta-form').submit(function(e) {

			e.preventDefault();

			var form = $(this);

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					data: form.serialize(),
					action: 'edit_meta',
				},
				dataType: "json",
				success: function( response ) {
					console.log( response );

				},
				beforeSend: function() {
					form.find( '.spinner' ).addClass( 'is-active' );
				},
				complete: function() {
					form.find( '.spinner' ).removeClass( 'is-active' );
				}

			}).fail(function (response) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});

		});

	});
})(jQuery);