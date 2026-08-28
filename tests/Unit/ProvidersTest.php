<?php
/**
 * Provider detection, id extraction, and embed-src tests.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

use Soderlind\KjeksEmbeds\Providers;

it( 'detects YouTube from every host variant', function ( string $url ): void {
	expect( Providers::detect( $url ) )->toBe( Providers::YOUTUBE );
} )->with(
	array(
		'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
		'https://youtu.be/dQw4w9WgXcQ',
		'https://m.youtube.com/watch?v=dQw4w9WgXcQ',
		'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
	)
);

it( 'detects Vimeo from its host variants', function ( string $url ): void {
	expect( Providers::detect( $url ) )->toBe( Providers::VIMEO );
} )->with(
	array(
		'https://vimeo.com/123456789',
		'https://player.vimeo.com/video/123456789',
	)
);

it( 'returns null for an unknown provider', function (): void {
	expect( Providers::detect( 'https://twitter.com/foo/status/1' ) )->toBeNull()
		->and( Providers::detect( 'not-a-url' ) )->toBeNull();
} );

it( 'detects Spotify, SoundCloud, and Dailymotion from their hosts', function ( string $url, string $expected ): void {
	expect( Providers::detect( $url ) )->toBe( $expected );
} )->with(
	array(
		array( 'https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC', Providers::SPOTIFY ),
		array( 'https://soundcloud.com/artist/some-track', Providers::SOUNDCLOUD ),
		array( 'https://www.dailymotion.com/video/x8abcde', Providers::DAILYMOTION ),
		array( 'https://dai.ly/x8abcde', Providers::DAILYMOTION ),
	)
);

it( 'extracts the YouTube id from watch, short, embed, shorts, and live URLs', function ( string $url ): void {
	expect( Providers::extract_id( Providers::YOUTUBE, $url ) )->toBe( 'dQw4w9WgXcQ' );
} )->with(
	array(
		'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
		'https://youtu.be/dQw4w9WgXcQ',
		'https://www.youtube.com/embed/dQw4w9WgXcQ',
		'https://www.youtube.com/shorts/dQw4w9WgXcQ',
		'https://www.youtube.com/live/dQw4w9WgXcQ',
	)
);

it( 'builds a privacy-friendly YouTube src', function (): void {
	expect( Providers::embed_src( Providers::YOUTUBE, 'dQw4w9WgXcQ' ) )
		->toBe( 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ' );
} );

it( 'extracts a plain Vimeo id', function (): void {
	expect( Providers::extract_id( Providers::VIMEO, 'https://vimeo.com/123456789' ) )->toBe( '123456789' );
} );

it( 'preserves the unlisted-video hash from a path-form Vimeo URL', function (): void {
	expect( Providers::extract_id( Providers::VIMEO, 'https://vimeo.com/123456789/abc123def' ) )->toBe( '123456789:abc123def' );
} );

it( 'preserves the unlisted-video hash from a query-form Vimeo URL', function (): void {
	expect( Providers::extract_id( Providers::VIMEO, 'https://player.vimeo.com/video/123456789?h=abc123def' ) )->toBe( '123456789:abc123def' );
} );

it( 'builds a Vimeo src without a hash', function (): void {
	expect( Providers::embed_src( Providers::VIMEO, '123456789' ) )
		->toBe( 'https://player.vimeo.com/video/123456789' );
} );

it( 'appends the hash as the h query arg when building a Vimeo src', function (): void {
	expect( Providers::embed_src( Providers::VIMEO, '123456789:abc123def' ) )
		->toBe( 'https://player.vimeo.com/video/123456789?h=abc123def' );
} );

it( 'keeps the Spotify resource kind when extracting and building a src', function (): void {
	$id = Providers::extract_id( Providers::SPOTIFY, 'https://open.spotify.com/album/1DFixLWuPkv3KT3TnV35m3?si=abc' );
	expect( $id )->toBe( 'album:1DFixLWuPkv3KT3TnV35m3' )
		->and( Providers::embed_src( Providers::SPOTIFY, $id ) )
		->toBe( 'https://open.spotify.com/embed/album/1DFixLWuPkv3KT3TnV35m3' );
} );

it( 'wraps the whole SoundCloud URL in the player src', function (): void {
	$url = 'https://soundcloud.com/artist/some-track';
	expect( Providers::extract_id( Providers::SOUNDCLOUD, $url ) )->toBe( $url )
		->and( Providers::embed_src( Providers::SOUNDCLOUD, $url ) )
		->toBe( 'https://w.soundcloud.com/player/?url=' . rawurlencode( $url ) . '&visual=true' );
} );

it( 'extracts the Dailymotion id from long and short URLs', function ( string $url ): void {
	expect( Providers::extract_id( Providers::DAILYMOTION, $url ) )->toBe( 'x8abcde' );
} )->with(
	array(
		'https://www.dailymotion.com/video/x8abcde',
		'https://dai.ly/x8abcde',
	)
);

it( 'builds a geo Dailymotion player src', function (): void {
	expect( Providers::embed_src( Providers::DAILYMOTION, 'x8abcde' ) )
		->toBe( 'https://geo.dailymotion.com/player.html?video=x8abcde' );
} );

it( 'extracts a lone iframe src and dimensions', function (): void {
	$iframe = Providers::extract_iframe( '<iframe src="https://example.com/embed/1" width="640" height="360"></iframe>' );

	expect( $iframe )->toBe(
		array(
			'src'    => 'https://example.com/embed/1',
			'width'  => 640,
			'height' => 360,
		)
	);
} );

it( 'defaults missing dimensions to zero', function (): void {
	expect( Providers::extract_iframe( '<iframe src="https://example.com/embed/1"></iframe>' ) )
		->toBe(
			array(
				'src'    => 'https://example.com/embed/1',
				'width'  => 0,
				'height' => 0,
			)
		);
} );

it( 'decodes HTML entities in the iframe src', function (): void {
	$iframe = Providers::extract_iframe( '<iframe src="https://example.com/e?a=1&amp;b=2"></iframe>' );

	expect( $iframe['src'] )->toBe( 'https://example.com/e?a=1&b=2' );
} );

it( 'returns null when the markup is not a single iframe', function ( string $html ): void {
	expect( Providers::extract_iframe( $html ) )->toBeNull();
} )->with(
	array(
		'no iframe'        => '<blockquote class="twitter-tweet"><p>hi</p></blockquote>',
		'script embed'     => '<script async src="https://platform.twitter.com/widgets.js"></script>',
		'two iframes'      => '<iframe src="https://a.test/1"></iframe><iframe src="https://b.test/2"></iframe>',
		'no src'           => '<iframe width="640" height="360"></iframe>',
		'javascript src'   => '<iframe src="javascript:alert(1)"></iframe>',
	)
);
