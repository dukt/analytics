<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2018, Dukt
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

    /**
     * @var array|null
     */
    private $selectDimensionOptions;

    /**
     * @var array|null
     */
    private $selectMetricOptions;

    // Public Methods
    // =========================================================================

    /**
     * Checks whether the dimensions & metrics file exists
     *
     * @return bool
     */
    public function dimmetsFileExists(): bool
    {
        $path = Analytics::$plugin->metadata->getDimmetsFilePath();

        if (file_exists($path)) {
            return true;
        }

        return false;
    }

    /**
     * Returns available data types for Google Analytics
     *
     * @param mixed
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
     * @param mixed
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
     * @param string $id
     *
     * @return mixed
     */
    public function getDimMet($id)
    {
        $columns = $this->getColumns();

        if (isset($columns[$id])) {
            return $columns[$id]->uiName;
        }

        return null;
    }

    /**
     * Returns columns based on a search string `$q`
     *
     * @param string $q
     *
     * @return array
     */
    public function searchColumns($q): array
    {
        $columns = $this->getColumns();
        $results = [];

        foreach ($columns as $column) {
            if (stripos($column->id, $q) !== false || stripos($column->uiName, $q) !== false) {
                $results[] = $column;
            }
        }

        return $results;
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
     * Returns select dimension options
     *
     * @param array $filters
     *
     * @return array
     */
    public function getSelectDimensionOptions(array $filters = null): array
    {
        if (!$this->selectDimensionOptions) {
            $this->selectDimensionOptions = $this->getSelectOptions('DIMENSION');
        }

        if ($filters) {
            $this->selectDimensionOptions = $this->filterOptions($this->selectDimensionOptions, $filters);
        }

        return $this->selectDimensionOptions;
    }

    /**
     * Returns select metric options
     *
     * @param array $filters
     *
     * @return array
     */
    public function getSelectMetricOptions(array $filters = null): array
    {
        if (!$this->selectMetricOptions) {
            $this->selectMetricOptions = $this->getSelectOptions('METRIC');
        }

        if ($filters) {
            $this->selectMetricOptions = $this->filterOptions($this->selectMetricOptions, $filters);
        }

        return $this->selectMetricOptions;
    }

    /**
     * Returns select options
     *
     * @param null  $type
     * @param array $filters
     *
     * @return array
     */
    public function getSelectOptions($type = null, array $filters = null): array
    {
        $options = [];

        foreach ($this->getColumnGroups($type) as $group) {
            $options[]['optgroup'] = Craft::t('analytics', $group);

            foreach ($this->getColumns($type) as $column) {
                if ($column->group === $group) {
                    $options[$column->id] = Craft::t('analytics', $column->uiName);
                }
            }
        }

        if ($filters) {
            $options = $this->filterOptions($options, $filters);
        }

        return $options;
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
        $path = Analytics::$plugin->metadata->getDimmetsFilePath();
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
     * @param array $options
     * @param array $filters
     *
     * @return array
     */
    private function filterOptions(array $options, array $filters): array
    {
        if (\count($filters) === 0) {
            return $options;
        }

        return $this->getFilteredOptions($options, $filters);
    }

    /**
     * Get filtered options.
     *
     * @param array $options
     * @param array $filters
     *
     * @return array
     */
    private function getFilteredOptions(array $options, array $filters): array
    {
        $filteredOptions = [];
        $optgroup = null;
        $lastOptgroup = null;

        foreach ($options as $id => $option) {
            if (isset($option['optgroup'])) {
                $optgroup = null;
                $lastOptgroup = $option['optgroup'];
                continue;
            }

            foreach ($filters as $filter) {
                if ($id !== $filter) {
                    continue;
                }

                if (!$optgroup) {
                    $optgroup = $lastOptgroup;
                    $filteredOptions[]['optgroup'] = $optgroup;
                }

                $filteredOptions[$id] = $option;
            }
        }

        return $filteredOptions;
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
