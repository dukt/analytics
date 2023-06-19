# Configuration

Analytics supports several configuration settings. You can override their values in your `config/analytics.php` file.

```php
return [
    'cacheDuration' => 'PT30M', 
];
```

## cacheDuration

The amount of time cache should last. The value should be set as a [PHP date interval](http://www.php.net/manual/en/dateinterval.construct.php).

```php
'cacheDuration' => 'PT10M',
```

## enableCache

Whether requests to APIs should be cached or not.

```php
'enableCache' => true,
```

## enableFieldtype

Whether the Report field type is enabled or not.

```php
'enableFieldtype' => true,
```

## enableRealtime

Whether the Realtime widget is enabled or not.

```php
'enableRealtime' => false,
```

## enableWidgets

Whether Analytics widgets are enabled or disabled.

```php
'enableWidgets' => true,
```

## mapsApiKey

Google Maps API key. Used by the Geo chart.

```php
'mapsApiKey' => 'xxxxxxxxxxxxxxxxxxxxxxxx'
```

## oauthClientId

The Google API application’s OAuth client ID.

```php
'oauthClientId' => '000000000000-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com',
```

## oauthClientSecret

The Google API application’s OAuth client Secret.

```php
'oauthClientSecret' => 'xxxxxxxxxxxxxxxxxxxxxxxx',
```

## oauthProviderOptions

OAuth provider options.

```php
'oauthProviderOptions' => [],
```

## realtimeRefreshInterval

Interval at which the realtime widget should refresh its data (in seconds).

```php
'realtimeRefreshInterval' => 60,
```
