<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\models;

use craft\base\Model;
use dukt\analytics\Plugin as Analytics;

/**
 * Class SiteView
 *
 * @package dukt\analytics\models
 */
class SiteView extends Model
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
     * @var int View ID
     */
    public $viewId;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['id'], 'number', 'integerOnly' => true]
        ];

        return $rules;
    }

    /**
     * @return View|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getView()
    {
        return Analytics::$plugin->getViews()->getViewById($this->viewId);
    }
}
