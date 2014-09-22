## craft.analytics.track

craft.analytics.track returns an AnalyticsTracking object.

### Syntax

#### Basic

    {{ craft.analytics.track.page({
        documentPath: '/test/pageview/green.jpg',
        documentTitle: 'Test Image Green',
    }).send() }}

#### Deconstructed

    {{ craft.analytics.track.page({
        documentPath: '/test/pageview/blue.jpg',
        documentTitle: 'Test Image Blue',
    }) }}

    {{ craft.analytics.track.page({
        documentPath: '/test/pageview/red.jpg',
        documentTitle: 'Test Image Red',
    }) }}

    {{ craft.analytics.track.send() }}

#### Chained

    {{ craft.analytics.track.page({
        documentPath: '/test/pageview/green.jpg',
        documentTitle: 'Test Image Green',
    }).page({
        documentPath: '/test/pageview/yellow.jpg',
        documentTitle: 'Test Image Yellow',
    }).send() }}

#### Tracking Options

These options can be applied for any kind of tracking.

If the `accountId` is not set, the account ID selected in the plugin's settings will be used.

    {{ craft.analytics.track({
        accountId: 'UA-XXXXXXXXX',
        proxy: true,
        clientId: '35009a79-1a05-49d7-b876-2b884d0f825b',
        userId: '11223344'
    }).page({
        ...
    }).send() }}

### Campaign Tracking

    {{ craft.analytics.track.campaign({
        documentPath: '/test/path2',
        documentTitle: 'Test Title',
        campaignName: 'Test Campaign Name',
        campaignSource: 'Test Source',
        campaignMedium: 'Test Medium',
        campaignContent: 'Test Content',
        campaignKeywords: ['keyword 1', 'keyword 2'],
    }).send() }}

### Ecommerce Transaction Tracking

    {{ craft.analytics.track.ecommerceTransaction({
        id: '1234',
        affiliation: 'Affiliation name',
        revenue: 123.45,
        shipping: 12.34,
        tax: 12.34,
        currency: 'EUR',
        transactionHost: 'www.domain.tld',
    }).send() }}

### Ecommerce Item Tracking

    {{ craft.analytics.track.ecommerceItem({
        transactionID: '1234',
        name: 'Product name',
        price: 123.45,
        quantity: 1,
        sku: 'product_sku',
        category: 'Category',
        currency: 'EUR',
        transactionHost: 'www.domain.tld',
    }).send() }}

### Page Tracking

    {{ craft.analytics.track.page({
        documentPath: '/test/pageview/blub.jpg',
        documentTitle: 'Test Image Title',
    }).send() }}

### Event Tracking

    {{ craft.analytics.track.event({
        eventCategory: 'Test Category',
        eventValue: 300,
        eventLabel: 'Test Label',
        eventAction: 'Test Action',
    }).send() }}

### Social Tracking

    {{ craft.analytics.track.social({
        socialAction: 'like',
        socialNetwork: 'facebook',
        socialTarget: '/home',
    }).send() }}

### App Event Tracking

    {{ craft.analytics.track.appEvent({
        eventCategory: 'App Category',
        eventAction: 'App Action',
        appName: 'Application Name',
    }).send() }}

### App Screen Tracking

    {{ craft.analytics.track.appScreen({
        appName: 'Application Name',
        appVersion: '1.0',
        contentDescription: 'Description',
    }).send() }}

### Exception

    {{ craft.analytics.track.exception({
        exceptionDescription: 'Test Description',
        exceptionFatal: true,
    }).send() }}
