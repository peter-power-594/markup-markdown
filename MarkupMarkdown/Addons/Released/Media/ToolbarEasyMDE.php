<?php

namespace MarkupMarkdown\Addons\Released\Media;

defined( 'ABSPATH' ) || exit;

final class ToolbarEasyMDE {


	protected $prop = array(
		"default_buttons" => array(
			"mmd_pipe" => array(
				"action"  => "none",
				"class"    => "mmd_fa mmd_fa-pipe"
			),
			"mmd_bold" => array(
				"action"  => "toggleBold",
				"class"    => "mmd_fa mmd_fa-bold"
			),
			"mmd_italic" => array(
				"action"  => "toggleItalic",
				"class"    => "mmd_fa mmd_fa-italic"
			),
			"mmd_strikethrough" => array(
				"action"  => "toggleStrikethrough",
				"class"    => "mmd_fa mmd_fa-strikethrough"
			),
			"mmd_heading" => array(
				"action"  => "toggleHeading",
				"class"    => "mmd_fa mmd_fa-header mmd_fa-heading"
			),
			"mmd_heading_smaller" => array(
				"action"  => "toggleHeadingSmaller",
				"class"    => "mmd_fa mmd_fa-header mmd_fa-heading"
			),
			"mmd_heading_bigger" => array(
				"action"  => "toggleHeadingBigger",
				"class"    => "mmd_fa mmd_fa-lg mmd_fa-header mmd_fa-heading"
			),
			"mmd_heading_1" => array(
				"action"  => "toggleHeading1",
				"class"    => "mmd_fa mmd_fa-header mmd_fa-heading"
			),
			"mmd_heading_2" => array(
				"action"  => "toggleHeading2",
				"class"    => "mmd_fa mmd_fa-header mmd_fa-heading"
			),
			"mmd_heading_3" => array(
				"action"  => "toggleHeading3",
				"class"    => "mmd_fa mmd_fa-header mmd_fa-heading"
			),
			"mmd_code" => array(
				"action"  => "toggleCodeBlock",
				"class"    => "mmd_fa mmd_fa-code"
			),
			"mmd_quote" => array(
				"action"  => "toggleBlockquote",
				"class"    => "mmd_fa mmd_fa-quote-left"
			),
			"mmd_unordered_list" => array(
				"action"  => "toggleGenericList",
				"class"    => "mmd_fa mmd_fa-list-ul"
			),
			"mmd_ordered_list" => array(
				"action"  => "toggleOrderedList",
				"class"    => "mmd_fa mmd_fa-list-ol"
			),
			"mmd_clean_block" => array(
				"action"  => "cleanBlock",
				"class"    => "mmd_fa mmd_fa-eraser"
			),
			"mmd_link" => array(
				"action"  => "drawLink",
				"class"    => "mmd_fa mmd_fa-link"
			),
			"mmd_wpsimage" => array( # image
				"action"  => "WPLibraryHandler",
				"class"    => "mmd_fa mmd_fa-images"
			),
			"mmd_table" => array(
				"action"  => "drawTable",
				"class"    => "mmd_fa mmd_fa-table"
			),
			"mmd_horizontal_rule" => array(
				"action"  => "drawHorizontalRule",
				"class"    => "mmd_fa mmd_fa-minus"
			),
			"mmd_preview" => array(
				"action"  => "togglePreview",
				"class"    => "mmd_fa mmd_fa-eye no-disable"
			),
			"mmd_side_by_side" => array(
				"action"  => "toggleSideBySide",
				"class"    => "mmd_fa mmd_fa-columns no-disable no-mobile"
			),
			"mmd_fullscreen" => array(
				"action"  => "toggleFullScreen",
				"class"    => "mmd_fa mmd_fa-arrows-alt no-disable no-mobile"
			),
			"mmd_guide" => array(
				"action"  => "This link",
				"class"    => "mmd_fa mmd_fa-question-circle"
			),
			"mmd_undo" => array(
				"action"  => "undo",
				"class"    => "mmd_fa mmd_fa-undo"
			),
			"mmd_redo" => array(
				"action"  => "redo",
				"class"    => "mmd_fa mmd_fa-redo"
			),
			"mmd_spell_check" => array(
				"action"  => "spellcheck",
				"class"    => "mmd_fa mmd_fa-globe"
			),
			"mmd_rtltextdir" => array(
				"action"  => "textdir",
				"class"    => "mmd_fa mmd_fa-caret-square-left"
			),
			"mmd_ltrtextdir" => array(
				"action"  => "textdir",
				"class"    => "mmd_fa mmd_fa-caret-square-right"
			)
		),
		"unused_buttons" => array(),
		"active_buttons" => array()
	);


