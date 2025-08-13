<?php

defined( 'ABSPATH' ) || exit;

$blog_conf_file = mmd()->conf_blog_prefix . 'conf.php';
if ( mmd()->exists( $blog_conf_file ) ) :
	printf( '<div id="mmd_debug_settings">' );
	printf( '<h3>%s</h3>', esc_html( 'Below is the summary of the settings used for the current blog:' ) );
	$blog_conf = mmd()->get_contents( $blog_conf_file );
	$blog_conf = str_replace( [ '<?php', '?>', 'define(', ')', '[', ']' ], '', $blog_conf );
	$blog_conf = preg_replace( '#defined.*?;#', '', $blog_conf );
	$blog_conf = str_replace( array( '\',', '\'', ';' ), ' | ', $blog_conf );
	$blog_conf = preg_replace( '#\n[\s\t]*#', "\n", $blog_conf );
	if ( defined( 'MMD_ADDONS' ) ) :
		$blog_conf .= "\n" . '| MMD_ADDONS | "' . implode( '", "', MMD_ADDONS ) . '" |';
	endif;
	if ( defined( 'MMD_AUTOPLUGS' ) ) :
		$active_plugs = array();
		foreach( MMD_AUTOPLUGS as $plug_name => $plug_bool ) :
			if ( (int)$plug_bool > 0 ) :
				$active_plugs[] = $plug_name;
			endif;
		endforeach;
		$blog_conf .= "\n" . '| MMD_AUTOPLUGS | "' . implode( '", "', $active_plugs ) . '" |';
	endif;
	$blog_conf = '| ' . esc_html( 'Constants', 'markup-markdown' )
		. ' | ' . esc_html( 'Values', 'markup-markdown' ) . ' |'
		. "\n" . '| ---- | ---- |' . "\n" . $blog_conf . "\n";
	$blog_conf = preg_replace( '#\n+#', "\n", $blog_conf );
	printf( '%s', wp_kses( str_replace( '<table>', '<table class="wp-list-table widefat fixed striped table-view-list">', mmd()->markdown2html( $blog_conf ) ), array( 'table' => array( 'class' => true ), 'thead' => array(), 'tbody' => array(), 'tr' => array(), 'th' => array(), 'td' => array(), 'br' => array() ) ) );	
	printf( '</div>' );
endif;

printf( '<div id="mmd_debug_plugins">' );
# https://stackoverflow.com/questions/20488264/how-do-i-get-activated-plugin-list-in-wordpress-plugin-development
printf( '<h3>%s</h3>', esc_html( 'List of active plugins for the current blog:' ) );
$blog_plugins = esc_html( 'Plugin', 'markup-markdown' )
	. ' | ' . esc_html( 'Version', 'markup-markdown' )
	. ' | ' . esc_html( 'Description', 'markup-markdown' ) . ' |'
	. "\n" . '| ---- | ---- | ---- |' . "\n";
$apl = get_option( 'active_plugins' );
$plugins = get_plugins();
foreach ( $apl as $p ) :
	if ( isset( $plugins[ $p ] ) ) :
		$blog_plugins .= '| ';
		if ( isset( $plugins[ $p ][ 'PluginURI' ] ) && ! empty( $plugins[ $p ][ 'PluginURI' ] ) ) :
			$blog_plugins .= '[' . $plugins[ $p ][ 'Name' ] . '](' .  $plugins[ $p ][ 'PluginURI' ] . ')';
		else :
			$blog_plugins .= $plugins[ $p ][ 'Name' ];
		endif;
		$blog_plugins .= ' | ' . ( isset( $plugins[ $p ][ 'Version' ] ) ? $plugins[ $p ][ 'Version' ] : '' );
		$blog_plugins .= ' | ' . $plugins[ $p ][ 'Description' ] . ' |' . "\n";
	endif;
endforeach;
printf( '%s', wp_kses( str_replace( '<table>', '<table class="wp-list-table widefat fixed striped table-view-list">', mmd()->markdown2html( $blog_plugins ) ), array( 'table' => array( 'class' => true ), 'thead' => array(), 'tbody' => array(), 'tr' => array(), 'th' => array(), 'td' => array(), 'br' => array(), 'a' => array( 'href' => array() ) ) ) );	
printf( '</div>' );

printf( '<h2>%s</h2>', esc_html( 'Information to attach to the support ticket if need be:' ) );
printf( '%s', '<pre id="mmd_debug_info"></pre>' );
