<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\base;

use Craft;
use craft\db\Query;
use craft\helpers\Db;
use dukt\analytics\models\Info;
use dukt\analytics\Plugin as Analytics;
use yii\web\ServerErrorHttpException;

/**
 * PluginTrait implements the common methods and properties for plugin classes.
 *
 * @property \dukt\analytics\services\Analytics                 $analytics                  The analytics service
 * @property \dukt\analytics\services\Apis                      $apis                       The apis service
 * @property \dukt\analytics\services\Cache                     $cache                      The cache service
 * @property \dukt\analytics\services\Geo                       $geo                        The geo service
 * @property \dukt\analytics\services\MetadataUA                  $metadataUA                   The metadataUA service
 * @property \dukt\analytics\services\Oauth                     $oauth                      The oauth service
 * @property \dukt\analytics\services\Reports                   $reports                    The reports service
 * @property \dukt\analytics\services\Sources                     $sources                      The sources service
 */
trait PluginTrait
{
    // Properties
    // =========================================================================

    /**
     * @var Info|null
     */
    private ?Info $_info = null;

    /**
     * @var bool|null
     */
    private ?bool $_isInstalled = null;

    // Public Methods
    // =========================================================================

    /**
     * Returns the analytics service.
     *
     * @return \dukt\analytics\services\Analytics The analytics service
     * @throws \yii\base\InvalidConfigException
     */
    public function getAnalytics()
    {
        /** @var Analytics $this */
        return $this->get('analytics');
    }

    /**
     * Returns the apis service.
     *
     * @return \dukt\analytics\services\Apis The apis service
     * @throws \yii\base\InvalidConfigException
     */
    public function getApis()
    {
        /** @var Analytics $this */
        return $this->get('apis');
    }

    /**
     * Returns the cache service.
     *
     * @return \dukt\analytics\services\Cache The cache service
     * @throws \yii\base\InvalidConfigException
     */
    public function getCache()
    {
        /** @var Analytics $this */
        return $this->get('cache');
    }

    /**
     * Returns the geo service.
     *
     * @return \dukt\analytics\services\Geo The geo service
     * @throws \yii\base\InvalidConfigException
     */
    public function getGeo()
    {
        /** @var Analytics $this */
        return $this->get('geo');
    }

    /**
     * Returns the metadata service.
     *
     * @return \dukt\analytics\services\MetadataUA The metadata service
     * @throws \yii\base\InvalidConfigException
     */
    public function getMetadataUA()
    {
        /** @var Analytics $this */
        return $this->get('metadataUA');
    }

    /**
     * Returns the oauth service.
     *
     * @return \dukt\analytics\services\Oauth The oauth service
     * @throws \yii\base\InvalidConfigException
     */
    public function getOauth()
    {
        /** @var Analytics $this */
        return $this->get('oauth');
    }

    /**
     * Returns the reports service.
     *
     * @return \dukt\analytics\services\Reports The reports service
     * @throws \yii\base\InvalidConfigException
     */
    public function getReports()
    {
        /** @var Analytics $this */
        return $this->get('reports');
    }

    /**
     * Returns the sources service.
     *
     * @return \dukt\analytics\services\Sources The sources service
     * @throws \yii\base\InvalidConfigException
     */
    public function getSources()
    {
        /** @var Analytics $this */
        return $this->get('sources');
    }

    /**
     * Updates the info row.
     *
     * @param Info $info
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveInfo(Info $info): bool
    {
        $attributes = Db::prepareValuesForDb($info);

        if (array_key_exists('id', $attributes) && $attributes['id'] === null) {
            unset($attributes['id']);
        }

        if ($this->getIsInstalled()) {
            Craft::$app->getDb()->createCommand()
                ->update('{{%analytics_info}}', $attributes)
                ->execute();
        } else {
            Craft::$app->getDb()->createCommand()
                ->insert('{{%analytics_info}}', $attributes)
                ->execute();

            if (Craft::$app->getIsInstalled()) {
                // Set the new id
                $info->id = Craft::$app->getDb()->getLastInsertID('{{%analytics_info}}');
            }
        }

        $this->_info = $info;

        return true;
    }

    /**
     * Returns the info model, or just a particular attribute.
     *
     * @return Info
     * @throws ServerErrorHttpException if the info table is missing its row
     */
    public function getInfo(): Info
    {
        if ($this->_info !== null) {
            return $this->_info;
        }

        if (!$this->getIsInstalled()) {
            return new Info();
        }

        $row = (new Query())
            ->from(['{{%analytics_info}}'])
            ->one();

        if (!$row) {
            $tableName = Craft::$app->getDb()->getSchema()->getRawTableName('{{%analytics_info}}');
            throw new ServerErrorHttpException(sprintf('The %s table is missing its row', $tableName));
        }

        return $this->_info = new Info($row);
    }

    /**
     * Returns whether Craft is installed.
     *
     * @return bool
     */
    public function getIsInstalled(): bool
    {
        if ($this->_isInstalled !== null) {
            return $this->_isInstalled;
        }

        $infoRowExists = false;

        if(Craft::$app->getDb()->tableExists('{{%analytics_info}}', false)) {
            $infoRowExists = (new Query())
                ->from(['{{%analytics_info}}'])
                ->one();
        }

        return $this->_isInstalled = (
            Craft::$app->getIsDbConnectionValid() &&
            $infoRowExists
        );
    }
}
