<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 * @link      https://dukt.net/craft/analytics/
 */

namespace Craft;

class AnalyticsController extends BaseController
{
    public function actionElementReport(array $variables = array())
    {
        $elementId = craft()->request->getParam('id');
        $metric = craft()->request->getParam('metric');
        $element = craft()->elements->getElementById($elementId);

        $profile = craft()->analytics->getProfile();
        $start = date('Y-m-d', strtotime('-1 month'));
        $end = date('Y-m-d');
        $metrics = $metric;
        $dimensions = 'ga:date';

        $response = craft()->analytics->api()->data_ga->get(
            'ga:'.$profile['id'],
            $start,
            $end,
            $metrics,
            array(
                'dimensions' => $dimensions,
                'filters' => "ga:pagePath==/".$element->uri
            )
        );

        $this->returnJson(array('apiResponse' => $response));
    }

    public function actionCustomReport(array $variables = array())
    {
        // widget

        $id = craft()->request->getParam('id');
        $widget = craft()->dashboard->getUserWidgetById($id);


        // profile
        $profile = craft()->analytics->getProfile();

        // start / end dates
        $start = craft()->request->getParam('start');
        $end = craft()->request->getParam('end');

        if(empty($start))
        {
            $start = date('Y-m-d', strtotime('-1 month'));
        }

        if(empty($end))
        {
            $end = date('Y-m-d');
        }

        // filters
        $filters = false;
        $queryFilters = '';

        if(!empty($widget->settings['options']['filters']))
        {
            $filters = $widget->settings['options']['filters'];

            foreach($filters as $filter)
            {
                $visibility = $filter['visibility'];



                switch($filter['operator'])
                {
                    case 'exactMatch':
                    $operator = ($visibility == 'hide' ? '!=' : '==');
                    break;

                    case 'regularExpression':
                    $operator = ($visibility == 'hide' ? '!~' : '=~');
                    break;

                    case 'contains':
                    // contains or doesn't contain
                    $operator = ($visibility == 'hide' ? '!@' : '=@');
                    break;
                }

                $queryFilter = '';
                $queryFilter .= $filter['dimension'];
                $queryFilter .= $operator;
                $queryFilter .= $filter['value'];

                $queryFilters .= $queryFilter.";"; //AND
            }

            if(strlen($queryFilters) > 0)
            {
                // remove last AND
                $queryFilters = substr($queryFilters, 0, -1);
            }
        }

        // dimensions & metrics

        $metric = $widget->settings['options']['metric'];

        $options = array(
            'dimensions' => $widget->settings['options']['dimension']
        );

        if(!empty($queryFilters))
        {
            $options['filters'] = $queryFilters;
        }
        switch($widget->settings['options']['chartType'])
        {
            case 'ColumnChart':
            case 'PieChart':
            case 'Table':
                $slices = (!empty($widget->settings['options']['slices']) ? $widget->settings['options']['slices'] : 2);

                $options['sort'] = '-'.$widget->settings['options']['metric'];
                $options['max-results'] = $slices;

                $response = craft()->analytics->api()->data_ga->get(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    $options
                );

                break;

            case 'GeoChart':

                $options['sort'] = '-'.$widget->settings['options']['metric'];

                $continent = $this->continents($widget->settings['options']['region']);
                $subContinent = $this->subContinents($widget->settings['options']['region']);
                $country = $this->countries($widget->settings['options']['region']);

                $options['filters'] = (!empty($options['filters']) ? $options['filters'].';' : '');
                $options['filters'] = $widget->settings['options']['dimension'].'!=(not set)';


                if($continent)
                {
                    //$options['filters'] = (!empty($options['filters']) ? $options['filters'].';' : '');
                    $options['filters'] .= ';ga:continent=='.$continent;
                }
                elseif ($subContinent)
                {
                    //$options['filters'] = (!empty($options['filters']) ? $options['filters'].';' : '');
                    $options['filters'] .= ';ga:subContinent=='.$subContinent;
                }
                elseif ($country)
                {
                    $options['filters'] .= ';ga:country=='.$country;
                }

                if($widget->settings['options']['chartType'] == 'GeoChart'
                    && $widget->settings['options']['dimension'] == 'ga:city')
                {
                    $options['dimensions'] = "ga:latitude,ga:longitude,".$options['dimensions'];
                }

                $response = craft()->analytics->api()->data_ga->get(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    $options
                );

                foreach($response['rows'] as $k => $row)
                {
                    $continent = $this->rcontinents($row[0]);
                    $subContinent = $this->rsubContinents($row[0]);
                    $usState = $this->usstates($row[0]);

                    if($continent)
                    {
                        $response['rows'][$k][0] = $continent;
                    }
                    elseif($subContinent)
                    {
                        $response['rows'][$k][0] = $subContinent;
                    }
                    elseif($usState)
                    {
                        //$response['rows'][$k][0] = $usState;
                    }
                }

                break;

            default:

                $response = craft()->analytics->api()->data_ga->get(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    $options
                );

                break;
        }



        $this->returnJson(array(
            'widget' => $widget,
            'apiResponse' => $response
        ));
    }

