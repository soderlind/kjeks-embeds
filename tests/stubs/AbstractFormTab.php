<?php
/**
 * Test stub for the core AddonKit base class.
 *
 * The unit suite instantiates {@see \Soderlind\KjeksEmbeds\Settings} only to
 * satisfy the {@see \Soderlind\KjeksEmbeds\MapsShortcode} constructor. In an
 * isolated add-on checkout the core plugin (which ships the real base class)
 * is not on the autoloader, so a minimal stand-in keeps the suite runnable.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

namespace Soderlind\Kjeks\AddonKit;

if ( ! class_exists( AbstractFormTab::class, false ) ) {
	/**
	 * Minimal stand-in for the core form-tab base class.
	 */
	abstract class AbstractFormTab {}
}
