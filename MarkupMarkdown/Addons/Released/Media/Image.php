<?php

namespace MarkupMarkdown\Addons\Released\Media;

defined( 'ABSPATH' ) || exit;

require_once mmd()->plugin_dir . '/MarkupMarkdown/Abstracts/ImageTinyAPI.php';

final class Image extends \MarkupMarkdown\Abstracts\ImageTinyAPI {


	private $prop = array(
		'slug' => 'Image',
		'release' => 'stable',
		'active' => 1
	);


	public $home_url = '';


	public $def_sizes = [];


	public $gutenberg = 0;


	protected $asset_cache_dir = '';

	protected $upload_dir = [];


	public function __construct( ) {
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		if ( ! is_admin() ) :
			add_filter( 'addon_markdown2html', array( $this, 'render_responsive_images' ), 9, 1 );
		endif;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) :
			return $this->prop[ $name ];
		elseif ( $name === 'label' ) :
			return esc_html__( 'Responsive Image', 'markup-markdown' );
		elseif ( $name === 'desc' ) :
			return esc_html__( 'Add basic html code syntax for responsive media.', 'markup-markdown' );
		endif;
		return 'mmd_undefined';
	}


	final public function load_image_block_assets() {
		if ( $this->gutenberg > 0 ) : # Already loaded
			return FALSE;
		endif;
		$this->gutenberg = 1;
		$blog_version = get_bloginfo( 'version' );
		wp_enqueue_style( 'wp-block-image', '/wp-includes/blocks/image/style.min.css', array(), $blog_version );
		wp_enqueue_style( 'wp-block-embed', '/wp-includes/blocks/embed/style.min.css', array(), $blog_version );
		wp_enqueue_style( 'wp-block-gallery', '/wp-includes/blocks/gallery/style.min.css', array(), $blog_version );
		wp_enqueue_style( 'mmd-block-gallery', mmd()->plugin_uri . '/assets/markup-markdown/css/gallery-compatibility.min.css', array(), $blog_version );
		add_filter( 'the_content', array( $this, 'render_gutenberg_html4_galleries' ), 19, 1 );
		return TRUE;
	}


	private function parse_img_tags( $content = '' ) {
		$html_imgs = [];
		if ( ! preg_match_all( '#<' . 'img.*?src="(.*?)"[^>]*' . '>#', $content, $html_imgs ) ) :
			return $content;
		endif;
		if ( ! isset( $html_imgs ) || ! isset( $html_imgs[ 0 ] ) || ! is_array( $html_imgs[ 0 ] ) ) :
			return $content;
		endif;
		foreach( $html_imgs[ 0 ] as $idx => $img_tag ) :
			$img_id = $this->get_cached_asset_id( $html_imgs[ 1 ][ $idx ] );
			if ( $img_id > 0 ) :
				$new_img = $this->native_wp_image( $img_id, $html_imgs[ 1 ][ $idx ], $img_tag );
				$content = str_replace( $html_imgs[ 0 ][ $idx ], $new_img, $content );
			endif;
		endforeach;
		return $content;
	}


	private function clean_html( $content = '' ) {
		if ( ! isset( $content ) || empty( $content ) ) :
			return '';
		endif;
		if ( preg_match( '#</figure></a>#', $content ) ) :
			$content = preg_replace( '#(<a[^>]+>)(<figure[^>]+>)#', '$2$1', str_replace( '</figure></a>', '</a></figure>', $content ) );
		endif;
		if ( preg_match( '#</figure></p>#', $content ) ) :
			$content = str_replace( [ '<p><figure', '</figure></p>' ], [ '<figure', '</figure>' ], $content );
		endif;
		return $content;
	}


	/**
	 * Format the images html tags as wordpress
	 *
	 * @access public
	 *
	 * @params string $content The html generated from the markdown
	 * @return string $content The modified html code
	 */
	final public function render_responsive_images( $content = '' ) {
		if ( defined( 'MMD_USE_BLOCKSTYLES' ) && MMD_USE_BLOCKSTYLES ) :
			$this->load_image_block_assets();
		endif;
		if ( empty( $this->home_url ) ) :
			$this->home_url = preg_replace( '#(\.[a-z]+)\/.*?$#', '$1/', get_home_url() );
		endif;
		if ( empty( $this->asset_cache_dir ) ) :
			$this->asset_cache_dir = mmd()->cache_dir . '/.assets';
			if ( ! mmd()->exists( $this->asset_cache_dir ) ) :
				mmd()->mkdir( $this->asset_cache_dir );
			endif;
		endif;
		if ( empty( $this->upload_dir ) ) :
			$this->upload_dir = wp_upload_dir();
		endif;
		# Cleanup HTML
		return $this->clean_html( $this->parse_img_tags( $content ) );
	}


	/**
	 * Format the gallery classnames generated from the shortcode
	 *
	 * @access public
	 * @since 3.20.7
	 *
	 * @param String $content The html generated
	 * @return String $content The modified html code
	 */
	final public function render_gutenberg_html4_galleries( $content = '' ) {
		preg_match_all( '#<' . 'div id\=[\"|\']{1}gallery-\d*[\"|\']{1} class\=[\"|\']{1}(gallery galleryid-\d* gallery-columns-\d* gallery-size-[a-z]+)' . '[\"|\']{1}>#', $content, $html4_galleries );
		if ( ! isset( $html4_galleries ) || ! isset( $html4_galleries[ 0 ] ) || ! is_array( $html4_galleries[ 0 ] ) ) :
			return $content;
		endif;
		foreach( $html4_galleries[ 0 ] as $idx => $gal_opener ) :
				$content = str_replace( $html4_galleries[ 0 ][ $idx ], str_replace( $html4_galleries[ 1 ][ $idx ], $html4_galleries[ 1 ][ $idx ] . ' ' . str_replace( 'gallery', 'wp-block-gallery', $html4_galleries[ 1 ][ $idx ] ) . ' has-nested-images columns-default is-cropped is-layout-flex wp-block-gallery-is-layout-flex', $html4_galleries[ 0 ][ $idx ] ), $content );
		endforeach;
		$content = preg_replace( '#figure class\=[\"|\']{1}gallery-item[\"|\']{1}#', 'figure class="gallery-item wp-block-image"', $content );
		return $content;
	}


}
