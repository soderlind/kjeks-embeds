<?php
/**
 * MapsShortcode source-validation tests.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

use Soderlind\KjeksEmbeds\MapsShortcode;
use Soderlind\KjeksEmbeds\Settings;

/**
 * Invokes the private validate_src() without exercising the full render path.
 */
function validate_src( string $src ): string {
	$method = new ReflectionMethod( MapsShortcode::class, 'validate_src' );
	$method->setAccessible( true );

	return (string) $method->invoke( new MapsShortcode( new Settings() ), $src );
}

it( 'accepts a genuine Google Maps embed URL', function (): void {
	$src = 'https://www.google.com/maps/embed?pb=!1m18!1m12';
	expect( validate_src( $src ) )->toBe( $src );
} );

it( 'rejects a non-Google host', function (): void {
	expect( validate_src( 'https://evil.example.com/maps/embed?pb=1' ) )->toBe( '' );
} );

it( 'rejects a Google URL that is not a maps embed path', function (): void {
	expect( validate_src( 'https://www.google.com/search?q=maps' ) )->toBe( '' );
} );

it( 'rejects a value that is not a URL', function (): void {
	expect( validate_src( 'javascript:alert(1)' ) )->toBe( '' );
} );