	protected $i18n = array();


	public function __construct( $json = '' ) {
		if ( empty( $json ) ) :
			return false;
		endif;
		if ( ! mmd()->exists( $json ) ) :
			$toolbar_conf = [ "mmd_bold", "mmd_italic", "mmd_heading", "mmd_spell_check", "mmd_pipe", "mmd_quote", "mmd_unordered_list", "mmd_ordered_list", "mmd_pipe", "mmd_link", "mmd_wpsimage", "mmd_table", "mmd_pipe", "mmd_fullscreen", "mmd_side_by_side", "mmd_preview", "mmd_guide" ];
			mmd()->put_contents( $json, '{"my_buttons":' . json_encode( $toolbar_conf ) . '}' );
		endif;
		mmd()->clear_cache( $json );
		$toolbar_conf = mmd()->json_decode( $json, false );
		foreach ( $toolbar_conf->my_buttons as $idx => $button_slug ) :
			if ( strpos( $button_slug, "mmd_" ) === false ) :
				$toolbar_conf->my_buttons[ $idx ] = "mmd_" . $button_slug;
			endif;
		endforeach;
		$ext_buttons = apply_filters( 'mmd_toolbar_buttons', array() );
		if ( isset( $ext_buttons ) && is_array( $ext_buttons ) && count( $ext_buttons ) > 0 ) :
			$this->prop[ 'default_buttons' ] = array_merge( $this->prop[ 'default_buttons' ], $ext_buttons );
		endif;
		foreach ( $toolbar_conf->my_buttons as $button_slug ) :
			if ( ! in_array( $button_slug, $this->prop[ 'active_buttons' ] ) && isset( $this->prop[ 'default_buttons' ][ $button_slug ] ) ) :
				$this->prop[ 'active_buttons' ][] = array_merge( [ "slug" => $button_slug ], $this->prop[ 'default_buttons' ][ $button_slug ] );
			endif;
		endforeach;
		foreach ( $this->prop[ 'default_buttons' ] as $button_slug => $button_prop ) :
			if ( ! in_array( $button_slug, $toolbar_conf->my_buttons ) ) :
				$this->prop[ 'unused_buttons' ][] = array_merge( [ "slug" => $button_slug ], $button_prop );
			endif;
		endforeach;
		# Add a few pipes
		$this->prop[ 'unused_buttons' ][] = array( "slug" => "pipe" );
		$this->prop[ 'unused_buttons' ][] = array( "slug" => "pipe" );
		$this->prop[ 'unused_buttons' ][] = array( "slug" => "pipe" );
	}



