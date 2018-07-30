<?php

// Establece el tipo de archivo como texto plano
header("Content-Type:application/json");

// Obtiene la configuracion de WordPress
require_once('../../../wp-config.php');

function prowpl_ga_crypt($string, $salt) {
	$hash = strtolower( md5( strrev( crypt( $string, $salt ) ) ) );
	return $hash;
}

// Si es un usuario de WordPress que puede leer
if ( current_user_can( 'read' ) ) {
	// Obtiene la libreria de GoogleAuthenticator
	require_once('lib/GoogleAuthenticator.php');
	
	// Obtiene la informacion del usuario actual
	$current_user = wp_get_current_user();
	
	// Genera el hash y el codigo QR
	$ga = new PHPGangsta_GoogleAuthenticator();
	$ga_secret = $ga->createSecret();
	
	// Guarda en el meta ga_otpauth del usuario el nuevo hash
	update_user_meta( $current_user->ID, 'ga_otpauth', $ga_secret );
	
	// Genera la URL OTPauth para la aplicacion de Google Authenticator
	$ga_url = 'otpauth://totp/' . str_replace( array(' ','@'), '-', strtolower( $current_user->user_login ) ) . '@' . str_replace(' ', '-', strtolower( get_bloginfo( 'name' ) ) ) . '?secret=' . $ga_secret;
	
	//set it to writable location, a place for temp generated PNG files
	$PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
	//html PNG location prefix
	$PNG_WEB_DIR = 'temp/';
	
	// Libreria para generar el código QR en un archivo con formato PNG
	require_once('lib/phpqrcode/qrlib.php');    
	//ofcourse we need rights to create temp dir
	if (!file_exists($PNG_TEMP_DIR))
	mkdir($PNG_TEMP_DIR);
	// Archivo PNG temporal con el código QR
	$ga_qr_png = $PNG_TEMP_DIR . prowpl_ga_crypt( $ga_secret, $current_user->user_login ) . '.png';
	// Genera el código QR en el archivo temporal PNG
	QRcode::png($ga_url, $ga_qr_png, 'M', 7, 2);
	// Codifica el archivo PNG en Base64 
	$ga_qr_image_src = 'data:image/png;base64,' . base64_encode( file_get_contents( $ga_qr_png ) );
	
	
	// Imprime el resultado
	echo '{"ga_code":"'.prowpl_ga_crypt($ga_secret, time()).'", "ga_url":"'.addslashes($ga_url).'", "ga_image":"'.addslashes($ga_qr_image_src).'"}';
	
	// Elimina el archivo temporal de la imagen con el codigo QR
	unlink($ga_qr_png);
}