<?php
/**
 * Plugin Name: BTQ WP Login
 * Plugin URI: http://btqdesign.com/plugins/btq-wp-login/
 * Description: Control de la salida del sitio WordPress
 * Version: 1.0
 * Author: BTQ Design
 * Author URI: http://btqdesign.com/
 * Requires at least: 4.9.7
 * Tested up to: 4.9.7
 *
 * Text Domain: btq-wp-login
 * Domain Path: /languages
 *
 * @package btq-wp-login
 * @category Core
 * @author BTQ Design
 */
 
class BTQ_WP_Login {
	
	public function __construct() {	        
		return $this;
	}
	
	// Valida que NO! sea un correo electrónico al iniciar la sesion por /wp-login.php
	public function btq_wp_login_noemail( $user ) {
		// Si proviene de /wp-login.php
		if ( preg_match('/\/wp-login.php/', $_SERVER['HTTP_REFERER']) ) {
			if ( isset($_POST['log']) ) {
				if ( filter_var($_POST['log'], FILTER_VALIDATE_EMAIL) ) {
					$user = new WP_Error( 'invalid_username', __('<strong>ERROR</strong>: Invalid username.', 'btq-wp-login') );
				}
			}
		}
		
		return $user;
	}
	
	
	/** reCAPTCHA **/
	// Get a key from https://www.google.com/recaptcha/admin
	public $btq_wp_login_recaptcha_site_key   = '6LeUG2cUAAAAAHE6mv9EOHcxkhmWYdTe-4Yxv60L';
	public $btq_wp_login_recaptcha_secret_key = '6LeUG2cUAAAAANMCkIw3yo7uojW6f4Jnyfy9WVtQ';
	public $btq_wp_login_recaptcha_lang       = 'es-419';
	public $btq_wp_login_recaptcha_fails      = 1;
	
	// Inicia la sesión, necesaria para activar el reCAPTCHA
	public function btq_wp_login_recaptcha_session_start() {
		if ( !session_id() ) session_start();
	}
	/* add_action('init', 'btq_wp_login_recaptcha_session_start'); */
	
	// Establece los script's necesarios para personalizar el reCAPTCHA versión 2.0
	public function btq_wp_login_recaptcha_header() {
		?>
		<style>
			/** Corrige ubicacion del login **/
			#login { padding-top: 2.7% !important; }
			.g-recaptcha { margin-bottom: 1.5em; }
		</style>
		<?php
	}

	// Integra al formulario de /wp-login.php los campos de reCAPTCHA versión 2.0
	public function btq_wp_login_recaptcha_form() {
		?>
		<div class="g-recaptcha" data-sitekey="<?php echo $this->btq_wp_login_recaptcha_site_key; ?>" style="position: relative; left: -15px;"></div>
		<script src="https://www.google.com/recaptcha/api.js?hl=<?php echo $this->btq_wp_login_recaptcha_lang; ?>"></script>
		<?php 
	}

	// Integra al formulario el campo de reCAPTCHA versión 2.0 en /wp-login.php
	public function btq_wp_login_recaptcha_login_errors( $errors, $redirect_to = '' ) {
		// Si el login require el reCAPTCHA
		if (isset($_SESSION['user_recaptcha_require'])) {
			if ($_SESSION['user_recaptcha_require'] >= $this->btq_wp_login_recaptcha_fails) {
				add_action('login_head', array($this,'btq_wp_login_recaptcha_header'));
				add_action('login_form', array($this,'btq_wp_login_recaptcha_form'));
			}
		}
		else {
			if ( 
				in_array( 'recaptcha_invalid', $errors->get_error_codes() ) ||
				in_array( 'recaptcha_required', $errors->get_error_codes() ) ||
				in_array( 'authentication_failed', $errors->get_error_codes() ) ||
				in_array( 'invalid_username', $errors->get_error_codes() ) ||
				in_array( 'user_login', $errors->get_error_codes() ) ||
				in_array( 'pass', $errors->get_error_codes() ) ||
				in_array( 'empty_username', $errors->get_error_codes() ) ||
				in_array( 'incorrect_password', $errors->get_error_codes() )
			) {
				add_action('login_head', array($this,'btq_wp_login_recaptcha_header'));
				add_action('login_form', array($this,'btq_wp_login_recaptcha_form'));
			}
		}
		return $errors;
	}

