<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use Craft;
use craft\helpers\Json;
use dukt\analytics\models\Column;
use dukt\analytics\Plugin as Analytics;
use yii\base\Component;

class MetadataGA4 extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var array|null
     */
    private $data;

    // Public Methods
    // =========================================================================

    /**
     * Returns the file path of the dimensions-metrics.json file
     *
     * @return string|bool
     */
    public function getDimmetsFilePath()
    {
        return Craft::getAlias('@dukt/analytics/etc/data/dimensions-metrics/ga4.json');
    }

    /**
     * Get a dimension or a metric label from its id
     *
     *
     * @return mixed
     */
    public function getDimMet(string $id)
    {
        $data = $this->_loadData();

        if (isset($data['dimensions']) && isset($data['dimensions'][$id])) {
            return $data['dimensions'][$id];
        }

        if (isset($data['metrics']) && isset($data['metrics'][$id])) {
            return $data['metrics'][$id];
        }

        return $id;
    }

    // Private Methods
    // =========================================================================

    /**
     * Loads the columns from the dimensions-metrics.json file
     */
    private function _loadData()
    {
        if (!$this->data) {
            $path = Analytics::$plugin->getMetadataGA4()->getDimmetsFilePath();
            $contents = file_get_contents($path);
            $response = Json::decode($contents);

            if ($response) {
                $this->data = $response;
            }
        }

        return $this->data;
    }
}
