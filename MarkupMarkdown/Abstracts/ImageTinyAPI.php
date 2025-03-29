<?php

namespace MarkupMarkdown\Abstracts;

defined( 'ABSPATH' ) || exit;


abstract class ImageTinyAPI {


	# public function __construct() {
		# Overriden, nothing to do here mate !
	# }



	/**
	 * Get an attachment ID given a URL
	 * 
	 * @since 3.14
	 * @access protected
	 * @link https://gist.github.com/wpscholar/3b00af01863c9dc562e5#file-get-attachment-id-php
	 * 
	 * @param string $url
	 * @return int Attachment ID on success, 0 on failure 
	 */
	protected function get_attachment_id( $url ) {
		if ( strpos( $url, $this->upload_dir[ 'baseurl' ] . '/' ) === false ) :
			# $url does not contain the upload directory
			return 0;
		endif;
		$file = basename( $url );
		$query_args = [ 'post_type' => 'attachment','post_status' => 'inherit', 'fields' => 'ids', 'meta_key' => '_wp_attachment_metadata', 'meta_compare' => 'LIKE', 'meta_value' => \esc_sql( $file ) ];
		$query = new \WP_Query( $query_args );
		if ( ! $query->have_posts() ) :
			\wp_reset_postdata();
			return 0;
		endif;
		$attachment_id = 0;
		foreach ( $query->posts as $post_id ) :
			$meta = \wp_get_attachment_metadata( $post_id );
			if ( ! isset( $meta ) || ! $meta || ! isset( $meta[ 'file' ] ) || ! isset( $meta[ 'sizes' ] ) ) :
				continue;
			endif;
			$original_file = preg_replace( '#-scaled\.([a-z0-9]+)$#i', '.$1', basename( $meta[ 'file' ] ) );
			$cropped_image_files = \wp_list_pluck( $meta[ 'sizes' ], 'file' );
			if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) :
				$attachment_id = $post_id;
				break;
			endif;
		endforeach;
		\wp_reset_postdata();
		return $attachment_id;
	}


	/**
	 * Retrieve the WP Attachment ID if already cached
	 * 
	 * @since 3.14
	 * @access protected
	 * 
	 * @param string $img_src The image source URL
	 * @return Integer The attachment ID
	 */
	protected function get_cached_asset_id( $img_src = '' ) {
		if ( empty( $img_src ) ) :
			return 0;
		elseif ( ! preg_match( '#^/#', $img_src ) && strpos( $img_src, $this->home_url ) === false ) :
			return 0;
		endif;
		$asset_cached_id = $this->asset_cache_dir . '/' . md5( $img_src ) . '.txt';
		if ( file_exists( $asset_cached_id ) ) :
			return (int)file_get_contents( $asset_cached_id );
		endif;
		$img_src = $this->home_url . str_replace( $this->home_url, '', preg_replace( '#-(\d+)x(\d+)\.(\w+)$#', '.$3', $img_src ) ); # Wordpress images
		$img_id = $this->get_attachment_id( $img_src );
		if ( (int)$img_id > 0 ) :
			touch( $asset_cached_id );
			file_put_contents( $asset_cached_id, $img_id );
		endif;
		return (int)$img_id;
	}


	/**
	 * Extract the text and check for caption data
	 * 
	 * @since 3.14
	 * @access protected
	 * 
	 * @param string|undefined $caption Current image alternative text
	 * @return array Text used for the image's alternative text and its related caption
	 */
	protected function check_alt_attribute( $caption ) {
		if ( ! isset( $caption ) && empty( $caption ) ) :
			return [];
		endif;
		if ( strpos( $caption, '--' ) !== false ) :
			$text = explode( '--', $caption );
			return [
				'alt' => trim( $text[ 0 ] ),
				'caption' => trim( $text[ 1 ] )
			];
		else :
			return [
				'alt' => trim( $caption )
			];
		endif;
	}


	/**
	 * Check the align attribute extracted from the HTML image align attribute or an HTML link class attribute if available
	 * 
	 * @since 3.14
	 * @access protected
	 * 
	 * @param string
	 * @return array WP valide align value
	 */
	protected function check_align_attribute( $align = '' ) {
		if ( ! isset( $align ) || empty( $align ) ) :
			return [];
		endif;
		if ( in_array( $align, array( 'none', 'left', 'right', 'center' ) ) ) :
			return [
				'align' => $align
			];
		endif;
	}


	/**
	 * Check the width value extracted from the HTML image's width attribute if available
	 * 
	 * @since 3.14
	 * @access protected
	 * 
	 * @param array $width Extracted values of the image's width attribute
	 * @param string $src Extracted value of the image's source
	 * @return array Requested width number
	 */
	protected function check_width_attribute( $width = [], $src = '' ) {
		if ( isset( $width ) && is_array( $width ) && isset( $width[ 1 ] ) && is_numeric( $width[ 1 ] ) && (int)$width[ 1 ] > 0  ) :
			# Check first value extracted from the width's attribute
			return [
				'width' => (int)$width[ 1 ]
			];
		endif;
		$img_width = [];
		if ( isset( $src ) && ! empty( $src ) && preg_match( '#(\d+)x\d+\.[a-zA-Z0-9]+$#', $src, $img_width ) ) :
			# As a fallback try to extract the width from the thumbnail
			if ( isset( $img_width ) && is_array( $img_width ) && isset( $img_width[ 1 ] ) ) :
				return [
					'width' => (int)$img_width[ 1 ]
				];
			endif;
		endif;
		return [];
	}


	/**
	 * Check the height value extracted from the HTML image's width attribute if available
	 *
	 * @since 3.14
	 * @access protected
	 * 
	 * @param array $height Extracted value of the image's height attribute
	 * @param string $src Extracted value of the image's source
	 * @return array Requested height number
	 */
	protected function check_height_attribute( $height = [], $src = '' ) {
		if ( !isset( $height ) && is_array( $height ) && isset( $height[ 1 ] ) && is_numeric( $height[ 1 ] ) && (int)$height[ 1 ] > 0 ) :
			# Check first value extracted from the height's attribute
			return [
				'height' => (int)$height[ 1 ]
			];
		endif;
		$img_height = [];
		if ( isset( $src ) && ! empty( $src ) && preg_match( '#\d+x(\d+)\.[a-zA-Z0-9]+$#', $src, $img_height ) ) :
			# As a fallback try to extract the height from the thumbnail
			if ( isset( $img_height ) && is_array( $img_height ) && isset( $img_height[ 1 ] ) ) :
				return [
					'height' => (int)$img_height[ 1 ]
				];
			endif;
		endif;
		return [];
	}


	/**
	 * Following latest Gutenberg / Theme specification, the image is wrapped by a *figure* with a *figcaption* if need be
	 * 
	 * @param integer $img_id WP Attachment ID
	 * @param array $img_attrs Image related attribute
	 * @return string Modified image tag
	 */
	protected function wrap_image( $img_id = 0, $img_attrs = [] ) {
		if ( ! isset( $img_id ) || ! (int)$img_id || ! isset( $img_attrs ) || ! is_array( $img_attrs ) ) :
			return '';
		endif;
		$img_size = 'full';
		if ( isset( $img_attrs[ 'width' ] ) && isset( $img_attrs[ 'height' ] ) ) :
			$img_size = [ $img_attrs[ 'width' ], $img_attrs[ 'height' ] ];
			unset( $img_attrs[ 'width' ] ); unset( $img_attrs[ 'height' ] );
		endif;
		$img_caption = '';
		if ( isset( $img_attrs[ 'caption' ] ) ) :
			$img_caption = trim( $img_attrs[ 'caption' ] );
			unset( $img_attrs[ 'caption' ] );
		endif;
		return '<figure id="attachment_mmd_' . $img_id . '" '
			. ( ! empty( $img_caption ) ? 'aria-describedby="caption-attachment-mmd' . $img_id . '" class="wp-block-image wp-caption ' : 'class="wp-block-image ' )
			. ( isset( $img_attrs[ 'align' ] ) ? 'align' . $img_attrs[ 'align' ] : '' )
			. '">' . \wp_get_attachment_image( $img_id, $img_size, false, $img_attrs )
			. ( ! empty( $img_caption ) ? '<figcaption id="caption-attachment-mmd' . $img_id . '" class="wp-caption-text wp-element-caption">' . trim( $img_caption ) . '</figcaption>' : '' )
			. '</figure>';
	}


	/**
	 * Replace HTML image tags with customized wordpress generated version
	 * 
	 * @since 3.14
	 * @access protected
	 * 
	 * @param string $html Target HTML snippet to parse
	 * @param string $img_src Image original source
	 * @return string Modified html image
	 */
	protected function native_wp_image( $img_id = 0, $img_src = '', $img_html = '' ) {
		if ( ! isset( $img_id ) || ! (int)$img_id || ! isset( $img_html ) || empty( $img_html ) || ! isset( $img_src ) || empty( $img_src ) ) :
			return '';
		endif;
		$img_attrs = [ 'decoding' => 'async', 'loading' => 'lazy' ];
		$tmp_args = [];
		# Check for captions related values
		if ( preg_match( '#alt="(.*?)"#', $img_html, $tmp_args ) === 1 ) :
			$img_attrs = array_merge( $img_attrs, $this->check_alt_attribute( $tmp_args[ 1 ] ) );
		endif;
		# Check for a custom align value
		if ( preg_match( '#align([a-z]+)#', $img_html, $tmp_args ) === 1 ) :
			$img_attrs = array_merge( $img_attrs, $this->check_align_attribute( $tmp_args[ 1 ] ) );
		endif;
		# Check for a width value
		preg_match( '#width="(.*?)"#', $img_html, $tmp_args );
		$img_attrs = array_merge( $img_attrs, $this->check_width_attribute( $tmp_args, $img_src ) );
		# Check for a height value
		preg_match( '#height="(.*?)"#', $img_html, $tmp_args );
		$img_attrs = array_merge( $img_attrs, $this->check_height_attribute( $tmp_args, $img_src ) );
		$new_img = $this->wrap_image( $img_id, $img_attrs );
		return preg_replace( '#<img[^>]+>#', $new_img, $img_html );
	}


}
