<?php
/**
 * Google Maps embed shortcode.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

namespace Soderlind\KjeksEmbeds;

/**
 * Gates a Google Maps "Share > Embed a map" iframe via a shortcode.
 *
 * Maps has no oEmbed endpoint, so unlike YouTube/Vimeo it cannot be
 * intercepted automatically from a pasted URL: the author copies the src
 * Google's share dialog gives them into [kjeks_google_map] instead of a
 * raw iframe/Custom HTML block.
 */
final class MapsShortcode {

	public function __construct( private readonly Settings $settings ) {}

	public function hooks(): void {
		add_shortcode( 'kjeks_google_map', array( $this, 'render' ) );
	}

	/**
	 * @param array<string, mixed>|string $atts Shortcode attributes.
	 */
	public function render( $atts ): string {
		if ( ! function_exists( 'kjeks_embed' ) ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'src'      => '',
				'title'    => __( 'Google Map', 'kjeks-embeds' ),
				'width'    => 600,
				'height'   => 450,
				'category' => '',
			),
			$atts,
			'kjeks_google_map'
		);

		$src = $this->validate_src( (string) $atts['src'] );
		if ( '' === $src ) {
			return '';
		}

		$config   = $this->settings->resolve();
		$category = $this->resolve_category( (string) $atts['category'], (string) ( $config['maps'] ?? 'marketing' ) );

		return kjeks_embed(
			$src,
			$category,
			array(
				'title'    => (string) $atts['title'],
				'provider' => 'Google Maps',
				'width'    => (int) $atts['width'],
				'height'   => (int) $atts['height'],
			)
		);
	}

	/**
	 * Uses the shortcode's category="" when it is a valid category, else the
	 * configured Maps default.
	 */
	private function resolve_category( string $requested, string $default ): string {
		return isset( EmbedConfig::categories()[ $requested ] ) ? $requested : $default;
	}

	/**
	 * Only accepts Google's own Maps embed URL, so the shortcode cannot be
	 * used to gate-wrap an arbitrary third-party iframe.
	 */
	private function validate_src( string $src ): string {
		$host = wp_parse_url( $src, PHP_URL_HOST );
		if ( ! is_string( $host ) ) {
			return '';
		}

		$path = (string) wp_parse_url( $src, PHP_URL_PATH );

		if ( 'www.google.com' !== $host || ! str_starts_with( $path, '/maps/embed' ) ) {
			return '';
		}

		return esc_url_raw( $src );
	}
}
