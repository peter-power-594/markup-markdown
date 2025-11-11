<?php

namespace MarkupMarkdown\Core;

defined( 'ABSPATH' ) || exit;
defined( 'MMD_PLUGIN_ACTIVATED' ) || exit;


final class Addons {


	private $prop = array(
		'setup' => array(),
		'inst' => array()
	);


	private $addon_dir = '';


	public function __construct() {
		$addon_conf = mmd()->conf_blog_prefix . 'conf_screen.php';
		if ( mmd()->exists( $addon_conf ) ) :
			require_once $addon_conf;
		endif;
		$this->load_addons();
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) {
			return $this->prop[ $name ];
		}
		return null;
	}


	private function load_addons() {
		# Load addons modules
		$this->addon_dir = mmd()->plugin_dir . '/MarkupMarkdown/Addons/';
		add_filter( 'mmd_load_addon', array( $this, 'load_addon' ), 10, 2 );
		# Kind of stable addons for a daily use
		$my_buffer = include $this->addon_dir  . 'Released/EngineEasyMDE.php';
		$my_buffer = include $this->addon_dir  . 'Released/OPCache.php';
		$my_buffer = include $this->addon_dir  . 'Released/Layout.php';
		$my_buffer = include $this->addon_dir  . 'Released/Media/Youtube.php';
		$my_buffer = include $this->addon_dir  . 'Released/Media/Vimeo.php';
		$my_buffer = include $this->addon_dir  . 'Released/Media/Image.php';
		$my_buffer = include $this->addon_dir  . 'Released/CodeHighlighter.php';
		$my_buffer = include $this->addon_dir  . 'Released/Comments.php';
		$my_buffer = include $this->addon_dir  . 'Released/LaTeX.php';
		$my_buffer = include $this->addon_dir  . 'Released/Mermaid.php';
		# Kind of usable addons but I wouldn't bet for extensive use
		$my_buffer = include $this->addon_dir  . 'Unsupported/SpellChecker.php';
		$my_buffer = include $this->addon_dir  . 'Unsupported/AdvancedCustomFields.php';
		# End
		$my_buffer = include $this->addon_dir  . 'Released/Debug.php';
		unset( $my_buffer );
	}


	final public function load_addon( $slug, $instance ) {
		if ( ! isset( $slug ) || ! is_string( $slug ) || empty( $slug ) ) :
			return false; # Wrong slug
		elseif ( in_array( $slug, $this->prop[ 'setup' ] ) !== false ) :
			return false; # Already loaded
		endif;
		if ( in_array( $slug, array( 'engine__easymde', 'debug' ) ) === false) :
			$this->prop[ 'setup' ][] = $slug;
		endif;
		$this->prop[ 'inst' ][ $slug ] = $instance;
		return true;
	}


}


return new \MarkupMarkdown\Core\Addons();
