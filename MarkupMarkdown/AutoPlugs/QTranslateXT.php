<?php

namespace MarkupMarkdown\AutoPlugs;

defined( 'ABSPATH' ) || exit;


class QTranslateXT {


	/**
	 * @property String $plugin_uri
	 * The relative path to the plugin directory used for assets
	 * 
	 * @since 3.19.0
	 * @access private
	 */
	private $plugin_uri = '';


	public function __construct() {
		if ( mmd()->exists( WP_PLUGIN_DIR . '/qtranslate-xt/qtranslate.php' ) ) :
			if ( function_exists( 'qtranxf_init_language' ) ) :
				define( 'MMD_QTRANSLATEXT_PLUG', true );
			endif;
			$this->init();
		endif;
	}


	public function init() {
		if ( is_admin() ) :
			add_action( 'mmd_load_engine_scripts', array( $this, 'load_qtranslate_scripts' ) );
		endif;
	}


	public function load_qtranslate_scripts() {
		$my_bridge = mmd()->plugin_uri . 'assets/qtranslate-xt/js/bridge.';
		if ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'MMD_SCRIPT_DEBUG' ) && MMD_SCRIPT_DEBUG ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) :
			$my_bridge .= 'debug';
		else:
			$my_bridge .= 'min';
		endif;
		$my_bridge .= '.js';
		wp_enqueue_script( 'markup_markdown__qtranslate_bridge', $my_bridge, array( 'markup_markdown__wordpress_richedit' ), '1.0.0', true );
	}


}


new \MarkupMarkdown\AutoPlugs\QTranslateXT();
