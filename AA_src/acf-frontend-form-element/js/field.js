/* global jQuery */
(function( _win, $ ) {

	function mmd_initialize_field( $field ) {
		if ( ! $field || ! $field.length || ! _win.MarkupMarkdown || typeof _win.MarkupMarkdown !== "function" ) {
			return false;
		}
		var $textarea = $field.find( 'textarea:eq(0)' );
		// The _CodeMirrorSpellCheckerReady_ event can be triggered multiple times
		document.addEventListener( 'CodeMirrorSpellCheckerReady', function() {
			var $acfInputField = $textarea.closest( '.acf-input' );
			if ( $acfInputField.length && ! $acfInputField.hasClass( 'ready' ) ) {
				new MarkupMarkdown( $textarea );
				$acfInputField.addClass( 'ready' );
			}
		});
	}

	$(function() {
		var $myForms = $( 'form.frontend-form' );
		$myForms.each(function() {
			mmd_initialize_field( $( this ).find( 'div[data-name="fea_post_content"]' ) );
		});
		$( document ).on( 'renderModalContent', function() {
			mmd_initialize_field( $( '.fea-modal:visible' ).find( 'div[data-name="fea_post_content"]' ) );
			document.dispatchEvent( new CustomEvent( 'CodeMirrorSpellCheckerReady' ) );
		});
	});


})( window, window.jQuery );
