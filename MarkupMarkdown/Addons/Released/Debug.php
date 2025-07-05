<?php

namespace MarkupMarkdown\Addons\Released;

defined( 'ABSPATH' ) || exit;

final class Debug {


	private $prop = array(
		'slug' => 'tools',
		'release' => 'stable',
		'active' => 1
	);


	public function __construct() {
		if ( is_admin() ) :
			add_action( 'admin_enqueue_scripts', array( $this, 'load_layout_assets' ) );
		endif;
	}


	public function load_layout_assets( $hook ) {
		if ( 'settings_page_markup-markdown-admin' === $hook ) :
			add_action( 'mmd_tabmenu_options', array( $this, 'add_tabmenu' ), 9999 );
			add_action( 'mmd_tabcontent_options', array( $this, 'add_tabcontent' ), 9999 );
		endif;
	}


	/**
	 * Add the debug menu item inside the options screen
	 *
	 * @since 3.16.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabmenu() {
		echo "\t\t\t\t\t\t<li><a href=\"#tab-debug\" class=\"mmd-ico ico-file\">" . __( 'Debug', 'markup-markdown' ) . "</a></li>\n";
	}


	/**
	 * Display debug options inside the options screen
	 *
	 * @since 3.16.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabcontent() {
		printf( '<div id="tab-debug">' );
		$my_tmpl = mmd()->plugin_dir . '/MarkupMarkdown/Addons/Released/Templates/Status.php';
		if ( file_exists( $my_tmpl ) ) :
			mmd()->clear_cache( $my_tmpl );
			include $my_tmpl;
		endif;
		printf( '</div>' );
	}


}
