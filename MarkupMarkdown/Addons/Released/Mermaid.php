<?php

namespace MarkupMarkdown\Addons\Released;

defined( 'ABSPATH' ) || exit;


final class Mermaid {


	private $prop = array(
		'slug' => 'mermaid',
		'release' => 'stable',
		'active' => 0,
		'engine' => ''
	);


	private $plugin_uri = '';


	public function __construct() {
		if ( ! defined( 'MMD_ADDONS' ) || ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === false ) ) :
			$this->prop[ 'active' ] = 0;
			return false; # Addon has been desactivated
		endif;
		if ( is_admin() ) :
			add_filter( 'mmd_verified_config', array( $this, 'update_config' ) );
			add_filter( 'mmd_var2const', array( $this, 'create_const' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_layout_assets' ) );
		endif;
		if ( defined( 'MMD_USE_MERMAID' ) && isset( MMD_USE_MERMAID[ 0 ] ) && (int)MMD_USE_MERMAID[ 0 ] === 1 ) :
			if ( isset( MMD_USE_MERMAID[ 1 ] ) ) :
				$this->prop[ 'engine' ] = MMD_USE_MERMAID[ 1 ];
				$this->plugin_uri = mmd()->plugin_uri;
				if ( is_admin() ) :
					add_action( 'mmd_load_engine_scripts', array( $this, 'load_admin_mermaid_scripts' ) );
				elseif ( isset( MMD_USE_MERMAID[ 2 ] ) && (int)MMD_USE_MERMAID[ 2 ] > 0 ) :
					add_action( 'wp_footer', array( $this, 'load_front_mermaid_scripts' ) );
				endif;
			endif;
		endif;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) :
			return $this->prop[ $name ];
		elseif ( $name === 'label' ) :
			return esc_html__( 'Mermaid', 'markup-markdown' );
		elseif ( $name === 'desc' ) :
			return esc_html__( 'Easily display diagrams and charts inside your post.', 'markup-markdown' );
		endif;
		return 'mmd_undefined';
	}


	/**
	 * Filter to parse Latex options from the options screen when the form was submitted
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function update_config( $my_cnf ) {
		$my_cnf[ 'mermaid_engine' ] = filter_input( INPUT_POST, 'mmd_usemermaid', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$my_cnf[ 'mermaid_active' ] = isset( $my_cnf[ 'mermaid_engine' ] ) && in_array( $my_cnf[ 'mermaid_engine' ], [ 'mermaid' ] ) ? 1 : 0;
		$my_cnf[ 'mermaid_front' ] = filter_input( INPUT_POST, 'mermaid_front', FILTER_VALIDATE_INT );
		return $my_cnf;
	}
	public function create_const( $my_cnf ) {
		$my_cnf[ 'MMD_USE_MERMAID' ] = [
			isset( $my_cnf[ 'mermaid_active' ] ) && (int)$my_cnf[ 'mermaid_active' ] > 0  ? 1 : 0
		];
		unset( $my_cnf[ 'latex_active' ] );
		if ( $my_cnf[ 'MMD_USE_MERMAID' ][ 0 ] > 0 ) :
			$my_cnf[ 'MMD_USE_MERMAID' ][ 1 ] = isset( $my_cnf[ 'mermaid_engine' ] ) ? htmlspecialchars( $my_cnf[ 'mermaid_engine' ] ) : '';
			$my_cnf[ 'MMD_USE_MERMAID' ][ 2 ] = isset( $my_cnf[ 'mermaid_front' ] ) ? (int)$my_cnf[ 'mermaid_front' ] : '';
		endif;
		unset( $my_cnf[ 'mermaid_active' ] );
		unset( $my_cnf[ 'mermaid_engine' ] );
		unset( $my_cnf[ 'mermaid_front' ] );
		return $my_cnf;
	}


	public function load_layout_assets( $hook ) {
		if ( 'settings_page_markup-markdown-admin' === $hook ) :
			add_action( 'mmd_tabmenu_options', array( $this, 'add_tabmenu' ) );
			add_action( 'mmd_tabcontent_options', array( $this, 'add_tabcontent' ) );
		endif;
	}


	/**
	 * Add the layout menu item inside the options screen
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabmenu() {
		echo "\t\t\t\t\t\t<li><a href=\"#tab-mermaid\" class=\"mmd-ico ico-chart\">" . esc_html__( 'Mermaid', 'markup-markdown' ) . "</a></li>\n";
	}


	/**
	 * Display layout options inside the options screen
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabcontent() {
		$conf_file = mmd()->conf_blog_prefix . 'conf.php';
		if ( mmd()->exists( $conf_file ) ) :
			require_once $conf_file;
		endif;
		$my_tmpl = mmd()->plugin_dir . '/MarkupMarkdown/Addons/Released/Templates/MermaidForm.php';
		if ( mmd()->exists( $my_tmpl ) ) :
			mmd()->clear_cache( $my_tmpl );
			include $my_tmpl;
		endif;
	}


	/**
	 * Method to load the scripts related to the selected LaTeX Engine on the edit screen
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function load_admin_mermaid_scripts() {
		if ( ! isset( $this->prop[ 'engine' ] ) || empty( $this->prop[ 'engine' ] ) || $this->prop[ 'engine' ] === 'none' ) :
			# Do nothing
		elseif ( $this->prop[ 'engine' ] === 'mermaid' ) :
			wp_enqueue_script( 'markup_markdown__mermaid',$this->plugin_uri . 'assets/mermaid/dist/mermaid.min.js', array( 'markup_markdown__wordpress_richedit' ), '11.6.0', true );
		endif;
	}


	/**
	 * Method to load the scripts related to the selected LaTeX Engine on the frontend screen
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function load_front_mermaid_scripts() {
		if ( ! isset( $this->prop[ 'engine' ] ) || empty( $this->prop[ 'engine' ] ) || $this->prop[ 'engine' ] === 'none' ) :
			# Do nothing
		elseif ( $this->prop[ 'engine' ] === 'mermaid' ) :
			wp_enqueue_script( 'markup_markdown__mermaid_render', $this->plugin_uri . 'assets/mermaid/dist/mermaid.min.js', array(), '11.6.0', true );
			wp_add_inline_script( 'markup_markdown__mermaid_render', $this->add_inline_mermaid_conf() );
		endif;
	}


	/**
	 * Katex specific inline config for the frontend
	 *
	 * @since 3.8.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_inline_mermaid_conf() {
		$js = 'mermaid.initialize({ startOnLoad: true });';
		return $js;
	}


}
