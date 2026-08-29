=== Kjeks Embeds ===
Contributors: PerS
Tags: cookies, consent, oembed, youtube, privacy
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 8.3
Requires Plugins: kjeks
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Consent-gated YouTube, Vimeo, Spotify, SoundCloud, Dailymotion, and Google Maps embeds for Kjeks — withheld until the visitor consents.

== Description ==

Kjeks Embeds holds third-party embeds behind the Kjeks consent layer so no request reaches the provider until the visitor grants the configured category. The gated markup is identical for every visitor, so it is safe behind a full-page cache; consent is released client-side.

* YouTube, Vimeo, Spotify, SoundCloud, and Dailymotion are gated automatically wherever they are embedded (the `core/embed` block and the `[embed]` shortcode / autoembed).
* Any other oEmbed provider (Issuu, Scribd, TED, ...) is gated with a generic single-iframe fallback you can turn off.
* Google Maps is gated via the `[kjeks_google_map]` shortcode, since Maps has no oEmbed endpoint.
* Each embed source has its own consent category (Analytics or Marketing) or Off, so one embed can be marketing while another is analytics.
* Script/blockquote embeds (Twitter/X, TikTok, Facebook, Instagram) are not a single iframe and pass through untouched.
* YouTube uses `youtube-nocookie.com`; Vimeo unlisted-video privacy hashes are preserved.
* Self-updates from GitHub releases via wordpress-github-updater (bundled plugin-update-checker). Define the optional `KJEKS_GITHUB_TOKEN` constant for private repositories.

Requires the Kjeks plugin.

== Installation ==

1. Install and activate Kjeks first — this add-on requires it.
2. Place this plugin in `wp-content/plugins/kjeks-embeds` and activate it (on multisite, network-activate).
3. Configure under **Network Admin → Settings → Kjeks Embeds** (multisite) or **Settings → Kjeks Embeds** (single site).
4. Pick a consent category (or Off) per embed source.

For Google Maps, copy the src from Google's "Share → Embed a map" dialog into `[kjeks_google_map src="..."]`. Add `category="analytics"` to override the Maps category for a single map.

== Frequently Asked Questions ==

= A YouTube video I embedded before activating stays ungated =

WordPress caches the oEmbed HTML in post meta. Re-save the post or clear the oEmbed cache so the gated markup is generated. The same applies in reverse after deactivating.

== Changelog ==

= 0.1.0 =
* Initial release: consent-gated YouTube, Vimeo, Spotify, SoundCloud, and Dailymotion via oEmbed, a generic single-iframe fallback for any other provider, plus Google Maps via the `[kjeks_google_map]` shortcode.
* Per-embed consent categories (Analytics/Marketing/Off) plus the `kjeks_embeds_category` filter for per-URL overrides.
* Self-updates from GitHub releases; GitHub Actions workflows to build and attach the release ZIP.
