<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use Craft;
use dukt\analytics\errors\InvalidViewException;
use dukt\analytics\models\SiteView;
use dukt\analytics\models\View;
use dukt\analytics\records\View as ViewRecord;
use dukt\analytics\records\SiteView as SiteViewRecord;
use yii\base\Component;
use Exception;

class Views extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Get all views.
     *
     * @return array|null
     */
    public function getViews()
    {
        $results = ViewRecord::find()->all();

        $views = [];

        foreach ($results as $result) {
            $views[] = new View($result->toArray([
                'id',
                'name',
                'gaAccountId',
                'gaAccountName',
                'gaPropertyId',
                'gaPropertyName',
                'gaViewId',
                'gaViewName',
            ]));
        }

        return $views;
    }

    /**
     * Get view by ID.
     *
     * @param $id
     *
     * @return View|null
     */
    public function getViewById($id)
    {
        $result = ViewRecord::findOne($id);

        if ($result) {
            return new View($result->toArray([
                'id',
                'name',
                'gaAccountId',
                'gaAccountName',
                'gaPropertyId',
                'gaPropertyName',
                'gaViewCurrency',
                'gaViewId',
                'gaViewName',
            ]));
        }

        return null;
    }

    /**
     * Get site views.
     *
     * @return array
     */
    public function getSiteViews()
    {
        $results = SiteViewRecord::find()->all();

        $views = [];

        foreach ($results as $result) {
            $views[] = new SiteView($result->toArray([
                'siteId',
                'viewId',
            ]));
        }

        return $views;
    }

    /**
     * Get ite view by site ID.
     *
     * @param $id
     *
     * @return SiteView|null
     */
    public function getSiteViewBySiteId($id)
    {
        $result = SiteViewRecord::findOne([
            'siteId' => $id
        ]);

        if ($result) {
            return new SiteView($result->toArray([
                'id',
                'siteId',
                'viewId',
            ]));
        }

        return null;
    }

    /**
     * Saves a view.
     *
     * @param View $view          The view to be saved
     * @param bool $runValidation Whether the view should be validated
     *
     * @return bool
     * @throws InvalidViewException if $view->id is invalid
     * @throws Exception if reasons
     */
    public function saveView(View $view, bool $runValidation = true): bool
    {
        if ($runValidation && !$view->validate()) {
            Craft::info('View not saved due to validation error.', __METHOD__);

            return false;
        }

        if ($view->id) {
            $viewRecord = ViewRecord::find()
                ->where(['id' => $view->id])
                ->one();

            if (!$viewRecord) {
                throw new InvalidViewException("No view exists with the ID '{$view->id}'");
            }

            $isNewView = false;
        } else {
            $viewRecord = new ViewRecord();
            $isNewView = true;
        }

        // Shared attributes
        $viewRecord->name = $view->name;
        $viewRecord->gaAccountId = $view->gaAccountId;
        $viewRecord->gaAccountName = $view->gaAccountName;
        $viewRecord->gaPropertyId = $view->gaPropertyId;
        $viewRecord->gaPropertyName = $view->gaPropertyName;
        $viewRecord->gaViewId = $view->gaViewId;
        $viewRecord->gaViewName = $view->gaViewName;
        $viewRecord->gaViewCurrency = $view->gaViewCurrency;


        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Is the event giving us the go-ahead?
            $viewRecord->save(false);

            // Now that we have a view ID, save it on the model
            if ($isNewView) {
                $view->id = $viewRecord->id;
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Delete a view by ID.
     *
     * @param int $viewId
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteViewById(int $viewId): bool
    {
        $viewRecord = ViewRecord::findOne($viewId);

        if (!$viewRecord) {
            return true;
        }

        $viewRecord->delete();

        return true;
    }

    /**
     * Save a site view.
     *
     * @param SiteView $siteView
     * @param bool     $runValidation
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveSiteView(SiteView $siteView, bool $runValidation = true): bool
    {
        if ($runValidation && !$siteView->validate()) {
            Craft::info('Site view not saved due to validation error.', __METHOD__);

            return false;
        }

        if ($siteView->siteId) {
            $siteViewRecord = SiteViewRecord::find()
                ->where(['siteId' => $siteView->siteId])
                ->one();

            if (!$siteViewRecord) {
                $siteViewRecord = new SiteViewRecord();
                $isNewSiteView = true;
            } else {
                $isNewSiteView = false;
            }
        } else {
            $siteViewRecord = new SiteViewRecord();
            $isNewSiteView = true;
        }

        // Shared attributes
        $siteViewRecord->siteId = $siteView->siteId;
        $siteViewRecord->viewId = $siteView->viewId;


        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Is the event giving us the go-ahead?
            $siteViewRecord->save(false);

            // Now that we have a view ID, save it on the model
            if ($isNewSiteView) {
                $siteView->id = $siteViewRecord->id;
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }
}
