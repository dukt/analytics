# Tracking

## Tracking Object

```twig
{% set analyticsTracking = craft.analytics.tracking() %}
```

## Usage

```twig
{% set trackingId = 'UA-XXXXXXX-XX' %}

{% set analyticsTracking = craft.analytics.tracking().setTrackingId(trackingId) %}

{% do analyticsTracking
    .setDocumentPath('/mypage')
    .setDocumentTitle("My page")
    .sendPageview()
%}
```