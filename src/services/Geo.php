<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use Craft;
use yii\base\Component;

class Geo extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Get continents.
     *
     * @return array
     */
    public function getContinents(): array
    {
        return $this->_getData('continents');
    }

    /**
     * Get subcontinents.
     *
     * @return array
     */
    public function getSubContinents(): array
    {
        return $this->_getData('subContinents');
    }

    /**
     * Get Continent Code
     *
     * @param string $label
     *
     * @return mixed
     */
    public function getContinentCode($label)
    {
        foreach ($this->_getData('continents') as $continent) {
            if ($continent['label'] === $label) {
                return $continent['code'];
            }
        }

        return null;
    }

    /**
     * Get Sub-Continent Code
     *
     * @param string $label
     *
     * @return mixed
     */
    public function getSubContinentCode($label)
    {
        foreach ($this->_getData('subContinents') as $subContinent) {
            if ($subContinent['label'] === $label) {
                return $subContinent['code'];
            }
        }

        return null;
    }

    // Private Methods
    // =========================================================================

    /**
     * Get Data
     *
     * @param $name
     *
     * @return array
     * @internal param string $label
     *
     */
    private function _getData($name): array
    {
        $jsonData = file_get_contents(Craft::getAlias('@dukt/analytics/etc/data/'.$name.'.json'));

        return json_decode($jsonData, true);
    }
}
