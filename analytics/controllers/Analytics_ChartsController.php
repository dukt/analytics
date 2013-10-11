<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://dukt.net/craft/analytics/docs#license
 * @link      http://dukt.net/craft/analytics/
 */

namespace Craft;

class Analytics_ChartsController extends BaseController
{
    public function actionParse()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $chartQuery = $_POST['chartQuery'];

        $results = craft()->analytics->api()->data_ga->get(
            $chartQuery['param1'],
            $chartQuery['param2'],
            $chartQuery['param3'],
            $chartQuery['param4'],
            $chartQuery['param5']
        );


        // ga:keywords
        // ga:visits

        // array(2) {
        //   [0]=>
        //   string(32) ""ajax search"+"expressionengine""
        //   [1]=>
        //   string(1) "1"
        // }


        // ga:day, ga:month, ga:year
        // ga:visits
        // [0]=> array(4) {
        //     [0]=> string(2) "01" [1]=> string(2) "01" [2]=> string(4) "2012" [3]=> string(2) "13"
        // }


        $json = array(
                array('Day', 'Visitors')
            );

        foreach($results['rows'] as $row) {
            $itemMetric = (int) array_pop($row);
            $itemDimension = implode('.', $row);
            // $itemDimension = md5($itemDimension);

            if($itemDimension != "(not provided)" && $itemDimension != "(not set)") {
                $item = array($itemDimension, $itemMetric);
                array_push($json, $item);
            }
        }

        $variables = array('json' => $json);

        $templatePath = craft()->path->getPluginsPath().'analytics/templates/';

        craft()->path->setTemplatesPath($templatePath);

        $html = craft()->templates->render('_chartDraw', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        echo new \Twig_Markup($html, $charset);

        exit();
    }
}