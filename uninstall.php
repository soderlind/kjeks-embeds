<?php
/**
 * Uninstall handler.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'kjeks_embeds' );
delete_site_option( 'kjeks_embeds' );
