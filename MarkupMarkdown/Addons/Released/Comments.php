<?php

namespace MarkupMarkdown\Addons\Released;

defined( 'ABSPATH' ) || exit;

if ( defined( 'MMD_ADDONS_LOADED' ) ) :
	return false;
endif;


final class Comments {


	private $prop = array(
		'slug' => 'comments',
		'release' => 'stable',
		'active' => 0
	);


	private $comments_tags_conf = '';


	private $allowed_html = array();


	public function __construct() {
		$this->comments_tags_conf = mmd()->conf_blog_prefix . 'conf_comments_tags.json';
		if ( ! defined( 'MMD_ADDONS' ) || ( defined( 'MMD_ADDONS' ) && in_array( $this->prop[ 'slug' ], MMD_ADDONS ) === false ) ) :
			$this->prop[ 'active' ] = 0;
			return false; # Addon has been desactivated
		endif;
		if ( is_admin() ) :
			add_filter( 'mmd_verified_config', array( $this, 'update_config' ) );
			add_filter( 'mmd_var2const', array( $this, 'create_json' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_layout_assets' ) );
		else :
			add_filter( 'comment_text', array( $this, 'mmd_comments_text' ), 11, 2 );
		endif;
	}


	/**
	 * Filter to parse layout options inside the options screen when the form was submitted
	 *
	 * @since 3.17.0
	 * @access public
	 *
	 * @return Void
	 */
	final public function update_config( $my_cnf ) {
		$comment_tag_names = filter_input( INPUT_POST, 'comment_tag', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY );
		if ( ! isset( $comment_tag_names ) || ! is_array( $comment_tag_names ) ) :
			return $my_cnf;
		endif;
		$comment_tags = array();
		foreach( $comment_tag_names as $tag_name => $tag_val ) :
			$comment_tag_attrs = filter_input( INPUT_POST, 'comment_tag_' . $tag_name . '_attr', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY );
			$comment_tag_attrs[ 'active' ] = 1;
			$comment_tags[ $tag_name ] = $comment_tag_attrs;
		endforeach;
 		$my_cnf[ 'comment_tags' ] = $comment_tags;
		unset( $comment_tags );
		return $my_cnf;
	}
	final public function create_json( $my_cnf ) {
		if ( isset( $my_cnf[ 'comment_tags' ] ) && is_array( $my_cnf[ 'comment_tags' ] ) && count( $my_cnf[ 'comment_tags' ] ) > 0 ) :
			mmd()->put_contents( $this->comments_tags_conf, json_encode( $my_cnf[ 'comment_tags' ] ) );
		endif;
		unset( $my_cnf[ 'comment_tags' ] );
		return $my_cnf; # Required
	}


	final public function load_layout_assets( $hook ) {
		if ( 'settings_page_markup-markdown-admin' === $hook ) :
			add_action( 'mmd_tabmenu_options', array( $this, 'add_tabmenu' ) );
			add_action( 'mmd_tabcontent_options', array( $this, 'add_tabcontent' ) );
		endif;
	}


	/**
	 * Add the layout menu item inside the options screen
	 *
	 * @since 3.17.0
	 * @access public
	 *
	 * @return Void
	 */
	final public function add_tabmenu() {
		echo "\t\t\t\t\t\t<li><a href=\"#tab-comments\" class=\"mmd-ico ico-dialog\">" . esc_html__( 'Comments', 'markup-markdown' ) . "</a></li>\n";
	}


	/**
	 * Display layout options inside the options screen
	 *
	 * @since 3.17.0
	 * @access public
	 *
	 * @return Void
	 */
	final public function add_tabcontent() {
		$conf_file = mmd()->conf_blog_prefix . 'conf.php';
		if ( mmd()->exists( $conf_file ) ) :
			require_once $conf_file;
		endif;
		$my_tmpl = mmd()->plugin_dir . '/MarkupMarkdown/Addons/Released/Templates/CommentsForm.php';
		if ( mmd()->exists( $my_tmpl ) ) :
			$comments_tags_conf = $this->comments_tags_conf;
			mmd()->clear_cache( $my_tmpl );
			include $my_tmpl;
		endif;
	}


	/**
	 * Parse the comment content's markdown and filter the HTML output
	 *
	 * @since 3.17.0
	 * @access public
	 *
	 * @return Void
	 */
	final public function mmd_comments_text( $text = '', $comment = null ) {
		if ( ! isset( $comment ) || ! is_object( $comment ) || ! isset( $comment->comment_content ) ) :
			return $text;
		endif;
		$comment_body = apply_filters( 'field_markdown2html', $comment->comment_content );
		if ( ! count( $this->allowed_html ) ) :
			require_once mmd()->plugin_dir . "/MarkupMarkdown/Addons/Released/Media/CommentsTags.php";
			$my_toolbar = new \MarkupMarkdown\Addons\Released\Media\CommentsTags( $this->comments_tags_conf , true );
			$this->allowed_html = $my_toolbar->allowed_tags;
			unset( $my_toolbar );
		endif;
		$santized_content = wp_kses( $comment_body, $this->allowed_html );
		return $santized_content;
	}


	public function __get( $name ) {
		if ( array_key_exists( $name, $this->prop ) ) :
			return $this->prop[ $name ];
		elseif ( $name === 'label' ) :
			return esc_html__( 'Comments', 'markup-markdown' );
		elseif ( $name === 'desc' ) :
			return esc_html__( 'Use markdown inside your comments', 'markup-markdown' );
		endif;
		return 'mmd_undefined';
	}


}


return apply_filters( 'mmd_load_addon', 'comments', new \MarkupMarkdown\Addons\Released\Comments() );