	// Aplica la validacion de reCAPTCHA versión 2.0 en /wp-login.php al momento de iniciar sesión
	public function btq_wp_login_recaptcha_validate( $user, $password = '' ) {
		
		// Si proviene de /wp-login.php
		if ( preg_match('/\/wp-login.php/', $_SERVER['HTTP_REFERER']) ) {
			// Obtiene el metadato de si hubo un error anterior al iniciar sesión
			$user_recaptcha_require = get_user_meta( $user->ID, 'recaptcha_require', true );
			if ( !is_numeric($user_recaptcha_require) ) {
				$user_recaptcha_require = 0;
			}
			else {
				$user_recaptcha_require = (int)$user_recaptcha_require;
			}
			
			// Si hubo mas de tres errores al iniciar la sesión solicitara reCAPTCHA
			if ( $user_recaptcha_require >= $this->btq_wp_login_recaptcha_fails || isset($_POST['g-recaptcha-response']) ) {
				// Si el campo de reCAPTCHA existe
				if ( isset($_POST['g-recaptcha-response']) ) {
					// Obtiene la libraria de reCAPTCHA
					require_once('lib/recaptchalib.php');
					// Se crea el objeto
					$reCaptcha = new ReCaptcha($this->btq_wp_login_recaptcha_secret_key);
					// respuesta de reCAPTCHA
					$resp = null;
					$resp = $reCaptcha->verifyResponse(
						$_SERVER['REMOTE_ADDR'],
						$_POST['g-recaptcha-response']
					);
					// Si el reCAPTCHA es invalido
					if (!($resp != null && $resp->success)) {
						// Obtiene el numero de intentos de ingresar un reCAPTCHA invalido
						$user_recaptcha_invalid = get_user_meta( $user->ID, 'recaptcha_invalid', true );
						if ( !is_numeric($user_recaptcha_invalid) ) {
							update_user_meta( $user->ID, 'recaptcha_invalid', '1');
						}
						else {
							$user_recaptcha_invalid = (int)$user_recaptcha_invalid;
							update_user_meta( $user->ID, 'recaptcha_invalid', ++$user_recaptcha_invalid );
						}
						// Genera el error
						$user = new WP_Error( 'recaptcha_invalid', __('<strong>ERROR</strong>: Invalid reCAPTCHA.', 'btq-wp-login') );
					}
				}
				else {
					// Obtiene el numero de intentos ivalidos donde se requirio el reCAPTCHA
					$user_recaptcha_required = get_user_meta( $user->ID, 'recaptcha_required', true );
					if ( !is_numeric($user_recaptcha_required) ) {
						update_user_meta( $user->ID, 'recaptcha_required', '1');
					}
					else {
						$user_recaptcha_required = (int)$user_recaptcha_required;
						update_user_meta( $user->ID, 'recaptcha_required', ++$user_recaptcha_required );
					}
					// Genera el error
					$user = new WP_Error( 'recaptcha_required', __('<strong>ERROR</strong>: reCAPTCHA is required.', 'btq-wp-login') );
				}
			}
			else {
				// Si no es un login correcto
				if ( !wp_check_password( $password, $user->user_pass, $user->ID ) ) {
					// Actualiza el metadato con el número de errores al iniciar sesión
					update_user_meta( $user->ID, 'recaptcha_require', ++$user_recaptcha_require );
					// Actualiza la variable de sessión con el número de errores al iniciar sesión
					$_SESSION['user_recaptcha_require'] = $user_recaptcha_require;
				}
			}
		}

		return $user;
	}
	
	// Restablece los metadatos del usuario que uso reCAPTCHA
	public function btq_wp_login_recaptcha_login( $user_login ) {
		// Obtiene la informacion del usuario
		$user = get_user_by( 'login', $user_login );
		
		// Restablece los metadatos del usuario que uso reCAPTCHA 
		if( get_user_meta( $user->ID, 'recaptcha_require', true ) != '' ){
			update_user_meta( $user->ID, 'recaptcha_require', '0' );
		}
		if( get_user_meta( $user->ID, 'recaptcha_required', true ) != '' ){
			update_user_meta( $user->ID, 'recaptcha_required', '0' );
		}
		if( get_user_meta( $user->ID, 'recaptcha_invalid', true ) != '' ){
			update_user_meta( $user->ID, 'recaptcha_invalid', '0' );
		}
		
		// Restablece la variable de sessión con el número de errores al iniciar sesión
		$_SESSION['user_recaptcha_require'] = 0;
	}





