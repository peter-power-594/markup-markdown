<?php

namespace MarkupMarkdown\Addons\Released\Media;

defined( 'ABSPATH' ) || exit;

if ( defined( 'MMD_ADDONS_LOADED' ) ) :
	return false;
endif;

require_once mmd()->plugin_dir . '/MarkupMarkdown/Abstracts/OEmbedTinyAPI.php';


final class Youtube extends \MarkupMarkdown\Abstracts\OEmbedTinyAPI {


	private $prop = array(
		'slug' => 'youtube',
		'release' => 'stable',
		'active' => 1
	);


	public function __construct() {
		if ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === FALSE ) :
			$this->prop[ 'active' ] = 0;
			return FALSE; # Addon has been desactivated
		endif;
		add_filter( 'addon_markdown2html', array( $this, 'youtube2html' ) );
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) :
			return $this->prop[ $name ];
		elseif ( $name === 'label' ) :
			return esc_html__( 'Youtube', 'markup-markdown' );
		elseif ( $name === 'desc' ) :
			return esc_html__( 'Convert automatically Youtube links to an embedded iframe.', 'markup-markdown' );
		endif;
		return 'mmd_undefined';
	}


	/**
	 * Method to parse Youtube links and output the related iframes
	 * Previously in core from 1.6.0 until refactoring in v2
	 *
	 * @access public
	 * @since 2.0.0
	 *
	 * @param String $content The html to be parsed.
	 * @return String The html with Youtube iframes embed code.
	 */
	public function youtube2html( $content = '' ) {
		return $this->oembed_service([
			'content'  => $content,
			'provider' => 'youtube',
			'endpoint' => 'https://www.youtube.com/oembed',
			'regexp'   => '#[^"a-zA-Z\/\/:\.]{1}[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)#u'
		]);
	}


}


return apply_filters( 'mmd_load_addon', 'youtube', new \MarkupMarkdown\Addons\Released\Media\Youtube() );

