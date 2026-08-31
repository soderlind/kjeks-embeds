<?php
/**
 * Settings storage and admin screen.
 *
 * @package Soderlind\KjeksEmbeds
 */

declare(strict_types=1);

namespace Soderlind\KjeksEmbeds;

use Soderlind\Kjeks\AddonKit\AbstractFormTab;

/**
 * An "Embeds" tab on the core Cookie Consent screen where an administrator maps
 * each embed provider to a consent category. Built on the shared AddonKit
 * scaffold, so menus, nonces, saving, and resolution come for free.
 */
final class Settings extends AbstractFormTab {

	public const SLUG = 'kjeks-embeds';

	protected function option_key(): string {
		return 'kjeks_embeds';
	}

	protected function get_tab_slug(): string {
		return 'embeds';
	}

	protected function get_tab_label(): string {
		return __( 'Embeds', 'kjeks-embeds' );
	}

	protected function get_tab_intro(): string {
		return __( 'Pick the category that releases each embed, or Off to leave it ungated. Different providers can use different categories.', 'kjeks-embeds' );
	}

	/**
	 * @return array<string, string>
	 */
	protected function defaults(): array {
		return EmbedConfig::defaults();
	}

	/**
	 * @param array<string, mixed> $raw Raw values.
	 * @return array<string, string>
	 */
	protected function normalize( array $raw ): array {
		return EmbedConfig::normalize( $this->from_request( $raw ) );
	}

	/**
	 * @param array<string, mixed> $raw Raw select values as submitted or stored.
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

	/**
	 * @param string                $prefix Field-name prefix (always '').
	 * @param array<string, string> $config Effective config to pre-fill.
	 */
	protected function render_fields( string $prefix, array $config ): void {
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
			<tbody>
				<?php foreach ( $rows as $key => $label ) : ?>
					<tr>
						<th scope="row"><label for="ke-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td><?php $this->category_select( $name( $key ), 'ke-' . $key, (string) ( $config[ $key ] ?? 'marketing' ), true ); ?></td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<th scope="row"><label for="ke-maps"><?php esc_html_e( 'Google Maps', 'kjeks-embeds' ); ?></label></th>
					<td><?php $this->category_select( $name( 'maps' ), 'ke-maps', (string) ( $config['maps'] ?? 'marketing' ), false ); ?></td>
				</tr>
			</tbody>
		</table>
		<p class="description">
			<?php esc_html_e( 'Other iframe embeds covers any oEmbed provider without a dedicated adapter (Issuu, Scribd, TED, ...). Script embeds such as Twitter or TikTok are not gated here.', 'kjeks-embeds' ); ?><br />
			<?php esc_html_e( 'Google Maps is gated via the [kjeks_google_map] shortcode; this row is its default category (override per map with category="analytics").', 'kjeks-embeds' ); ?>
		</p>
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