	/** GoogleAuthenticator **/
	private function btq_wp_login_ga_crypt($string, $salt) {
		$hash = strtolower( md5( strrev( crypt( $string, $salt ) ) ) );
		return $hash;
	}

	public function btq_wp_login_ga_profile_field() {
		// Si es la pagina de perfil y tiene rol de administrador
		if ( defined( 'IS_PROFILE_PAGE' ) && current_user_can( 'read' ) ) {
			// Datos del usuario
			$current_user = wp_get_current_user();
			
			// Obtiene el hash OTPauth asignado al usuario
			$ga_secret = get_user_meta( $current_user->ID, 'ga_otpauth', true );
			
			// Si no tiene el hash OTPauth
			if ( $ga_secret == '' ) {
				// Libreria de Google Authenticator
				require_once('lib/GoogleAuthenticator.php');
				// Nueva clave secreta
				$ga = new PHPGangsta_GoogleAuthenticator();
				$ga_secret = $ga->createSecret();
				// Guarda en el meta ga_otpauth del usuario el nuevo hash
				update_user_meta( $current_user->ID, 'ga_otpauth', $ga_secret );
			}
			
			// Genera la URL OTPauth para la aplicacion de Google Authenticator
			$ga_url = 'otpauth://totp/' . str_replace( array(' ','@'), '-', strtolower( $current_user->user_login ) ) . '@' . str_replace(' ', '-', strtolower( get_bloginfo( 'name' ) ) ) . '?secret=' . $ga_secret;
			
			// Libreria para detectar dispositivos moviles (teléfonos y tabletas)
			require_once('lib/Mobile_Detect.php');
			$detect = new Mobile_Detect;
			
			// Si no es un dispositivo movil
			if ( !( $detect->isMobile() && !$detect->isTablet() ) ) {
				//set it to writable location, a place for temporal generated PNG files
				$PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR;
				//html PNG location prefix
				$PNG_WEB_DIR = 'tmp/';
				
				// Libreria para generar el código QR en un archivo con formato PNG
				require_once('lib/phpqrcode/qrlib.php');    
				//ofcourse we need rights to create temporal dir
				if (!file_exists($PNG_TEMP_DIR))
				mkdir($PNG_TEMP_DIR);
				// Archivo PNG temporal con el código QR
				$ga_qr_png = $PNG_TEMP_DIR . $this->btq_wp_login_ga_crypt( $ga_secret, $current_user->user_login ) . '.png';
				// Genera el código QR en el archivo temporal PNG
				QRcode::png($ga_url, $ga_qr_png, 'M', 7, 2);
				// Codifica el archivo PNG en Base64 
				$ga_qr_image_src = 'data:image/png;base64,' . base64_encode( file_get_contents( $ga_qr_png ) );
			}
			?>
			<h3><?php _e('Security', 'btq-wp-login'); ?></h3>
			<table class="form-table">
				<tr>
					<th><?php _e('Two factors authentication', 'btq-wp-login'); ?></th>
					<td>
						<label for="btq_wp_login_ga_activate">
							<input id="btq_wp_login_ga_activate" type="checkbox" value="1" name="btq_wp_login_ga_activate"></input>
							<?php _e('Activate two factors authentication', 'btq-wp-login'); ?>
						</label>
					</td>
				</tr>
				<tr class="btq_wp_login_ga_field_code">
					<?php if ( $detect->isMobile() && !$detect->isTablet() ) { ?>
					<th><?php _e('Register two factors authentication', 'btq-wp-login'); ?></th>
					<?php } else { ?>
					<th><?php _e('QR Code', 'btq-wp-login'); ?></th>
					<?php } ?>
					<td>
						<?php if ( $detect->isMobile() && !$detect->isTablet() ) { ?>
						<p><?php _e('The <a href="https://support.google.com/accounts/answer/1066447?hl=en" target="_blank">Google Authenticator</a> app must have in this device.', 'btq-wp-login'); ?></p>
						<p><?php _e('If you have the Google Authenticator app in this device, please touch on the button:', 'btq-wp-login'); ?><br/><a class="button button-secondary" id="btq_wp_login_ga_code_url" href="<?php echo $ga_url; ?>"><?php _e('Register two factors authentication in this device.', 'btq-wp-login'); ?></a></p>
						<?php } else { ?>
						<p><?php _e('Scan the QR code with your mobile from <a href="https://support.google.com/accounts/answer/1066447?hl=en" target="_blank">Google Authenticator</a> app.', 'btq-wp-login'); ?></p>
						<p><img id="btq_wp_login_ga_code_img" src="<?php echo $ga_qr_image_src; ?>"><p>
						<?php unlink($ga_qr_png); ?>
						<?php } ?>
						<p class="description"><?php _e('The hash is:'); ?> <span id="btq_wp_login_ga_code"><?php echo $this->btq_wp_login_ga_crypt($ga_secret,time()); ?></span></p>
						<p><input id="btq_wp_login_ga_code_new" type="button" class="button" value="Generar nuevo codigo"></input></p>
					</td>
				</tr>
			</table>
			<?php 
		}
	}
	/* add_action('show_user_profile', 'btq_wp_login_ga_profile_field'); */
	
