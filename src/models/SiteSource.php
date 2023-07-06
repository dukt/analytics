<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\models;

use craft\base\Model;
use dukt\analytics\Plugin as Analytics;

/**
 * Class SiteSource
 *
 * @package dukt\analytics\models
 */
class SiteSource extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int ID
     */
    public $id;

    /**
     * @var int Site ID
     */
    public $siteId;

    /**
     * @var int Source ID
     */
    public $sourceId;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = [
            [['id'], 'number', 'integerOnly' => true]
        ];

        return $rules;
    }

    /**
     * @return Source|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getSource()
    {
        return Analytics::$plugin->getSources()->getSourceById($this->sourceId);
    }
}
