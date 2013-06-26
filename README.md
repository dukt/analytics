# Analytics <small>_for Craft CMS_</small>

Connect your Craft website to Google Analytics and get advanced reports and simplified event tracking.

- [Installation](#install)
- [Updating the plugin](#update)
- [Usage](#usage)
- [Tracking Events](#track-events)
- [Tracking Downloads](#track-downloads)
- [API Reference](#api)
- [Developer API Reference](#developer-api)
- [Feedback](#feedback)
- [Licensing](#license)


<a id="install"></a>
## Installation

1. Unzip and drop the Analytics plugin in your `craft/plugin` directory.
2. Go to **Admin / Analytics** and follow the installation instructions.
3. Once installed, select a website profile to work with


<a id="update"></a>
## Updating the plugin

Analytics add-on notices you when an update is available and lets you download and install it automatically.

If you need to manually update Analytics, simply replace **craft/plugins/analytics** folder.

<a id="usage"></a>
## Usage

You must have selected a website profile in **Admin / Analytics** in order to get the Google Analytics code up and running.

Place this code in each page of your website that your want to track.

    {{craft.analytics.code}}

<a id="track-events"></a>
## Tracking Events

- **Category :** Typically the object that was interacted with (e.g. button)
- **Action :** The type of interaction (e.g. click)
- **Label :** Useful for categorizing events (e.g. nav buttons)
- **Value :** Values must be non-negative. Useful to pass counts (e.g. 4 times)

Example :

    <a href="http://some/link" onclick="{{craft.analytics.trackEvent('Category', 'Action', 'Label', 4)}}">Download</a>


<a id="track-downloads"></a>
## Tracking Downloads

Downloads are tracked through Google Analytics Events.

    <a href="http://domain/to/plugin.zip" onclick="{{craft.analytics.trackEvent('Download', 'My Plugin')}}">Download</a>


<a id="api"></a>
## API Reference

### craft.analytics.code()
Returns Google Analytics tracking code

### craft.analytics.trackEvent(category, action, label=null, number=0)
Returns JavaScript for tracking events

<a id="developer-api"></a>
## Developer API Reference

Developer APIs have been created for the development of this plugin. They can be subject to change and shouldn't be used by end-users.

### craft.analytics.api
### craft.analytics.checkUpdates(pluginClass, pluginHandle)
### craft.analytics.getSetting(settingKey)
### craft.analytics.isConfigured()
### craft.analytics.isInstalled()
### craft.analytics.properties()

<a id="feedback"></a>
## Feedback

**Please provide feedback!** We want this plugin to make fit your needs as much as possible.
Please [get in touch](mailto:hello@dukt.net), and point out what you do and don't like. **No issue is too small.**

This plugin is actively maintained by [Benjamin David](https://github.com/benjamindavid), from [Dukt](http://dukt.net/).


<a id="license"></a>
## Licensing

Analytics is free to use during beta. You are not allowed to reuse / distribute this software.

Dukt © 2013 - All rights reserved