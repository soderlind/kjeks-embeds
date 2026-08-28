<?php
/**
 * Settings storage and admin screen.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

namespace Soderlind\KjeksEmbeds;

/**
 * A single settings surface — Network Admin on Multisite, Settings on a
 * single site — matching Kjeks core's own "one admin surface" convention
 * rather than layering network defaults with per-site overrides.
 */
final class Settings {

	private const OPTION = 'kjeks_embeds';
	public const SLUG     = 'kjeks-embeds';

	/**
	 * @return array<string, string>
	 */
	public function resolve(): array {
		$stored = is_multisite() ? get_site_option( self::OPTION, array() ) : get_option( self::OPTION, array() );
		$config = EmbedConfig::normalize( is_array( $stored ) ? $stored : array() );

		/**
		 * Filters the resolved embed gating configuration.
		 *
		 * @param array<string, string> $config Resolved config: a category slug (or 'off') per embed source.
		 */
		return (array) apply_filters( 'kjeks_embeds_config', $config );
	}

	public function hooks(): void {
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'menu' ) );
			add_action( 'admin_post_kjeks_embeds_save', array( $this, 'save' ) );
			return;
		}

		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function menu(): void {
		if ( is_multisite() ) {
			add_submenu_page(
				'settings.php',
				__( 'Kjeks Embeds', 'kjeks-embeds' ),
				__( 'Kjeks Embeds', 'kjeks-embeds' ),
				'manage_network_options',
				self::SLUG,
				array( $this, 'render_network_page' )
			);
			return;
		}

		add_options_page(
			__( 'Kjeks Embeds', 'kjeks-embeds' ),
			__( 'Kjeks Embeds', 'kjeks-embeds' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render_site_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'kjeks_embeds',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * @param mixed $input Raw submitted values.
	 * @return array<string, string>
	 */
	public function sanitize( mixed $input ): array {
		return EmbedConfig::normalize( $this->from_request( is_array( $input ) ? $input : array() ) );
	}

	/**
	 * @param array<string, mixed> $raw Raw select values as submitted.
	 * @return array<string, string>
	 */
	private function from_request( array $raw ): array {
		$out = array();
		foreach ( EmbedConfig::gateable() as $key ) {
			$out[ $key ] = sanitize_key( (string) ( $raw[ $key ] ?? '' ) );
		}
		$out['maps'] = sanitize_key( (string) ( $raw['maps'] ?? '' ) );

		return $out;
	}

	public function render_site_page(): void {
		$config = $this->resolve();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Kjeks Embeds', 'kjeks-embeds' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'kjeks_embeds' ); ?>
				<?php $this->fields( self::OPTION, $config ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	public function render_network_page(): void {
		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'kjeks-embeds' ) );
		}

		$config = $this->resolve();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Kjeks Embeds', 'kjeks-embeds' ); ?></h1>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="kjeks_embeds_save" />
				<?php wp_nonce_field( 'kjeks_embeds_save' ); ?>
				<?php $this->fields( '', $config ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	public function save(): void {
		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'kjeks-embeds' ) );
		}

		check_admin_referer( 'kjeks_embeds_save' );

		$values = EmbedConfig::normalize( $this->from_request( wp_unslash( $_POST ) ) );

		update_site_option( self::OPTION, $values );

		wp_safe_redirect( add_query_arg( 'updated', '1', network_admin_url( 'settings.php?page=' . self::SLUG ) ) );
		exit;
	}

	/**
	 * @param string               $prefix Field-name prefix (site form) or '' (network form, top-level POST keys).
	 * @param array<string, string> $config Effective config to pre-fill.
	 */
	private function fields( string $prefix, array $config ): void {
		$name = static fn ( string $key ): string => '' === $prefix ? $key : $prefix . '[' . $key . ']';
		$rows = array(
			'youtube'       => __( 'YouTube', 'kjeks-embeds' ),
			'vimeo'         => __( 'Vimeo', 'kjeks-embeds' ),
			'spotify'       => __( 'Spotify', 'kjeks-embeds' ),
			'soundcloud'    => __( 'SoundCloud', 'kjeks-embeds' ),
			'dailymotion'   => __( 'Dailymotion', 'kjeks-embeds' ),
			'other_iframes' => __( 'Other iframe embeds', 'kjeks-embeds' ),
		);
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Consent category per embed', 'kjeks-embeds' ); ?></th>
				<td>
					<p class="description"><?php esc_html_e( 'Pick the category that releases each embed, or Off to leave it ungated. Different providers can use different categories.', 'kjeks-embeds' ); ?></p>
					<table class="widefat striped" style="max-width:30em;margin-top:.5em">
						<?php foreach ( $rows as $key => $label ) : ?>
							<tr>
								<td><label for="ke-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></td>
								<td><?php $this->category_select( $name( $key ), 'ke-' . $key, (string) ( $config[ $key ] ?? 'marketing' ), true ); ?></td>
							</tr>
						<?php endforeach; ?>
						<tr>
							<td><label for="ke-maps"><?php esc_html_e( 'Google Maps', 'kjeks-embeds' ); ?></label></td>
							<td><?php $this->category_select( $name( 'maps' ), 'ke-maps', (string) ( $config['maps'] ?? 'marketing' ), false ); ?></td>
						</tr>
					</table>
					<p class="description">
						<?php esc_html_e( 'Other iframe embeds covers any oEmbed provider without a dedicated adapter (Issuu, Scribd, TED, ...). Script embeds such as Twitter or TikTok are not gated here.', 'kjeks-embeds' ); ?><br />
						<?php esc_html_e( 'Google Maps is gated via the [kjeks_google_map] shortcode; this row is its default category (override per map with category="analytics").', 'kjeks-embeds' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	private function category_select( string $name, string $id, string $current, bool $allow_off ): void {
		?>
		<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
			<?php if ( $allow_off ) : ?>
				<option value="off" <?php selected( $current, 'off' ); ?>><?php esc_html_e( 'Off (not gated)', 'kjeks-embeds' ); ?></option>
			<?php endif; ?>
			<?php foreach ( EmbedConfig::categories() as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}
}
