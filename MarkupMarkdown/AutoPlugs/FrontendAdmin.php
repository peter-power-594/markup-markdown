<?php

namespace MarkupMarkdown\AutoPlugs;

defined( 'ABSPATH' ) || exit;


/**
 * Frontend Admin by DynamiApps
 *
 * @since 3.15.0
 *
 */
class FrontendAdmin {


	private $env;

	public function __construct() {
		define( 'MMD_FRONTENDADMIN_PLUG', true );
		$this->env[ 'version' ] = '1.0.0';
		if ( ! is_admin() ) :
			add_action( 'wp_enqueue_scripts', array( $this, 'mmd_enqueue' ) );
		endif;
	}


	public function mmd_enqueue() {
		if ( ! is_singular() ) :
			return false;
		endif;
		global $post;
		if ( ! isset( $post ) || ! isset( $post->post_content ) || ! preg_match( '#\[frontend_admin#', $post->post_content ) ) :
			return false;
		endif;
		add_filter( 'mmd_frontend_enabled', '__return_true' );
		$base_dir = mmd()->plugin_uri . 'assets/acf-frontend-form-element/';
		$ver = $this->env[ 'version' ];
		# Register and enqueue the plug's script
		wp_register_script( 'mmd-acf-frontendform', $base_dir . 'js/field.min.js', array(), $ver, true );
		wp_enqueue_script( 'mmd-acf-frontendform' );
		# Register and enqueue the plug's stylesheet
		wp_register_style( 'mmd-acf-frontendform', $base_dir . 'css/field.min.css', array(), $ver );
		wp_enqueue_style( 'mmd-acf-frontendform' );
		# frontend_admin/forms/before_render
		# frontend_admin/after_form
		return true;
	}


}


new \MarkupMarkdown\AutoPlugs\FrontendAdmin();
