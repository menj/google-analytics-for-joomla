# Google Analytics 4 Joomla Extension

## Version
2.1.0

## Requirements
- Joomla 4.4 LTS, 5.x, or 6.x — one package, same codebase
- PHP 8.1+

## Description
Installs the Google Analytics 4 (`gtag.js`) tracking snippet on your Joomla website, with Google Consent Mode v2 default signals for EEA/UK/CH compliance.

## Joomla 5 vs Joomla 6
This is a **single build**, not two separate ones. Joomla 5.x and 6.x share the same current plugin API (`CMSPlugin` + `SubscriberInterface`, `WebAssetManager`, DI service providers) — that API only starts getting trimmed down in Joomla 7.0. This plugin doesn't use anything from that removal list, and it doesn't rely on the "Behaviour – Backward Compatibility 6" shim plugin some legacy extensions need after upgrading to J6, so it's unaffected by that plugin's presence, absence, or Joomla 6's plugin-load-order change (#46233). A runtime check (`Joomla\CMS\Version::isCompatible()`) blocks activation with a logged warning on anything older than 4.4, in case someone installs it on an unsupported site.

## What changed in 2.0.0 / 2.1.0
- Rebuilt on the modern Joomla plugin architecture: `CMSPlugin` + `SubscriberInterface`, PSR-4 autoloaded (`src/`), registered via a DI `services/provider.php` — no dependency on the deprecated `JPlugin` base class. Verified against Joomla 5.x and 6.x.
- Added a minimum-version runtime guard.
- Added Google Consent Mode v2 default signals (`analytics_storage`, `ad_storage`, `ad_user_data`, `ad_personalization`, `wait_for_update`), configurable from the plugin settings. Required by Google for EEA/UK/CH traffic since March 2024.
- Script loading now goes through Joomla's `WebAssetManager` instead of `addCustomTag`.
- Added an "Anonymize IP" toggle.
- Fixed: XML manifest now actually references the shipped language constants (previously hardcoded English, ignoring translations).
- Validation no longer (ab)uses exceptions for expected invalid-input handling.

## Installation
1. Download the ZIP file.
2. Go to the Joomla dashboard.
3. Navigate to `System` -> `Install` -> `Extensions` (or `Extensions` -> `Manage` -> `Install` on older 4.x).
4. Upload the ZIP file and install it.
5. After installation, go to `System` -> `Manage` -> `Plugins`, search for "Google Analytics 4", and configure it.

## Configuration
1. Enter your GA4 Measurement ID (`G-XXXXXXXXXX`).
2. Review the Consent Mode v2 defaults under the "Consent Mode v2" tab. If you run a separate cookie-consent tool (CookieBot, Complianz, Klaro, etc.), leave the defaults as `denied` and have that tool push a `consent` `update` event to `window.dataLayer` once the visitor responds. This plugin only sets the pre-consent *default* state — it does not manage a consent banner.

## Error Handling
Invalid Measurement IDs are logged to Joomla's system log (`plg_system_google_analytics_4`) and surfaced as an admin warning; the tracking snippet is simply skipped rather than the page failing.

## Support
For support, please contact contact@menj.org.

## Backup and Recovery
Always back up your Joomla site before installing any new extension.

## Legal and Compliance
This plugin's Consent Mode v2 defaults help meet Google's technical requirements, but they are not legal advice. Make sure your overall setup (banner text, consent logging, data processing agreements) complies with privacy laws applicable to your site, such as GDPR/ePrivacy or UK GDPR.
