# Changelog

## 0.1.1 - 2026-08-29

- Fix gated embed rendering: correct the aspect ratio and remove the spacer gap.
- Register the settings submenu after the core Kjeks menu so the Embeds page resolves on single-site and Multisite.

## 0.1.0 - 2026-08-29

- Initial release: consent-gated YouTube, Vimeo, Spotify, SoundCloud, and Dailymotion via oEmbed, a generic single-iframe fallback for any other provider, plus Google Maps via the `[kjeks_google_map]` shortcode.
- Per-embed consent categories (Analytics / Marketing / Off), so one embed can be marketing while another is analytics; `kjeks_embeds_category` filter for per-URL overrides and `kjeks_embeds_config` for the resolved category map.
- Privacy touches: YouTube served via `youtube-nocookie.com`, Vimeo unlisted-video privacy hashes preserved.
- Self-updates from GitHub releases via the `wordpress-github-updater` library (bundled `plugin-update-checker`). Define the optional `KJEKS_GITHUB_TOKEN` constant for private repositories or higher GitHub API rate limits.
- GitHub Actions workflows to build and attach the release ZIP on published releases and on manual dispatch.
