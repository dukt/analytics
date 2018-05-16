Changelog
=========

## 4.0.0-beta.3 - 2018-05-16

### Fixed
- Updated google/apiclient to `^v2.1.3`. ([#6](https://github.com/dukt/analytics/issues/6))

## 4.0.0-beta.2 - 2018-05-03

### Changed
- Removed unused `\dukt\analytics\services\Oauth::$token`.
- Removed unused `\dukt\analytics\services\Analytics::$tracking`.

### Fixed
- Fixed a bug where realtime reporting was always showing one active user. ([#1](https://github.com/dukt/analytics/issues/1))
- Fixed a bug where the URI used by report field could be wrong. ([#2](https://github.com/dukt/analytics/issues/2))

## 4.0.0-beta.1 - 2017-12-19

### Added
- Added `craftcms/cms` as a composer dependency.
- Added `demoMode`, `enableRealtime`, `mapsApiKey`, `oauthClientId`, `oauthClientSecret` and `oauthProviderOptions` config settings.
- Added `dukt/oauth2-google` as a composer dependency.
- Added `google/apiclient` as a composer dependency.
- Added `league/oauth2-client` as a composer dependency.
- Added `theiconic/php-ga-measurement-protocol` as a composer dependency.
- Added Craft 3 compatibility.
- Added support for Analytics API v4.
- Added support for multiple sites with the ability to setup multiple Google Analytics views, and to define one view per site.
- It is now possible to create multiple instances of the Realtime widget.
- Realtime widget now show “Pages per minute“ and “Active pages” informations.
- Added Craft license.

### Changed
- Now using `theiconic/php-ga-measurement-protocol` for tracking.
- Now using Gstatic loader instead of JSAPI loader.
- Replaced Gulp with Laravel Mix + Webpack for building plugin resources.
- Reworked `\dukt\analytics\models\ReportRequestCriteria` for Google Analytics API v4.
- The plugin doesn’t require the OAuth plugin anymore.
- Updated plugin icon.
- Updated schema version to `1.1.0`.

### Removed
- Removed `apiKey` and `filters` config settings.
- Removed `ins0/google-measurement-php-client` composer dependency.
