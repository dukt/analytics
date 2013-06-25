# Analytics <small>_for Craft CMS_</small>

Advanced Google Analytics tracking for Craft CMS.

- [Installation](#install)
- [Updating the plugin](#update)
- [Usage](#usage)
- [Tracking Downloads](#track-downloads)
- [Tracking Custom Events](#track-events)
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

Put this code *"after the opening `<body>` tag"* as they say :

    {{craft.analytics.code}}

<a id="track-downloads"></a>
## Tracking Downloads

Downloads are tracked through Google Analytics Events.

    <a href="http://domain/to/plugin.zip" onclick="{{craft.analytics.trackDownload()}}">Download</a>

You can categorize downloads by passing the category name as a parameter :

    <a href="http://domain/to/plugin.zip" onclick="{{craft.analytics.trackDownload('Plugins')}}">Download</a>

<a id="track-events"></a>
## Tracking Custom Events

- **category :** Typically the object that was interacted with (e.g. button)
- **action :** The type of interaction (e.g. click)
- **label :** Useful for categorizing events (e.g. nav buttons)
- **value :** Values must be non-negative. Useful to pass counts (e.g. 4 times)

Example :

    <a
        href="http://domain/to/plugin.zip"
        onclick="{{craft.analytics.trackEvent('Category', 'Action', 'Label', 4)}}"
    >Download</a>


<a id="api"></a>
## API Reference

### craft.analytics.code()
Returns Google Analytics tracking code

### craft.analytics.trackDownload(category)
Returns JavaScript for tracking downloads

### craft.analytics.trackEvent(category, action, label, number)
Returns JavaScript for tracking events

<a id="developer-api"></a>
## Developer API Reference

End-user shouldn't be using these APIs as they are made for developers. Please be careful.

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