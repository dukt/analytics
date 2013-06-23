# Analytics <small>_for Craft CMS_</small>

Advanced Analytics tracking for your Craft CMS website.

- [Installation](#install)
- [Usage](#usage)
- [Tracking Downloads](#track-downloads)
- [Tracking Custom Events](#track-events)
- [API Reference](#api)
- [Feedback](#feedback)


<a id="install"></a>
## Installation

1. Unzip and drop the Analytics plugin in your `craft/plugin` directory.
2. Go to **Admin / Analytics** and follow the installation instructions.
3. Once installed, select a website profile to work with

<a id="usage"></a>
## Usage

Put this code *"after the opening `<body>` tag"* as they say :

    {{craft.analytics.code('UA-XXXXXXX-XX')}}

Alternatively, you can pass a custom entry, but remember that tracking data will be added to this entry.

    {% if entry is defined %}
        {{craft.analytics.code('UA-XXXXXXX-XX', entry)}}
    {% else %}
        {{craft.analytics.code('UA-XXXXXXX-XX')}}
    {% endif %}

### Tracked Data (not implemented yet)

Data being tracked through Google Analytics custom metrics :

- session
    - logged in
- entry
    - section name
    - author name
    - tags
    - publication year

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

### {{craft.analytics.code(code, entry)}}
### {{craft.analytics.trackDownload(category)}}
### {{craft.analytics.trackEvent(category, action, label, number)}}
### {{craft.analytics.api}}

<a id="feedback"></a>
## Feedback

**Please provide feedback!** We want this plugin to make fit your needs as much as possible.
Please [get in touch](mailto:hello@dukt.net), and point out what you do and don't like. **No issue is too small.**

This plugin is actively maintained by [Benjamin David](https://github.com/benjamindavid), from [Dukt](http://dukt.net/).
