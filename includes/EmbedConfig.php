<?php
/**
 * Embed gating configuration resolution.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

namespace Soderlind\KjeksEmbeds;

/**
 * Resolves the effective embed gating configuration from stored values.
 *
 * Pure input-to-output, like Kjeks Google's GoogleTagConfig: takes raw
 * stored values and returns the normalized config, so resolution and
 * validation are testable independently of where the option is stored.
 */
final class EmbedConfig {

	/**
	 * Allowed gating categories (never necessary).
	 *
	 * @return array<string, string>
	 */
	public static function categories(): array {
		return array(
			'analytics' => __( 'Analytics', 'kjeks-embeds' ),
			'marketing' => __( 'Marketing', 'kjeks-embeds' ),
		);
	}

	/**
	 * Providers whose category may also be "off" (passed through, not gated).
	 *
	 * @return list<string>
	 */
	public static function gateable(): array {
		return array( 'youtube', 'vimeo', 'spotify', 'soundcloud', 'dailymotion', 'other_iframes' );
	}

	/**
	 * The gating category chosen per embed source. Each value is a category
	 * slug, or "off" for the gateable providers. Google Maps is always gated
	 * when its shortcode is used, so it has no "off".
	 *
	 * @return array<string, string>
	 */
	public static function defaults(): array {
		return array(
			'youtube'       => 'marketing',
			'vimeo'         => 'marketing',
			'spotify'       => 'marketing',
			'soundcloud'    => 'marketing',
			'dailymotion'   => 'marketing',
			'other_iframes' => 'marketing',
			'maps'          => 'marketing',
		);
	}

	/**
	 * Normalizes raw values against the defaults.
	 *
	 * @param array<string, mixed> $values Raw values.
	 * @return array<string, string>
	 */
	public static function normalize( array $values ): array {
		$merged = array_merge( self::defaults(), $values );
		$out    = array();

		foreach ( self::gateable() as $key ) {
			$out[ $key ] = self::valid_category( (string) ( $merged[ $key ] ?? '' ), true );
		}
		$out['maps'] = self::valid_category( (string) ( $merged['maps'] ?? '' ), false );

		return $out;
	}

	private static function valid_category( string $value, bool $allow_off ): string {
		if ( $allow_off && 'off' === $value ) {
			return 'off';
		}

		return isset( self::categories()[ $value ] ) ? $value : 'marketing';
	}
}