    private function usstates($name)
    {
        $code = substr($name, (strlen($name) - 2));
        $code = 'US-'.$code;

        return array(
            'v' => $code,
            'f' => $name
        );
    }

    private function rcontinents($name, $returnArray = true)
    {
        $continents = $this->continents();

        foreach($continents as $code => $continent)
        {
            if($continent == $name)
            {
                return array(
                    'v' => $code,
                    'f' => $name
                );
            }
        }
    }

    private function rsubContinents($name, $returnArray = true)
    {
        $subContinents = $this->subContinents();

        foreach($subContinents as $code => $subContinent)
        {
            if($subContinent == $name)
            {
                return array(
                    'v' => $code,
                    'f' => $name
                );
            }
        }
    }

    private function countries($code = null)
    {
        $countries = array(
            "AD" => "Andorra",
            "AE" => "United Arab Emirates",
            "AF" => "Afghanistan",
            "AG" => "Antigua and Barbuda",
            "AI" => "Anguilla",
            "AL" => "Albania",
            "AM" => "Armenia",
            "AN" => "Netherlands Antilles",
            "AO" => "Angola",
            "AR" => "Argentina",
            "AS" => "American Samoa ",
            "AT" => "Austria",
            "AU" => "Australia",
            "AW" => "Aruba",
            "AX" => "Åland Islands",
            "AZ" => "Azerbaijan",
            "BA" => "Bosnia and Herzegovina",
            "BB" => "Barbados",
            "BD" => "Bangladesh",
            "BE" => "Belgium",
            "BF" => "Burkina Faso",
            "BG" => "Bulgaria",
            "BH" => "Bahrain",
            "BI" => "Burundi",
            "BJ" => "Benin",
            "BL" => "Saint Barthélemy",
            "BM" => "Bermuda",
            "BN" => "Brunei Darussalam",
            "BO" => "Bolivia",
            "BR" => "Brazil",
            "BS" => "Bahamas",
            "BT" => "Bhutan",
            "BU" => "Burma",
            "BW" => "Botswana",
            "BY" => "Belarus",
            "BZ" => "Belize",
            "CA" => "Canada",
            "CD" => "Congo, the Democratic Republic of the",
            "CF" => "Central African Republic",
            "CG" => "Congo",
            "CH" => "Switzerland",
            "CI" => "Côte d'Ivoire",
            "CK" => "Cook Islands",
            "CL" => "Chile",
            "CM" => "Cameroon",
            "CN" => "China",
            "CO" => "Colombia",
            "CR" => "Costa Rica",
            "CS" => "Serbia and Montenegro",
            "CU" => "Cuba",
            "CV" => "Cape Verde",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "DE" => "Germany",
            "DJ" => "Djibouti",
            "DK" => "Denmark",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "DZ" => "Algeria",
            "EC" => "Ecuador",
            "EE" => "Estonia",
            "EG" => "Egypt",
            "EH" => "Western Sahara",
            "ER" => "Eritrea",
            "ES" => "Spain",
            "ET" => "Ethiopia",
            "FI" => "Finland",
            "FJ" => "Fiji",
            "FK" => "Falkland Islands (Malvinas)",
            "FM" => "Micronesia, Federated States of",
            "FO" => "Faroe Islands",
            "FR" => "France",
            "FX" => "France, Metropolitan",
            "GA" => "Gabon",
            "GB" => "United Kingdom",
            "GD" => "Grenada",
            "GE" => "Georgia",
            "GF" => "French Guiana",
            "GG" => "Guernsey",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GL" => "Greenland",
            "GM" => "Gambia",
            "GN" => "Guinea",
            "GP" => "Guadeloupe",
            "GQ" => "Equatorial Guinea",
            "GR" => "Greece",
            "GT" => "Guatemala",
            "GU" => "Guam",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HK" => "Hong Kong",
            "HN" => "Honduras",
            "HR" => "Croatia",
            "HT" => "Haiti",
            "HU" => "Hungary",
            "ID" => "Indonesia",
            "IE" => "Ireland",
            "IL" => "Israel",
            "IM" => "Isle of Man",
            "IN" => "India",
            "IQ" => "Iraq",
            "IR" => "Iran, Islamic Republic of",
            "IS" => "Iceland",
            "IT" => "Italy",
            "JE" => "Jersey",
            "JM" => "Jamaica",
            "JO" => "Jordan",
            "JP" => "Japan",
            "KE" => "Kenya",
            "KG" => "Kyrgyzstan",
            "KH" => "Cambodia",
            "KI" => "Kiribati",
            "KM" => "Comoros",
            "KN" => "Saint Kitts and Nevis",
            "KP" => "Korea, Democratic People's Republic of",
            "KR" => "Korea, Republic of",
            "KW" => "Kuwait",
            "KY" => "Cayman Islands",
            "KZ" => "Kazakhstan",
            "LA" => "Lao People's Democratic Republic",
            "LB" => "Lebanon",
            "LC" => "Saint Lucia",
            "LI" => "Liechtenstein",
            "LK" => "Sri Lanka",
            "LR" => "Liberia",
            "LS" => "Lesotho",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "LV" => "Latvia",
            "LY" => "Libya",
            "MA" => "Morocco",
            "MC" => "Monaco",
            "MD" => "Moldova, Republic of",
            "ME" => "Montenegro",
            "MF" => "Saint Martin (French part)",
            "MG" => "Madagascar",
            "MH" => "Marshall Islands",
            "MK" => "Macedonia, the former Yugoslav Republic of",
            "ML" => "Mali",
            "MM" => "Myanmar",
            "MN" => "Mongolia",
            "MO" => "Macao",
            "MP" => "Northern Mariana Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MS" => "Montserrat",
            "MT" => "Malta",
            "MU" => "Mauritius",
            "MV" => "Maldives",
            "MW" => "Malawi",
            "MX" => "Mexico",
            "MY" => "Malaysia",
            "MZ" => "Mozambique",
            "NA" => "Namibia",
            "NC" => "New Caledonia",
            "NE" => "Niger",
            "NF" => "Norfolk Island",
            "NG" => "Nigeria",
            "NI" => "Nicaragua",
            "NL" => "Netherlands",
            "NO" => "Norway",
            "NP" => "Nepal",
            "NR" => "Nauru",
            "NT" => "Neutral Zone",
            "NU" => "Niue",
            "NZ" => "New Zealand",
            "OM" => "Oman",
            "PA" => "Panama",
            "PE" => "Peru",
            "PF" => "French Polynesia",
            "PG" => "Papua New Guinea",
            "PH" => "Philippines",
            "PK" => "Pakistan",
            "PL" => "Poland",
            "PM" => "Saint Pierre and Miquelon",
            "PN" => "Pitcairn",
            "PR" => "Puerto Rico",
            "PS" => "Palestine, State of",
            "PT" => "Portugal",
            "PW" => "Palau",
            "PY" => "Paraguay",
            "QA" => "Qatar",
            "RE" => "Réunion",
            "RO" => "Romania",
            "RS" => "Serbia",
            "RU" => "Russian Federation",
            "RW" => "Rwanda",
            "SA" => "Saudi Arabia",
            "SB" => "Solomon Islands",
            "SC" => "Seychelles",
            "SD" => "Sudan",
            "SE" => "Sweden",
            "SG" => "Singapore",
            "SH" => "Saint Helena, Ascension and Tristan da Cunha",
            "SI" => "Slovenia",
            "SJ" => "Svalbard and Jan Mayen",
            "SK" => "Slovakia",
            "SL" => "Sierra Leone",
            "SM" => "San Marino",
            "SN" => "Senegal",
            "SO" => "Somalia",
            "SR" => "Suriname",
            "ST" => "Sao Tome and Principe",
            "SU" => "USSR",
            "SV" => "El Salvador",
            "SY" => "Syrian Arab Republic",
            "SZ" => "Swaziland",
            "TC" => "Turks and Caicos Islands",
            "TD" => "Chad",
            "TG" => "Togo",
            "TH" => "Thailand",
            "TJ" => "Tajikistan",
            "TK" => "Tokelau",
            "TL" => "Timor-Leste",
            "TM" => "Turkmenistan",
            "TN" => "Tunisia",
            "TO" => "Tonga",
            "TP" => "East Timor",
            "TR" => "Turkey",
            "TT" => "Trinidad and Tobago",
            "TV" => "Tuvalu",
            "TW" => "Taiwan, Province of China",
            "TZ" => "Tanzania, United Republic of",
            "UA" => "Uganda",
            "UG" => "United States Minor Outlying Islands",
            "US" => "United States",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VA" => "Holy See (Vatican City State)",
            "VC" => "Saint Vincent and the Grenadines",
            "VE" => "Venezuela, Bolivarian Republic of",
            "VG" => "Virgin Islands, British",
            "VI" => "Virgin Islands, U.S.",
            "VN" => "Viet Nam",
            "VU" => "Vanuatu",
            "WF" => "Wallis and Futuna",
            "WS" => "Samoa",
            "YE" => "Yemen",
            "YT" => "Mayotte",
            "YU" => "Yugoslavia",
            "ZA" => "South Africa",
            "ZM" => "Zambia",
            "ZR" => "Zaire",
            "ZW" => "Zimbabwe"
        );

        if($code)
        {
            if(isset($countries[$code]))
            {

                    return $countries[$code];
            }
            else
            {
                return null;
            }
        }

        return $countries;
    }

