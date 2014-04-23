<?php

namespace Craft;

class Analytics_CustomReportWidget extends BaseWidget
{
    private $types = array(
        'acquisition' => "Acquisition",
        'conversions' => "Conversions",
        'counts'      => "Counts",
        'geo'         => "Geo",
        'mobile'      => "Mobile",
        'pages'       => "Pages",
        'realtime'    => "Real-Time",
        'technology'  => "Technology",
        'visits'      => "Visits"
    );

    protected function defineSettings()
    {
        return array(
           'name' => array(AttributeType::String),
           'colspan' => array(AttributeType::Number, 'default' => 2),
           'options' => array(AttributeType::Mixed),
        );
    }

    public function getName()
    {
        return Craft::t('Analytics Custom Report');
    }


    public function getSettingsHtml()
    {
        $types = $this->getTypes();

        foreach($types as $k => $type)
        {
            $types[$k] = Craft::t($type);
        }

        if(!empty($types['realtime'])) {
            $types['realtime'] .= ' (beta)';
        }

        $dimensionsOpts = $this->getDimensionsOpts();
        $metricsOpts = $this->getMetricsOpts();

        return craft()->templates->render('analytics/_widgets/customReportSettings', array(
           'settings' => $this->getSettings(),
           'types' => $types,
           'dimensionsOpts' => $dimensionsOpts,
           'metricsOpts' => $metricsOpts
        ));
    }

    public function getBodyHtml()
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $this->getSettings();

        $variables = array(
            'pluginSettings' => $plugin->getSettings(),
            'settings' => $settings,
            'widget' => $this,
            'colspan' => $this->getColspan()
        );

        $html = craft()->templates->render('analytics/_widgets/customReport', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }


    public function getType($k)
    {
        if(!empty($k)) {
            return $this->types[$k];
        }
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getColspan()
    {
        if(craft()->version > 1.3)
        {
            $settings = $this->getSettings();

            if(isset($settings->colspan))
            {
                if($settings->colspan > 0)
                {
                    return $settings->colspan;
                }
            }
        }

        return 1;
    }

    private function getMetricsOpts()
    {
        return array(

            // User
            array('optgroup' => 'User'),
            array(
                'label' => 'ga:users',
                'value' => 'ga:users'
            ),
            array(
                'label' => 'ga:newUsers',
                'value' => 'ga:newUsers'
            ),
            array(
                'label' => 'ga:percentNewSessions',
                'value' => 'ga:percentNewSessions'
            ),

            // Session
            array('optgroup' => 'Session'),
            array(
                'label' => 'ga:sessions',
                'value' => 'ga:sessions'
            ),
            array(
                'label' => 'ga:bounces',
                'value' => 'ga:bounces'
            ),
            array(
                'label' => 'ga:bounceRate',
                'value' => 'ga:bounceRate'
            ),
            array(
                'label' => 'ga:sessionDuration',
                'value' => 'ga:sessionDuration'
            ),
            array(
                'label' => 'ga:avgSessionDuration',
                'value' => 'ga:avgSessionDuration'
            ),


            // Traffic Sources
            array('optgroup' => 'Traffic Sources'),
            array(
                'label' => 'ga:organicSearches',
                'value' => 'ga:organicSearches'
            ),

            // Adwords
            array('optgroup' => 'Adwords'),
            array(
                'label' => 'ga:impressions',
                'value' => 'ga:impressions'
            ),
            array(
                'label' => 'ga:adClicks',
                'value' => 'ga:adClicks'
            ),
            array(
                'label' => 'ga:adCost',
                'value' => 'ga:adCost'
            ),
            array(
                'label' => 'ga:CPM',
                'value' => 'ga:CPM'
            ),
            array(
                'label' => 'ga:CPC',
                'value' => 'ga:CPC'
            ),
            array(
                'label' => 'ga:CTR',
                'value' => 'ga:CTR'
            ),
            array(
                'label' => 'ga:costPerTransaction',
                'value' => 'ga:costPerTransaction'
            ),
            array(
                'label' => 'ga:costPerGoalConversion',
                'value' => 'ga:costPerGoalConversion'
            ),
            array(
                'label' => 'ga:costPerConversion',
                'value' => 'ga:costPerConversion'
            ),
            array(
                'label' => 'ga:RPC',
                'value' => 'ga:RPC'
            ),
            array(
                'label' => 'ga:ROI',
                'value' => 'ga:ROI'
            ),
            array(
                'label' => 'ga:margin',
                'value' => 'ga:margin'
            ),

            // Goal Conversions
            array('optgroup' => 'Goal Conversions'),
                array(
                        'label' => 'ga:goalXXStarts',
                        'value' => 'ga:goalXXStarts'
                    ),
                array(
                        'label' => 'ga:goalStartsAll',
                        'value' => 'ga:goalStartsAll'
                    ),
                array(
                        'label' => 'ga:goalXXCompletions',
                        'value' => 'ga:goalXXCompletions'
                    ),
                array(
                        'label' => 'ga:goalCompletionsAll',
                        'value' => 'ga:goalCompletionsAll'
                    ),
                array(
                        'label' => 'ga:goalXXValue',
                        'value' => 'ga:goalXXValue'
                    ),
                array(
                        'label' => 'ga:goalValueAll',
                        'value' => 'ga:goalValueAll'
                    ),
                array(
                        'label' => 'ga:goalValuePerSession',
                        'value' => 'ga:goalValuePerSession'
                    ),
                array(
                        'label' => 'ga:goalXXConversionRate',
                        'value' => 'ga:goalXXConversionRate'
                    ),
                array(
                        'label' => 'ga:goalConversionRateAll',
                        'value' => 'ga:goalConversionRateAll'
                    ),
                array(
                        'label' => 'ga:goalXXAbandons',
                        'value' => 'ga:goalXXAbandons'
                    ),
                array(
                        'label' => 'ga:goalAbandonsAll',
                        'value' => 'ga:goalAbandonsAll'
                    ),
                array(
                        'label' => 'ga:goalXXAbandonRate',
                        'value' => 'ga:goalXXAbandonRate'
                    ),
                array(
                        'label' => 'ga:goalAbandonRateAll',
                        'value' => 'ga:goalAbandonRateAll'
                    ),

            // Platform or Device
            array('optgroup' => 'Platform or Device'),

            // Geo Network
            array('optgroup' => 'Geo Network'),

            // System
            array('optgroup' => 'System'),

            // Social Activities
            array('optgroup' => 'Social Activities'),
                array(
                        'label' => 'ga:socialActivities',
                        'value' => 'ga:socialActivities'
                    ),

            // Page Tracking
            array('optgroup' => 'Page Tracking'),
                array(
                        'label' => 'ga:pageValue',
                        'value' => 'ga:pageValue'
                    ),
                array(
                        'label' => 'ga:entrances',
                        'value' => 'ga:entrances'
                    ),
                array(
                        'label' => 'ga:entranceRate',
                        'value' => 'ga:entranceRate'
                    ),
                array(
                        'label' => 'ga:pageviews',
                        'value' => 'ga:pageviews'
                    ),
                array(
                        'label' => 'ga:pageviewsPerSession',
                        'value' => 'ga:pageviewsPerSession'
                    ),
                array(
                        'label' => 'ga:pageviewsPerVisit',
                        'value' => 'ga:pageviewsPerVisit'
                    ),
                array(
                        'label' => 'ga:uniquePageviews',
                        'value' => 'ga:uniquePageviews'
                    ),
                array(
                        'label' => 'ga:timeOnPage',
                        'value' => 'ga:timeOnPage'
                    ),
                array(
                        'label' => 'ga:avgTimeOnPage',
                        'value' => 'ga:avgTimeOnPage'
                    ),
                array(
                        'label' => 'ga:exits',
                        'value' => 'ga:exits'
                    ),
                array(
                        'label' => 'ga:exitRate',
                        'value' => 'ga:exitRate'
                    ),

            // Internal Search
            array('optgroup' => 'Internal Search'),
                array(
                        'label' => 'ga:searchResultViews',
                        'value' => 'ga:searchResultViews'
                    ),
                array(
                        'label' => 'ga:searchUniques',
                        'value' => 'ga:searchUniques'
                    ),
                array(
                        'label' => 'ga:avgSearchResultViews',
                        'value' => 'ga:avgSearchResultViews'
                    ),
                array(
                        'label' => 'ga:searchSessions',
                        'value' => 'ga:searchSessions'
                    ),
                array(
                        'label' => 'ga:percentSessionsWithSearch',
                        'value' => 'ga:percentSessionsWithSearch'
                    ),
                array(
                        'label' => 'ga:searchDepth',
                        'value' => 'ga:searchDepth'
                    ),
                array(
                        'label' => 'ga:avgSearchDepth',
                        'value' => 'ga:avgSearchDepth'
                    ),
                array(
                        'label' => 'ga:searchRefinements',
                        'value' => 'ga:searchRefinements'
                    ),
                array(
                        'label' => 'ga:percentSearchRefinements',
                        'value' => 'ga:percentSearchRefinements'
                    ),
                array(
                        'label' => 'ga:searchDuration',
                        'value' => 'ga:searchDuration'
                    ),
                array(
                        'label' => 'ga:avgSearchDuration',
                        'value' => 'ga:avgSearchDuration'
                    ),
                array(
                        'label' => 'ga:searchExits',
                        'value' => 'ga:searchExits'
                    ),
                array(
                        'label' => 'ga:searchExitRate',
                        'value' => 'ga:searchExitRate'
                    ),
                array(
                        'label' => 'ga:searchGoalXXConversionRate',
                        'value' => 'ga:searchGoalXXConversionRate'
                    ),
                array(
                        'label' => 'ga:searchGoalConversionRateAll',
                        'value' => 'ga:searchGoalConversionRateAll'
                    ),
                array(
                        'label' => 'ga:goalValueAllPerSearch',
                        'value' => 'ga:goalValueAllPerSearch'
                    ),

            // Site Speed
            array('optgroup' => 'Site Speed'),
                array(
                        'label' => 'ga:pageLoadTime',
                        'value' => 'ga:pageLoadTime'
                    ),
                array(
                        'label' => 'ga:pageLoadSample',
                        'value' => 'ga:pageLoadSample'
                    ),
                array(
                        'label' => 'ga:avgPageLoadTime',
                        'value' => 'ga:avgPageLoadTime'
                    ),
                array(
                        'label' => 'ga:domainLookupTime',
                        'value' => 'ga:domainLookupTime'
                    ),
                array(
                        'label' => 'ga:avgDomainLookupTime',
                        'value' => 'ga:avgDomainLookupTime'
                    ),
                array(
                        'label' => 'ga:pageDownloadTime',
                        'value' => 'ga:pageDownloadTime'
                    ),
                array(
                        'label' => 'ga:avgPageDownloadTime',
                        'value' => 'ga:avgPageDownloadTime'
                    ),
                array(
                        'label' => 'ga:redirectionTime',
                        'value' => 'ga:redirectionTime'
                    ),
                array(
                        'label' => 'ga:avgRedirectionTime',
                        'value' => 'ga:avgRedirectionTime'
                    ),
                array(
                        'label' => 'ga:serverConnectionTime',
                        'value' => 'ga:serverConnectionTime'
                    ),
                array(
                        'label' => 'ga:avgServerConnectionTime',
                        'value' => 'ga:avgServerConnectionTime'
                    ),
                array(
                        'label' => 'ga:serverResponseTime',
                        'value' => 'ga:serverResponseTime'
                    ),
                array(
                        'label' => 'ga:avgServerResponseTime',
                        'value' => 'ga:avgServerResponseTime'
                    ),
                array(
                        'label' => 'ga:speedMetricsSample',
                        'value' => 'ga:speedMetricsSample'
                    ),
                array(
                        'label' => 'ga:domInteractiveTime',
                        'value' => 'ga:domInteractiveTime'
                    ),
                array(
                        'label' => 'ga:avgDomInteractiveTime',
                        'value' => 'ga:avgDomInteractiveTime'
                    ),
                array(
                        'label' => 'ga:domContentLoadedTime',
                        'value' => 'ga:domContentLoadedTime'
                    ),
                array(
                        'label' => 'ga:avgDomContentLoadedTime',
                        'value' => 'ga:avgDomContentLoadedTime'
                    ),
                array(
                        'label' => 'ga:domLatencyMetricsSample',
                        'value' => 'ga:domLatencyMetricsSample'
                    ),

            // App Tracking
            array('optgroup' => 'App Tracking'),
                array(
                    'label' => 'ga:screenviews',
                    'value' => 'ga:screenviews'
                ),
                array(
                    'label' => 'ga:uniqueScreenviews',
                    'value' => 'ga:uniqueScreenviews'
                ),
                array(
                    'label' => 'ga:screenviewsPerSession',
                    'value' => 'ga:screenviewsPerSession'
                ),
                array(
                    'label' => 'ga:timeOnScreen',
                    'value' => 'ga:timeOnScreen'
                ),
                array(
                    'label' => 'ga:avgScreenviewDuration',
                    'value' => 'ga:avgScreenviewDuration'
                ),

            // Event Tracking
            array('optgroup' => 'Event Tracking'),
                array(
                    'label' => 'ga:totalEvents',
                    'value' => 'ga:totalEvents'
                ),
                array(
                    'label' => 'ga:uniqueEvents',
                    'value' => 'ga:uniqueEvents'
                ),
                array(
                    'label' => 'ga:eventValue',
                    'value' => 'ga:eventValue'
                ),
                array(
                    'label' => 'ga:avgEventValue',
                    'value' => 'ga:avgEventValue'
                ),
                array(
                    'label' => 'ga:sessionsWithEvent',
                    'value' => 'ga:sessionsWithEvent'
                ),
                array(
                    'label' => 'ga:eventsPerSessionWithEvent',
                    'value' => 'ga:eventsPerSessionWithEvent'
                ),

            // Ecommerce
            array('optgroup' => 'Ecommerce'),
                array(
                    'label' => 'ga:transactions',
                    'value' => 'ga:transactions'
                ),
                array(
                    'label' => 'ga:transactionsPerSession',
                    'value' => 'ga:transactionsPerSession'
                ),
                array(
                    'label' => 'ga:transactionRevenue',
                    'value' => 'ga:transactionRevenue'
                ),
                array(
                    'label' => 'ga:revenuePerTransaction',
                    'value' => 'ga:revenuePerTransaction'
                ),
                array(
                    'label' => 'ga:transactionRevenuePerSession',
                    'value' => 'ga:transactionRevenuePerSession'
                ),
                array(
                    'label' => 'ga:transactionShipping',
                    'value' => 'ga:transactionShipping'
                ),
                array(
                    'label' => 'ga:transactionTax',
                    'value' => 'ga:transactionTax'
                ),
                array(
                    'label' => 'ga:totalValue',
                    'value' => 'ga:totalValue'
                ),
                array(
                    'label' => 'ga:itemQuantity',
                    'value' => 'ga:itemQuantity'
                ),
                array(
                    'label' => 'ga:uniquePurchases',
                    'value' => 'ga:uniquePurchases'
                ),
                array(
                    'label' => 'ga:revenuePerItem',
                    'value' => 'ga:revenuePerItem'
                ),
                array(
                    'label' => 'ga:itemRevenue',
                    'value' => 'ga:itemRevenue'
                ),
                array(
                    'label' => 'ga:itemsPerPurchase',
                    'value' => 'ga:itemsPerPurchase'
                ),
                array(
                    'label' => 'ga:localTransactionRevenue',
                    'value' => 'ga:localTransactionRevenue'
                ),
                array(
                    'label' => 'ga:localTransactionShipping',
                    'value' => 'ga:localTransactionShipping'
                ),
                array(
                    'label' => 'ga:localTransactionTax',
                    'value' => 'ga:localTransactionTax'
                ),
                array(
                    'label' => 'ga:localItemRevenue',
                    'value' => 'ga:localItemRevenue'
                ),

            // Social Interactions
            array('optgroup' => 'Social Interactions'),
                array(
                    'label' => 'ga:socialInteractions',
                    'value' => 'ga:socialInteractions'
                ),
                array(
                    'label' => 'ga:uniqueSocialInteractions',
                    'value' => 'ga:uniqueSocialInteractions'
                ),
                array(
                    'label' => 'ga:socialInteractionsPerSession',
                    'value' => 'ga:socialInteractionsPerSession'
                ),

            // User Timings
            array('optgroup' => 'User Timings'),
                array(
                    'label' => 'ga:userTimingValue',
                    'value' => 'ga:userTimingValue'
                ),
                array(
                    'label' => 'ga:userTimingSample',
                    'value' => 'ga:userTimingSample'
                ),
                array(
                    'label' => 'ga:avgUserTimingValue',
                    'value' => 'ga:avgUserTimingValue'
                ),

            // Exceptions
            array('optgroup' => 'Exceptions'),
                array(
                    'label' => 'ga:exceptions',
                    'value' => 'ga:exceptions'
                ),
                array(
                    'label' => 'ga:exceptionsPerScreenview',
                    'value' => 'ga:exceptionsPerScreenview'
                ),
                array(
                    'label' => 'ga:fatalExceptions',
                    'value' => 'ga:fatalExceptions'
                ),
                array(
                    'label' => 'ga:fatalExceptionsPerScreenview',
                    'value' => 'ga:fatalExceptionsPerScreenview'
                ),

            // Content Experiments
            array('optgroup' => 'Content Experiments'),

            // Custom Variables or Columns
            array('optgroup' => 'Custom Variables or Columns'),
                array(
                    'label' => 'ga:metricXX',
                    'value' => 'ga:metricXX'
                ),

            // System
            array('optgroup' => 'System'),

            // Time
            array('optgroup' => 'Time'),

            // Audience
            array('optgroup' => 'Audience'),

            // Other
            array('optgroup' => 'Other'),
                array(
                    'label' => 'ga:adsenseRevenue',
                    'value' => 'ga:adsenseRevenue'
                ),
                array(
                    'label' => 'ga:adsenseAdUnitsViewed',
                    'value' => 'ga:adsenseAdUnitsViewed'
                ),
                array(
                    'label' => 'ga:adsenseAdsViewed',
                    'value' => 'ga:adsenseAdsViewed'
                ),
                array(
                    'label' => 'ga:adsenseAdsClicks',
                    'value' => 'ga:adsenseAdsClicks'
                ),
                array(
                    'label' => 'ga:adsensePageImpressions',
                    'value' => 'ga:adsensePageImpressions'
                ),
                array(
                    'label' => 'ga:adsenseCTR',
                    'value' => 'ga:adsenseCTR'
                ),
                array(
                    'label' => 'ga:adsenseECPM',
                    'value' => 'ga:adsenseECPM'
                ),
                array(
                    'label' => 'ga:adsenseExits',
                    'value' => 'ga:adsenseExits'
                ),
        );
    }

    private function getDimensionsOpts()
    {
        return array(

            // User

            array('optgroup' => "User"),
                array(
                    'label' => "ga:userType",
                    'value' => "ga:userType"
                ),
                array(
                    'label' => 'ga:sessionCount',
                    'value' => 'ga:sessionCount'
                ),
                array(
                    'label' => 'ga:daysSinceLastSession',
                    'value' => 'ga:daysSinceLastSession'
                ),
                array(
                    'label' => 'ga:userDefinedValue',
                    'value' => 'ga:userDefinedValue'
                ),


            // Session

            array('optgroup' => 'Session'),
                array(
                    'label' => 'ga:sessionDurationBucket',
                    'value' => 'ga:sessionDurationBucket'
                ),


            // Traffic Sources

            array('optgroup' => 'Traffic Sources'),

                array(
                    'label' => 'ga:referralPath',
                    'value' => 'ga:referralPath'
                ),
                array(
                    'label' => 'ga:fullReferrer',
                    'value' => 'ga:fullReferrer'
                ),
                array(
                    'label' => 'ga:campaign',
                    'value' => 'ga:campaign'
                ),
                array(
                    'label' => 'ga:source',
                    'value' => 'ga:source'
                ),
                array(
                    'label' => 'ga:medium',
                    'value' => 'ga:medium'
                ),
                array(
                    'label' => 'ga:sourceMedium',
                    'value' => 'ga:sourceMedium'
                ),
                array(
                    'label' => 'ga:keyword',
                    'value' => 'ga:keyword'
                ),
                array(
                    'label' => 'ga:adContent',
                    'value' => 'ga:adContent'
                ),
                array(
                    'label' => 'ga:socialNetwork',
                    'value' => 'ga:socialNetwork'
                ),
                array(
                    'label' => 'ga:hasSocialSourceReferral',
                    'value' => 'ga:hasSocialSourceReferral'
                ),

            // Adwords

            array('optgroup' => 'Adwords'),
                array(
                    'label' => 'ga:adGroup',
                    'value' => 'ga:adGroup'
                ),
                array(
                    'label' => 'ga:adSlot',
                    'value' => 'ga:adSlot'
                ),
                array(
                    'label' => 'ga:adSlotPosition',
                    'value' => 'ga:adSlotPosition'
                ),
                array(
                    'label' => 'ga:adDistributionNetwork',
                    'value' => 'ga:adDistributionNetwork'
                ),
                array(
                    'label' => 'ga:adMatchType',
                    'value' => 'ga:adMatchType'
                ),
                array(
                    'label' => 'ga:adKeywordMatchType',
                    'value' => 'ga:adKeywordMatchType'
                ),
                array(
                    'label' => 'ga:adMatchedQuery',
                    'value' => 'ga:adMatchedQuery'
                ),
                array(
                    'label' => 'ga:adPlacementDomain',
                    'value' => 'ga:adPlacementDomain'
                ),
                array(
                    'label' => 'ga:adPlacementUrl',
                    'value' => 'ga:adPlacementUrl'
                ),
                array(
                    'label' => 'ga:adFormat',
                    'value' => 'ga:adFormat'
                ),
                array(
                    'label' => 'ga:adTargetingType',
                    'value' => 'ga:adTargetingType'
                ),
                array(
                    'label' => 'ga:adTargetingOption',
                    'value' => 'ga:adTargetingOption'
                ),
                array(
                    'label' => 'ga:adDisplayUrl',
                    'value' => 'ga:adDisplayUrl'
                ),
                array(
                    'label' => 'ga:adDestinationUrl',
                    'value' => 'ga:adDestinationUrl'
                ),
                array(
                    'label' => 'ga:adwordsCustomerID',
                    'value' => 'ga:adwordsCustomerID'
                ),
                array(
                    'label' => 'ga:adwordsCampaignID',
                    'value' => 'ga:adwordsCampaignID'
                ),
                array(
                    'label' => 'ga:adwordsAdGroupID',
                    'value' => 'ga:adwordsAdGroupID'
                ),
                array(
                    'label' => 'ga:adwordsCreativeID',
                    'value' => 'ga:adwordsCreativeID'
                ),
                array(
                    'label' => 'ga:adwordsCriteriaID',
                    'value' => 'ga:adwordsCriteriaID'
                ),
                array(
                    'label' => 'ga:isTrueViewVideoAd',
                    'value' => 'ga:isTrueViewVideoAd'
                ),



            // Goal Conversions
            array('optgroup' => 'Goal Conversions'),
                array(
                        'label' => 'ga:goalCompletionLocation',
                        'value' => 'ga:goalCompletionLocation'
                    ),
                array(
                        'label' => 'ga:goalPreviousStep1',
                        'value' => 'ga:goalPreviousStep1'
                    ),
                array(
                        'label' => 'ga:goalPreviousStep2',
                        'value' => 'ga:goalPreviousStep2'
                    ),
                array(
                        'label' => 'ga:goalPreviousStep3',
                        'value' => 'ga:goalPreviousStep3'
                    ),

            // Platform or Device
            array('optgroup' => 'Platform or Device'),
                array(
                        'label' => 'ga:browser',
                        'value' => 'ga:browser'
                    ),
                array(
                        'label' => 'ga:browserVersion',
                        'value' => 'ga:browserVersion'
                    ),
                array(
                        'label' => 'ga:operatingSystem',
                        'value' => 'ga:operatingSystem'
                    ),
                array(
                        'label' => 'ga:operatingSystemVersion',
                        'value' => 'ga:operatingSystemVersion'
                    ),
                array(
                        'label' => 'ga:mobileDeviceBranding',
                        'value' => 'ga:mobileDeviceBranding'
                    ),
                array(
                        'label' => 'ga:mobileDeviceModel',
                        'value' => 'ga:mobileDeviceModel'
                    ),
                array(
                        'label' => 'ga:mobileInputSelector',
                        'value' => 'ga:mobileInputSelector'
                    ),
                array(
                        'label' => 'ga:mobileDeviceInfo',
                        'value' => 'ga:mobileDeviceInfo'
                    ),
                array(
                        'label' => 'ga:mobileDeviceMarketingName',
                        'value' => 'ga:mobileDeviceMarketingName'
                    ),
                array(
                        'label' => 'ga:deviceCategory',
                        'value' => 'ga:deviceCategory'
                    ),

            // Geo Network
            array('optgroup' => 'Geo Network'),
                array(
                        'label' => 'ga:continent',
                        'value' => 'ga:continent'
                    ),
                array(
                        'label' => 'ga:subContinent',
                        'value' => 'ga:subContinent'
                    ),
                array(
                        'label' => 'ga:country',
                        'value' => 'ga:country'
                    ),
                array(
                        'label' => 'ga:region',
                        'value' => 'ga:region'
                    ),
                array(
                        'label' => 'ga:metro',
                        'value' => 'ga:metro'
                    ),
                array(
                        'label' => 'ga:city',
                        'value' => 'ga:city'
                    ),
                array(
                        'label' => 'ga:latitude',
                        'value' => 'ga:latitude'
                    ),
                array(
                        'label' => 'ga:longitude',
                        'value' => 'ga:longitude'
                    ),
                array(
                        'label' => 'ga:networkDomain',
                        'value' => 'ga:networkDomain'
                    ),
                array(
                        'label' => 'ga:networkLocation',
                        'value' => 'ga:networkLocation'
                    ),

            // System
            array('optgroup' => 'System'),
                array(
                        'label' => 'ga:flashVersion',
                        'value' => 'ga:flashVersion'
                    ),
                array(
                        'label' => 'ga:javaEnabled',
                        'value' => 'ga:javaEnabled'
                    ),
                array(
                        'label' => 'ga:language',
                        'value' => 'ga:language'
                    ),
                array(
                        'label' => 'ga:screenColors',
                        'value' => 'ga:screenColors'
                    ),
                array(
                        'label' => 'ga:sourcePropertyId',
                        'value' => 'ga:sourcePropertyId'
                    ),
                array(
                        'label' => 'ga:sourcePropertyName',
                        'value' => 'ga:sourcePropertyName'
                    ),
                array(
                        'label' => 'ga:screenResolution',
                        'value' => 'ga:screenResolution'
                    ),
            // Social Activities
            array('optgroup' => 'Social Activities'),
                array(
                        'label' => 'ga:socialActivityEndorsingUrl',
                        'value' => 'ga:socialActivityEndorsingUrl'
                    ),
                array(
                        'label' => 'ga:socialActivityDisplayName',
                        'value' => 'ga:socialActivityDisplayName'
                    ),
                array(
                        'label' => 'ga:socialActivityPost',
                        'value' => 'ga:socialActivityPost'
                    ),
                array(
                        'label' => 'ga:socialActivityTimestamp',
                        'value' => 'ga:socialActivityTimestamp'
                    ),
                array(
                        'label' => 'ga:socialActivityUserHandle',
                        'value' => 'ga:socialActivityUserHandle'
                    ),
                array(
                        'label' => 'ga:socialActivityUserPhotoUrl',
                        'value' => 'ga:socialActivityUserPhotoUrl'
                    ),
                array(
                        'label' => 'ga:socialActivityUserProfileUrl',
                        'value' => 'ga:socialActivityUserProfileUrl'
                    ),
                array(
                        'label' => 'ga:socialActivityContentUrl',
                        'value' => 'ga:socialActivityContentUrl'
                    ),
                array(
                        'label' => 'ga:socialActivityTagsSummary',
                        'value' => 'ga:socialActivityTagsSummary'
                    ),
                array(
                        'label' => 'ga:socialActivityAction',
                        'value' => 'ga:socialActivityAction'
                    ),
                array(
                        'label' => 'ga:socialActivityNetworkAction',
                        'value' => 'ga:socialActivityNetworkAction'
                    ),

            // Page Tracking

            array('optgroup' => 'Page Tracking'),
                array(
                        'label' => 'ga:hostname',
                        'value' => 'ga:hostname'
                    ),
                array(
                        'label' => 'ga:pagePath',
                        'value' => 'ga:pagePath'
                    ),
                array(
                        'label' => 'ga:pagePathLevel1',
                        'value' => 'ga:pagePathLevel1'
                    ),
                array(
                        'label' => 'ga:pagePathLevel2',
                        'value' => 'ga:pagePathLevel2'
                    ),
                array(
                        'label' => 'ga:pagePathLevel3',
                        'value' => 'ga:pagePathLevel3'
                    ),
                array(
                        'label' => 'ga:pagePathLevel4',
                        'value' => 'ga:pagePathLevel4'
                    ),
                array(
                        'label' => 'ga:pageTitle',
                        'value' => 'ga:pageTitle'
                    ),
                array(
                        'label' => 'ga:landingPagePath',
                        'value' => 'ga:landingPagePath'
                    ),
                array(
                        'label' => 'ga:secondPagePath',
                        'value' => 'ga:secondPagePath'
                    ),
                array(
                        'label' => 'ga:exitPagePath',
                        'value' => 'ga:exitPagePath'
                    ),
                array(
                        'label' => 'ga:previousPagePath',
                        'value' => 'ga:previousPagePath'
                    ),
                array(
                        'label' => 'ga:nextPagePath',
                        'value' => 'ga:nextPagePath'
                    ),
                array(
                        'label' => 'ga:pageDepth',
                        'value' => 'ga:pageDepth'
                    ),


            // Internal Search
            array('optgroup' => 'Internal Search'),
                array(
                        'label' => 'ga:searchUsed',
                        'value' => 'ga:searchUsed'
                    ),
                array(
                        'label' => 'ga:searchKeyword',
                        'value' => 'ga:searchKeyword'
                    ),
                array(
                        'label' => 'ga:searchKeywordRefinement',
                        'value' => 'ga:searchKeywordRefinement'
                    ),
                array(
                        'label' => 'ga:searchCategory',
                        'value' => 'ga:searchCategory'
                    ),
                array(
                        'label' => 'ga:searchStartPage',
                        'value' => 'ga:searchStartPage'
                    ),
                array(
                    'label' => 'ga:searchDestinationPage',
                    'value' => 'ga:searchDestinationPage'
                ),

            // Site Speed
            array('optgroup' => 'Site Speed'),

            // App Tracking
            array('optgroup' => 'App Tracking'),
                array(
                    'label' => 'ga:appInstallerId',
                    'value' => 'ga:appInstallerId'
                ),
                array(
                    'label' => 'ga:appVersion',
                    'value' => 'ga:appVersion'
                ),
                array(
                    'label' => 'ga:appName',
                    'value' => 'ga:appName'
                ),
                array(
                    'label' => 'ga:appId',
                    'value' => 'ga:appId'
                ),
                array(
                    'label' => 'ga:screenName',
                    'value' => 'ga:screenName'
                ),
                array(
                    'label' => 'ga:screenDepth',
                    'value' => 'ga:screenDepth'
                ),
                array(
                    'label' => 'ga:landingScreenName',
                    'value' => 'ga:landingScreenName'
                ),
                array(
                    'label' => 'ga:exitScreenName',
                    'value' => 'ga:exitScreenName'
                ),

            // Event Tracking
            array('optgroup' => 'Event Tracking'),
                array(
                    'label' => 'ga:eventCategory',
                    'value' => 'ga:eventCategory'
                ),
                array(
                    'label' => 'ga:eventAction',
                    'value' => 'ga:eventAction'
                ),
                array(
                    'label' => 'ga:eventLabel',
                    'value' => 'ga:eventLabel'
                ),

            // Ecommerce
            array('optgroup' => 'Ecommerce'),
                array(
                    'label' => 'ga:transactionId',
                    'value' => 'ga:transactionId'
                ),
                array(
                    'label' => 'ga:affiliation',
                    'value' => 'ga:affiliation'
                ),
                array(
                    'label' => 'ga:sessionsToTransaction',
                    'value' => 'ga:sessionsToTransaction'
                ),
                array(
                    'label' => 'ga:daysToTransaction',
                    'value' => 'ga:daysToTransaction'
                ),
                array(
                    'label' => 'ga:productSku',
                    'value' => 'ga:productSku'
                ),
                array(
                    'label' => 'ga:productName',
                    'value' => 'ga:productName'
                ),
                array(
                    'label' => 'ga:productCategory',
                    'value' => 'ga:productCategory'
                ),
                array(
                    'label' => 'ga:currencyCode',
                    'value' => 'ga:currencyCode'
                ),
            // Social Interactions
            array('optgroup' => 'Social Interactions'),
                array(
                    'label' => 'ga:socialInteractionNetwork',
                    'value' => 'ga:socialInteractionNetwork'
                ),
                array(
                    'label' => 'ga:socialInteractionAction',
                    'value' => 'ga:socialInteractionAction'
                ),
                array(
                    'label' => 'ga:socialInteractionNetworkAction',
                    'value' => 'ga:socialInteractionNetworkAction'
                ),
                array(
                    'label' => 'ga:socialInteractionTarget',
                    'value' => 'ga:socialInteractionTarget'
                ),
                array(
                    'label' => 'ga:socialEngagementType',
                    'value' => 'ga:socialEngagementType'
                ),

            // User Timings
            array('optgroup' => 'User Timings'),
                array(
                    'label' => 'ga:userTimingCategory',
                    'value' => 'ga:userTimingCategory'
                ),
                array(
                    'label' => 'ga:userTimingLabel',
                    'value' => 'ga:userTimingLabel'
                ),
                array(
                    'label' => 'ga:userTimingVariable',
                    'value' => 'ga:userTimingVariable'
                ),

            // Exceptions
            array('optgroup' => 'Exceptions'),
                array(
                    'label' => 'ga:exceptionDescription',
                    'value' => 'ga:exceptionDescription'
                ),

            // Content Experiments
            array('optgroup' => 'Content Experiments'),
                array(
                    'label' => 'ga:experimentId',
                    'value' => 'ga:experimentId'
                ),
                array(
                    'label' => 'ga:experimentVariant',
                    'value' => 'ga:experimentVariant'
                ),

            // Custom Variables or Columns
            array('optgroup' => 'Custom Variables or Columns'),
                array(
                    'label' => 'ga:dimensionXX',
                    'value' => 'ga:dimensionXX'
                ),
                array(
                    'label' => 'ga:customVarNameXX',
                    'value' => 'ga:customVarNameXX'
                ),
                array(
                    'label' => 'ga:customVarValueXX',
                    'value' => 'ga:customVarValueXX'
                ),


            // Time

            array('optgroup' => 'Time'),
                array(
                    'label' => 'ga:date',
                    'value' => 'ga:date'
                ),
                array(
                    'label' => 'ga:year',
                    'value' => 'ga:year'
                ),
                array(
                    'label' => 'ga:month',
                    'value' => 'ga:month'
                ),
                array(
                    'label' => 'ga:week',
                    'value' => 'ga:week'
                ),
                array(
                    'label' => 'ga:day',
                    'value' => 'ga:day'
                ),
                array(
                    'label' => 'ga:hour',
                    'value' => 'ga:hour'
                ),
                array(
                    'label' => 'ga:minute',
                    'value' => 'ga:minute'
                ),
                array(
                    'label' => 'ga:nthMonth',
                    'value' => 'ga:nthMonth'
                ),
                array(
                    'label' => 'ga:nthWeek',
                    'value' => 'ga:nthWeek'
                ),
                array(
                    'label' => 'ga:nthDay',
                    'value' => 'ga:nthDay'
                ),
                array(
                    'label' => 'ga:nthMinute',
                    'value' => 'ga:nthMinute'
                ),
                array(
                    'label' => 'ga:dayOfWeek',
                    'value' => 'ga:dayOfWeek'
                ),
                array(
                    'label' => 'ga:dayOfWeekName',
                    'value' => 'ga:dayOfWeekName'
                ),
                array(
                    'label' => 'ga:dateHour',
                    'value' => 'ga:dateHour'
                ),
                array(
                    'label' => 'ga:yearMonth',
                    'value' => 'ga:yearMonth'
                ),
                array(
                    'label' => 'ga:yearWeek',
                    'value' => 'ga:yearWeek'
                ),
                array(
                    'label' => 'ga:isoWeek',
                    'value' => 'ga:isoWeek'
                ),
                array(
                    'label' => 'ga:isoYear',
                    'value' => 'ga:isoYear'
                ),
                array(
                    'label' => 'ga:isoYearIsoWeek',
                    'value' => 'ga:isoYearIsoWeek'
                ),
                array(
                    'label' => 'ga:nthHour',
                    'value' => 'ga:nthHour'
                ),

            // Audience
            array('optgroup' => 'Audience'),
                array(
                    'label' => 'ga:userAgeBracket',
                    'value' => 'ga:userAgeBracket'
                ),
                array(
                    'label' => 'ga:userGender',
                    'value' => 'ga:userGender'
                ),
                array(
                    'label' => 'ga:interestOtherCategory',
                    'value' => 'ga:interestOtherCategory'
                ),
                array(
                    'label' => 'ga:interestAffinityCategory',
                    'value' => 'ga:interestAffinityCategory'
                ),
                array(
                    'label' => 'ga:interestInMarketCategory',
                    'value' => 'ga:interestInMarketCategory'
                ),

            // Other
            array('optgroup' => 'Other'),
        );
    }
}