	public function btq_wp_login_ga_ajax(){
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
			$ga_qr_png = $PNG_TEMP_DIR . $this->btq_wp_login_ga_crypt( $ga_secret, $current_user->user_login ) . '.png';
			// Genera el código QR en el archivo temporal PNG
			QRcode::png($ga_url, $ga_qr_png, 'M', 7, 2);
			// Codifica el archivo PNG en Base64 
			$ga_qr_image_src = 'data:image/png;base64,' . base64_encode( file_get_contents( $ga_qr_png ) );
			
			
			// Imprime el resultado
			echo '{"ga_code":"' . $this->btq_wp_login_ga_crypt($ga_secret, time()) . '", "ga_url":"' . addslashes($ga_url) . '", "ga_image":"'.addslashes($ga_qr_image_src) . '"}';
			
			// Elimina el archivo temporal de la imagen con el codigo QR
			unlink($ga_qr_png);
		}
		
		wp_die();
	}
	// add_action( 'wp_ajax_btq_wp_login_ga', 'btq_wp_login_ga_ajax' );
	
	public function btq_wp_login_ga_validate_ajax(){
		$isValidOut = 0;
		
		if(isset($_POST['code'])){
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
			
			$isValidOut = $is_valid ? '1' : '0';
		}
		
		echo '{"isvalid":'.$isValidOut.'}';
		
		wp_die();
	}
	// add_action( 'wp_ajax_btq_wp_login_ga_validate', 'btq_wp_login_ga_validate_ajax' );
	
	// Si es la pagina de perfil y tiene rol de administrador
	public function btq_wp_login_ga_profile_ajax() {
		// Si es la pagina de perfil
		if ( defined( 'IS_PROFILE_PAGE' ) && current_user_can( 'read' ) ) {
			// Libreria para detectar dispositivos moviles (teléfonos y tabletas)
			require_once('lib/Mobile_Detect.php');
			$detect = new Mobile_Detect;
			?>
			<script>
				jQuery(document).ready(function($) {
				
					function btq_wp_login_ga_prompt(text) {
						var out = false;
						var that = this;
						var prompt_code = prompt(text);
						if (prompt_code === null) {
							return;
						}
						else {
							$.post(
							    "/wp-admin/admin-ajax.php",
							    {
									"action" : "btq_wp_login_ga_validate",
									"data"   : prompt_code
							    },
							    function(response) {
									if(response.isvalid == 1) {
										that.out = true;
									}
									else{
										that.out = false;
									}
							    },
								"json"
							)
							.done(function(response) {
								//if( response == 1) {
								//	that.out = true;
								//}
								alert( '<?php _e('Se cargo el ajax', 'btq-wp-login'); ?>' );
							})
							.fail(function() {
								alert( '<?php _e('Error validating the code', 'btq-wp-login'); ?>' );
							});
							
							return that.out;
						}
					}
					
					// Cuando da click al boton "Actualizar Perfil"
					$('#submit').click(function() {
						// Si esta activa la configuracion por dos factores
						if( $('#btq_wp_login_ga_activate').is(":checked") ) {
							console.log('Que pedo?');
							
							var intentos_validos = 0;
							var intentos_erroneo = 0;
							var error_anterior = 0;
							
							while (intentos_validos <= 1) {
								if (intentos_validos == 0 && intentos_erroneo == 0) {
									if (btq_wp_login_ga_prompt('Escribe el codigo:')){
										intentos_validos++;
										error_anterior = 0;
									}
									else {
										intentos_erroneo++;
										error_anterior = 1;
									}
								}
								else if (intentos_validos <= 1 && error_anterior == 1) {
									if (btq_wp_login_ga_prompt("Error en el codigo\nEscribe el codigo nuevamente:")){
										intentos_validos++;
										error_anterior = 0;
									}
									else {
										intentos_erroneo++;
										error_anterior = 1;
									}
								}
								else if (intentos_validos <= 1 && error_anterior == 0) {
									if (btq_wp_login_ga_prompt('Escribe el codigo:')){
										intentos_validos++;
										error_anterior = 0;
									}
									else {
										intentos_erroneo++;
										error_anterior = 1;
									}
								}
								else {
									alert('Guardando');
								}
							}
							
							return false;
						}
					});
					
					$('#btq_wp_login_ga_code_new').click(function() {
						$('#btq_wp_login_ga_code').text('<?php _e('Loading...', 'btq-wp-login'); ?>');
						
						$.post(
							"/wp-admin/admin-ajax.php", 
							{ "action": "btq_wp_login_ga", "ga": "1" }, 
							function(data) {
								<?php if ( $detect->isMobile() && !$detect->isTablet() ) { ?>
									$('#btq_wp_login_ga_code_url').attr('href', data.ga_url);
								<?php } else { ?>
									$('#btq_wp_login_ga_code_img').attr('src', data.ga_image);
								<?php } ?>
								$('#btq_wp_login_ga_code').text(data.ga_code);
							}, 
							"json"
						)
						.done(function( data ) {
							// Done
						})
						.fail(function() {
							$('#btq_wp_login_ga_code').text('<?php _e('Error', 'btq-wp-login'); ?>');
						});
					});
					
					function btq_wp_login_ga_toggle(e) {
						if($(e).is(":checked")) {
							$('.btq_wp_login_ga_field_code').show();
						}
						else {
							$('.btq_wp_login_ga_field_code').hide();
						}
					}
					
					btq_wp_login_ga_toggle($('#btq_wp_login_ga_activate'));
					
					$('#btq_wp_login_ga_activate').change(function() {
						btq_wp_login_ga_toggle(this);
					});
				});
			</script>
			<?php 
		}
	}
	/* add_action('admin_footer', 'btq_wp_login_ga_profile_ajax'); */

	public function btq_wp_login_form_ga() {
		?>
		<p><a href="#"><?php _e('Two factors authentication', 'btq-wp-login'); ?></a></p>
		<?php 
	}

} // class BTQ_WP_Login

