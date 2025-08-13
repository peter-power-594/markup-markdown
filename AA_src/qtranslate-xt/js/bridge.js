/* global wp */
(function( $ ) {

	function qTranslateXBridge() {
		var _self = this;
		_self.options = {
			lang: 'none'
		};
		$(function() {
			_self.init();
		});
	}


	qTranslateXBridge.prototype.getActiveLanguage = function() {
		var _self = this;
		if ( typeof qtranxj_get_cookie === 'function' ) {
			// Old qTranslateX
			var cookieLang = qtranxj_get_cookie( 'qtrans_edit_language' );
			if ( cookieLang && cookieLang.length ) {
				_self.options.lang = cookieLang;
				return _self.options.lang;
			}
		}
		var sessionLang = sessionStorage.getItem( 'qtranslate-xt-admin-edit-language' );
		if ( sessionLang && sessionStorage.length ) {
			_self.options.lang = sessionLang;
		} else if ( _self.options.lang === 'none' ) {
			_self.options.lang = $( 'li.qtranxs-lang-switch.active:eq(0)' ).attr( 'lang' );
		}
		return _self.options.lang;
	};


	qTranslateXBridge.prototype.switchEditorLanguage = function( $parent ) {
		var _self = this;
		_self.getActiveLanguage();
		if ( ! wp || ! wp.pluginMarkupMarkdown || ! wp.pluginMarkupMarkdown.instances ) {
			return false;
		}
		var myInstances = wp.pluginMarkupMarkdown.instances;
		for ( var i = 0, myInstance, myFieldID, myLang = _self.options.lang; i < myInstances.length; i++ ) {
			myInstance = myInstances[ i ];
			myFieldID = myInstance.element ? myInstance.element.id || false : false;
			if ( ! myFieldID ) {
				continue;
			}
			if ( /^[a-z]+\[/.test( myFieldID ) ) {
				$myTranslateField = $( 'input[name="' + 'qtranslate-fields' + myFieldID.replace( /^([a-z]+)\[/, '[$1][' ) + '[' + myLang + ']' + '"]' );
			} else {
				$myTranslateField = $( 'input[name="' + 'qtranslate-fields[' + myFieldID + '][' + myLang + ']' + '"]' );
			}
			if ( ! $myTranslateField.length ) {
				continue;
			}
			if ( typeof myInstance.value === 'function' ) {
				myInstance.value( $myTranslateField.val() );
			}
		}
		return true;
	};


	qTranslateXBridge.prototype.init = function() {
		var _self = this;
		_self.getActiveLanguage();
		$( 'body' ).on( 'click', 'li.qtranxs-lang-switch', function() {
			setTimeout(function() { _self.switchEditorLanguage(); }, 50);
		});
	};


	new qTranslateXBridge();

})( window.jQuery );
