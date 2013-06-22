# Analytics *for Craft*

This plugin helps you print Google Analytics tracking code.

## Installation

Put the **analytics/** folder inside **craft/plugins/**.

## Usage

Put this code *"after the opening `<body>` tag"* as they say :

    {{craft.analytics.code('UA-XXXXXXX-XX')}}

Alternatively, you can pass a custom entry, but remember that tracking data will be added to this entry.

    {% if entry is defined %}
        {{craft.analytics.code('UA-XXXXXXX-XX', entry)}}
    {% else %}
        {{craft.analytics.code('UA-XXXXXXX-XX')}}
    {% endif %}

## Tracked Data

Data being tracked through Google Analytics custom metrics :

- session
    - logged in
- entry
    - section name
    - author name
    - tags
    - publication year

## Tracking Downloads

Downloads are tracked through Google Analytics Events.

    <a href="http://domain/to/plugin.zip" onclick="{{craft.analytics.trackDownload()}}">Download</a>

You can categorize downloads by passing the category name as a parameter :

    <a href="http://domain/to/plugin.zip" onclick="{{craft.analytics.trackDownload('Plugins')}}">Download</a>

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


## Feedback

**Please provide feedback!** We want this plugin to make fit your needs as much as possible.
Please [get in touch](mailto:hello@dukt.net), and point out what you do and don't like. **No issue is too small.**

This plugin is actively maintained by [Benjamin David](https://github.com/benjamindavid), from [Dukt](http://dukt.net/).
