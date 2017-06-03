<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use dukt\analytics\models\View;
use dukt\analytics\records\View as ViewRecord;
use yii\base\Component;

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
                'reportingViewId',
            ]));
        }

        return $views;
    }

    public function getViewById($id)
    {
        $result = ViewRecord::findOne($id);

        if($result) {
            return new View($result->toArray([
                'id',
                'name',
                'reportingViewId',
            ]));
        }
    }

    /**
     * Saves a view.
     *
     * @param View $view          The view to be saved
     * @param bool $runValidation Whether the view should be validated
     *
     * @return bool
     * @throws ViewNotFoundException if $view->id is invalid
     * @throws \Exception if reasons
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
                throw new ViewNotFoundException("No view exists with the ID '{$view->id}'");
            }

            $isNewView = false;
        } else {
            $viewRecord = new ViewRecord();
            $isNewView = true;
        }

        // Shared attributes
        $viewRecord->name = $view->name;
        $viewRecord->reportingViewId = $view->reportingViewId;


        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Is the event giving us the go-ahead?
            $viewRecord->save(false);

            // Now that we have a view ID, save it on the model
            if ($isNewView) {
                $view->id = $viewRecord->id;
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    public function deleteViewById(int $viewId): bool
    {
        $viewRecord = ViewRecord::findOne($viewId);

        if (!$viewRecord) {
            return true;
        }

        $viewRecord->delete();

/*        Craft::$app->getDb()->createCommand()
            ->delete('{{%analytics_views}}', ['id' => $viewId])
            ->execute();*/


        return true;
    }
}
