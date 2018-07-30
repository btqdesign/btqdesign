<?php
	
// Establece el tipo de archivo como texto plano
header("Content-Type:text/plain");

 // Enforce the use of HTTPS
//header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
// Prevent Clickjacking
header("X-Frame-Options: SAMEORIGIN");
// Prevent XSS Attack
header("Content-Security-Policy: default-src 'self';"); // FF 23+ Chrome 25+ Safari 7+ Opera 19+
header("X-Content-Security-Policy: default-src 'self';"); // IE 10+
// Block Access If XSS Attack Is Suspected
header("X-XSS-Protection: 1; mode=block");
// Prevent MIME-Type Sniffing
header("X-Content-Type-Options: nosniff");
// Referrer Policy
header("Referrer-Policy: no-referrer-when-downgrade");

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
}
else {
	$varPost = $_POST;
	$userCan = current_user_can( 'read' );
	
	$exportUserCan = var_export($userCan, true);
	$exportPost = var_export($varPost, true);
	
	echo 'User can: ' . $exportUserCan . "\n\n";
	echo '_POST: ' . $exportPost;
}