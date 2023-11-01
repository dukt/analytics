<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\models\SiteSource;
use dukt\analytics\Plugin as Analytics;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use yii\web\Response;

class SitesController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Sites index.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex(): Response
    {
        $isOauthProviderConfigured = Analytics::$plugin->getAnalytics()->isOauthProviderConfigured();

        $variables = [
            'isConnected' => false
        ];

        try {
            $token = Analytics::$plugin->getOauth()->getToken();

            if ($isOauthProviderConfigured && $token) {
                $variables['isConnected'] = true;
                $variables['sites'] = Craft::$app->getSites()->getAllSites();
                $variables['siteSources'] = Analytics::$plugin->getSources()->getSiteSources();
            }
        } catch (IdentityProviderException $identityProviderException) {
            $variables['error'] = $identityProviderException->getMessage();

            $data = $identityProviderException->getResponseBody();

            if (isset($data['error_description'])) {
                $variables['error'] = $data['error_description'];
            }
        }

        return $this->renderTemplate('analytics/sites/_index', $variables);
    }

    /**
     * Edit a site.
     *
     * @param $siteId
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEdit($siteId): Response
    {
        $site = Craft::$app->getSites()->getSiteById($siteId);
        $siteSource = Analytics::$plugin->getSources()->getSiteSourceBySiteId($siteId);
        $sources = Analytics::$plugin->getSources()->getSources();

        return $this->renderTemplate('analytics/sites/_edit', [
            'site' => $site,
            'siteSource' => $siteSource,
            'sources' => $sources,
        ]);
    }

    /**
     * Saves a site.
     *
     * @return null|Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $siteSource = new SiteSource();
        $siteSource->siteId = $request->getBodyParam('siteId');
        $siteSource->sourceId = $request->getBodyParam('sourceId');

        // Save it
        if (!Analytics::$plugin->getSources()->saveSiteSource($siteSource)) {
            Craft::$app->getSession()->setError(Craft::t('analytics', 'Couldn’t save the site source.'));

            // Send the view back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'siteSource' => $siteSource
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('analytics', 'Site source saved.'));

        return $this->redirectToPostedUrl($siteSource);
    }
}