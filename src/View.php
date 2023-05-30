<?php

namespace Kinola\KinolaWp;

class View {

	/**
	 * Output the rendered template
	 */
	public static function render( string $template, array $data = [] ) {
		echo self::get_rendered_template($template, $data);
	}

	/**
	 * Render a template and pass any given data into it.
	 */
	public static function get_rendered_template( string $template, array $data = [] ): string {
		extract( $data );
		ob_start();
		require self::get_template_path( $template );

		return ob_get_clean();
	}

	/**
	 * Get the first existing file from all possible template directories.
	 */
	public static function get_template_path( string $template ): string {
		foreach ( self::get_template_directories() as $directory ) {
			if ( file_exists( $directory . $template . '.php' ) ) {
				return apply_filters( 'kinola/template', $directory . $template . '.php', $template );
			}
		}

		trigger_error( "Template {$template} not found.", E_USER_WARNING );

		return '';
	}

	/**
	 * In the order of: child theme, parent theme, plugin.
	 * Filter out files that do not exist.
	 */
	public static function get_template_directories(): array {
		$directories = array_filter( [
			get_stylesheet_directory() . '/kinola/',
			get_template_directory() . '/kinola/',
			KINOLA_PATH . '/templates/'
		], 'is_dir' );

		return array_unique( apply_filters( 'kinola/template_directories', $directories ) );
	}
}
