<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use Craft;
use dukt\analytics\errors\InvalidViewException;
use dukt\analytics\models\SiteSource;
use dukt\analytics\models\Source;
use dukt\analytics\records\Source as SourceRecord;
use dukt\analytics\records\SiteSource as SiteSourceRecord;
use yii\base\Component;
use Exception;

class Sources extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Get all sources.
     *
     * @return array|null
     */
    public function getSources()
    {
        $results = SourceRecord::find()->all();

        $sources = [];

        foreach ($results as $result) {
            $sources[] = new Source($result->toArray([
                'id',
                'type',
                'name',
                'gaAccountId',
                'gaAccountName',
                'gaCurrency',
                'gaPropertyId',
                'gaPropertyName',
                'gaViewId',
                'gaViewName',
            ]));
        }

        return $sources;
    }

    /**
     * Get source by ID.
     *
     * @param $id
     *
     * @return Source|null
     */
    public function getSourceById($id)
    {
        $result = SourceRecord::findOne($id);

        if ($result !== null) {
            return new Source($result->toArray([
                'id',
                'type',
                'name',
                'gaAccountId',
                'gaAccountName',
                'gaPropertyId',
                'gaPropertyName',
                'gaCurrency',
                'gaViewId',
                'gaViewName',
            ]));
        }

        return null;
    }

    /**
     * Get site sources.
     *
     * @return array
     */
    public function getSiteSources()
    {
        $results = SiteSourceRecord::find()->all();

        $sources = [];

        foreach ($results as $result) {
            $sources[] = new SiteSource($result->toArray([
                'siteId',
                'sourceId',
            ]));
        }

        return $sources;
    }

    /**
     * Get ite source by site ID.
     *
     * @param $id
     *
     * @return SiteSource|null
     */
    public function getSiteSourceBySiteId($id)
    {
        $result = SiteSourceRecord::findOne([
            'siteId' => $id
        ]);

        if ($result !== null) {
            return new SiteSource($result->toArray([
                'id',
                'siteId',
                'sourceId',
            ]));
        }

        return null;
    }

    /**
     * Saves a source.
     *
     * @param Source $source          The source to be saved
     * @param bool $runValidation Whether the source should be validated
     *
     * @return bool
     * @throws InvalidViewException if $source->id is invalid
     * @throws Exception if reasons
     */
    public function saveSource(Source $source, bool $runValidation = true): bool
    {
        if ($runValidation && !$source->validate()) {
            Craft::info('View not saved due to validation error.', __METHOD__);
            throw new InvalidViewException(sprintf("View doesn't validate"));

            return false;
        }

        if ($source->id) {
            $sourceRecord = SourceRecord::findOne($source->id);

            if (!$sourceRecord) {
                throw new InvalidViewException(sprintf("No source exists with the ID '%d'", $source->id));
            }

            $isNewSource = false;
        } else {
            $sourceRecord = new SourceRecord();
            $isNewSource = true;
        }

        // Shared attributes
        $sourceRecord->type = $source->type;
        $sourceRecord->name = $source->name;
        $sourceRecord->gaAccountId = $source->gaAccountId;
        $sourceRecord->gaAccountName = $source->gaAccountName;
        $sourceRecord->gaPropertyId = $source->gaPropertyId;
        $sourceRecord->gaPropertyName = $source->gaPropertyName;
        $sourceRecord->gaViewId = $source->gaViewId;
        $sourceRecord->gaViewName = $source->gaViewName;
        $sourceRecord->gaCurrency = $source->gaCurrency;


        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Is the event giving us the go-ahead?
            $sourceRecord->save(false);

            // Now that we have a source ID, save it on the model
            if ($isNewSource) {
                $source->id = $sourceRecord->id;
            }

            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }

    /**
     * Delete a source by ID.
     *
     * @param int $sourceId
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteSourceById(int $sourceId): bool
    {
        $sourceRecord = SourceRecord::findOne($sourceId);

        if (!$sourceRecord instanceof \dukt\analytics\records\Source) {
            return true;
        }

        $sourceRecord->delete();

        return true;
    }

    /**
     * Save a site source.
     *
     * @param SiteSource $siteSource
     * @param bool     $runValidation
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveSiteSource(SiteSource $siteSource, bool $runValidation = true): bool
    {
        if ($runValidation && !$siteSource->validate()) {
            Craft::info('Site source not saved due to validation error.', __METHOD__);

            return false;
        }

        if ($siteSource->siteId) {
            $siteSourceRecord = SiteSourceRecord::findOne(['siteId' => $siteSource->siteId]);

            if (!$siteSourceRecord) {
                $siteSourceRecord = new SiteSourceRecord();
                $isNewSiteView = true;
            } else {
                $isNewSiteView = false;
            }
        } else {
            $siteSourceRecord = new SiteSourceRecord();
            $isNewSiteView = true;
        }

        // Shared attributes
        $siteSourceRecord->siteId = $siteSource->siteId;
        $siteSourceRecord->sourceId = $siteSource->sourceId;


        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Is the event giving us the go-ahead?
            $siteSourceRecord->save(false);

            // Now that we have a source ID, save it on the model
            if ($isNewSiteView) {
                $siteSource->id = $siteSourceRecord->id;
            }

            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }
}
