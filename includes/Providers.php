<?php
/**
 * Embed provider detection.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

namespace Soderlind\KjeksEmbeds;

/**
 * Detects and normalizes the embed providers Kjeks Embeds knows about.
 *
 * Detection and id extraction both work from the pasted URL, never from a
 * provider's returned oEmbed markup, so behaviour cannot drift if a
 * provider changes what it renders.
 */
final class Providers {

	public const YOUTUBE     = 'youtube';
	public const VIMEO       = 'vimeo';
	public const SPOTIFY     = 'spotify';
	public const SOUNDCLOUD  = 'soundcloud';
	public const DAILYMOTION = 'dailymotion';

	/**
	 * @return array<string, string>
	 */
	public static function labels(): array {
		return array(
			self::YOUTUBE     => 'YouTube',
			self::VIMEO       => 'Vimeo',
			self::SPOTIFY     => 'Spotify',
			self::SOUNDCLOUD  => 'SoundCloud',
			self::DAILYMOTION => 'Dailymotion',
		);
	}

	public static function detect( string $url ): ?string {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! is_string( $host ) ) {
			return null;
		}

		$host = strtolower( (string) preg_replace( '/^www\./', '', $host ) );

		return match ( true ) {
			in_array( $host, array( 'youtube.com', 'youtu.be', 'youtube-nocookie.com', 'm.youtube.com' ), true ) => self::YOUTUBE,
			in_array( $host, array( 'vimeo.com', 'player.vimeo.com' ), true ) => self::VIMEO,
			in_array( $host, array( 'open.spotify.com', 'spotify.com' ), true ) => self::SPOTIFY,
			in_array( $host, array( 'soundcloud.com', 'w.soundcloud.com', 'm.soundcloud.com' ), true ) => self::SOUNDCLOUD,
			in_array( $host, array( 'dailymotion.com', 'geo.dailymotion.com', 'dai.ly' ), true ) => self::DAILYMOTION,
			default => null,
		};
	}

	public static function extract_id( string $provider, string $url ): ?string {
		return match ( $provider ) {
			self::YOUTUBE => self::youtube_id( $url ),
			self::VIMEO => self::vimeo_id( $url ),
			self::SPOTIFY => self::spotify_id( $url ),
			self::SOUNDCLOUD => self::soundcloud_id( $url ),
			self::DAILYMOTION => self::dailymotion_id( $url ),
			default => null,
		};
	}

	public static function embed_src( string $provider, string $id ): string {
		return match ( $provider ) {
			self::YOUTUBE => 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $id ),
			self::VIMEO => self::vimeo_src( $id ),
			self::SPOTIFY => self::spotify_src( $id ),
			self::SOUNDCLOUD => self::soundcloud_src( $id ),
			self::DAILYMOTION => 'https://geo.dailymotion.com/player.html?video=' . rawurlencode( $id ),
			default => '',
		};
	}

	private static function youtube_id( string $url ): ?string {
		if ( 1 === preg_match( '#youtu\.be/([A-Za-z0-9_-]{6,})#', $url, $matches ) ) {
			return $matches[1];
		}
		if ( 1 === preg_match( '#[?&]v=([A-Za-z0-9_-]{6,})#', $url, $matches ) ) {
			return $matches[1];
		}
		if ( 1 === preg_match( '#youtube(?:-nocookie)?\.com/(?:embed|shorts|live)/([A-Za-z0-9_-]{6,})#', $url, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	// Returns the numeric id, plus the unlisted-video privacy hash as `id:hash` when present.
	private static function vimeo_id( string $url ): ?string {
		if ( 1 !== preg_match( '#(?:player\.)?vimeo\.com/(?:video/)?(\d+)#', $url, $matches ) ) {
			return null;
		}

		$id = $matches[1];

		if ( 1 === preg_match( '#vimeo\.com/(?:video/)?\d+/([A-Za-z0-9]+)#', $url, $hash )
			|| 1 === preg_match( '#[?&]h=([A-Za-z0-9]+)#', $url, $hash ) ) {
			return $id . ':' . $hash[1];
		}

		return $id;
	}

	private static function vimeo_src( string $id ): string {
		if ( str_contains( $id, ':' ) ) {
			[ $video, $hash ] = explode( ':', $id, 2 );
			return 'https://player.vimeo.com/video/' . rawurlencode( $video ) . '?h=' . rawurlencode( $hash );
		}

		return 'https://player.vimeo.com/video/' . rawurlencode( $id );
	}

	// Returns the resource kind and id as `type:id` so the embed keeps the same kind (track, album, ...).
	private static function spotify_id( string $url ): ?string {
		if ( 1 === preg_match( '#(?:open\.)?spotify\.com/(?:embed/)?(track|album|playlist|episode|show|artist)/([A-Za-z0-9]+)#', $url, $matches ) ) {
			return $matches[1] . ':' . $matches[2];
		}

		return null;
	}

	private static function spotify_src( string $id ): string {
		[ $type, $resource ] = array_pad( explode( ':', $id, 2 ), 2, '' );
		if ( '' === $type || '' === $resource ) {
			return '';
		}

		return 'https://open.spotify.com/embed/' . rawurlencode( $type ) . '/' . rawurlencode( $resource );
	}

	// SoundCloud has no id in the URL; the player resolves the whole track/set URL.
	private static function soundcloud_id( string $url ): ?string {
		return '' !== $url ? $url : null;
	}

	private static function soundcloud_src( string $id ): string {
		return 'https://w.soundcloud.com/player/?url=' . rawurlencode( $id ) . '&visual=true';
	}

	private static function dailymotion_id( string $url ): ?string {
		if ( 1 === preg_match( '#dailymotion\.com/(?:embed/)?video/([A-Za-z0-9]+)#', $url, $matches )
			|| 1 === preg_match( '#dai\.ly/([A-Za-z0-9]+)#', $url, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Extracts a single http(s) iframe from returned oEmbed markup, for gating
	 * providers Kjeks Embeds has no dedicated adapter for. Returns null unless
	 * the markup contains exactly one iframe, so mixed/script embeds are left
	 * untouched.
	 *
	 * @return array{src: string, width: int, height: int}|null
	 */
	public static function extract_iframe( string $html ): ?array {
		if ( 1 !== substr_count( strtolower( $html ), '<iframe' ) ) {
			return null;
		}
		if ( 1 !== preg_match( '#<iframe\b[^>]*?\ssrc=("|\')(.*?)\1#i', $html, $matches ) ) {
			return null;
		}

		$src    = esc_url_raw( html_entity_decode( $matches[2], ENT_QUOTES ) );
		$scheme = strtolower( (string) wp_parse_url( $src, PHP_URL_SCHEME ) );
		if ( '' === $src || ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
			return null;
		}

		$width  = 1 === preg_match( '#\swidth=("|\')?(\d+)#i', $html, $w ) ? (int) $w[2] : 0;
		$height = 1 === preg_match( '#\sheight=("|\')?(\d+)#i', $html, $h ) ? (int) $h[2] : 0;

		return array(
			'src'    => $src,
			'width'  => $width,
			'height' => $height,
		);
	}
}
