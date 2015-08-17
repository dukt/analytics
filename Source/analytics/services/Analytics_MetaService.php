<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_MetaService extends BaseApplicationComponent
{
    private $columns;

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

    public function getSelectOptions($type = null)
    {
        $options = [];

        foreach($this->getGroups($type) as $group)
        {
            $options[]['optgroup'] = $group;

            foreach($this->getColumns($type) as $column)
            {
                if($column->group == $group)
                {
                    $options[$column->id] = $column->uiName;
                }
            }
        }

        return $options;
    }

    public function getMetrics()
    {
        return $this->getColumns('DIMENSION');
    }

    private function loadColumns()
    {
        $columns = [];

        $items = craft()->analytics->getApiDimensionsMetrics()->items;

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

                    $columns[] = $column;
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

                $columns[] = $column;
            }
        }

        return $columns;
    }
}
