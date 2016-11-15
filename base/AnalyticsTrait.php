<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

trait AnalyticsTrait
{
	// Public Methods
	// =========================================================================

	/**
	 * Checks plugin requirements (dependencies, configured OAuth provider, forceConnect, and token)
	 *
	 * @return bool
	 */
	public function checkPluginRequirements()
	{
		if(!$this->areDependenciesMissing())
		{
			if($this->isOauthProviderConfigured())
			{
                $plugin = craft()->plugins->getPlugin('analytics');
                $settings = $plugin->getSettings();

                if($settings['forceConnect'] === true)
                {
                    return false;
                }
                else
                {
                    if($this->isTokenSet())
                    {
                        if($this->isGoogleAnalyticsAccountConfigured())
                        {
                            return true;
                        }
                        else
                        {
                            return false;
                        }
                    }
                    else
                    {
                        return false;
                    }
                }
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Checks if the OAuth provider is configured
	 *
	 * @return bool
	 */
	public function isOauthProviderConfigured()
	{
		if(isset(craft()->oauth))
		{
			$provider = craft()->oauth->getProvider('google');

			if($provider && $provider->isConfigured())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Checks dependencies and redirects to install if one or more are missing
	 *
	 * @return bool
	 */
	public function requireDependencies()
	{
		if ($this->areDependenciesMissing())
		{
			$url = UrlHelper::getUrl('analytics/install');
			craft()->request->redirect($url);
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Get Missing Dependencies
	 *
	 * @return array
	 */
	public function getMissingDependencies()
	{
		return $this->getDependencies(true);
	}

	// Private Methods
	// =========================================================================

	/**
	 * Is Google Analytics Account Configured (OAuth Token and Google Analytics Profile ID set)
	 *
	 * @return bool
	 */
	private function isGoogleAnalyticsAccountConfigured()
	{
		if(!$this->areDependenciesMissing())
		{
			if(!$this->isTokenSet())
			{
				return false;
			}

			// check if profile id is set up

			$plugin = craft()->plugins->getPlugin('analytics');

			$settings = $plugin->getSettings();

			$profileId = $settings['profileId'];


			if(!$profileId)
			{
				AnalyticsPlugin::log('Analytics profileId not found', LogLevel::Info, true);
				return false;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Checks if token is set
	 *
	 * @return bool
	 */
	private function isTokenSet()
	{
		if($this->isOauthProviderConfigured())
		{
			$token = craft()->analytics_oauth->getToken();

			if ($token)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns `true` if dependencies are missing, and `false` otherwise
	 *
	 * @return bool
	 */
	private function areDependenciesMissing()
	{
		$missingDependencies = $this->getMissingDependencies();

		if(count($missingDependencies) > 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * Get dependencies
	 *
	 * @return array
	 */
	private function getDependencies($missingOnly = false)
	{
		$dependencies = array();

		$plugin = craft()->plugins->getPlugin('analytics');
		$plugins = $plugin->getRequiredPlugins();

		foreach($plugins as $key => $plugin)
		{
			$dependency = $this->getPluginDependency($plugin);

			if($missingOnly)
			{
				if($dependency['isMissing'])
				{
					$dependencies[] = $dependency;
				}
			}
			else
			{
				$dependencies[] = $dependency;
			}
		}

		return $dependencies;
	}

	/**
	 * Get dependency
	 *
	 * @return array
	 */
	private function getPluginDependency($dependency)
	{
		$isMissing = true;

		$plugin = craft()->plugins->getPlugin($dependency['handle'], false);

		if($plugin)
		{
			$currentVersion = $plugin->version;


			// requires update ?

			if(version_compare($currentVersion, $dependency['version']) >= 0)
			{
				if($plugin->isInstalled && $plugin->isEnabled)
				{
					$isMissing = false;
				}
			}
		}

		$dependency['isMissing'] = $isMissing;
		$dependency['plugin'] = $plugin;
		$dependency['pluginLink'] = 'https://dukt.net/craft/'.$dependency['handle'];

		return $dependency;
	}
}