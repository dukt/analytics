<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use Craft;
use dukt\analytics\Plugin;
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

    private $metadatas = [];

    // Public Methods
    // =========================================================================

    /**
     * Get a dimension or a metric label from its apiName
     *
     *
     * @return mixed
     */
    public function getDimMet(int $sourceId, string $apiName)
    {
        $metadata = $this->getMetadataBySourceId($sourceId);

        foreach ($metadata->getDimensions() as $dimension) {
            if ($dimension->apiName === $apiName) {
                return $dimension->uiName;
            }
        }
        foreach ($metadata->getMetrics() as $metric) {
            if ($metric->apiName === $apiName) {
                return $metric->uiName;
            }
        }

        return $apiName;
    }

    /**
     * Get metadata for a given source.
     *
     * @param $sourceId
     * @return \Google\Service\AnalyticsData\Metadata|mixed|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getMetadataBySourceId($sourceId)
    {
        if (isset($this->metadatas[$sourceId])) {
            return $this->metadatas[$sourceId];
        }

        $cacheId = ['analytics:metadata', $sourceId];

        $response = Analytics::$plugin->getCache()->get($cacheId);

        if (!$response) {
            $source = Analytics::$plugin->getSources()->getSourceById($sourceId);
            $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();

            $response = $analyticsData->properties->getMetadata($source->gaPropertyId.'/metadata');

            if ($response) {
                Analytics::$plugin->getCache()->set($cacheId, $response);
            }
        }

        return $this->metadatas[$sourceId] = $response;
    }
}
