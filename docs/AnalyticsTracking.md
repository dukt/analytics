# AnalyticsTracking


## Usage


### Basic

    // tracking object
    $track = new AnalyticsTracking;

    // track page
    $track->page(array(
        'documentPath' => '/test/pageview/blub.jpg',
        'documentTitle' => 'Test Image Title',
    ));

    // send
    $track->send();

### Extended

    // tracking object
    $track = new AnalyticsTracking;


    // track page

    $pageOptions = array(
        'documentPath' => '/test/pageview/blub.jpg',
        'documentTitle' => 'Test Image Title',
    );

    $track->page($pageOptions);


    // track campaign

    $campaignOptions = array(
        'documentPath' => '/test/path2',
        'documentTitle' => 'Test Title',
        'campaignName' => 'Test Campaign Name',
        'campaignSource' => 'Test Source',
        'campaignMedium' => 'Test Medium',
        'campaignContent' => 'Test Content',
        'campaignKeywords' => array('keyword 1', 'keyword 2'),
    );

    $track->campaign($campaignOptions);


    // send
    $track->send();

### Chained

    $pageOptions = array(
        'documentPath' => '/test/pageview/blub.jpg',
        'documentTitle' => 'Test Image Title',
    );

    $campaignOptions = array(
        'documentPath' => '/test/path2',
        'documentTitle' => 'Test Title',
        'campaignName' => 'Test Campaign Name',
        'campaignSource' => 'Test Source',
        'campaignMedium' => 'Test Medium',
        'campaignContent' => 'Test Content',
        'campaignKeywords' => array('keyword 1', 'keyword 2'),
    );

    $track = new AnalyticsTracking;
    $track->page($pageOptions)->campaign($campaignOptions)->send();

## Methods

### __construct ( $options = null )

**Options**

- `accountId` — If not set, the account ID selected in the plugin's settings will be used. Example value: 'UA-XXXXXXXXX'
- `proxy` — Example value: true
- `clientId` — Example value: '35009a79-1a05-49d7-b876-2b884d0f825b'
- `userId` — Example value: '11223344

### campaign ( $options )

**Options**

- `documentPath` — Example value: '/test/path2'
- `documentTitle` — Example value: 'Test Title'
- `campaignName` — Example value: 'Test Campaign Name'
- `campaignSource` — Example value: 'Test Source'
- `campaignMedium` — Example value: 'Test Medium'
- `campaignContent` — Example value: 'Test Content'
- `campaignKeywords` — Example value: ['keyword 1', 'keyword 2']


### ecommerceTransaction ( $options )

**Options**

- `id` — Example value: '1234'
- `affiliation` — Example value: 'Affiliation name'
- `revenue` — Example value: 123.45
- `shipping` — Example value: 12.34
- `tax` — Example value: 12.34
- `currency` — Example value: 'EUR'
- `transactionHost` — Example value: 'www.domain.tld'


### ecommerceItem ( $options )

**Options**

- `transactionID` — Example value: '1234'
- `name` — Example value: 'Product name'
- `price` — Example value: 123.45
- `quantity` — Example value: 1
- `sku` — Example value: 'product_sku'
- `category` — Example value: 'Category'
- `currency` — Example value: 'EUR'
- `transactionHost` — Example value: 'www.domain.tld'


### page ( $options )

**Options**

- `documentPath` — Example value: '/test/pageview/blub.jpg'
- `documentTitle` — Example value: 'Test Image Title'


### event ( $options )

**Options**

- `eventCategory` — Example value: 'Test Category'
- `eventValue` — Example value: 300
- `eventLabel` — Example value: 'Test Label'
- `eventAction` — Example value: 'Test Action'


### social ( $options )

**Options**

- `socialAction` — Example value: 'like'
- `socialNetwork` — Example value: 'facebook'
- `socialTarget` — Example value: '/home'


### appEvent ( $options )

**Options**

- `eventCategory` — Example value: 'App Category'
- `eventAction` — Example value: 'App Action'
- `appName` — Example value: 'Application Name'


### appScreen ( $options )

**Options**

- `appName` — Example value: 'Application Name'
- `appVersion` — Example value: '1.0'
- `contentDescription` — Example value: 'Description'


### exception ( $options )

**Options**

- `exceptionDescription` — Example value: 'Test Description'
- `exceptionFatal` — Example value: true