    private function continents($code = null)
    {
        $continents = array(
            '002' => "Africa",
            '019' => "Americas",
            '142' => "Asia",
            '150' => "Europe",
            '009' => "Oceania",
        );

        if($code)
        {
            if(isset($continents[$code]))
            {

                    return $continents[$code];
            }
            else
            {
                return null;
            }
        }
        return $continents;
    }

    private function subContinents($code = null)
    {
        $subContinents = array(
            '053' => "Australia and New Zeland",
            '029' => "Caribbean",
            '013' => "Central America",
            '143' => "Central Asia",
            '014' => "Eastern Africa",
            '030' => "Eastern Asia",
            '151' => "Eastern Europe",
            '054' => "Melanesia",
            '057' => "Micronesia",
            '017' => "Middle Africa",
            '015' => "Northern Africa",
            '021' => "Northern America",
            '154' => "Northern Europe",
            '061' => "Polynesia",
            '005' => "South America",
            '035' => "South-Eastern Asia",
            '018' => "Southern Africa",
            '034' => "Southern Asia",
            '039' => "Southern Europe",
            '011' => "Western Africa",
            '145' => "Western Asia",
            '155' => "Western Europe",
        );

        if($code)
        {
            if(isset($subContinents[$code]))
            {
                return $subContinents[$code];
            }
            else
            {
                return null;
            }
        }
        return $subContinents;
    }
}