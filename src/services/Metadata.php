<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use yii\base\Component;

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
        // TODO: fix dimmet mapping

        return $id;
    }
}
