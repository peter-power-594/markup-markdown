<?php defined( 'ABSPATH' ) || exit; ?>

<div id="tab-codehighlight" class="vertical-rows">
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
					<label for="mmd_usecodehighlighter1">
						<input type="radio" name="mmd_usecodehighlighter" id="mmd_usecodehighlighter1" value="prism" <?php echo ! isset( $my_cnf[ 'code_highlighter' ] ) || $my_cnf[ 'code_highlighter' ] === 'prism' ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Prism.js rendering (Default)', 'markup-markdown' ); ?>
					</label>&nbsp;&nbsp;
					<label for="mmd_usecodehighlighter2">
						<input type="radio" name="mmd_usecodehighlighter" id="mmd_usecodehighlighter2" value="highlight" <?php echo isset( $my_cnf[ 'code_highlighter' ] ) && $my_cnf[ 'code_highlighter' ] === 'highlight' ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Highlight.js rendering', 'markup-markdown' ); ?>
					</label>&nbsp;&nbsp;<br />
					<em><?php esc_html_e( 'Dark mode not supported on the backend, Visual Studio based theme is setup by default for the admin screen and the built-in preview.' ); ?></em>
				</td>
			</tr>
			<tr class="site-load-front">
				<th scope="row">
					<?php esc_html_e( 'Load assets', 'markup-markdown' ); ?>
				</th>
				<td>
					<label for="code_highlighter_front">
						<input type="checkbox" name="mmd_codehighlighter_front" id="code_highlighter_front" value="1" <?php echo isset( $my_cnf[ 'code_highlighter_front' ] ) && (int)$my_cnf[ 'code_highlighter_front' ] > 0 ? 'checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Activate syntax highlighting on the frontend as well (Disabled by default)', 'markup-markdown' ); ?><br />
						<em><?php esc_html_e( 'Useful if your theme does not support by default syntax highlighting for code snippets.', 'markup-markdown' ); ?></em>
					</label>
				</td>
			</tr>
			<tr class="site-pickup-theme">
				<th scope="row">
					<?php esc_html_e( 'Theme', 'markup-markdown' ); ?>
				</th>
				<td>
					<select name="mmd_codehighlighter_theme" id="code_highlighter_theme">
						<option value="#"><?php esc_html_e( 'Please select a theme', 'markup-markdown' ); ?></option>
					<?php
					if ( ! preg_match( '#^(prism|hl)-#', $my_theme ) ) :
						$my_theme = 'prism-' . $my_theme;
					endif;
					foreach( $my_themes as $engine_slug => $engine_themes ) :
						$engine_slug = str_replace( 'js', '', $engine_slug );
						printf( '<optgroup label="%s" class="%s">', esc_attr( strtoupper( $engine_slug ) ), esc_attr( $engine_slug ) );
						foreach( $engine_themes as $theme_slug => $theme_label ) :
							printf( '<option value="%s"%s>%s</option>', esc_attr( $theme_slug ), $theme_slug === $my_theme ? ' selected="selected"' : '', esc_html( $theme_label ) );
						endforeach;
						printf( '</optgroup>' );
					endforeach;
					?>
					</select>
					<br>
					<em><?php esc_html_e( 'Dark themes are available, the selected theme will only be applied to the frontend when activated.', 'markup-markdown' ); ?></em><br>
					<a href="https://github.com/PrismJS/prism-themes" target="_blank" rel="nofollow"><?php esc_html_e( 'View the previews of Prism.js themes' ); ?></a> /
					<a href="https://highlightjs.org/demo" target="_blank" rel="nofollow"><?php esc_html_e( 'Test in live Highlight.js themes' ); ?></a>
				</td>
			</tr>
		</tbody>
	</table>
</div><!-- #tab-latex  -->
