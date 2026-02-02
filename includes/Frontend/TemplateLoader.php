<?php
/**
 * Template loader.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Frontend;

/**
 * Loads templates with theme override support.
 */
final class TemplateLoader {

	/**
	 * Get template path.
	 *
	 * @param string $template Template name.
	 * @return string
	 */
	public static function get_template_path( string $template ): string {
		// Check theme override.
		$theme_path = get_stylesheet_directory() . '/lw-memberships/' . $template;

		if ( file_exists( $theme_path ) ) {
			return $theme_path;
		}

		// Check parent theme.
		$parent_path = get_template_directory() . '/lw-memberships/' . $template;

		if ( file_exists( $parent_path ) ) {
			return $parent_path;
		}

		// Default plugin template.
		return LW_MEMBERSHIPS_PATH . 'templates/' . $template;
	}

	/**
	 * Load template.
	 *
	 * @param string              $template Template name.
	 * @param array<string,mixed> $args     Template arguments.
	 * @return void
	 */
	public static function load( string $template, array $args = [] ): void {
		$path = self::get_template_path( $template );

		if ( ! file_exists( $path ) ) {
			return;
		}

		// Extract args for template use.
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		include $path;
	}

	/**
	 * Get template content.
	 *
	 * @param string              $template Template name.
	 * @param array<string,mixed> $args     Template arguments.
	 * @return string
	 */
	public static function get( string $template, array $args = [] ): string {
		ob_start();
		self::load( $template, $args );
		return ob_get_clean();
	}
}
