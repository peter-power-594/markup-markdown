<?php

namespace MarkupMarkdown\Addons\Released;

defined( 'ABSPATH' ) || exit;

if ( defined( 'MMD_ADDONS_LOADED' ) ) :
	return false;
endif;


final class CodeHighlighter {


	private $prop = array(
		'slug' => 'codehighlighter',
		'release' => 'stable',
		'active' => 1,
		'engine' => 'prism',
		'theme' => 'vs'
	);


	private $plugin_uri = '';


	private $themes = array();


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
				$this->prop[ 'theme' ] = isset( MMD_USE_CODEHIGHLIGHT[ 3 ] ) ? MMD_USE_CODEHIGHLIGHT[ 3 ] : 'vs';
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
		$my_cnf[ 'code_highlighter_active' ] = isset( $my_cnf[ 'code_highlighter_engine' ] ) && in_array( $my_cnf[ 'code_highlighter_engine' ], [ 'prism', 'highlight' ] ) !== false ? 1 : 0;
		$my_cnf[ 'code_highlighter_front' ] = filter_input( INPUT_POST, 'mmd_codehighlighter_front', FILTER_VALIDATE_INT );
		$my_cnf[ 'code_highlighter_theme' ] = filter_input( INPUT_POST, 'mmd_codehighlighter_theme', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		return $my_cnf;
	}
	final public function create_const( $my_cnf ) {
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


	final public function load_layout_assets( $hook ) {
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
	final public function add_tabmenu() {
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
	final public function add_tabcontent() {
		$conf_file = mmd()->conf_blog_prefix . 'conf.php';
		if ( mmd()->exists( $conf_file ) ) :
			require_once $conf_file;
		endif;
		$my_tmpl = mmd()->plugin_dir . '/MarkupMarkdown/Addons/Released/Templates/CodehighlighterForm.php';
		if ( mmd()->exists( $my_tmpl ) ) :
			mmd()->clear_cache( $my_tmpl );
			$this->get_themes();
			$my_themes = $this->themes;
			$this->themes = array();
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
	final public function load_front_highlighter_stylesheets() {
		if ( ! isset( $this->prop[ 'engine' ] ) || empty( $this->prop[ 'engine' ] ) || $this->prop[ 'engine' ] === 'none' ) :
			# Do nothing
		elseif ( $this->prop[ 'engine' ] === 'prism' ) :
			if ( file_exists( mmd()->plugin_dir . 'assets/prism/v1/themes/' . $this->prop[ 'theme' ] . '.min.css' ) ) :
				wp_enqueue_style( 'markup_markdown__prism_theme', mmd()->plugin_uri . 'assets/prism/v1/themes/' . $this->prop[ 'theme' ] . '.min.css', [], '1.30.1001' );
			endif;
		elseif ( $this->prop[ 'engine' ] === 'highlight' ) :
			if ( file_exists( mmd()->plugin_dir . 'assets/highlightjs/styles/' . $this->prop[ 'theme' ] . '.min.css' ) ) :
				wp_enqueue_style( 'markup_markdown__hl_theme', mmd()->plugin_uri . 'assets/highlightjs/styles/' . $this->prop[ 'theme' ] . '.min.css', [], '11.11.1001' );
			endif;
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
	final public function load_front_highlighter_scripts() {
		$plugin_uri = mmd()->plugin_uri;
		if ( ! isset( $this->prop[ 'engine' ] ) || empty( $this->prop[ 'engine' ] ) || $this->prop[ 'engine' ] === 'none' ) :
			# Do nothing
		elseif ( $this->prop[ 'engine' ] === 'prism' ) :
			wp_enqueue_script( 'markup_markdown__prism_core', $plugin_uri . 'assets/prism/v1/components/prism-core.js', [], '1.30.1001', true );
			wp_enqueue_script( 'markup_markdown__prism_autoloader', $plugin_uri . 'assets/prism/v1/plugins/autoloader/prism-autoloader.js', [ 'markup_markdown__prism_core' ], '1.30.1001', true );
			wp_add_inline_script( 'markup_markdown__prism_autoloader', $this->add_inline_prism_conf() );
		elseif ( $this->prop[ 'engine' ] === 'highlight' ) :
			wp_enqueue_script( 'markup_markdown__hl_core', $plugin_uri . 'assets/highlightjs/highlight.min.js', [], '11.11.1', true );
			wp_add_inline_script( 'markup_markdown__hl_core', $this->add_inline_hl_conf() );
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
	final public function add_inline_prism_conf() {
		return 'Prism.plugins.autoloader.languages_path = "' . mmd()->plugin_uri . '/assets/prism/v1/components/";';
	}


	/**
	 * Highlightjs specific inline config for the frontend
	 *
	 * @since 3.21.0
	 * @access public
	 *
	 * @return Void
	 */
	final public function add_inline_hl_conf() {
		return 'hljs.highlightAll();';
	}


	final public function get_themes() {
		if ( count( $this->themes ) > 0 ) :
			return false;
		endif;
		$this->themes = array(
			'prismjs' => array(
				'prism-a11y-dark' => __( 'A11Y Dark', 'markup-markdown' ),
				'prism-atom-dark' => __( 'Atom Dark', 'markup-markdown' ),
				'prism-base16-ateliersulphurpool.light' => __( 'Base 16 AtelierSulphurpool Light', 'markup-markdown' ),
				'prism-cb' => __( 'CB', 'markup-markdown' ),
				'prism-coldark-cold' => __( 'Coldark Cold', 'markup-markdown' ),
				'prism-coldark-dark' => __( 'Coldark Dark', 'markup-markdown' ),
				'prism-coy' => __( 'Coy', 'markup-markdown' ),
				'prism-coy-without-shadows' => __( 'Coy without shadows', 'markup-markdown' ),
				'prism-darcula' => __( 'Darcula', 'markup-markdown' ),
				'prism-dark' => __( 'Dark', 'markup-markdown' ),
				'prism-dracula' => __( 'Dracula', 'markup-markdown' ),
				'prism-duotone-dark' => __( 'Duotone Dark', 'markup-markdown' ),
				'prism-duotone-earth' => __( 'Duotone Earth', 'markup-markdown' ),
				'prism-duotone-forest' => __( 'Duotone Forest', 'markup-markdown' ),
				'prism-duotone-light' => __( 'Duotone Light', 'markup-markdown' ),
				'prism-duotone-sea' => __( 'Duotone Sea', 'markup-markdown' ),
				'prism-duotone-space' => __( 'Duotone Space', 'markup-markdown' ),
				'prism-funky' => __( 'Duotone Funky', 'markup-markdown' ),
				'prism-ghcolors' => __( 'GhColors', 'markup-markdown' ),
				'prism-gruvbox-dark' => __( 'Gruvbox Dark', 'markup-markdown' ),
				'prism-gruvbox-light' => __( 'Gruvbox Light', 'markup-markdown' ),
				'prism-holi-theme' => __( 'Holi Theme', 'markup-markdown' ),
				'prism-hopscotch' => __( 'Hopscotch', 'markup-markdown' ),
				'prism-lucario' => __( 'Lucario', 'markup-markdown' ),
				'prism-material-dark' => __( 'Material Dark', 'markup-markdown' ),
				'prism-material-light' => __( 'Material Light', 'markup-markdown' ),
				'prism-material-oceanic' => __( 'Material Oceanic', 'markup-markdown' ),
				'prism-night-owl' => __( 'Night Owl', 'markup-markdown' ),
				'prism-okaidia' => __( 'Okaidia', 'markup-markdown' ),
				'prism-one-dark' => __( 'One Dark', 'markup-markdown' ),
				'prism-one-light' => __( 'One Light', 'markup-markdown' ),
				'prism-pojoaque' => __( 'Pojoaque', 'markup-markdown' ),
				'prism-shades-of-purple' => __( 'Shades of purple', 'markup-markdown' ),
				'prism-solarized-dark-atom' => __( 'Solarized Dark Atom', 'markup-markdown' ),
				'prism-solarizedlight' => __( 'Solarized Light', 'markup-markdown' ),
				'prism-synthwave84' => __( 'Synthwave84', 'markup-markdown' ),
				'prism-tomorrow' => __( 'Tomorrow', 'markup-markdown' ),
				'prism-twilight' => __( 'Twilight', 'markup-markdown' ),
				'prism-vs' => __( 'Visual Studio', 'markup-markdown' ),
				'prism-vsc-dark-plus' => __( 'Visual Studio Code Dark+', 'markup-markdown' ),
				'prism-xonokai' => __( 'Xonokai', 'markup-markdown' ),
				'prism-z-touch' => __( 'Z-touch', 'markup-markdown' )
			),
			'highlightjs' => array(
				'hl-1c-light' => __( '1C Light', 'markup-markdown' ),
				'hl-a11y-dark' => __( 'A11Y Dark', 'markup-markdown' ),
				'hl-a11y-light' => __( 'A11Y Light', 'markup-markdown' ),
				'hl-agate' => __( 'Agate', 'markup-markdown' ),
				'hl-an-old-hope' => __( 'An Old-Hope', 'markup-markdown' ),
				'hl-androidstudio' => __( 'Android Studio', 'markup-markdown' ),
				'hl-arduino-light' => __( 'Arduino Light', 'markup-markdown' ),
				'hl-arta' => __( 'Arta', 'markup-markdown' ),
				'hl-ascetic' => __( 'Ascetic', 'markup-markdown' ),
				'hl-atom-one-dark-reasonable' => __( 'Atom One Dark Reasonable', 'markup-markdown' ),
				'hl-atom-one-dark' => __( 'Atom One Dark', 'markup-markdown' ),
				'hl-atom-one-light' => __( 'Atom One Light', 'markup-markdown' ),
				'hl-base16-3024' => __( 'Base16 3024', 'markup-markdown' ),
				'hl-base16-apathy' => __( 'Base16 Apathy', 'markup-markdown' ),
				'hl-base16-apprentice' => __( 'Base16 Apprentice', 'markup-markdown' ),
				'hl-base16-ashes' => __( 'Base16 Ashes', 'markup-markdown' ),
				'hl-base16-atelier-cave-light' => __( 'Base16 Atelier Cave Light', 'markup-markdown' ),
				'hl-base16-atelier-cave' => __( 'Base16 Atelier Cave', 'markup-markdown' ),
				'hl-base16-atelier-dune-light' => __( 'Base16 Atelier Dune Light', 'markup-markdown' ),
				'hl-base16-atelier-dune' => __( 'Base16 Atelier Dune', 'markup-markdown' ),
				'hl-base16-atelier-estuary-light' => __( 'Base16 Atelier Estuary Light', 'markup-markdown' ),
				'hl-base16-atelier-estuary' => __( 'Base16 Atelier Estuary', 'markup-markdown' ),
				'hl-base16-atelier-forest-light' => __( 'Base16 Atelier Forest Light', 'markup-markdown' ),
				'hl-base16-atelier-forest' => __( 'Base16 Atelier Forest', 'markup-markdown' ),
				'hl-base16-atelier-heath-light' => __( 'Base16 Atelier Heath Light', 'markup-markdown' ),
				'hl-base16-atelier-heath' => __( 'Base16 Atelier Heath', 'markup-markdown' ),
				'hl-base16-atelier-lakeside-light' => __( 'Base16 Atelier Lakeside Light', 'markup-markdown' ),
				'hl-base16-atelier-lakeside' => __( 'Base16 Atelier Lakeside', 'markup-markdown' ),
				'hl-base16-atelier-plateau-light' => __( 'Base16 Atelier Plateau Light', 'markup-markdown' ),
				'hl-base16-atelier-plateau' => __( 'Base16 Atelier Plateau', 'markup-markdown' ),
				'hl-base16-atelier-savanna-light' => __( 'Base16 Atelier Savanna Light', 'markup-markdown' ),
				'hl-base16-atelier-savanna' => __( 'Base16 Atelier Savanna', 'markup-markdown' ),
				'hl-base16-atelier-seaside-light' => __( 'Base16 Atelier Seaside Light', 'markup-markdown' ),
				'hl-base16-atelier-seaside' => __( 'Base16 Atelier Seaside', 'markup-markdown' ),
				'hl-base16-atelier-sulphurpool-light' => __( 'Base16 Atelier Sulphurpool Light', 'markup-markdown' ),
				'hl-base16-atelier-sulphurpool' => __( 'Base16 Atelier Sulphurpool', 'markup-markdown' ),
				'hl-base16-atlas' => __( 'Base16 Atlas', 'markup-markdown' ),
				'hl-base16-bespin' => __( 'Base16 Bespin', 'markup-markdown' ),
				'hl-base16-black-metal-bathory' => __( 'Base16 Black Metal Bathory', 'markup-markdown' ),
				'hl-base16-black-metal-burzum' => __( 'Base16 Black Metal Burzum', 'markup-markdown' ),
				'hl-base16-black-metal-dark-funeral' => __( 'Base16 Black Metal Dark Funeral', 'markup-markdown' ),
				'hl-base16-black-metal-gorgoroth' => __( 'Base16 Black Metal Gorgoroth', 'markup-markdown' ),
				'hl-base16-black-metal-immortal' => __( 'Base16 Black Metal Immortal', 'markup-markdown' ),
				'hl-base16-black-metal-khold' => __( 'Base16 Black Metal Khold', 'markup-markdown' ),
				'hl-base16-black-metal-marduk' => __( 'Base16 Black Metal Marduk', 'markup-markdown' ),
				'hl-base16-black-metal-mayhem' => __( 'Base16 Black Metal Mayhem', 'markup-markdown' ),
				'hl-base16-black-metal-nile' => __( 'Base16 Black Metal Nile', 'markup-markdown' ),
				'hl-base16-black-metal-venom' => __( 'Base16 Black Metal Venom', 'markup-markdown' ),
				'hl-base16-black-metal' => __( 'Base16 Black Metal', 'markup-markdown' ),
				'hl-base16-brewer' => __( 'Base16 Brewer', 'markup-markdown' ),
				'hl-base16-bright' => __( 'Base16 Bright', 'markup-markdown' ),
				'hl-base16-brogrammer' => __( 'Base16 Brogrammer', 'markup-markdown' ),
				'hl-base16-brush-trees-dark' => __( 'Base16 BrushTrees Dark', 'markup-markdown' ),
				'hl-base16-brush-trees' => __( 'Base16 Brush Trees', 'markup-markdown' ),
				'hl-base16-chalk' => __( 'Base16 Chalk', 'markup-markdown' ),
				'hl-base16-circus' => __( 'Base16 Circus', 'markup-markdown' ),
				'hl-base16-classic-dark' => __( 'Base16 Classic Dark', 'markup-markdown' ),
				'hl-base16-classic-light' => __( 'Base16 Classic Light', 'markup-markdown' ),
				'hl-base16-codeschool' => __( 'Base16 Codeschool', 'markup-markdown' ),
				'hl-base16-colors' => __( 'Base16 Colors', 'markup-markdown' ),
				'hl-base16-cupcake' => __( 'Base16 Cupcake', 'markup-markdown' ),
				'hl-base16-cupertino' => __( 'Base16 Cupertino', 'markup-markdown' ),
				'hl-base16-danqing' => __( 'Base16 Danqing', 'markup-markdown' ),
				'hl-base16-darcula' => __( 'Base16 Darcula', 'markup-markdown' ),
				'hl-base16-dark-violet' => __( 'Base16 Dark Violet', 'markup-markdown' ),
				'hl-base16-darkmoss' => __( 'Base16 Darkmoss', 'markup-markdown' ),
				'hl-base16-darktooth' => __( 'Base16 Darktooth', 'markup-markdown' ),
				'hl-base16-decaf' => __( 'Base16 Decaf', 'markup-markdown' ),
				'hl-base16-default-dark' => __( 'Base16 Default Dark', 'markup-markdown' ),
				'hl-base16-default-light' => __( 'Base16 Default Light', 'markup-markdown' ),
				'hl-base16-dirtysea' => __( 'Base16 Dirtysea', 'markup-markdown' ),
				'hl-base16-dracula' => __( 'Base16 Dracula', 'markup-markdown' ),
				'hl-base16-edge-dark' => __( 'Base16 Edge Dark', 'markup-markdown' ),
				'hl-base16-edge-light' => __( 'Base16 Edge Light', 'markup-markdown' ),
				'hl-base16-eighties' => __( 'Base16 Eighties', 'markup-markdown' ),
				'hl-base16-embers' => __( 'Base16 Embers', 'markup-markdown' ),
				'hl-base16-equilibrium-dark' => __( 'Base16 Equilibrium Dark', 'markup-markdown' ),
				'hl-base16-equilibrium-gray-dark' => __( 'Base16 Equilibrium Gray Dark', 'markup-markdown' ),
				'hl-base16-equilibrium-gray-light' => __( 'Base16 Equilibrium Gray Light', 'markup-markdown' ),
				'hl-base16-equilibrium-light' => __( 'Base16 Equilibrium Light', 'markup-markdown' ),
				'hl-base16-espresso' => __( 'Base16 Espresso', 'markup-markdown' ),
				'hl-base16-eva-dim' => __( 'Base16 Eva Dim', 'markup-markdown' ),
				'hl-base16-eva' => __( 'Base16 Eva', 'markup-markdown' ),
				'hl-base16-flat' => __( 'Base16 Flat', 'markup-markdown' ),
				'hl-base16-framer' => __( 'Base16 Framer', 'markup-markdown' ),
				'hl-base16-fruit-soda' => __( 'Base16 Fruit Soda', 'markup-markdown' ),
				'hl-base16-gigavolt' => __( 'Base16 Gigavolt', 'markup-markdown' ),
				'hl-base16-github' => __( 'Base16 Github', 'markup-markdown' ),
				'hl-base16-google-dark' => __( 'Base16 Google Dark', 'markup-markdown' ),
				'hl-base16-google-light' => __( 'Base16 Google Light', 'markup-markdown' ),
				'hl-base16-grayscale-dark' => __( 'Base16 Grayscale Dark', 'markup-markdown' ),
				'hl-base16-grayscale-light' => __( 'Base16 Grayscale Light', 'markup-markdown' ),
				'hl-base16-green-screen' => __( 'Base16 Green Screen', 'markup-markdown' ),
				'hl-base16-gruvbox-dark-hard' => __( 'Base16 Gruvbox Dark Hard', 'markup-markdown' ),
				'hl-base16-gruvbox-dark-medium' => __( 'Base16 Gruvbox Dark Medium', 'markup-markdown' ),
				'hl-base16-gruvbox-dark-pale' => __( 'Base16 Gruvbox Dark Pale', 'markup-markdown' ),
				'hl-base16-gruvbox-dark-soft' => __( 'Base16 Gruvbox Dark Soft', 'markup-markdown' ),
				'hl-base16-gruvbox-light-hard' => __( 'Base16 Gruvbox Light Hard', 'markup-markdown' ),
				'hl-base16-gruvbox-light-medium' => __( 'Base16 Gruvbox Light Medium', 'markup-markdown' ),
				'hl-base16-gruvbox-light-soft' => __( 'Base16 Gruvbox Light Soft', 'markup-markdown' ),
				'hl-base16-hardcore' => __( 'Base16 Hardcore', 'markup-markdown' ),
				'hl-base16-harmonic16-dark' => __( 'Base16 Harmonic16 Dark', 'markup-markdown' ),
				'hl-base16-harmonic16-light' => __( 'Base16 Harmonic16 Light', 'markup-markdown' ),
				'hl-base16-heetch-dark' => __( 'Base16 Heetch Dark', 'markup-markdown' ),
				'hl-base16-heetch-light' => __( 'Base16 Heetch Light', 'markup-markdown' ),
				'hl-base16-helios' => __( 'Base16 Helios', 'markup-markdown' ),
				'hl-base16-hopscotch' => __( 'Base16 Hopscotch', 'markup-markdown' ),
				'hl-base16-horizon-dark' => __( 'Base16 Horizon Dark', 'markup-markdown' ),
				'hl-base16-horizon-light' => __( 'Base16 Horizon Light', 'markup-markdown' ),
				'hl-base16-humanoid-dark' => __( 'Base16 Humanoid Dark', 'markup-markdown' ),
				'hl-base16-humanoid-light' => __( 'Base16 Humanoid Light', 'markup-markdown' ),
				'hl-base16-ia-dark' => __( 'Base16 IA Dark', 'markup-markdown' ),
				'hl-base16-ia-light' => __( 'Base16 IA Light', 'markup-markdown' ),
				'hl-base16-icy-dark' => __( 'Base16 Icy Dark', 'markup-markdown' ),
				'hl-base16-ir-black' => __( 'Base16 IR Black', 'markup-markdown' ),
				'hl-base16-isotope' => __( 'Base16 Isotope', 'markup-markdown' ),
				'hl-base16-kimber' => __( 'Base16 Kimber', 'markup-markdown' ),
				'hl-base16-london-tube' => __( 'Base16 London Tube', 'markup-markdown' ),
				'hl-base16-macintosh' => __( 'Base16 Macintosh', 'markup-markdown' ),
				'hl-base16-marrakesh' => __( 'Base16 Marrakesh', 'markup-markdown' ),
				'hl-base16-materia' => __( 'Base16 Materia', 'markup-markdown' ),
				'hl-base16-material-darker' => __( 'Base16 Material Darker', 'markup-markdown' ),
				'hl-base16-material-lighter' => __( 'Base16 Material Lighter', 'markup-markdown' ),
				'hl-base16-material-palenight' => __( 'Base16 Material Palenight', 'markup-markdown' ),
				'hl-base16-material-vivid' => __( 'Base16 Material Vivid', 'markup-markdown' ),
				'hl-base16-material' => __( 'Base16 Material', 'markup-markdown' ),
				'hl-base16-mellow-purple' => __( 'Base16 Mellow Purple', 'markup-markdown' ),
				'hl-base16-mexico-light' => __( 'Base16 Mexico Light', 'markup-markdown' ),
				'hl-base16-mocha' => __( 'Base16 Mocha', 'markup-markdown' ),
				'hl-base16-monokai' => __( 'Base16 Monokai', 'markup-markdown' ),
				'hl-base16-nebula' => __( 'Base16 Nebula', 'markup-markdown' ),
				'hl-base16-nord' => __( 'Base16 Nord', 'markup-markdown' ),
				'hl-base16-nova' => __( 'Base16 Nova', 'markup-markdown' ),
				'hl-base16-ocean' => __( 'Base16 Ocean', 'markup-markdown' ),
				'hl-base16-oceanicnext' => __( 'Base16 Oceanicnext', 'markup-markdown' ),
				'hl-base16-one-light' => __( 'Base16 One Light', 'markup-markdown' ),
				'hl-base16-onedark' => __( 'Base16 Onedark', 'markup-markdown' ),
				'hl-base16-outrun-dark' => __( 'Base16 Outrun Dark', 'markup-markdown' ),
				'hl-base16-papercolor-dark' => __( 'Base16 Papercolor Dark', 'markup-markdown' ),
				'hl-base16-papercolor-light' => __( 'Base16 Papercolor Light', 'markup-markdown' ),
				'hl-base16-paraiso' => __( 'Base16 Paraiso', 'markup-markdown' ),
				'hl-base16-pasque' => __( 'Base16 Pasque', 'markup-markdown' ),
				'hl-base16-phd' => __( 'Base16 Phd', 'markup-markdown' ),
				'hl-base16-pico' => __( 'Base16 Pico', 'markup-markdown' ),
				'hl-base16-pop' => __( 'Base16 Pop', 'markup-markdown' ),
				'hl-base16-porple' => __( 'Base16 Porple', 'markup-markdown' ),
				'hl-base16-qualia' => __( 'Base16 Qualia', 'markup-markdown' ),
				'hl-base16-railscasts' => __( 'Base16 Railscasts', 'markup-markdown' ),
				'hl-base16-rebecca' => __( 'Base16 Rebecca', 'markup-markdown' ),
				'hl-base16-ros-pine-dawn' => __( 'Base16 Ros Pine Dawn', 'markup-markdown' ),
				'hl-base16-ros-pine-moon' => __( 'Base16 Ros Pine Moon', 'markup-markdown' ),
				'hl-base16-ros-pine' => __( 'Base16 Ros Pine', 'markup-markdown' ),
				'hl-base16-sagelight' => __( 'Base16 Sagelight', 'markup-markdown' ),
				'hl-base16-sandcastle' => __( 'Base16 Sandcastle', 'markup-markdown' ),
				'hl-base16-seti-ui' => __( 'Base16 Seti UI', 'markup-markdown' ),
				'hl-base16-shapeshifter' => __( 'Base16 Shapeshifter', 'markup-markdown' ),
				'hl-base16-silk-dark' => __( 'Base16 Silk Dark', 'markup-markdown' ),
				'hl-base16-silk-light' => __( 'Base16 Silk Light', 'markup-markdown' ),
				'hl-base16-snazzy' => __( 'Base16 Snazzy', 'markup-markdown' ),
				'hl-base16-solar-flare-light' => __( 'Base16 Solar Flare Light', 'markup-markdown' ),
				'hl-base16-solar-flare' => __( 'Base16 Solar Flare', 'markup-markdown' ),
				'hl-base16-solarized-dark' => __( 'Base16 Solarized Dark', 'markup-markdown' ),
				'hl-base16-solarized-light' => __( 'Base16 Solarized Light', 'markup-markdown' ),
				'hl-base16-spacemacs' => __( 'Base16 Spacemacs', 'markup-markdown' ),
				'hl-base16-summercamp' => __( 'Base16 Summercamp', 'markup-markdown' ),
				'hl-base16-summerfruit-dark' => __( 'Base16 Summerfruit Dark', 'markup-markdown' ),
				'hl-base16-summerfruit-light' => __( 'Base16 Summerfruit Light', 'markup-markdown' ),
				'hl-base16-synth-midnight-terminal-dark' => __( 'Base16 Synth Midnight Terminal Dark', 'markup-markdown' ),
				'hl-base16-synth-midnight-terminal-light' => __( 'Base16 Synth Midnight Terminal Light', 'markup-markdown' ),
				'hl-base16-tango' => __( 'Base16 Tango', 'markup-markdown' ),
				'hl-base16-tender' => __( 'Base16 Tender', 'markup-markdown' ),
				'hl-base16-tomorrow-night' => __( 'Base16 Tomorrow Night', 'markup-markdown' ),
				'hl-base16-tomorrow' => __( 'Base16 Tomorrow', 'markup-markdown' ),
				'hl-base16-twilight' => __( 'Base16 Twilight', 'markup-markdown' ),
				'hl-base16-unikitty-dark' => __( 'Base16 Unikitty Dark', 'markup-markdown' ),
				'hl-base16-unikitty-light' => __( 'Base16 Unikitty Light', 'markup-markdown' ),
				'hl-base16-vulcan' => __( 'Base16 Vulcan', 'markup-markdown' ),
				'hl-base16-windows-10-light' => __( 'Base16 Windows 10 Light', 'markup-markdown' ),
				'hl-base16-windows-10' => __( 'Base16 Windows 10', 'markup-markdown' ),
				'hl-base16-windows-95-light' => __( 'Base16 Windows 95 Light', 'markup-markdown' ),
				'hl-base16-windows-95' => __( 'Base16 Windows 95', 'markup-markdown' ),
				'hl-base16-windows-high-contrast-light' => __( 'Base16 Windows High Contrast Light', 'markup-markdown' ),
				'hl-base16-windows-high-contrast' => __( 'Base16 Windows High Contrast', 'markup-markdown' ),
				'hl-base16-windows-nt-light' => __( 'Base16 Windows NT Light', 'markup-markdown' ),
				'hl-base16-windows-nt' => __( 'Base16 Windows NT', 'markup-markdown' ),
				'hl-base16-woodland' => __( 'Base16 Woodland', 'markup-markdown' ),
				'hl-base16-xcode-dusk' => __( 'Base16 XCode Dusk', 'markup-markdown' ),
				'hl-base16-zenburn' => __( 'Base16 Zenburn', 'markup-markdown' ),
				'hl-brown-paper' => __( 'Brown Paper', 'markup-markdown' ),
				'hl-codepen-embed' => __( 'Codepen Embed', 'markup-markdown' ),
				'hl-color-brewer' => __( 'Color Brewer', 'markup-markdown' ),
				'hl-cybertopia-cherry' => __( 'Cybertopia Cherry', 'markup-markdown' ),
				'hl-cybertopia-dimmer' => __( 'Cybertopia Dimmer', 'markup-markdown' ),
				'hl-cybertopia-icecap' => __( 'Cybertopia Icecap', 'markup-markdown' ),
				'hl-cybertopia-saturated' => __( 'Cybertopia Saturated', 'markup-markdown' ),
				'hl-dark' => __( 'Dark', 'markup-markdown' ),
				'hl-default' => __( 'Default', 'markup-markdown' ),
				'hl-devibeans' => __( 'Devibeans', 'markup-markdown' ),
				'hl-docco' => __( 'Docco', 'markup-markdown' ),
				'hl-far' => __( 'Far', 'markup-markdown' ),
				'hl-felipec' => __( 'Felipec', 'markup-markdown' ),
				'hl-foundation' => __( 'Foundation', 'markup-arkdown' ),
				'hl-github-dark-dimmed' => __( 'Github Dark Dimmed', 'markup-markdown' ),
				'hl-github-dark' => __( 'Github Dark', 'markup-markdown' ),
				'hl-github' => __( 'Github', 'markup-markdown' ),
				'hl-gml' => __( 'GML', 'markup-markdown' ),
				'hl-googlecode' => __( 'Googlecode', 'markup-markdown' ),
				'hl-gradient-dark' => __( 'Gradient Dark', 'markup-markdown' ),
				'hl-gradient-light' => __( 'Gradient light', 'markup-markdown' ),
				'hl-grayscale' => __( 'Grayscale', 'markup-markdown' ),
				'hl-hybrid' => __( 'Hybrid', 'markup-markdown' ),
				'hl-idea' => __( 'Idea', 'markup-markdown' ),
				'hl-intellij-light' => __( 'Intellij Light', 'markup-markdown' ),
				'hl-ir-black' => __( 'IR Black', 'markup-markdown' ),
				'hl-isbl-editor-dark' => __( 'ISBL Editor Dark', 'markup-markdown' ),
				'hl-isbl-editor-light' => __( 'ISBL Editor Light', 'markup-markdown' ),
				'hl-kimbie-dark' =>  __( 'Kimbie Dark', 'markup-markdown' ),
				'hl-kimbie-light' => __( 'Kimbie Light', 'markup-markdown' ),
				'hl-lightfair' => __( 'Lightfair', 'markup-markdown' ),
				'hl-lioshi' => __( 'Lioshi', 'markup-markdown' ),
				'hl-magula' => __( 'Magula', 'markup-markdown' ),
				'hl-mono-blue' => __( 'Mono Blue', 'markup-markdown' ),
				'hl-monokai-sublime' => __( 'Monokai Sublime', 'markup-markdown' ),
				'hl-monokai' => __( 'Monokai', 'markup-markdown' ),
				'hl-night-owl' => __( 'Night Owl', 'markup-markdown' ),
				'hl-nnfx-dark' => __( 'NNFX Dark', 'markup-markdown' ),
				'hl-nnfx-light' => __( 'NNFX light', 'markup-markdown' ),
				'hl-nord' => __( 'Nord', 'markup-markdown' ),
				'hl-obsidian' => __( 'Obsidian', 'markup-markdown' ),
				'hl-panda-syntax-dark' => __( 'Panda Syntax Dark', 'markup-markdown' ),
				'hl-panda-syntax-light' => __( 'Panda Syntax Light', 'markup-markdown' ),
				'hl-paraiso-dark' => __( 'Paraiso Dark', 'markup-markdown' ),
				'hl-paraiso-light' => __( 'Paraiso Light', 'markup-markdown' ),
				'hl-pojoaque' => __( 'Pojoaque', 'markup-markdown' ),
				'hl-purebasic' => __( 'Purebasic', 'markup-markdown' ),
				'hl-qtcreator-dark' => __( 'QTCreator Dark', 'markup-markdown' ),
				'hl-qtcreator-light' => __( 'QTCreator Light', 'markup-markdown' ),
				'hl-rainbow' => __( 'Rainbow', 'markup-markdown' ),
				'hl-rose-pine-dawn' => __( 'Rose Pine Dawn', 'markup-markdown' ),
				'hl-rose-pine-moon' => __( 'Rose Pine Moon', 'markup-markdown' ),
				'hl-rose-pine' => __( 'Rose Pine', 'markup-markdown' ),
				'hl-routeros' => __( 'Routeros', 'markup-markdown' ),
				'hl-school-book' => __( 'School Book', 'markup-markdown' ),
				'hl-shades-of-purple' => __( 'Shades of Purple', 'markup-markdown' ),
				'hl-srcery' => __( 'Srcery', 'markup-markdown' ),
				'hl-stackoverflow-dark' => __( 'Stackoverflow Dark', 'markup-markdown' ),
				'hl-stackoverflow-light' => __( 'Stackoverflow Light', 'markup-markdown' ),
				'hl-sunburst' => __( 'Sunburst', 'markup-markdown' ),
				'hl-tokyo-night-dark' => __( 'Tokyo Night Dark', 'markup-markdown' ),
				'hl-tokyo-night-light' => __( 'Tokyo Night Light', 'markup-markdown' ),
				'hl-tomorrow-night-blue' => __( 'Tomorrow Night Blue', 'markup-markdown' ),
				'hl-tomorrow-night-bright' => __( 'Tomorrow Night Bright', 'markup-markdown' ),
				'hl-vs' => __( 'VS', 'markup-markdown' ),
				'hl-vs2015' => __( 'VS2015', 'markup-markdown' ),
				'hl-xcode' => __( 'XCode', 'markup-markdown' ),
				'hl-xt256' => __( 'XT256', 'markup-markdown' )
			)
		);
		return true;
	}


}


return apply_filters( 'mmd_load_addon', 'codehighlighter', new \MarkupMarkdown\Addons\Released\CodeHighlighter() );
