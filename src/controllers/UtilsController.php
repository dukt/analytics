<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use craft\web\Controller;
use dukt\analytics\Plugin;
use yii\web\Response;
use dukt\analytics\Plugin as Analytics;
use Craft;
use craft\helpers\FileHelper;

/**
 * Class UtilsController
 *
 * @package dukt\analytics\controllers
 */
class UtilsController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @var int
     */
    private $addedInApiVersion = 3;

    // Public Methods
    // =========================================================================

    /**
     * Index
     *
     * @return Response
     */
    public function actionIndex(): Response {
        return $this->renderTemplate('analytics/utils/_index');
    }

    // Public Methods
    // =========================================================================

    public function actionMetadataGa4(string $property = null, array $columns = null): Response
    {
        $variables = [];
        $variables['property'] = $property;


        $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();
        $metadataResponse = $analyticsData->properties->getMetadata($property.'/metadata');
        $metadata = $metadataResponse->toSimpleObject();


        $variables['metadata'] = $metadata;

        return $this->renderTemplate('analytics/utils/metadata-ga4/_index', $variables);
    }

    /**
     * Get the GA meta data.
     *
     * @return null
     */
    public function actionGetMetadataGa4()
    {
        $property = Craft::$app->getRequest()->getParam('property');
//        $columns = Analytics::$plugin->getMetadataUA()->searchColumns($property);

        // Send the source back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'property' => $property,
        ]);

        return null;
    }

    /**
     * Metadata.
     * Index
     *
     * @param string|null $q
     * @param array|null $columns
     * @return Response
     */
    public function actionMetadataUa(string $q = null, array $columns = null): Response
    {
        $variables = [];
        $variables['q'] = $q;
        $variables['columns'] = $columns;
        $variables['dimensions'] = Analytics::$plugin->getMetadataUA()->getDimensions();
        $variables['metrics'] = Analytics::$plugin->getMetadataUA()->getMetrics();

        $variables['dimmetsFileExists'] = Analytics::$plugin->getMetadataUA()->dimmetsFileExists();

        return $this->renderTemplate('analytics/utils/metadata-ua/_index', $variables);
    }

    /**
     * Searches the meta data.
     *
     * @return null
     */
    public function actionSearchMetadataUa()
    {
        $q = Craft::$app->getRequest()->getParam('q');
        $columns = Analytics::$plugin->getMetadataUA()->searchColumns($q);

        // Send the source back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'q' => $q,
            'columns' => $columns,
        ]);

        return null;
    }

    /**
     * Loads metadata.
     *
     * @return Response
     */
    public function actionLoadMetadataUa()
    {
        $this->_deleteMetadata();
        $this->_importMetadata();

        Craft::$app->getSession()->setNotice(Craft::t('analytics', 'Metadata loaded.'));

        $referrer = Craft::$app->getRequest()->referrer;

        return $this->redirect($referrer);
    }

    // Private Methods
    // =========================================================================


    /**
     * Deletes metadata.
     */
    private function _deleteMetadata()
    {
        $path = Analytics::$plugin->getMetadataUA()->getDimmetsFilePath();

        FileHelper::unlink($path);
    }

    /**
     * Imports metadata.
     */
    private function _importMetadata()
    {
        $columns = [];

        $items = Analytics::$plugin->getApis()->getAnalytics()->getColumns();

        if ($items) {
            foreach ($items as $item) {
                if ($item->attributes['status'] == 'DEPRECATED') {
                    continue;
                }

                if ($item->attributes['addedInApiVersion'] > $this->addedInApiVersion) {
                    continue;
                }

                $this->addColumnFromItem($columns, $item);
            }
        }

        $contents = json_encode($columns, JSON_PRETTY_PRINT);

        $path = Analytics::$plugin->getMetadataUA()->getDimmetsFilePath();

        FileHelper::writeToFile($path, $contents);
    }

    /**
     * Add column from item.
     *
     * @param $columns
     * @param Column $item
     */
    private function addColumnFromItem(&$columns, Column $item)
    {
        if (isset($item->attributes['minTemplateIndex'])) {
            for ($i = $item->attributes['minTemplateIndex']; $i <= $item->attributes['maxTemplateIndex']; ++$i) {
                $column = [];
                $column['id'] = str_replace('XX', $i, $item->id);
                $column['uiName'] = str_replace('XX', $i, $item->attributes['uiName']);
                $column['description'] = str_replace('XX', $i, $item->attributes['description']);

                $columns[$column['id']] = $this->populateColumnAttributes($column, $item);
            }
        } else {
            $column = [];
            $column['id'] = $item->id;
            $column['uiName'] = $item->attributes['uiName'];
            $column['description'] = $item->attributes['description'];

            $columns[$column['id']] = $this->populateColumnAttributes($column, $item);
        }
    }

    /**
     * Populates the coloumn attribute.
     *
     * @param $column
     * @param $item
     *
     * @return mixed
     */
    private function populateColumnAttributes($column, $item)
    {
        $column['type'] = $item->attributes['type'];
        $column['dataType'] = $item->attributes['dataType'];
        $column['group'] = $item->attributes['group'];
        $column['status'] = $item->attributes['status'];

        if (isset($item->attributes['allowInSegments'])) {
            $column['allowInSegments'] = $item->attributes['allowInSegments'];
        }

        if (isset($item->attributes['addedInApiVersion'])) {
            $column['addedInApiVersion'] = $item->attributes['addedInApiVersion'];
        }

        return $column;
    }
}