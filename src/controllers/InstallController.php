<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use craft\web\Controller;

class InstallController extends Controller
{
	// Public Methods
	// =========================================================================

	/**
	 * Install Index
	 *
	 * @return null
	 */
	public function actionIndex()
	{
		$missingDependencies = Analytics::$plugin->analytics->getMissingDependencies();

		if (count($missingDependencies) > 0)
		{
			$this->renderTemplate('analytics/_special/install/dependencies', [
				'pluginDependencies' => $missingDependencies
			]);
		}
		else
		{
			$this->redirect('analytics/settings');
		}
	}
}