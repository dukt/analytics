<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_MetaService extends BaseApplicationComponent
{
    private $columns;
    private $selectDimensionOptions;
    private $selectMetricOptions;

    /**
     * Get Continent Code
     *
     * @param string $label
     */
    public function getContinentCode($label)
    {
        $continents = $this->getData('continents');

        foreach($continents as $continent)
        {
            if($continent['label'] == $label)
            {
                return $continent['code'];
            }
        }
    }

    /**
     * Get Sub-Continent Code
     *
     * @param string $label
     */
    public function getSubContinentCode($label)
    {
        $subContinents = $this->getData('subContinents');

        foreach($subContinents as $subContinent)
        {
            if($subContinent['label'] == $label)
            {
                return $subContinent['code'];
            }
        }
    }

    /**
     * Get a dimension or a metric label from its key
     *
     * @param string $key
     */
    public function getDimMet($id)
    {
        $columns = $this->getColumns();

        if(isset($columns[$id]))
        {
            return $columns[$id]->uiName;
        }
    }

    public function searchColumns($q)
    {
        $columns = $this->getColumns();
        $results = [];

        foreach($columns as $column)
        {
            if(stripos($column->id, $q) !== false || stripos($column->uiName, $q) !== false)
            {
                $results[] = $column;
            }
        }

        return $results;
    }

    public function getColumns($type = null)
    {
        if(!$this->columns)
        {
            $this->columns = $this->loadColumns();
        }

        if($type)
        {
            $columns = [];

            foreach($this->columns as $column)
            {
                if($column->type == $type)
                {
                    $columns[] = $column;
                }
            }

            return $columns;
        }
        else
        {
            return $this->columns;
        }
    }

    public function getDimensions()
    {
        return $this->getColumns('DIMENSION');
    }

    public function getGroups($type = null)
    {
        $groups = [];

        foreach($this->getColumns() as $column)
        {
            if(!$type || ($type && $column->type == $type))
            {
                $groups[$column->group] = $column->group;
            }
        }

        return $groups;
    }

    public function getSelectDimensionOptions($filters = null)
    {
        if(!$this->selectDimensionOptions)
        {
            $this->selectDimensionOptions = $this->getSelectOptions('DIMENSION');
        }

        if($filters && is_array($filters))
        {
            $filteredOptions = [];

            foreach($this->selectDimensionOptions as $id => $option)
            {
                if(isset($option['optgroup']))
                {
                    $optgroup = null;
                    $lastOptgroup = $option['optgroup'];
                }
                else
                {
                    foreach($filters as $filter)
                    {
                        if($id == $filter)
                        {
                            if(!$optgroup)
                            {
                                $optgroup = $lastOptgroup;
                                $filteredOptions[]['optgroup'] = $optgroup;
                            }

                            $filteredOptions[$id] = $option;
                        }
                    }
                }
            }

            return $filteredOptions;
        }
        else
        {
            return $this->selectDimensionOptions;
        }
    }

    public function getSelectMetricOptions($filters = null)
    {
        if(!$this->selectMetricOptions)
        {
            $this->selectMetricOptions = $this->getSelectOptions('METRIC');
        }

        if($filters && is_array($filters))
        {
            $filteredOptions = [];

            foreach($this->selectMetricOptions as $id => $option)
            {
                if(isset($option['optgroup']))
                {
                    $optgroup = null;
                    $lastOptgroup = $option['optgroup'];
                }
                else
                {
                    foreach($filters as $filter)
                    {
                        if($id == $filter)
                        {
                            if(!$optgroup)
                            {
                                $optgroup = $lastOptgroup;
                                $filteredOptions[]['optgroup'] = $optgroup;
                            }

                            $filteredOptions[$id] = $option;
                        }
                    }
                }
            }

            return $filteredOptions;
        }
        else
        {
            return $this->selectMetricOptions;
        }
    }

    public function getSelectOptions($type = null, $filters = null)
    {
        $options = [];

        foreach($this->getGroups($type) as $group)
        {
            $options[]['optgroup'] = Craft::t($group);

            foreach($this->getColumns($type) as $column)
            {
                if($column->group == $group)
                {
                    $options[$column->id] = Craft::t($column->uiName);
                }
            }
        }


        // filters

        if($filters && is_array($filters))
        {
            $filteredOptions = [];

            foreach($options as $id => $option)
            {
                if(isset($option['optgroup']))
                {
                    $optgroup = null;
                    $lastOptgroup = $option['optgroup'];
                }
                else
                {
                    foreach($filters as $filter)
                    {
                        if($id == $filter)
                        {
                            if(!$optgroup)
                            {
                                $optgroup = $lastOptgroup;
                                $filteredOptions[]['optgroup'] = $optgroup;
                            }

                            $filteredOptions[$id] = $option;
                        }
                    }
                }
            }

            return $filteredOptions;
        }
        else
        {
            return $options;
        }
    }

    public function getMetrics()
    {
        return $this->getColumns('METRIC');
    }

    // Private Methods
    // =========================================================================

    private function loadColumns()
    {
        $columns = [];

        $cacheId = ['Analytics_MetaService.loadColumns.columns'];
        $items = craft()->analytics_cache->get($cacheId);

        if(!$items)
        {
            $metadataColumns = craft()->analytics_api->getMetadataColumns();

            if($metadataColumns)
            {
                $items = $metadataColumns->listMetadataColumns('ga');

                if($items)
                {
                    craft()->analytics_cache->set($cacheId, $items);
                }
            }
        }

        if($items)
        {
            foreach($items as $item)
            {
                if($item->attributes['status'] == 'DEPRECATED')
                {
                    continue;
                }

                if(isset($item->attributes['minTemplateIndex']))
                {
                    for($i = $item->attributes['minTemplateIndex']; $i <= $item->attributes['maxTemplateIndex']; $i++)
                    {
                        $column = new Analytics_ColumnModel;
                        $column->id = str_replace('XX', $i, $item->id);
                        $column->type = $item->attributes['type'];
                        $column->group = $item->attributes['group'];
                        $column->status = $item->attributes['status'];
                        $column->uiName = str_replace('XX', $i, $item->attributes['uiName']);
                        $column->description = str_replace('XX', $i, $item->attributes['description']);

                        if(isset($item->attributes['allowInSegments']))
                        {
                            $column->allowInSegments = $item->attributes['allowInSegments'];
                        }

                        $columns[$column->id] = $column;
                    }
                }
                else
                {
                    $column = new Analytics_ColumnModel;
                    $column->id = $item->id;
                    $column->type = $item->attributes['type'];
                    $column->group = $item->attributes['group'];
                    $column->status = $item->attributes['status'];
                    $column->uiName = $item->attributes['uiName'];
                    $column->description = $item->attributes['description'];

                    if(isset($item->attributes['allowInSegments']))
                    {
                        $column->allowInSegments = $item->attributes['allowInSegments'];
                    }

                    $columns[$column->id] = $column;
                }
            }
        }

        return $columns;
    }


    /**
     * Get Data
     *
     * @param string $label
     */
    private function getData($name)
    {
        $jsonData = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/etc/data/'.$name.'.json');
        $data = json_decode($jsonData, true);

        return $data;
    }
}
