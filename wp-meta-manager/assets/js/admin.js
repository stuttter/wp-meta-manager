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

		$('.wp-meta-delete').on('click', function(e) {

			e.preventDefault();

			var meta = $(this);

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					meta_id: meta.data( 'meta-id' ),
					object_type: meta.data( 'object-type' ),
					nonce: meta.data( 'nonce' ),
					action: 'delete_meta',
				},
				dataType: "json",
				success: function( response ) {
					console.log( response );

				},
				beforeSend: function() {
				},
				complete: function() {
				}

			}).fail(function (response) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});

		});

	});
})(jQuery);