	private function make_translations() {
		if ( ! isset( $this->i18n ) || ! is_array( $this->i18n ) || count( $this->i18n ) > 0 ) :
			return false;
		endif;
		$this->i18n[ 'mmd_pipe' ][ 'tooltip' ] = '';
		$this->i18n[ 'mmd_pipe' ][ 'label' ] = esc_html__( 'Pipe', 'markup-markdown' );
		$this->i18n[ 'mmd_bold' ][ 'tooltip' ] = esc_html__( 'Bold', 'markup-markdown' );
		$this->i18n[ 'mmd_bold' ][ 'label' ] = esc_html__( 'Bold', 'markup-markdown' );
		$this->i18n[ 'mmd_italic' ][ 'tooltip' ] = esc_html__( 'Italic', 'markup-markdown' );
		$this->i18n[ 'mmd_italic' ][ 'label' ] = esc_html__( 'Italic', 'markup-markdown' );
		$this->i18n[ 'mmd_strikethrough' ][ 'tooltip' ] = esc_html__( 'Strikethrough', 'markup-markdown' );
		$this->i18n[ 'mmd_strikethrough' ][ 'label' ] = esc_html__( 'Strikethrough', 'markup-markdown' );
		$this->i18n[ 'mmd_heading' ][ 'tooltip' ] = esc_html__( 'Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading' ][ 'label' ] = esc_html__( 'Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading_smaller' ][ 'tooltip' ] = esc_html__( 'Smaller Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading_smaller' ][ 'label' ] = esc_html__( 'Smaller Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading_bigger' ][ 'tooltip' ] = esc_html__( 'Bigger Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading_bigger' ][ 'label' ] = esc_html__( 'Bigger Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading_1' ][ 'tooltip' ] = esc_html__( 'Big Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading_1' ][ 'label' ] = esc_html__( 'Big Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading_2' ][ 'tooltip' ] = esc_html__( 'Medium Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading_2' ][ 'label' ] = esc_html__( 'Medium Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading_3' ][ 'tooltip' ] = esc_html__( 'Small Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_heading_3' ][ 'label' ] = esc_html__( 'Small Heading', 'markup-markdown' );
		$this->i18n[ 'mmd_code' ][ 'tooltip' ] = esc_html__( 'Code', 'markup-markdown' );
		$this->i18n[ 'mmd_code' ][ 'label' ] = esc_html__( 'Code', 'markup-markdown' );
		$this->i18n[ 'mmd_quote' ][ 'tooltip' ] = esc_html__( 'Quote', 'markup-markdown' );
		$this->i18n[ 'mmd_quote' ][ 'label' ] = esc_html__( 'Quote', 'markup-markdown' );
		$this->i18n[ 'mmd_unordered_list' ][ 'tooltip' ] = esc_html__( 'Generic List', 'markup-markdown' );
		$this->i18n[ 'mmd_unordered_list' ][ 'label' ] = esc_html__( 'List', 'markup-markdown' );
		$this->i18n[ 'mmd_ordered_list' ][ 'tooltip' ] = esc_html__( 'Numbered List', 'markup-markdown' );
		$this->i18n[ 'mmd_ordered_list' ][ 'label' ] = esc_html__( 'List', 'markup-markdown' );
		$this->i18n[ 'mmd_clean_block' ][ 'tooltip' ] = esc_html__( 'Clean block', 'markup-markdown' );
		$this->i18n[ 'mmd_clean_block' ][ 'label' ] = esc_html__( 'Clean', 'markup-markdown' );
		$this->i18n[ 'mmd_link' ][ 'tooltip' ] = esc_html__( 'Create Link', 'markup-markdown' );
		$this->i18n[ 'mmd_link' ][ 'label' ] = esc_html__( 'Link', 'markup-markdown' );
		$this->i18n[ 'mmd_wpsimage' ][ 'tooltip' ] = esc_html__( 'Insert or Upload Media', 'markup-markdown' );
		$this->i18n[ 'mmd_wpsimage' ][ 'label' ] = esc_html__( 'Media Library', 'markup-markdown' );
		$this->i18n[ 'mmd_table' ][ 'tooltip' ] = esc_html__( 'Insert Table', 'markup-markdown' );
		$this->i18n[ 'mmd_table' ][ 'label' ] = esc_html__( 'Table', 'markup-markdown' );
		$this->i18n[ 'mmd_horizontal_rule' ][ 'tooltip' ] = esc_html__( 'Insert Horizontal Line', 'markup-markdown' );
		$this->i18n[ 'mmd_horizontal_rule' ][ 'label' ] = esc_html__( 'Horizontal Line', 'markup-markdown' );
		$this->i18n[ 'mmd_preview' ][ 'tooltip' ] = esc_html__( 'Toggle Preview', 'markup-markdown' );
		$this->i18n[ 'mmd_preview' ][ 'label' ] = esc_html__( 'Preview', 'markup-markdown' );
		$this->i18n[ 'mmd_side_by_side' ][ 'tooltip' ] = esc_html__( 'Toggle Side by Side', 'markup-markdown' );
		$this->i18n[ 'mmd_side_by_side' ][ 'label' ] = esc_html__( 'Side by Side', 'markup-markdown' );
		$this->i18n[ 'mmd_fullscreen' ][ 'tooltip' ] = esc_html__( 'Toggle Fullscreen', 'markup-markdown' );
		$this->i18n[ 'mmd_fullscreen' ][ 'label' ] = esc_html__( 'Fullscreen', 'markup-markdown' );
		$this->i18n[ 'mmd_guide' ][ 'tooltip' ] = esc_html__( 'Markdown Guide', 'markup-markdown' );
		$this->i18n[ 'mmd_guide' ][ 'label' ] = esc_html__( 'Guide', 'markup-markdown' );
		$this->i18n[ 'mmd_undo' ][ 'tooltip' ] = esc_html__( 'Undo', 'markup-markdown' );
		$this->i18n[ 'mmd_undo' ][ 'label' ] = esc_html__( 'Undo', 'markup-markdown' );
		$this->i18n[ 'mmd_redo' ][ 'tooltip' ] = esc_html__( 'Redo', 'markup-markdown' );
		$this->i18n[ 'mmd_redo' ][ 'label' ] = esc_html__( 'Redo', 'markup-markdown' );
		$this->i18n[ 'mmd_spell_check' ][ 'tooltip' ] = esc_html__( 'Spellchecker', 'markup-markdown' );
		$this->i18n[ 'mmd_spell_check' ][ 'label' ] = esc_html__( 'Spellchecker', 'markup-markdown' );
		$this->i18n[ 'mmd_rtltextdir' ][ 'tooltip' ] = esc_html__( 'Switch text direction to right', 'markup-markdown' );
		$this->i18n[ 'mmd_rtltextdir' ][ 'label' ] = esc_html__( 'Right to Left text direction', 'markup-markdown' );
		$this->i18n[ 'mmd_ltrtextdir' ][ 'tooltip' ] = esc_html__( 'Switch text direction to left', 'markup-markdown' );
		$this->i18n[ 'mmd_ltrtextdir' ][ 'label' ] = esc_html__( 'Left to Right text direction', 'markup-markdown' );
		$ext_translations = apply_filters( 'mmd_button_translations', array() );
		if ( isset( $ext_translations ) && is_array( $ext_translations ) && count( $ext_translations ) > 0 ) :
			$this->i18n = array_merge( $this->i18n, $ext_translations );
		endif;
		return true;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) :
			if ( strpos( $name, 'button' ) !== false ) :
				$this->make_translations();
				foreach( $this->prop[ $name ] as $button_idx => $button_meta ) :
					if ( isset( $button_meta[ 'slug' ] ) && array_key_exists( $button_meta[ 'slug' ], $this->i18n ) ) :
						$this->prop[ $name ][ $button_idx ][ 'tooltip' ] = $this->i18n[ $button_meta[ 'slug' ] ][ 'tooltip' ];
						$this->prop[ $name ][ $button_idx ][ 'label' ] = $this->i18n[ $button_meta[ 'slug' ] ][ 'label' ];
					endif;
				endforeach;
			endif;
			return $this->prop[ $name ];
		endif;
		return 'mmd_undefined';
	}

}
