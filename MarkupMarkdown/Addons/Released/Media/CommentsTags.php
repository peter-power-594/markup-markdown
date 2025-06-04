<?php

namespace MarkupMarkdown\Addons\Released\Media;

defined( 'ABSPATH' ) || exit;

class CommentsTags {

	protected $prop = array(
		"default_tags" => array(),
		"noscope_tags" => array( 'address', 'area', 'article', 'audio', 'aside', 'bdo', 'big', 'button', 'caption', 'col', 'colgroup', 'dfn', 'del', 'details', 'dl', 'dt', 'dd', 'div', 'embed', 'fieldset', 'figure', 'figcaption', 'font', 'footer', 'header', 'hgroup', 'h1', 'h2', 'h3', 'h6', 'input', 'ins', 'kbd', 'legend', 'label', 'main', 'map', 'mark', 'menu', 'nav', 'object', 'pre', 'rb', 'rp', 'rt', 'rtc', 'ruby', 'small', 'samp', 'section', 'summary', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'textarea', 'title', 'tr', 'track', 'var', 'video' ),
		"noscope_attrs" => array( 'align', 'aria-controls', 'aria-current', 'aria-describedby', 'aria-details', 'aria-expanded', 'aria-hidden', 'aria-expanded', 'aria-label', 'aria-labelledby', 'aria-live', 'border', 'data-*', 'hidden', 'hspace', 'download', 'id', 'name', 'role', 'style', 'target', 'usemap', 'value', 'valign', 'vspace', 'xml:lang' ),
		"allowed_tags" => array()
	);


	public function __construct( $json = '', $associative = false ) {
		if ( empty( $json ) ) :
			return false;
		endif;
		if ( ! file_exists( $json ) ) :
			$this->make_default_tags();
			file_put_contents( $json, json_encode( $this->prop[ 'default_tags' ] ) );
			$this->prop[ 'allowed_tags' ] = $this->prop[ 'default_tags' ];
		endif;
		mmd()->clear_cache( $json );
		$my_tags = mmd()->json_decode( $json, $associative );
		if ( isset( $my_tags ) ) :
			$this->prop[ 'allowed_tags' ] = $my_tags;
		else :
			$this->make_default_tags();
			$this->prop[ 'allowed_tags' ] = $this->prop[ 'default_tags' ];
		endif;
	}


	/**
	 * Create an array from Wordpress data with a set of possible html tags used for comments
	 * 
	 * @since 3.17.0
	 * @access public
	 * 
	 * @return Boolean FALSE if default tags list were already generated, TRUE otherwise
	 */
	public function make_default_tags() {
		if ( count( $this->prop[ 'default_tags' ] ) > 0 ) :
			return false;
		endif;
		$html_tags = wp_kses_allowed_html( 'post' );
		foreach( $html_tags as $my_tag => $my_attrs ) :
			$my_tag = strtolower( $my_tag );
			if ( in_array( $my_tag, $this->prop[ 'noscope_tags' ] ) !== false ) :
				continue;
			endif;
			$this->prop[ 'default_tags' ][ $my_tag ] = array( 'active' => 1 );
			foreach( $my_attrs as $attr_name => $attr_value ) :
				$attr_name = strtolower( $attr_name );
				if ( in_array( $attr_name, $this->prop[ 'noscope_attrs' ] ) && $attr_name !== 'active' ) :
					continue;
				endif;
				$this->prop[ 'default_tags' ][ $my_tag ][ $attr_name ] = 1;
			endforeach;
		endforeach;
		return true;
	}


	public function __get( $name = '' ) {
		if ( array_key_exists( $name, $this->prop ) ) :
			if ( 'default_tags' === $name && ! count( $this->prop[ 'default_tags' ] ) ) :
				$this->make_default_tags();
			endif;
			return $this->prop[ $name ];
		endif;
		return 'mmd_undefined';
	}

}
