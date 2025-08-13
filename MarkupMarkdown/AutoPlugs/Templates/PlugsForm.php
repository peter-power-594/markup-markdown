<?php defined( 'ABSPATH' ) || exit; ?>

<div id="tab-plugs">
	<h2><?php esc_html_e( 'Autoplugs', 'markup-markdown' ); ?></h2>
	<p><?php esc_html_e( 'Bridges to activate markdown with existing plugins if available.', 'markup-markdown' ); ?></p>
	<table class="form-table" role="presentation">
		<tbody>
<?php
	if ( ! defined( 'MMD_AUTOPLUGS' ) || ! is_array( MMD_AUTOPLUGS ) ) :
		define( 'MMD_AUTOPLUGS', [] );
	endif;
	foreach( MMD_AUTOPLUGS as $slug => $active ) :
		$slug_class = esc_attr( strtolower( $slug ) );
?>
			<tr class="<?php echo esc_attr( 'site-plug-' . $slug_class ); ?>">
				<th scope="row">
				<?php
					if ( isset( $default_plugs ) && isset( $default_plugs[ $slug ] ) && is_array( $default_plugs[ $slug ] ) ) :
						if ( isset( $default_plugs[ $slug ][ 2 ] ) ) :
							echo '<a href="' . esc_attr( $default_plugs[ $slug ][ 1 ] ) . '" target="_blank" rel="nofollow">' . esc_html( $default_plugs[ $slug ][ 2 ] )  . '</a>';
						elseif ( isset( $default_plugs[ $slug ][ 1 ] ) ) :
							echo esc_html( $default_plugs[ $slug ][ 1 ] );
						else:
							echo esc_html( $slug );
						endif;
					else:
						echo esc_html( $slug );
					endif;
				?>
				</th>
				<td>
					<label for="<?php echo esc_attr( 'mmd_plug_' . $slug_class ); ?>">
						<input type="checkbox" name="mmd_plugs[]" id="mmd_plug_<?php echo esc_attr( $slug_class ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php echo isset( $active ) && (int)$active > 0  ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Activated', 'markup-markdown' ); ?>
					</label>
				</td>
			</tr>
<?php
	endforeach;
?>
		</tbody>
	</table>
</div>
