<?php
/**
 * oEmbed gating for every provider WordPress can embed.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

namespace Soderlind\KjeksEmbeds;

/**
 * Gates oEmbed embeds behind Kjeks consent.
 *
 * Runs on `embed_oembed_html`, which both the `core/embed` block and the
 * `[embed]` shortcode/autoembed are filtered through before the result is
 * cached in post meta. Providers with a dedicated adapter (YouTube, Vimeo,
 * Spotify, SoundCloud, Dailymotion) are rebuilt from the requested URL into a
 * privacy-friendly src; any other provider is gated generically when its
 * returned markup is a single iframe. Script/blockquote embeds (Twitter, TikTok,
 * ...) are not a lone iframe and pass through untouched — they belong to Kjeks
 * core's script gating.
 *
 * Note: because the filtered result is what WordPress caches, a post
 * embedded before this plugin was active keeps its ungated cached HTML
 * until the post is re-saved or the oEmbed cache expires/is cleared. The
 * reverse also holds: after deactivation a post keeps its gated cached
 * HTML (with no banner.js left to release it) until the same happens, so
 * flush oEmbed caches or re-save affected posts when toggling the plugin.
 */
final class OembedGate {

	public function __construct( private readonly Settings $settings ) {}

	public function hooks(): void {
		add_filter( 'embed_oembed_html', array( $this, 'filter_html' ), 10, 4 );
	}

	/**
	 * @param string                $html    The oEmbed HTML.
	 * @param string                $url     The original URL.
	 * @param array<string, mixed>  $attr    Shortcode/block attributes.
	 * @param int                   $post_id Post the embed is inserted into.
	 */
	public function filter_html( string $html, string $url, array $attr, int $post_id ): string {
		// Leave the block editor's own live preview alone; only the saved
		// front-end render (and any front-end AJAX render) is gated.
		if ( is_admin() ) {
			return $html;
		}

		if ( ! function_exists( 'kjeks_embed' ) ) {
			return $html;
		}

		$config   = $this->settings->resolve();
		$provider = Providers::detect( $url );

		if ( null !== $provider ) {
			$category = $config[ $provider ] ?? 'marketing';
			$id       = Providers::extract_id( $provider, $url );
			$src      = null === $id ? '' : Providers::embed_src( $provider, $id );
			$label    = Providers::labels()[ $provider ];
			$width    = isset( $attr['width'] ) ? (int) $attr['width'] : 560;
			$height   = isset( $attr['height'] ) ? (int) $attr['height'] : 315;
		} else {
			// Any other oEmbed provider: gate it generically if it is a lone iframe.
			$category = $config['other_iframes'];
			$iframe   = 'off' === $category ? null : Providers::extract_iframe( $html );
			if ( null === $iframe ) {
				return $html;
			}
			$src    = $iframe['src'];
			$label  = '';
			$width  = $iframe['width'] > 0 ? $iframe['width'] : 560;
			$height = $iframe['height'] > 0 ? $iframe['height'] : 315;
		}

		/**
		 * Filters the consent category for a single embed, so one video can be
		 * marketing while another is analytics. Return 'off' to skip gating.
		 *
		 * @param string $category Resolved category ('analytics', 'marketing', or 'off').
		 * @param string $provider Provider slug, or '' for a generic iframe embed.
		 * @param string $url      The embedded URL.
		 */
		$category = (string) apply_filters( 'kjeks_embeds_category', $category, (string) $provider, $url );

		if ( 'off' === $category || '' === $src ) {
			return $html;
		}

		$title = '' !== $label
			// translators: %s: provider name (YouTube, Vimeo, Spotify, ...).
			? sprintf( __( '%s embed', 'kjeks-embeds' ), $label )
			: __( 'Embedded content', 'kjeks-embeds' );

		return kjeks_embed(
			$src,
			$category,
			array(
				'title'    => $title,
				'provider' => $label,
				'width'    => $width,
				'height'   => $height,
			)
		);
	}
}
