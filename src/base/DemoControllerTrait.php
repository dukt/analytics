<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\base;

use dukt\analytics\Plugin as Analytics;
use yii\web\Response;

trait DemoControllerTrait
{
    // Private Methods
    // =========================================================================

    /**
     * @return Response
     * @throws \Exception
     */
    private function getEcommerceDemoResponse(): Response
    {
        $date = new \DateTime();
        $date = $date->modify('-12 months');

        $rows = [];

        for ($i = 1; $i <= 12; ++$i) {
            $rows[] = [
                $date->format('Ym'),
                random_int(50000, 150000)
            ];

            $date->modify('+1 month');
        }

        $reportData = [
            'chart' => [
                'cols' => [
                    [
                        'id' => 'ga:yearMonth',
                        'label' => 'Month of Year',
                        'type' => 'date',
                    ],
                    [
                        'id' => 'ga:transactionRevenue',
                        'label' => 'Revenue',
                        'type' => 'currency',
                    ],
                ],
                'rows' => $rows,
                'totals' => [
                    [
                        '11385.0',
                        '97.3076923076923',
                    ]
                ],
            ],
            'period' => 'year',
            'periodLabel' => 'This year',
            'view' => 'Craft Shop',
        ];

        $totalRevenue = 0;

        foreach($rows as $row) {
            $totalRevenue += $row[1];
        }

        $totalTransactions = random_int(3400, 3800);
        $totalRevenuePerTransaction = $totalRevenue / $totalTransactions;
        $totalTransactionsPerSession = 8.291991495393338;

        return $this->asJson([
            'period' =>  '365daysAgo - today',
            'reportData' => $reportData,
            'totalRevenue' => $totalRevenue,
            'totalRevenuePerTransaction' => $totalRevenuePerTransaction,
            'totalTransactions' => $totalTransactions,
            'totalTransactionsPerSession' => $totalTransactionsPerSession,
        ]);
    }

    /**
     * Get realtime demo test response.
     *
     * @return Response
     * @throws \Exception
     */
    private function getRealtimeDemoTestResponse(): Response
    {
        $pageviews = [
            'rows' => []
        ];

        for ($i = 0; $i <= 30; ++$i) {
            $pageviews['rows'][] = [$i, random_int(0, 20)];
        }

        $activePages = [
            'rows' => [
                ['/some-url/', random_int(1, 20)],
                ['/some-super-long-url/with-kebab-case/', random_int(1, 20)],
                ['/somesuperlongurlwithoutkebabcasebutstillsuperlong/', random_int(10000000, 20000000)],
                ['/someothersuperlongurl/withoutkebabcasebutstillsuperlong/', random_int(1, 20)],
                ['/one-last-url/', random_int(1, 20)],
            ]
        ];

        $activeUsers = 0;

        foreach ($activePages['rows'] as $row) {
            $activeUsers += $row[1];
        }

        return $this->asJson([
            'activeUsers' => $activeUsers,
            'pageviews' => $pageviews,
            'activePages' => $activePages,
        ]);
    }

    /**
     * Get realtime demo response.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    private function getRealtimeDemoResponse(): Response
    {
        if (Analytics::$plugin->getAnalytics()->demoMode === 'test') {
            return $this->getRealtimeDemoTestResponse();
        }

        $pageviews = [
            'rows' => []
        ];

        for ($i = 0; $i <= 30; ++$i) {
            $pageviews['rows'][] = [$i, random_int(0, 20)];
        }

        $activePages = [
            'rows' => [
                ['/a-new-toga/', random_int(1, 20)],
                ['/parka-with-stripes-on-back/', random_int(1, 20)],
                ['/romper-for-a-red-eye/', random_int(1, 20)],
                ['/the-fleece-awakens/', random_int(1, 20)],
                ['/the-last-knee-high/', random_int(1, 20)],
            ]
        ];

        $activeUsers = 0;

        foreach ($activePages['rows'] as $row) {
            $activeUsers += $row[1];
        }

        return $this->asJson([
            'activeUsers' => $activeUsers,
            'pageviews' => $pageviews,
            'activePages' => $activePages,
        ]);
    }
}
