Changelog
=========

## 4.0.13 - 2021-04-14

### Added
- Added environment variable suggestions support for the OAuth client ID and secret.

### Changed
- The plugin’s icon has been updated.
- Improved OAuth configuration instructions.
- Updated `laravel-mix` to 6.0.16.

### Fixed
- Removed the trailing slash of the OAuth JavaScript origin URL.

## 4.0.12 - 2021-03-04

### Added
- Added `GoogleChartsAsset` to handle Google Charts related resources.
- Added `webpack` 5.9 dependency.

### Changed
- Updated `league/oauth2-client` to 2.5.
- Updated `theiconic/php-ga-measurement-protocol` to 2.8.
- Updated `google/apiclient` to 2.6.
- Updated `sass-loader` to 11.0.
- Updated `laravel-mix` to 6.0.
- Updated `del` to 6.0.
- Updated `sass` to 1.32.


### Fixed
- Fixed currency formatting for Craft 3.6.
- Fixed a bug where the environment variables were not being parsed OAuth options like client ID or secret.
- Fixed a bug where the settings dropdown were masked by other widgets. ([#28](https://github.com/dukt/analytics/issues/28))
- Fixed a bug where the report widget could try to refresh the CP dashboard grid that didn't exist.

## 4.0.11 - 2020-09-18

### Fixed
- Fixed the styles of the sidebar in the plugin's settings.
- Fixed a bug where the report widget’s default title was not properly hidden.
- Fixed a bug where geo charts were not loading.
- Fixed a bug where charts were not getting redrawn when widgets size changed.
- Fixed a bug where horizontal axis labels were blinking when changing the type of another report widget.
- Fixed a bug where horizontal axis labels were not showing properly.
- Fixed widget icons.

## 4.0.10 - 2020-01-09

### Fixed
- Fixed a bug where the plugin was unable to refresh an OAuth token.

## 4.0.9 - 2019-05-30

### Fixed
- Fixed a bug where the horizontal axis’ ticks could be mixed with the ones from another widget for Area charts. ([#16](https://github.com/dukt/analytics/issues/16))

## 4.0.8 - 2019-05-15

### Fixed
- Fixed an issue where `m180529_125418_info` migration was trying to use undefined settings. ([#15](https://github.com/dukt/analytics/issues/15))

## 4.0.7 - 2019-02-01

### Changed
- Replaced `dukt/oauth2-google` composer dependency with `league/oauth2-google`.

## 4.0.6 - 2019-02-01

### Fixed
- Fixed a bug where the plugin was not properly getting all accounts, properties, and views from Google Analytics. ([#10](https://github.com/dukt/analytics/issues/10))
- Fixed a bug where the Report field was trying to get a data report for new elements. ([#13](https://github.com/dukt/analytics/issues/13))

## 4.0.5 - 2018-10-26

### Fixed
- Fixed a bug where it was not possible to create a new view in the plugin’s settings when the account explorer data was already cached.

## 4.0.4 - 2018-09-21

### Fixed
- Fixed a bug where pagination was not working for Table charts. ([#9](https://github.com/dukt/analytics/issues/9))
- Fixed the indentation of the template files.

## 4.0.3 - 2018-09-10

### Added
- Added missing Craft 3 upgrade migration.

### Changed
- Improved exception handling for the Reports controller.
- Updated `google/apiclient` dependency to `^v2.2.0`.
- Moved continent and subcontinent related methods to a new Geo service.
- Added Craft 3 upgrade migration.

## 4.0.2 - 2018-06-27

### Fixed
- Fixed a bug where the Google Charts library was not loaded early enough, resulting in javascript errors.

## 4.0.1 - 2018-06-26

### Fixed
- Fixed a bug where plugin info couldn’t be saved.

## 4.0.0 - 2018-05-29

### Added
- Added E-commerce widget to display revenue and transactions data.

### Changed
- Moved `forceConnect` and `token` settings to a new `analytics_info` table.
- Removed `forceConnect` config setting.
- Removed `token` config setting.

### Fixed
- Fixed a bug where the Analytics asset bundle was getting registered even though the plugin was not installed.
- Fixed a bug where the Realtime widget was trying to use the plugin instance even though the plugin was not installed.

## 4.0.0-beta.4 - 2018-05-24

### Fixed
- Fixed a bug where saving a view was throwing an error. ([#7](https://github.com/dukt/analytics/issues/7))

## 4.0.0-beta.3 - 2018-05-16

### Changed
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
