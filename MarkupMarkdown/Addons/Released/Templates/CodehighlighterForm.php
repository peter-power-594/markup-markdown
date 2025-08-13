<?php defined( 'ABSPATH' ) || exit; ?>

<div id="tab-codehighlight">
	<h2><?php esc_html_e( 'Syntax Highlighting', 'markup-markdown' ); ?></h2>
	<p><?php esc_html_e( 'Colorful syntax highlighting for your snippets code.', 'markup-markdown' ); ?></p>
	<table class="form-table" role="presentation">
		<tbody>
<?php
	$my_cnf = array(
		'code_highlighter' => 'none',
		'code_highlighter_front' => 0,
		'code_highlighter_theme' => 'vs'
	);
	if ( defined( 'MMD_USE_CODEHIGHLIGHT' ) && is_array( MMD_USE_CODEHIGHLIGHT ) ) :
		if ( isset( MMD_USE_CODEHIGHLIGHT[ 1 ] ) ) :
			$my_cnf[ 'code_highlighter' ] = MMD_USE_CODEHIGHLIGHT[ 1 ];
		endif;
		if ( isset( MMD_USE_CODEHIGHLIGHT[ 2 ] ) ) :
			$my_cnf[ 'code_highlighter_front' ] = MMD_USE_CODEHIGHLIGHT[ 2 ];
		endif;
		if ( isset( MMD_USE_CODEHIGHLIGHT[ 3 ] ) ) :
			$my_cnf[ 'code_highlighter_theme' ] = MMD_USE_CODEHIGHLIGHT[ 3 ];
		endif;
	endif;
?>
			<tr class="site-use-codehighlighter">
				<th scope="row">
					<?php esc_html_e( 'Rendering engine', 'markup-markdown' ); ?>
				</th>
				<td>
					<label for="mmd_usecodehighlighter0">
						<input type="radio" name="mmd_usecodehighlighter" id="mmd_usecodehighlighter0" value="none" <?php echo ! isset( $my_cnf[ 'code_highlighter' ] ) || $my_cnf[ 'code_highlighter' ] === 'none' ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'None', 'markup-markdown' ); ?>
					</label>&nbsp;&nbsp;
					<label for="mmd_usecodehighlighter1">
						<input type="radio" name="mmd_usecodehighlighter" id="mmd_usecodehighlighter1" value="prism" <?php echo isset( $my_cnf[ 'code_highlighter' ] ) && $my_cnf[ 'code_highlighter' ] === 'prism' ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Prism.js rendering', 'markup-markdown' ); ?>
					</label>&nbsp;&nbsp;
				</td>
			</tr>
			<tr class="site-load-front">
				<th scope="row">
					<?php esc_html_e( 'Load assets', 'markup-markdown' ); ?>
				</th>
				<td>
					<label for="code_highlighter_front">
						<input type="checkbox" name="mmd_codehighlighter_front" id="code_highlighter_front" value="1" <?php echo isset( $my_cnf[ 'code_highlighter_front' ] ) && (int)$my_cnf[ 'code_highlighter_front' ] > 0 ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Activate syntax highlighting on the frontend. (Loaded only on the edit screen by default)', 'markup-markdown' ); ?>
					</label>
				</td>
			</tr>
			<tr class="site-pickup-theme">
				<th scope="row">
					<?php esc_html_e( 'Theme', 'markup-markdown' ); ?>
				</th>
				<td>
					<select name="mmd_codehighlighter_theme" id="code_highlighter_theme">
						<option value="vs"><?php esc_html_e( 'Please select a skin', 'markup-markdown' ); ?></option>
					<?php
						foreach( $my_themes as $theme_slug => $theme_label ) :
							printf( '<option value="%s"%s>%s</option>', esc_attr( $theme_slug ), $theme_slug === $my_theme ? ' selected="selected"' : '', esc_html( $theme_label ) );
						endforeach;
					?>
					</select> <a href="https://github.com/PrismJS/prism-themes" target="_blank" rel="nofollow"><?php esc_html_e( 'Preview of skins' ); ?></a>
				</td>
			</tr>
		</tbody>
	</table>
</div><!-- #tab-latex  -->