$BTQ_WP_Login = new BTQ_WP_Login();

add_filter('wp_authenticate_user',	array($BTQ_WP_Login, 'btq_wp_login_noemail') );

add_action('wp_ajax_btq_wp_login_ga',			array($BTQ_WP_Login, 'btq_wp_login_ga_ajax') );
add_action('wp_ajax_btq_wp_login_ga_validate',	array($BTQ_WP_Login, 'btq_wp_login_ga_validate_ajax') );

add_action('init',					array($BTQ_WP_Login, 'btq_wp_login_recaptcha_session_start') );
add_filter('wp_login_errors',		array($BTQ_WP_Login, 'btq_wp_login_recaptcha_login_errors') );
add_filter('wp_authenticate_user',	array($BTQ_WP_Login, 'btq_wp_login_recaptcha_validate') );
add_action('wp_login',				array($BTQ_WP_Login, 'btq_wp_login_recaptcha_login') );

add_action('show_user_profile',		array($BTQ_WP_Login, 'btq_wp_login_ga_profile_field') );
add_action('admin_footer',			array($BTQ_WP_Login, 'btq_wp_login_ga_profile_ajax') );
add_action('login_form',			array($BTQ_WP_Login, 'btq_wp_login_form_ga') );





// Permite cargar en la libraría multimedia gráficos en formato SVG y SVGZ
function cc_mime_types($mimes) {
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');