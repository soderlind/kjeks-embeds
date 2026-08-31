<?php
/**
 * Plugin Name:       Kjeks Embeds
 * Plugin URI:        https://github.com/soderlind/kjeks-embeds
 * Description:       Consent-gated YouTube, Vimeo, and Google Maps embeds for Kjeks — withheld behind an accessible placeholder until the visitor consents.
 * Version:           0.2.0
 * Requires at least: 6.8
 * Requires PHP:      8.3
 * Requires Plugins:  kjeks
 * Author:            Per Søderlind
 * Author URI:        https://soderlind.no
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kjeks-embeds
 * Domain Path:       /languages
 * Network:           true
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

namespace Soderlind\KjeksEmbeds;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KJEKS_EMBEDS_VERSION', '0.1.1' );
define( 'KJEKS_EMBEDS_FILE', __FILE__ );
define( 'KJEKS_EMBEDS_DIR', plugin_dir_path( __FILE__ ) );
define( 'KJEKS_EMBEDS_URL', plugin_dir_url( __FILE__ ) );

$kjeks_embeds_autoload = KJEKS_EMBEDS_DIR . 'vendor/autoload.php';
if ( is_readable( $kjeks_embeds_autoload ) ) {
	require $kjeks_embeds_autoload;
}

// Self-updates from GitHub releases. Private repos need a KJEKS_GITHUB_TOKEN constant.
if ( class_exists( \Soderlind\WordPress\GitHubUpdater::class ) ) {
	\Soderlind\WordPress\GitHubUpdater::init(
		github_url:   'https://github.com/soderlind/kjeks-embeds',
		plugin_file:  __FILE__,
		plugin_slug:  'kjeks-embeds',
		name_regex:   '/kjeks-embeds\.zip/',
		branch:       'main',
		check_period: 6,
		auth_token:   defined( 'KJEKS_GITHUB_TOKEN' ) ? KJEKS_GITHUB_TOKEN : '',
	);
}

require_once KJEKS_EMBEDS_DIR . 'includes/Providers.php';
require_once KJEKS_EMBEDS_DIR . 'includes/EmbedConfig.php';

add_action(
	'plugins_loaded',
	static function (): void {
		// Settings extends a core AddonKit base class, so load these after all
		// plugins (including Kjeks core and its autoloader) are available.
		require_once KJEKS_EMBEDS_DIR . 'includes/Settings.php';
		require_once KJEKS_EMBEDS_DIR . 'includes/OembedGate.php';
		require_once KJEKS_EMBEDS_DIR . 'includes/MapsShortcode.php';
		require_once KJEKS_EMBEDS_DIR . 'includes/Plugin.php';

		Plugin::instance()->boot();
	}
);
