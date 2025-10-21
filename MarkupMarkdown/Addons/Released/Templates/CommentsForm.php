<?php defined( 'ABSPATH' ) || exit; ?>


<div id="tab-comments" class="vertical-rows">
	<h2><?php esc_html_e( 'Comments', 'markup-markdown' ); ?></h2>
	<p><?php esc_html_e( 'Use markdown inside your comments.', 'markup-markdown' ); ?></p>
	<table class="form-table" role="presentation">
		<tbody>
<?php
	require_once mmd()->plugin_dir . "/MarkupMarkdown/Addons/Released/Media/CommentsTags.php";
	$my_toolbar = new \MarkupMarkdown\Addons\Released\Media\CommentsTags( $comments_tags_conf );
	foreach( $my_toolbar->default_tags as $default_tag => $default_attrs ) :
		$my_attrs = isset( $my_toolbar->allowed_tags->{$default_tag} ) ? $my_toolbar->allowed_tags->{$default_tag} : [];
?>
			<tr><?php
				printf( '<th scope="row"><label for="comment_tag_%s"><input id="comment_tag_%s" name="comment_tag[%s]" type="checkbox" value="1" %s/> %s</label></th>', esc_attr( $default_tag ), esc_attr( $default_tag ), esc_attr( $default_tag ), isset( $my_attrs->active ) ? ' checked="checked"' : '', esc_html( strtoupper( $default_tag ) ) );
				printf( '<td>' );
				unset( $default_attrs[ 'active' ] );
				foreach( $default_attrs as $attr_name => $attr_value ) :
					printf( '<label for="comment_tag_%s_attr_%s"><input id="comment_tag_%s_attr_%s" name="comment_tag_%s_attr[%s]" type="checkbox" value="1" %s/> %s</label>  &nbsp; ', esc_attr( $default_tag ), esc_attr( $attr_name ), esc_attr( $default_tag ), esc_attr( $attr_name ), esc_attr( $default_tag ), esc_attr( $attr_name ), isset( $my_attrs->{$attr_name} ) ? ' checked="checked"' : '', esc_html( $attr_name ) );
				endforeach;
				printf( '</td>' );
			?></tr>
<?php
	endforeach;
?>
		</tbody>
	</table>
</div>
