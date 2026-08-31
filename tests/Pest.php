<?php
/**
 * Pest bootstrap: Brain Monkey lifecycle and shared WordPress stubs.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

use Brain\Monkey;
use Brain\Monkey\Functions;

require_once __DIR__ . '/stubs/AbstractFormTab.php';

uses()
	->beforeEach(
		function (): void {
			Monkey\setUp();
			Functions\when( '__' )->returnArg( 1 );
			Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
			Functions\when( 'esc_url_raw' )->returnArg( 1 );
		}
	)
	->afterEach(
		function (): void {
			Monkey\tearDown();
		}
	)
	->in( 'Unit' );
