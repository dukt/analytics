<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class AnalyticsHelper
{
    // Public Methods
    // =========================================================================

    /**
     * Transforms a GA Data object to an array
     */
    public static function gaDataToArray($data)
    {
        // cols
        $cols = [];

        foreach($data->columnHeaders as $col)
        {
            // define the right type for the chart

            $dataType = $col->dataType;
            $type = $col->dataType;
            $id = $col->name;
            $label = craft()->analytics->getDimMet($col->name);

            switch($col->name)
            {
                case 'ga:date':
                case 'ga:yearMonth':
                $type = 'date';
                break;

                case 'ga:latitude':
                case 'ga:longitude':
                $type = 'number';
                $dataType = 'FLOAT';
                break;
            }

            switch($type)
            {
                case 'INTEGER':
                case 'CURRENCY':
                case 'FLOAT':
                case 'TIME':
                case 'PERCENT':
                $type = 'number';
                break;

                case 'STRING':
                $type = 'string';
                break;
            }

            $cols[] = array(
                'type' => $type,
                'dataType' => $dataType,
                'id' => $id,
                'label' => Craft::t($label),
            );
        }

        // rows
        $rows = [];

        if($data->rows)
        {
            $rows = $data->rows;

            foreach($rows as $kRow => $row)
            {
                foreach($row as $kCell => $value)
                {
                    $col = $cols[$kCell];

                    // replace value by cell

                    $cell = array(
                        'v' => AnalyticsHelper::formatRawValue($col['dataType'], $value),
                        'f' => AnalyticsHelper::formatValue($col['dataType'], $value)
                    );

                    if($col['id'] == 'ga:continent')
                    {
                        $cell['v'] = craft()->analytics->getContinentCode($cell['v']);
                    }

                    if($col['id'] == 'ga:subContinent')
                    {
                        $cell['v'] = craft()->analytics->getSubContinentCode($cell['v']);
                    }

                    // translate values
                    switch($col['id'])
                    {
                        case 'ga:country':
                        case 'ga:city':
                        case 'ga:continent':
                        case 'ga:subContinent':
                        case 'ga:userType':
                        case 'ga:javaEnabled':
                        case 'ga:deviceCategory':
                        case 'ga:mobileInputSelector':
                        case 'ga:channelGrouping':
                        case 'ga:medium':
                            $cell['f'] = Craft::t($cell['f']);
                            break;
                    }

                    // update cell
                    $rows[$kRow][$kCell] = $cell;
                }
            }
        }

        return array(
            'cols' => $cols,
            'rows' => $rows
        );
    }

    /**
     * Format Cell
     */
    public static function formatCell($value, $column)
    {
        switch($column['name'])
        {
            case "ga:avgTimeOnPage":
                $value = self::formatTime($value);
                return $value;
                break;

            case 'ga:pageviewsPerSession':
                $value = round($value, 2);
                return $value;
                break;

            case 'ga:entranceRate':
            case 'ga:visitBounceRate':
            case 'ga:exitRate':
                $value = round($value, 2)."%";
                return $value;
                break;

            default:
                return $value;
        }
    }

    /**
     * Format RAW value
     *
     * @param string $type
     * @param string $value
     */
    public static function formatRawValue($type, $value)
    {
        switch($type)
        {
            case 'INTEGER':
            case 'CURRENCY':
            case 'FLOAT':
            case 'TIME':
            case 'PERCENT':
            $value = (float) $value;
            break;

            default:
            $value = (string) $value;
        }

        return $value;
    }

    /**
     * Format Value
     *
     * @param string $type
     * @param string $value
     */
    public static function formatValue($type, $value)
    {
        switch($type)
        {
            case 'INTEGER':
            case 'CURRENCY':
            case 'FLOAT':
            $value = (float) $value;
            $value = round($value, 2);
            $value = craft()->numberFormatter->formatDecimal($value);
            break;

            case 'TIME':
            $value = (float) $value;
            $value = self::formatTime($value);
            break;

            case 'PERCENT':
            $value = (float) $value;
            $value = round($value, 2);
            $value = $value.'%';

            break;

            default:
            $value = (string) $value;
        }

        return (string) $value;
    }

    /**
     * Format Time in HH:MM:SS from seconds
     *
     * @param int $seconds
     */
    public static function formatTime($seconds)
    {
        return gmdate("H:i:s", $seconds);
    }
}
