<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_MetaController extends BaseController
{
    public function actionManage(array $variables = array())
    {
        $variables['dimensions'] = craft()->analytics_meta->getDimensions();
        $variables['metrics'] = craft()->analytics_meta->getMetrics();

        $variables['metadataFileExists'] = craft()->analytics_meta->metadataFileExists();

        $this->renderTemplate('analytics/meta/manage/_index', $variables);
    }

    public function actionSearch(array $variables = array())
    {

        $variables['q'] = craft()->request->getParam('q');
        $variables['columns'] = craft()->analytics_meta->searchColumns($variables['q']);

        $this->renderTemplate('analytics/meta/search/_index', $variables);
    }

    public function actionDeleteMetadata()
    {
        $path = craft()->analytics_meta->getMetadataFilePath();

        IOHelper::deleteFile($path);

        $referer = craft()->request->getUrlReferrer();
        $this->redirect($referer);
    }

    public function actionImportMetadata()
    {
        $columns = [];

        $metadataColumns = craft()->analytics_api->getMetadataColumns();

        if($metadataColumns)
        {
            $items = $metadataColumns->listMetadataColumns('ga');

            if($items)
            {
                foreach($items as $item)
                {
                    if($item->attributes['status'] == 'DEPRECATED')
                    {
                        continue;
                    }

                    if(isset($item->attributes['minTemplateIndex']))
                    {
                        for($i = $item->attributes['minTemplateIndex']; $i <= $item->attributes['maxTemplateIndex']; $i++)
                        {
                            $column = [];
                            $column['id'] = str_replace('XX', $i, $item->id);
                            $column['type'] = $item->attributes['type'];
                            $column['group'] = $item->attributes['group'];
                            $column['status'] = $item->attributes['status'];
                            $column['uiName'] = str_replace('XX', $i, $item->attributes['uiName']);
                            $column['description'] = str_replace('XX', $i, $item->attributes['description']);

                            if(isset($item->attributes['allowInSegments']))
                            {
                                $column['allowInSegments'] = $item->attributes['allowInSegments'];
                            }

                            $columns[$column['id']] = $column;
                        }
                    }
                    else
                    {
                        $column = [];
                        $column['id'] = $item->id;
                        $column['type'] = $item->attributes['type'];
                        $column['group'] = $item->attributes['group'];
                        $column['status'] = $item->attributes['status'];
                        $column['uiName'] = $item->attributes['uiName'];
                        $column['description'] = $item->attributes['description'];

                        if(isset($item->attributes['allowInSegments']))
                        {
                            $column['allowInSegments'] = $item->attributes['allowInSegments'];
                        }

                        $columns[$column['id']] = $column;
                    }
                }
            }
        }

        $contents = json_encode($columns);

        $path = craft()->analytics_meta->getMetadataFilePath();

        $res = IOHelper::writeToFile($path, $contents);

        $referer = craft()->request->getUrlReferrer();
        $this->redirect($referer);
    }
}