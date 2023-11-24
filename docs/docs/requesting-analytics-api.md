# Requesting Google Analytics’ API

## Request
Use the `craft.analytics.api` method to request the [Google Analytics Data API](https://developers.google.com/analytics/devguides/reporting/data/v1) from your templates and show reporting data on the front-end.

```twig
{% set response = craft.analytics.api({
    sourceId: 1,
    startDate: date('-1 year')|date("Y-m-d"),
    endDate: 'today',
    metrics: 'sessions',
    dimensions: 'firstUserSource',
    orderBys: {
      dimension: {
        dimensionName: 'firstUserSource',
      },
      desc: true,
    },
    offset: 0,
    limit: 10,
    dimensionFilter: {
        notExpression: {
            filter: {
                fieldName: 'firstUserSource',
                stringFilter: {
                    value: '(not set)',
                    matchType: 'EXACT',
                },
            },
        }
    },
}).send() %}
```

### Options
You can customize the request using the following options:

#### sourceId
The source ID.

#### startDate
Start date for fetching Analytics data. Requests can specify a start date formatted as YYYY-MM-DD, or as a relative date (e.g., today, yesterday, or 7daysAgo). The default value is 7daysAgo.

#### endDate
End date for fetching Analytics data. Request can should specify an end date formatted as YYYY-MM-DD, or as a relative date (e.g., today, yesterday, or 7daysAgo). The default value is yesterday.

#### dimensions
A comma-separated list of Analytics dimensions. E.g., 'firstUserSource,country'.

#### metrics
A comma-separated list of Analytics metrics. E.g., 'sessions,screenPageViews'. At least one metric must be specified.

#### orderBys
An [OrderBy](https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/OrderBy) object that specifies the sorting order of the result.

#### offset
The first row of data to retrieve, starting at 0. The default value is 0.

#### limit
The maximum number of rows to include in the response. The default value is 10,000.

#### dimensionFilter
A [FilterExpression](https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/FilterExpression).

#### metricFilter
A [FilterExpression](https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/FilterExpression).

#### keepEmptyRows
If set to `false`, the response does not include rows if all the retrieved metrics are equal to zero. The default is `false` which will exclude these rows.
    
## Response

```twig
{% if response.success %}
    <table class="table">
        <thead>
            <tr>
                <th>Page Path</th>
                <th>Source</th>
            </tr>
        </thead>

        <tbody>
        {% for row in response.report.rows %}
          <tr>
            <td>{{ row.dimensionValues[0].value }}</td>
            <td>{{ row.metricValues[0].value }}</td>
          </tr>
        {% endfor %}
        </tbody>
    </table>
{% else %}
    Error: {{ response.errorMessage }}
{% endif %}
```