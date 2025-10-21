(function( $ ) {

	function MarkupMarkdownApp() {
		var _self = this;
		$( document ).ready(function() {
			_self.tabOptions();
			_self.toolbarButtons();
			_self.toggleButtons();
			_self.blogDebugInfo();
			_self.blogCodeHighlightThemes();
		});
	}


	MarkupMarkdownApp.prototype.toggleButtons = function() {
		$( '#wrap a.toggler' ).on( 'click', function( event ) {
			event.preventDefault();
			$( '#' + this.href.replace( /.*?#/, '' ) ).trigger( 'click' );
		});
	};


	MarkupMarkdownApp.prototype.tabOptions = function() {
		var hash = window.location.hash,
			$tabs = $( '#tabs' );
		if ( ! $tabs.length ) {
			return false;
		}
		$( "#tabs" ).tabs({
			active: $( hash && hash.length ? hash : '#tab-layout' ).prevAll().length - 1,
			activate: function( event, ui ) {
				if ( $( window ).width() < 601 ) {
					$( 'html, body' ).animate({
						scrollTop: $( ui.newPanel ).offset().top - 5
					}, 250 );
				}
			}
		});
		return true;
	};


	MarkupMarkdownApp.prototype.toolbarButtons = function() {
		var $toolbar_buttons = $( '#toolbar_buttons' ),
			$my_buttons = $( '#my_buttons' );
		if ( ! $toolbar_buttons.length || ! $my_buttons.length ) {
			return false;
		}
		var updateSetting = function() {
			var mmdToolbar = $.map( $( '#my_buttons' ).find( 'li' ), function( item ) {
				return ( item.getAttribute( 'data-slug' ) || '' ).replace( /[^a-z0-9_-]/, '' );
			} ).join( ',' );
			$( 'input[name="mmd_toolbar"]' ).val( mmdToolbar );
		};
		$( '#toolbar_buttons, #my_buttons' ).sortable({
			items:       "li:not(.ui-state-disabled)",
			connectWith: ".connected",
			cancel:      "a.ui-icon",
			revert:      "invalid",
			cursor:      "move",
			update:      updateSetting
		}).disableSelection();
		$my_buttons.on( 'click', function( event ) {
			var $target = $( event.target );
			if ( $target.closest( 'a.ui-trash-link' ).length ) {
				event.preventDefault();
				event.stopPropagation();
				$target.closest( 'li' ).appendTo( $toolbar_buttons );
			}
			updateSetting();
		});
		return true;
	};


	MarkupMarkdownApp.prototype.blogDebugInfo = function() {
		var myInfo = {
			settings: [],
			plugins: []
		};
		$( '#mmd_debug_settings table tbody tr' ).each(function() {
			myInfo.settings.push({
				name: $( this ).find( 'td:first-child' ).text(),
				val: $( this ).find( 'td:last-child' ).text()
			});
		});
		$( '#mmd_debug_plugins table tbody tr' ).each(function() {
			var $firstCell = $( this ).find( 'td:eq(0)' );
			myInfo.plugins.push({
				name: $firstCell.children( 'a' ).length ? $firstCell.find( 'a' ).text() : $firstCell.text(),
				url: $firstCell.children( 'a' ).length ? $firstCell.find( 'a' ).attr( 'href' ) : '',
				ver: $( this ).find( 'td:eq(1)' ).text(),
				desc: $( this ).find( 'td:eq(2)' ).text()
			});
		});
		$( '#mmd_debug_info' ).html( JSON.stringify( myInfo, null, 4 ) );
	};


	MarkupMarkdownApp.prototype.blogCodeHighlightThemes = function() {
		$( 'tr.site-use-codehighlighter' ).on( 'click', function() {
			setTimeout(function() {
				var engineSlug = $( 'input[name="mmd_usecodehighlighter"]:checked' ).val(),
					$themePullDown = $( '#code_highlighter_theme' );
				$themePullDown.find( 'optgroup[class!="' + engineSlug + '"]' ).css( 'display', 'none' ).find( 'option:selected' ).removeAttr( 'selected' );
				$themePullDown.find( 'optgroup[class="' + engineSlug + '"]' ).removeAttr( 'style' );
			}, 150);
		}).trigger( 'click' );
	};


	new MarkupMarkdownApp();


})( window.jQuery );
