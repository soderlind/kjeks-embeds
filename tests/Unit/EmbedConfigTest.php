<?php
/**
 * EmbedConfig normalization tests.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

use Soderlind\KjeksEmbeds\EmbedConfig;

it( 'defaults every embed source to the marketing category', function (): void {
	expect( EmbedConfig::defaults() )->toBe(
		array(
			'youtube'       => 'marketing',
			'vimeo'         => 'marketing',
			'spotify'       => 'marketing',
			'soundcloud'    => 'marketing',
			'dailymotion'   => 'marketing',
			'other_iframes' => 'marketing',
			'maps'          => 'marketing',
		)
	);
} );

it( 'exposes only analytics and marketing as gating categories', function (): void {
	expect( array_keys( EmbedConfig::categories() ) )->toBe( array( 'analytics', 'marketing' ) );
} );

it( 'lets each provider keep its own category', function (): void {
	$config = EmbedConfig::normalize(
		array(
			'youtube' => 'marketing',
			'spotify' => 'analytics',
		)
	);

	expect( $config['youtube'] )->toBe( 'marketing' )
		->and( $config['spotify'] )->toBe( 'analytics' );
} );

it( 'allows off for gateable providers', function (): void {
	expect( EmbedConfig::normalize( array( 'vimeo' => 'off' ) )['vimeo'] )->toBe( 'off' );
} );

it( 'never allows off for Google Maps', function (): void {
	expect( EmbedConfig::normalize( array( 'maps' => 'off' ) )['maps'] )->toBe( 'marketing' );
} );

it( 'falls back to marketing for an unknown or empty category', function (): void {
	expect( EmbedConfig::normalize( array( 'youtube' => 'necessary' ) )['youtube'] )->toBe( 'marketing' )
		->and( EmbedConfig::normalize( array( 'youtube' => '' ) )['youtube'] )->toBe( 'marketing' );
} );
