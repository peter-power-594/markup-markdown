<?php

namespace MarkupMarkdown\Addons\Released;

defined( 'ABSPATH' ) || exit;


final class CodeHighlighter {


	private $prop = array(
		'slug' => 'codehighlighter',
		'release' => 'stable',
		'active' => 1,
		'engine' => 'prism',
		'theme' => 'vs'
	);


	private $plugin_uri = '';


	private $themes = array(
		'a11y-dark',
		'atom-dark',
		'base16-ateliersulphurpool.light',
		'cb',
		'coldark-cold',
		'coldark-dark',
		'coy',
		'coy-without-shadows',
		'darcula',
		'dark',
		'dracula',
		'duotone-dark',
		'duotone-earth',
		'duotone-forest',
		'duotone-light',
		'duotone-sea',
		'duotone-space',
		'funky',
		'ghcolors',
		'gruvbox-dark',
		'gruvbox-light',
		'holi-theme',
		'hopscotch',
		'lucario',
		'material-dark',
		'material-light',
		'material-oceanic',
		'night-owl',
		'okaidia',
		'one-dark',
		'one-light',
		'pojoaque',
		'shades-of-purple',
		'solarized-dark-atom',
		'solarizedlight',
		'synthwave84',
		'tomorrow',
		'twilight',
		'vs',
		'vsc-dark-plus',
		'xonokai',
		'z-touch'
	);


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
		if ( defined( 'MMD_USE_CODEHIGHLIGHT' ) && isset( MMD_USE_CODEHIGHLIGHT[ 0 ] ) && (int)MMD_USE_CODEHIGHLIGHT[ 0 ] === 1 ) :
			if ( isset( MMD_USE_CODEHIGHLIGHT[ 1 ] ) ) :
				$this->prop[ 'engine' ] = MMD_USE_CODEHIGHLIGHT[ 1 ];
				$this->prop[ 'theme' ] = ( isset( MMD_USE_CODEHIGHLIGHT[ 3 ] ) && in_array( MMD_USE_CODEHIGHLIGHT[ 3 ], $this->themes ) !== false ) ? MMD_USE_CODEHIGHLIGHT[ 3 ] : 'vs';
				$this->plugin_uri = mmd()->plugin_uri;
				if ( isset( MMD_USE_CODEHIGHLIGHT[ 2 ] ) && (int)MMD_USE_CODEHIGHLIGHT[ 2 ] > 0 ) :
					add_action( 'wp_head', array( $this, 'load_front_highlighter_stylesheets' ) );
					add_action( 'wp_footer', array( $this, 'load_front_highlighter_scripts' ) );
				endif;
			endif;
		endif;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) :
			return $this->prop[ $name ];
		elseif ( $name === 'label' ) :
			return esc_html__( 'Code Highlighter', 'markup-markdown' );
		elseif ( $name === 'desc' ) :
			return esc_html__( 'Colorful syntax highlighting for your snippets code.', 'markup-markdown' );
		endif;
		return 'mmd_undefined';
	}


	/**
	 * Filter to parse code highlighter options from the options screen when the form was submitted
	 *
	 * @since 3.19.0
	 * @access public
	 *
	 * @return Void
	 */
	public function update_config( $my_cnf ) {
		$my_cnf[ 'code_highlighter_engine' ] = filter_input( INPUT_POST, 'mmd_usecodehighlighter', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$my_cnf[ 'code_highlighter_active' ] = isset( $my_cnf[ 'code_highlighter_engine' ] ) && in_array( $my_cnf[ 'code_highlighter_engine' ], [ 'prism' ] ) !== false ? 1 : 0;
		$my_cnf[ 'code_highlighter_front' ] = filter_input( INPUT_POST, 'mmd_codehighlighter_front', FILTER_VALIDATE_INT );
		$my_cnf[ 'code_highlighter_theme' ] = filter_input( INPUT_POST, 'mmd_codehighlighter_theme', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		return $my_cnf;
	}
	public function create_const( $my_cnf ) {
		$my_cnf[ 'MMD_USE_CODEHIGHLIGHT' ] = [
			isset( $my_cnf[ 'code_highlighter_active' ] ) && (int)$my_cnf[ 'code_highlighter_active' ] > 0  ? 1 : 0
		];
		unset( $my_cnf[ 'code_highlighter_active' ] );
		if ( $my_cnf[ 'MMD_USE_CODEHIGHLIGHT' ][ 0 ] > 0 ) :
			$my_cnf[ 'MMD_USE_CODEHIGHLIGHT' ][ 1 ] = isset( $my_cnf[ 'code_highlighter_engine' ] ) ? htmlspecialchars( $my_cnf[ 'code_highlighter_engine' ] ) : '';
			$my_cnf[ 'MMD_USE_CODEHIGHLIGHT' ][ 2 ] = 1;
			$my_cnf[ 'MMD_USE_CODEHIGHLIGHT' ][ 3 ] = isset( $my_cnf[ 'code_highlighter_theme' ] ) ? htmlspecialchars( $my_cnf[ 'code_highlighter_theme' ] ) : '';
		endif;
		unset( $my_cnf[ 'code_highlighter_engine' ] );
		unset( $my_cnf[ 'code_highlighter_front' ] );
		unset( $my_cnf[ 'code_highlighter_theme' ] );
		return $my_cnf;
	}


	public function load_layout_assets( $hook ) {
		if ( 'settings_page_markup-markdown-admin' === $hook ) :
			add_action( 'mmd_tabmenu_options', array( $this, 'add_tabmenu' ) );
			add_action( 'mmd_tabcontent_options', array( $this, 'add_tabcontent' ) );
		endif;
	}


	/**
	 * Add the code highlighter menu item inside the options screen
	 *
	 * @since 3.19.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabmenu() {
		echo "\t\t\t\t\t\t<li><a href=\"#tab-codehighlight\" class=\"mmd-ico ico-highlight\">" . esc_html__( 'Syntax Highlighting', 'markup-markdown' ) . "</a></li>\n";
	}


	/**
	 * Display syntax highlighting options inside the options screen
	 *
	 * @since 3.19.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_tabcontent() {
		$conf_file = mmd()->conf_blog_prefix . 'conf.php';
		if ( mmd()->exists( $conf_file ) ) :
			require_once $conf_file;
		endif;
		$my_tmpl = mmd()->plugin_dir . '/MarkupMarkdown/Addons/Released/Templates/CodehighlighterForm.php';
		if ( mmd()->exists( $my_tmpl ) ) :
			mmd()->clear_cache( $my_tmpl );
			$my_themes = array(
				'a11y-dark' => __( 'A11Y Dark', 'markup-markdown' ),
				'atom-dark' => __( 'Atom Dark', 'markup-markdown' ),
				'base16-ateliersulphurpool.light' => __( 'Base 16 AtelierSulphurpool Light', 'markup-markdown' ),
				'cb' => __( 'CB', 'markup-markdown' ),
				'coldark-cold' => __( 'Coldark Cold', 'markup-markdown' ),
				'coldark-dark' => __( 'Coldark Dark', 'markup-markdown' ),
				'coy' => __( 'Coy', 'markup-markdown' ),
				'coy-without-shadows' => __( 'Coy without shadows', 'markup-markdown' ),
				'darcula' => __( 'Darcula', 'markup-markdown' ),
				'dark' => __( 'Dark', 'markup-markdown' ),
				'dracula' => __( 'Dracula', 'markup-markdown' ),
				'duotone-dark' => __( 'Duotone Dark', 'markup-markdown' ),
				'duotone-earth' => __( 'Duotone Earth', 'markup-markdown' ),
				'duotone-forest' => __( 'Duotone Forest', 'markup-markdown' ),
				'duotone-light' => __( 'Duotone Light', 'markup-markdown' ),
				'duotone-sea' => __( 'Duotone Sea', 'markup-markdown' ),
				'duotone-space' => __( 'Duotone Space', 'markup-markdown' ),
				'funky' => __( 'Duotone Funky', 'markup-markdown' ),
				'ghcolors' => __( 'GhColors', 'markup-markdown' ),
				'gruvbox-dark' => __( 'Gruvbox Dark', 'markup-markdown' ),
				'gruvbox-light' => __( 'Gruvbox Light', 'markup-markdown' ),
				'holi-theme' => __( 'Holi Theme', 'markup-markdown' ),
				'hopscotch' => __( 'Hopscotch', 'markup-markdown' ),
				'lucario' => __( 'Lucario', 'markup-markdown' ),
				'material-dark' => __( 'Material Dark', 'markup-markdown' ),
				'material-light' => __( 'Material Light', 'markup-markdown' ),
				'material-oceanic' => __( 'Material Oceanic', 'markup-markdown' ),
				'night-owl' => __( 'Night Owl', 'markup-markdown' ),
				'okaidia' => __( 'Okaidia', 'markup-markdown' ),
				'one-dark' => __( 'One Dark', 'markup-markdown' ),
				'one-light' => __( 'One Light', 'markup-markdown' ),
				'pojoaque' => __( 'Pojoaque', 'markup-markdown' ),
				'shades-of-purple' => __( 'Shades of purple', 'markup-markdown' ),
				'solarized-dark-atom' => __( 'Solarized Dark Atom', 'markup-markdown' ),
				'solarizedlight' => __( 'Solarized Light', 'markup-markdown' ),
				'synthwave84' => __( 'Synthwave84', 'markup-markdown' ),
				'tomorrow' => __( 'Tomorrow', 'markup-markdown' ),
				'twilight' => __( 'Twilight', 'markup-markdown' ),
				'vs' => __( 'Visual Studio', 'markup-markdown' ),
				'vsc-dark-plus' => __( 'Visual Studio Code Dark+', 'markup-markdown' ),
				'xonokai' => __( 'Xonokai', 'markup-markdown' ),
				'z-touch' => __( 'Z-touch', 'markup-markdown' )
			);
			$my_theme = $this->prop[ 'theme' ];
			include $my_tmpl;
		endif;
	}


	/**
	 * Method to load the stylesheets related to the selected Code Highlighter Engine
	 *
	 * @since 3.19.0
	 * @access public
	 *
	 * @return Void
	 */
	public function load_front_highlighter_stylesheets() {
		if ( ! isset( $this->prop[ 'engine' ] ) || empty( $this->prop[ 'engine' ] ) || $this->prop[ 'engine' ] === 'none' ) :
			# Do nothing
		elseif ( $this->prop[ 'engine' ] === 'prism' ) :
			wp_enqueue_style( 'markup_markdown__prism_theme', mmd()->plugin_uri . 'assets/prism/v1/themes/prism-' . $this->prop[ 'theme' ] . '.min.css', [], '1.30.1001' );
		endif;
	}


	/**
	 * Method to load the scripts related to the selected Code Highlighter Engine on the frontend screen
	 *
	 * @since 3.19.0
	 * @access public
	 *
	 * @return Void
	 */
	public function load_front_highlighter_scripts() {
		if ( ! isset( $this->prop[ 'engine' ] ) || empty( $this->prop[ 'engine' ] ) || $this->prop[ 'engine' ] === 'none' ) :
			# Do nothing
		elseif ( $this->prop[ 'engine' ] === 'prism' ) :
			$plugin_uri = mmd()->plugin_uri;
			wp_enqueue_script( 'markup_markdown__prism_core', $plugin_uri . 'assets/prism/v1/components/prism-core.js', [], '1.30.0', true );
			wp_enqueue_script( 'markup_markdown__prism_autoloader', $plugin_uri . 'assets/prism/v1/plugins/autoloader/prism-autoloader.js', [ 'markup_markdown__prism_core' ], '1.30.0', true );
			wp_add_inline_script( 'markup_markdown__prism_autoloader', $this->add_inline_prism_conf() );
		endif;
	}


	/**
	 * Prism specific inline config for the frontend
	 *
	 * @since 3.19.0
	 * @access public
	 *
	 * @return Void
	 */
	public function add_inline_prism_conf() {
		return 'Prism.plugins.autoloader.languages_path = "' . mmd()->plugin_uri . '/assets/prism/v1/components/";';
	}


}
