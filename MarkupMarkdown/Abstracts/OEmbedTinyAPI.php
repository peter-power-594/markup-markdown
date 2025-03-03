<?php

namespace MarkupMarkdown\Abstracts;

defined( 'ABSPATH' ) || exit;


abstract class OEmbedTinyAPI {


	# public function __construct() {
		# Overriden, nothing to do here mate !
	#}


	/**
	 * Method to parse video links and output the related iframes
	 * Previously in core from 1.6.0 until refactoring in v2
	 *
	 * @access public
	 * @since 2.0.0
	 *
	 * @param array $ops the options [
	 *   'content'  => String the content to be parsed
	 *   'endpoint' => String the target service endpoint
	 *   'regexp'   => String the regular expression to use
	 * ]
	 * @returns string HTML with Vimeo iframes embed code
	 */
	protected function oembed_service( $ops = [] ) {
		if ( ! isset( $ops[ 'regexp' ] ) || ! isset( $ops[ 'endpoint' ] ) || ! isset( $ops[ 'content' ] ) || empty( $ops[ 'content' ] ) ) :
			return isset( $ops[ 'content' ] ) ? $ops[ 'content' ] : '';
		endif;
		$medias = [];
		$my_content = $ops[ 'content' ];
		preg_match_all( $ops[ 'regexp' ], $my_content, $medias );
		if ( ! isset( $medias ) || ! is_array( $medias ) || count( $medias ) < 1 ) :
			# No video links found
			return $my_content;
		endif;
		return $this->format_medias([
			"medias" => array_unique( $medias[ 0 ] ),
			"endpoint" => $ops[ 'endpoint' ],
			"content" => $ops[ 'content' ],
			"provider" => $ops[ 'provider' ]
		]);
	}


	protected function check_url_parts( $target_media = '' ) {
		$my_media = preg_replace( '#^[^"a-zA-Z\/\/:\.]{1}#', '', $target_media );
		# From here we assume there are no question mark and only ampersand character
		$tok = strtok( str_replace( '&amp;', '&', $my_media ), '&' ); # Decode & if need be
		$parts = [];
		while ( $tok !== false ) :
			$parts[] = $tok;
			$tok = strtok( '&' );
		endwhile;
		$my_url = array_shift( $parts );
		$my_options = count( $parts ) > 1 ? '&' . implode( '&', $parts ) : '';
		if ( strpos( $my_options, 'width' ) === false ) :
			# We assume there are no width. Set a minimum of 640
			$my_options .= '&width=640';
		endif;
		if ( strpos( $my_options, 'maxwidth' ) === false ) :
			$my_options .= '&maxwidth=640';
		endif;
		return [
			'original' => $my_media,
			'url' => $my_url,
			'options' => $my_options
		];
	}


	/**
	 * Wrap iframe media elements with Gutenberg / WordPress styles
	 * 
	 * @since 3.14.0
	 * @access protected
	 * 
	 * @param Object $body The oEmbed API response object
	 * @param String $provider_name The oEmbed provider slug
	 * @return Object The oEmbed modified reponse object
	 */
	protected function wrap_iframe( $body, $provider_name = '' ) {
		$html = '<figure class="mmd-block-embed wp-block-embed is-type-video is-provider-' . $provider_name . ' wp-block-embed-' . $provider_name;
		if ( isset( $body->width ) && (int)$body->width > 0 && isset( $body->height ) && (int)$body->height > 0 ) :
			if ( number_format( $body->width / $body->height, 1 ) === '1.8' ) :
				$html .= ' wp-embed-aspect-16-9 wp-has-aspect-ratio';
			endif;
		endif;
		$html .= '"><div class="wp-block-embed__wrapper">' . $body->html . '</div></figure>';
		return $html;
	}


