# Kjeks Embeds

Consent-gated YouTube, Vimeo, Spotify, SoundCloud, Dailymotion, and Google Maps
embeds for [Kjeks](https://github.com/soderlind/kjeks). Requires the Kjeks plugin.

> Part of the **kjeks family** — an adapter over the Kjeks public API. See the
> [kjeks ecosystem overview](https://github.com/soderlind/kjeks/blob/main/docs/architecture.md#9-ecosystem-the-kjeks-family).

## Installation

1. Install and activate [Kjeks](https://github.com/soderlind/kjeks) first — this add-on requires it.
2. Download the latest [`kjeks-embeds.zip`](https://github.com/soderlind/kjeks-embeds/releases/latest/download/kjeks-embeds.zip).
3. In WordPress, go to **Plugins → Add New → Upload Plugin** and upload the zip.
4. Activate the plugin (on multisite, **Network Activate** it).

The plugin updates itself automatically via GitHub releases using
[plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker).
Define the optional `KJEKS_GITHUB_TOKEN` constant for private repositories or
higher GitHub API rate limits.

## How it works

The embed is never loaded until the visitor grants the configured category —
the request to the provider is withheld, not just hidden. Because the gated
markup is identical for every visitor and consent is released client-side, the
output is safe behind a full-page cache.

- **Provider is detected from the pasted URL** for the adapters below, so those
  are rebuilt into a privacy-friendly embed. **Any other oEmbed provider** is
  gated generically when its returned markup is a single iframe.
- **oEmbed providers** (YouTube, Vimeo, Spotify, SoundCloud, Dailymotion) are
  gated automatically on `embed_oembed_html`, which both the `core/embed` block
  and the `[embed]` shortcode / autoembed run through.
- **Other iframe embeds** (Issuu, Scribd, TED, …) are gated with a generic
  fallback that reads the iframe returned by the provider — enable or disable it
  with the **Other iframe embeds** setting.
- **Script / `<blockquote>` embeds** (Twitter/X, TikTok, Facebook, Instagram)
  are not a single iframe and pass through untouched — they belong to Kjeks
  core's script gating, not this add-on.
- **Google Maps** has no oEmbed endpoint, so it is gated via a shortcode with a
  host allowlist that only accepts Google's own Maps embed URL.

Privacy touches: YouTube uses `youtube-nocookie.com`, and Vimeo unlisted-video
privacy hashes are preserved.

## Providers

| Provider | How it's gated | Notes |
| --- | --- | --- |
| YouTube | oEmbed (automatic) | Served via `youtube-nocookie.com`. |
| Vimeo | oEmbed (automatic) | Unlisted `?h=` privacy hash preserved. |
| Spotify | oEmbed (automatic) | Track, album, playlist, episode, show, artist. |
| SoundCloud | oEmbed (automatic) | Tracks and sets. |
| Dailymotion | oEmbed (automatic) | `dailymotion.com/video/…` and `dai.ly/…`. |
| Other iframe embeds | oEmbed (automatic) | Generic single-iframe fallback for any other provider. |
| Google Maps | `[kjeks_google_map]` shortcode | Maps has no oEmbed endpoint. |

## Configuration

- **Network Admin → Settings → Kjeks Embeds** (multisite) — the single settings screen.
- **Settings → Kjeks Embeds** (single site).

Fields: a consent category (**Analytics** or **Marketing**, default
`marketing`) or **Off** per embed source, so one provider can be marketing
while another is analytics. Google Maps has no **Off** option — it is only
rendered where you place the shortcode.

### Google Maps shortcode

```text
[kjeks_google_map src="https://www.google.com/maps/embed?pb=..." title="Our office" width="600" height="450"]
```

Copy the `src` from Google's **Share → Embed a map** dialog. Only
`www.google.com/maps/embed…` URLs are accepted. Add `category="analytics"` to
override the configured Maps category for a single map.

## Filters

- `kjeks_embeds_config` — filter the resolved category map, one category slug
  (or `off`) per source:
  `{ youtube, vimeo, spotify, soundcloud, dailymotion, other_iframes, maps }`.
- `kjeks_embeds_category` — filter the category for a single embed as
  `( string $category, string $provider, string $url )`; return `off` to skip
  gating that one embed.

## Notes and limitations

- An embed added **before** activation keeps its ungated cached HTML until the
  post is re-saved or the oEmbed cache is cleared; the reverse applies after
  deactivation.
- Script/`<blockquote>` embeds (Twitter/X, TikTok, Facebook) are out of scope —
  they are not a single iframe and do not fit the gated-iframe model.
- This add-on assists with consent wiring; it does not guarantee legal
  compliance.

## Development

```bash
composer install
composer test   # Pest + Brain Monkey unit tests
```

## License

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)
