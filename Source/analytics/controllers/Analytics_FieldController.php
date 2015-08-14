<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_FieldController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * Element Report
     *
     * @param array $variables
     *
     * @return null
     */
    public function actionElementReport(array $variables = array())
    {
        try {
            $elementId = craft()->request->getRequiredParam('elementId');
            $locale = craft()->request->getRequiredParam('locale');
            $metric = craft()->request->getRequiredParam('metric');

            $uri = craft()->analytics->getElementUrlPath($elementId, $locale);

            if($uri)
            {
                if($uri == '__home__')
                {
                    $uri = '';
                }

                $start = date('Y-m-d', strtotime('-1 month'));
                $end = date('Y-m-d');
                $dimensions = 'ga:date';

                $optParams = array(
                        'dimensions' => $dimensions,
                        'filters' => "ga:pagePath==".$uri
                    );

                $criteria = new Analytics_RequestCriteriaModel;
                $criteria->startDate = $start;
                $criteria->endDate = $end;
                $criteria->metrics = $metric;
                $criteria->optParams = $optParams;

                $response = craft()->analytics->sendRequest($criteria);

                $this->returnJson(array('data' => $response));
            }
            else
            {
               throw new Exception("Element doesn't support URLs.", 1);
            }
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }
}