	protected function retrieve_media_info( $api_endpoint = '', $provider_name = '' ) {
		if ( ! isset( $api_endpoint ) || empty( $api_endpoint ) ) :
			return json_decode( '{"error","Missing API Endpoint"}' );
		elseif ( ! isset( $provider_name ) || empty( $provider_name ) ) :
			return json_decode( '{"error","Missing API Provider Name"}' );
		endif;
		$response = wp_remote_get( $api_endpoint );
		if ( is_wp_error( $response ) || ! is_array( $response ) || ! isset( $response[ 'body' ] ) ) :
			return json_decode( '{"error","Error while trying to retrieve info about the following video' . $api_endpoint . '"}' );
		endif;
		$body = json_decode( $response[ 'body' ] );
		if ( defined( 'MMD_USE_BLOCKSTYLES' ) && MMD_USE_BLOCKSTYLES ) :
			$body->html = $this->wrap_iframe( $body, $provider_name );
			return $body;
		endif;
		$body->mmd_html = $body->html;
		return $body;
	}


	/**
	 * Trigger the iframe wrapper for standalone iframes
	 * 
	 * @since 3.14.0
	 * @access protected
	 * 
	 * @param string $content Post content
	 * @param string $provider_name The oEmbed provider slug
	 * @return string Updated HTML content
	 */
	protected function format_standalone_iframes( $content = '', $provider_name = '' ) {
		if ( ! isset( $content ) || empty( $content ) ) :
			return '';
		endif;
		$standalone_iframes = [];
		preg_match_all( '#</.*?>[\n]*(<iframe.*?src\=\".*?' . $provider_name . '.*?></iframe>)#', $content, $standalone_iframes );
		if ( ! isset( $standalone_iframes ) || ! is_array( $standalone_iframes ) || ! isset( $standalone_iframes[ 1 ] ) ) :
			return $content;
		endif;
		foreach( $standalone_iframes[ 1 ] as $iframe ) :
			$my_iframe = [ 'html' => $iframe ];
			$tmp_args = [];
			if ( preg_match( '#width\=\"(.*?)\"#', $iframe, $tmp_args ) === 1 ) :
				$my_iframe[ 'width' ] = $tmp_args[ 1 ];
			endif;
			if ( preg_match( '#height\=\"(.*?)\"#', $iframe, $tmp_args ) === 1 ) :
				$my_iframe[ 'height' ] = $tmp_args[ 1 ];
			endif;
			$content = preg_replace( '#' . preg_quote( $iframe ) . '#', $this->wrap_iframe( (object)$my_iframe, $provider_name ), $content );
		endforeach;
		return $content;
	}


	/**
	 * Format the iframe media retrieved from the target api
	 * 
	 * 
	 * @param array $ops Media options
	 * @return string Modified HTML contenet
	 */
	protected function format_medias( $ops ) {
		$my_content = $ops[ 'content' ];
		foreach( $ops[ 'medias' ] as $my_media ) :
			if ( empty( $my_media ) ) :
				continue;
			endif;
			$media = $this->check_url_parts( $my_media );
			$data = $this->retrieve_media_info(
				$ops[ 'endpoint' ] . '?url=' . rawurlencode( $media[ 'url' ] ) . $media[ 'options' ],
				isset( $ops[ 'provider' ] ) && ! empty( $ops[ 'provider' ] ) ? $ops[ 'provider' ] : 'unknown'
			);
			if ( isset( $data->html ) ) :
				$my_content = preg_replace( "#<a href=\"" . preg_quote( $media[ 'original' ], '#' ) . "\">.*?</a>#u", $data->html, $my_content );
			elseif ( isset( $data->error ) ) :
				$error_log = "\nWP Markup Markdown: " . $data->error;
				error_log( $error_log );
				$my_content = preg_replace( "#(<a href=\"" . preg_quote( $media[ 'original' ], '#' ) . "\">.*?</a>)#u", '$1' . "\n<!-- " . $error_log . " -->", $my_content );
			endif;
		endforeach;
		return defined( 'MMD_USE_BLOCKSTYLES' ) && MMD_USE_BLOCKSTYLES ? $this->format_standalone_iframes( $my_content ) : $my_content;
	}


}