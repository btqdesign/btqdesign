<?php

/**
 * Output Buffering
 *
 * Buffers the entire WP process, capturing the final output for manipulation.
 */

ob_start();

add_action(
	'shutdown', 
	function() {
	    $final = '';
	
	    // We'll need to get the number of ob levels we're in, so that we can iterate over each, collecting
	    // that buffer's output into the final output.
	    $levels = ob_get_level();
	
	    for ($i = 0; $i < $levels; $i++)
	    {
	        $final .= ob_get_clean();
	    }
	
	    // Apply any filters to the final output
		echo apply_filters('final_output', $final);
	}, 
	0
);

add_filter('final_output', function($output) {
	// Soporte HTTPS
	$output = str_replace('http:', 'https:', $output);

	// Corrige errores con esquemas establecidos en http y no https
	$output = str_replace('https://schemas.xmlsoap.org', 'http://schemas.xmlsoap.org', $output);
	$output = str_replace('https://docs.oasisopen.org', 'http://docs.oasisopen.org', $output);
	$output = str_replace('https://www.sitemaps.org', 'http://www.sitemaps.org', $output);
	$output = str_replace('https://ogp.me', 'http://ogp.me', $output);
	
	$output = str_replace(" type='text/javascript'", '', $output);
	$output = str_replace(' type="text/javascript"', '', $output);
	$output = str_replace(" type='text/css'", '', $output);
	$output = str_replace(' type="text/css"', '', $output);

	// Optimizacion de cache, elimina query vars de version y ver
	$patt = array(
	'/(("|\')https?:\/\/((btqdesign\.idevol\.net)|(btqdesign\.com))\/[a-zA-Z0-9\._\/-]*\.(css|js))(\?(ver|version)=[a-zA-Z0-9%_.-]{1,8})("|\')/'
	,'/(("|\')https?:\\\\\/\\\\\/((btqdesign\.idevol\.net)|(btqdesign\.com))\\\\\/[a-zA-Z0-9\._\\\\\/-]*\.(css|js))(\?(ver|version)=[a-zA-Z0-9%_.-]{1,8})("|\')/'
	);
	$repl = array(
	'${1}${9}'
	,'${1}${9}'
	);
	$output = preg_replace($patt, $repl, $output);    
	
	return $output;
});

add_filter('wp_redirect', function($output) {
	$output = str_replace('http://btqdesign.idevol.net', 'https://btqdesign.idevol.net', $output);
	$output = str_replace('http://btqdesign.com', 'https://btqdesign.com', $output);
	return $output;
});
