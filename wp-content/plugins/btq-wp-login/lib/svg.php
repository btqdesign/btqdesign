<?php

require_once('../../../../wp-blog-header.php');

class SVGinfo {

	public $path;
	public $url;
	public $width;
	public $height;

	public function __construct( $file )
	{
		$this->validate( $file );
	}
	
	public function validate( $file ) {
		// Tiene extensión SVG
		if ( preg_match("/\.(svg|svgz)$/", $file) ) {
			// Si inicia en el mismo directorio
			if ( preg_match("/^\.\//", $file) ) {
				// Si es archvio
				if ( is_file($file) ) {					
					if ( is_file(__DIR__.str_replace('./', '', $file, 1)) ) {
						$this->path = __DIR__.str_replace('./', '/', $file, 1);
					}
				}
			}
			// Si la ruta es raiz
			else if ( preg_match("/^".preg_quote(DIRECTORY_SEPARATOR,'/')."/", $file) ) {
				// si es archivo
				if ( is_file($file) ) {
					$this->path = $file;
				}
				else {
					$this->path = home_url( $file );
				}
			}
			// Si es URL
			else if ( preg_match("/^(http|https)\:\/\//", $file) ) {
				$this->path = '';
				$this->url  = $file;
			}
			// Si es un subdirectorio
			else {
				
			}
		}
		else {
			echo 'no tiene extensión SVG';
		}
	}
    
    
}

$svg = new SVGinfo('https://media/logo.svg');