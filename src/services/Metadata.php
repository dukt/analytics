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
    private $groups;

    /**
     * @var array|null
     */
    private $dimensions;

    /**
     * @var array|null
     */
    private $metrics;

    /**
     * @var array|null
     */
    private $columns;

    // Public Methods
    // =========================================================================

    /**
     * Returns available data types for Google Analytics
     *
     * @return array
     */
    public function getGoogleAnalyticsDataTypes(): array
    {
        $columns = $this->getColumns();

        $dataTypes = [];

        foreach ($columns as $column) {
            if (!isset($dataTypes[$column->dataType]) && !empty($column->dataType)) {
                $dataTypes[$column->dataType] = $column->dataType;
            }
        }

        return $dataTypes;
    }

    /**
     * Returns available data types
     *
     * @return array
     */
    public function getDataTypes(): array
    {
        return [
            'string',
            'integer',
            'percent',
            'time',
            'currency',
            'float',
            'date'
        ];
    }

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
     * Returns dimension columns
     *
     * @return array
     */
    public function getDimensions(): array
    {
        if (!$this->dimensions) {
            $this->dimensions = $this->getColumns('DIMENSION');
        }

        return $this->dimensions;
    }

    /**
     * Returns column groups
     *
     * @param string|null $type
     *
     * @return array
     */
    public function getColumnGroups(string $type = null): array
    {
        if ($type && isset($this->groups[$type])) {
            return $this->groups[$type];
        }

        $groups = $this->_getColumnGroups($type);

        if ($type) {
            $this->groups[$type] = $groups;
        }

        return $groups;
    }

    /**
     * Returns the metrics
     *
     * @return array
     */
    public function getMetrics(): array
    {
        if (!$this->metrics) {
            $this->metrics = $this->getColumns('METRIC');
        }

        return $this->metrics;
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
     * Get column groups.
     *
     * @param string|null $type
     * @return array
     */
    private function _getColumnGroups(string $type = null): array
    {
        $groups = [];

        foreach ($this->getColumns() as $column) {
            if (!$type || ($type && $column->type === $type)) {
                $groups[$column->group] = $column->group;
            }
        }

        return $groups;
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
