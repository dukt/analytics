<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use Craft;
use yii\base\Component;
use craft\helpers\Json;
use dukt\analytics\models\Column;
use dukt\analytics\Plugin as Analytics;

class Metadata extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var array|null
     */
    private $columns;

    // Public Methods
    // =========================================================================

    /**
     * Get a dimension or a metric label from its id
     *
     *
     * @return mixed
     */
    public function getDimMet(string $id)
    {
        $columns = $this->getColumns();

        if (isset($columns[$id])) {
            return $columns[$id]->uiName;
        }

        return null;
    }

    /**
     * Returns columns
     *
     * @param string $type
     *
     * @return array
     */
    public function getColumns(string $type = null): array
    {
        if (!$this->columns) {
            $this->columns = $this->_loadColumns();
        }

        if (!$type) {
            return $this->columns;
        }

        return $this->getColumnsByType($type);
    }

    /**
     * Returns the file path of the dimensions-metrics.json file
     *
     * @return string|bool
     */
    public function getDimmetsFilePath()
    {
        return Craft::getAlias('@dukt/analytics/etc/data/dimensions-metrics.json');
    }

    // Private Methods
    // =========================================================================

    /**
     * Loads the columns from the dimensions-metrics.json file
     *
     * @return array
     */
    private function _loadColumns(): array
    {
        $cols = [];
        $path = Analytics::$plugin->getMetadata()->getDimmetsFilePath();
        $contents = file_get_contents($path);
        $columnsResponse = Json::decode($contents);

        if (!$columnsResponse) {
            return $cols;
        }

        foreach ($columnsResponse as $columnResponse) {
            $cols[$columnResponse['id']] = new Column($columnResponse);

            if ($columnResponse['id'] === 'ga:countryIsoCode') {
                $cols[$columnResponse['id']]->uiName = 'Country';
            }
        }

        return $cols;
    }

    /**
     * Get columns by type.
     *
     * @param string $type
     * @return array
     */
    private function getColumnsByType(string $type): array
    {
        $columns = [];

        foreach ($this->columns as $column) {
            if ($column->type === $type) {
                $columns[] = $column;
            }
        }

        return $columns;
    }
}
