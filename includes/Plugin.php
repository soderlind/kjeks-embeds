<?php
/**
 * Plugin bootstrap.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

namespace Soderlind\KjeksEmbeds;

/**
 * Wires the add-on's subsystems.
 */
final class Plugin {

	private static ?Plugin $instance = null;

	private bool $booted = false;

	public static function instance(): self {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function boot(): void {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		$settings = new Settings();
		$settings->hooks();

		( new OembedGate( $settings ) )->hooks();
		( new MapsShortcode( $settings ) )->hooks();
	}
}
