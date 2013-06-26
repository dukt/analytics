<?php

/**
 * Craft Directory by Dukt
 *
 * @package   Craft Directory
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://docs.dukt.net/craft/directory/license
 * @link      http://dukt.net/craft/analytics/license
 */

namespace Craft;

class Analytics_ChartsController extends BaseController
{
    public function actionParse()
    {
        $accountId = false;
        $webPropertyId = false;

        $properties = craft()->analytics->api()->management_webproperties->listManagementWebproperties("~all");

        foreach($properties['items'] as $item) {
            if($item['id'] == craft()->analytics->getSetting('profileId')) {
                $accountId = $item['accountId'];
                $webPropertyId = $item['id'];
            }
        }

        $profiles = craft()->analytics->api()->management_profiles->listManagementProfiles($accountId, $webPropertyId);
        $profileId = $profiles['items'][0]['id'];

        $chartQuery = $_POST['chartQuery'];

        $results = craft()->analytics->api()->data_ga->get(
            $chartQuery['param1'],
            $chartQuery['param2'],
            $chartQuery['param3'],
            $chartQuery['param4'],
            $chartQuery['param5']
        );

        $json = array(
                array('Day', 'Visitors')
            );

        foreach($results['rows'] as $row) {
            if(count($row) == 2) {
                array_push($json, array($row[0], (int) $row[1]));
            } else {
                array_push($json, array($row[2].'/'.$row[1], (int) $row[3]));
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