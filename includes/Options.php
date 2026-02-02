<?php
/**
 * Options management class.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships;

/**
 * Handles plugin options and settings.
 */
final class Options {

	/**
	 * Option name in database.
	 */
	public const OPTION_NAME = 'lw_mship_options';

	/**
	 * Meta key prefix for post meta.
	 */
	public const META_PREFIX = '_lw_mship_';

	/**
	 * Cached options.
	 *
	 * @var array<string, mixed>|null
	 */
	private static ?array $options = null;

	/**
	 * Get default options.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults(): array {
		return [
			// General.
			'restricted_message'     => __( 'This content is restricted to members.', 'lw-memberships' ),
			'not_logged_in_message'  => __( 'Please log in to access this content.', 'lw-memberships' ),
			'expired_message'        => __( 'Your membership has expired.', 'lw-memberships' ),
			'paused_message'         => __( 'Your membership is currently paused.', 'lw-memberships' ),

			// WooCommerce.
			'auto_grant_on_complete' => true,
			'revoke_on_refund'       => true,

			// Content restriction.
			'hide_restricted_in_archive' => false,
			'show_excerpt_restricted'    => false,

			// Cron.
			'check_expiration_daily' => true,
		];
	}

	/**
	 * Get all options.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_all(): array {
		if ( null === self::$options ) {
			$saved         = get_option( self::OPTION_NAME, [] );
			self::$options = wp_parse_args( $saved, self::get_defaults() );
		}

		return self::$options;
	}

	/**
	 * Get a single option.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Default value if not set.
	 * @return mixed
	 */
	public static function get( string $key, mixed $default = null ): mixed {
		$options = self::get_all();

		if ( array_key_exists( $key, $options ) ) {
			return $options[ $key ];
		}

		return $default ?? ( self::get_defaults()[ $key ] ?? null );
	}

	/**
	 * Set a single option.
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Option value.
	 * @return bool
	 */
	public static function set( string $key, mixed $value ): bool {
		$options         = self::get_all();
		$options[ $key ] = $value;

		return self::save( $options );
	}

	/**
	 * Save all options.
	 *
	 * @param array<string, mixed> $options Options to save.
	 * @return bool
	 */
	public static function save( array $options ): bool {
		self::$options = $options;
		return update_option( self::OPTION_NAME, $options );
	}

	/**
	 * Reset options to defaults.
	 *
	 * @return bool
	 */
	public static function reset(): bool {
		self::$options = null;
		return delete_option( self::OPTION_NAME );
	}

	/**
	 * Get post meta value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key (without prefix).
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get_post_meta( int $post_id, string $key, mixed $default = '' ): mixed {
		$value = get_post_meta( $post_id, self::META_PREFIX . $key, true );

		return '' !== $value ? $value : $default;
	}

	/**
	 * Set post meta value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key (without prefix).
	 * @param mixed  $value   Value to save.
	 * @return bool
	 */
	public static function set_post_meta( int $post_id, string $key, mixed $value ): bool {
		if ( '' === $value || null === $value ) {
			return delete_post_meta( $post_id, self::META_PREFIX . $key );
		}

		return (bool) update_post_meta( $post_id, self::META_PREFIX . $key, $value );
	}

	/**
	 * Clear options cache.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$options = null;
	}
}
