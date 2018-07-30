<?php
	
// Establece el tipo de archivo como texto plano
header("Content-Type:text/plain");

// Obtiene la configuracion de WordPress
require_once('../../../wp-config.php');

// Si es un usuario de WordPress que puede leer
if ( current_user_can( 'read' ) && isset( $_POST['code'] ) ) {
	$code = $_POST['code'];
	
	// Obtiene la informacion del usuario actual
	$current_user = wp_get_current_user();
	// Obtiene la clave secreta
	$ga_secret = get_user_meta($current_user->ID, 'ga_otpauth', true);
	
	// Obtiene la libreria de GoogleAuthenticator
	require_once('lib/GoogleAuthenticator.php');
	// Verifica el codigo
	$ga = new PHPGangsta_GoogleAuthenticator();
	$is_valid = $ga->verifyCode($ga_secret, $code);
	
	$out = $is_valid ? '1' : '0';
	
	echo $out;
	
	//wp_die();
}