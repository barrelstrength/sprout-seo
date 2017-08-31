<?php

namespace Craft;

/**
 * Class SproutSeo_RedirectsService
 *
 * @package Craft
 */
class SproutSeo_RedirectsService extends BaseApplicationComponent
{
	/**
	 * Returns a Redirect by its ID.
	 *
	 * @param int $redirectId
	 *
	 * @return SproutSeo_RedirectModel|null
	 */
	public function getRedirectById($redirectId)
	{
		return craft()->elements->getElementById($redirectId, 'SproutSeo_Redirect');
	}

	/**
	 * Saves a redirect.
	 *
	 * @param SproutSeo_RedirectModel $redirect
	 *
	 * @throws Exception
	 * @return bool
	 */
	public function saveRedirect(SproutSeo_RedirectModel $redirect)
	{
		$isNewRedirect = !$redirect->id;

		// Event data
		if (!$isNewRedirect)
		{
			$redirectRecord = SproutSeo_RedirectRecord::model()->findById($redirect->id);

			if (!$redirectRecord)
			{
				throw new Exception(Craft::t('No redirect exists with the ID “{id}”', array('id' => $redirect->id)));
			}
		}
		else
		{
			$redirectRecord = new SproutSeo_RedirectRecord();
		}

		$redirectRecord->oldUrl = $redirect->oldUrl;
		$redirectRecord->newUrl = $redirect->newUrl;
		$redirectRecord->method = $redirect->method;
		$redirectRecord->regex  = $redirect->regex;
		$redirectRecord->count  = $redirect->count;

		$redirectRecord->validate();
		$redirect->addErrors($redirectRecord->getErrors());

		if (!$redirect->hasErrors())
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{

				if (craft()->elements->saveElement($redirect))
				{
					// Now that we have an element ID, save it on the other stuff
					if ($isNewRedirect)
					{
						$redirectRecord->id = $redirect->id;
					}

					$redirectRecord->save(false);

					if ($isNewRedirect)
					{
						//Set the root structure
						craft()->structures->appendToRoot(sproutSeo()->redirects->getStructureId(), $redirect);
					}

					if ($transaction !== null)
					{
						$transaction->commit();
					}

					return true;
				}
			}
			catch (\Exception $e)
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				throw $e;
			}
		}

		return false;
	}

	/**
	 * Update the current method in the record
	 *
	 * @param array $ids
	 * @param int   $method value to update
	 *
	 * @return bool
	 */
	public function updateMethods($ids, $newMethod)
	{
		$response = craft()->db->createCommand()->update(
			'sproutseo_redirects',
			array(
				'method' => $newMethod,
				'count' => 0
			),
			array('in', 'id', $ids)
		);

		return $response;
	}

	/**
	 * Find a regex url using the preg_match php function and replace
	 * capture groups if any using the preg_replace php function also check normal urls
	 *
	 * @param string $url
	 *
	 * @return SproutSeo_RedirectRecord $redirect
	 */
	public function findUrl($url)
	{
		$redirectRecords = SproutSeo_RedirectRecord::model()->structured()->findAll();

		$redirects = SproutSeo_RedirectModel::populateModels($redirectRecords);
		$url       = urldecode($url);

		if ($redirects)
		{
			foreach ($redirects as $redirect)
			{
				if ($redirect->regex)
				{
					// Use backticks as delimiters as they are invalid characters for URLs
					$oldUrlPattern = "`" . $redirect->oldUrl . "`";

					if (preg_match($oldUrlPattern, $url))
					{
						// Replace capture groups if any
						$redirect->newUrl = preg_replace($oldUrlPattern, $redirect->newUrl, $url);

						return $redirect;
					}
				}
				else
				{
					if ($redirect->oldUrl == $url)
					{
						return $redirect;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Save a 404 redirect and check total404Redirects setting
	 *
	 * @param $url string
	 *
	 * @return SproutSeo_RedirectModel|null
	 */
	public function save404Redirect($url)
	{
		$redirect    = new SproutSeo_RedirectModel();
		$plugin      = craft()->plugins->getPlugin('sproutseo');
		$seoSettings = $plugin->getSettings();

		$redirect->oldUrl  = $url;
		$redirect->newUrl  = null;
		$redirect->method  = SproutSeo_RedirectMethods::PageNotFound;
		$redirect->regex   = 0;
		$redirect->enabled = 0;
		$redirect->count   = 0;

		// delete new one
		if (isset($seoSettings['total404Redirects']) && $seoSettings['total404Redirects'])
		{
			$count = SproutSeo_RedirectRecord::model()->count('method=:method', array(':method' => SproutSeo_RedirectMethods::PageNotFound));

			if ($count > $seoSettings['total404Redirects'])
			{
				$model = SproutSeo_RedirectRecord::model()->find('method=404',
					array(
					'order'=> 'dateUpdated DESC'
					)
				);

				if ($model)
				{
					craft()->elements->deleteElementById($model->id);
				}
			}
		}

		if (!sproutSeo()->redirects->saveRedirect($redirect))
		{
			$redirect = null;
		}

		return $redirect;
	}

	/**
	 * Logs a redirect when a match is found
	 *
	 * @todo - escape this log data when we output it
	 *         https://stackoverflow.com/questions/13199095/escaping-variables
	 *
	 * @param $redirectId int
	 *
	 * @return bool
	 */
	public function logRedirect($redirectId)
	{
		$log = array();

		try
		{
			$log['redirectId']  = $redirectId;
			$log['referralURL'] = craft()->request->getUrlReferrer();
			$log['ipAddress']   = $_SERVER["REMOTE_ADDR"];
			$log['dateCreated'] = date('Y-m-d h:m:s');

			SproutSeoPlugin::log('404 Redirect Log: '.json_encode($log), LogLevel::Info, true);

			$redirect        = $this->getRedirectById($redirectId);
			$redirect->count += 1;

			$this->saveRedirect($redirect);
		}
		catch (\Exception $e)
		{
			SproutSeoPlugin::log('Unable to log redirect.', LogLevel::Info, true);
		}

		return true;
	}

	/**
	 * Get methods
	 *
	 * @return array
	 */
	public function getMethods()
	{
		$methods    = array_flip(SproutSeo_RedirectMethods::getConstants());
		$newMethods = array();
		foreach ($methods as $key => $value)
		{
			$newMethods[$key] = $key . ' - ' . $value;
		}

		return $newMethods;
	}

	/**
	 * Add Slash
	 *
	 * @return array
	 */
	public function addSlash($url)
	{
		$slash    = '/';
		$external = false;
		//Check if the url is external
		if (filter_var($url, FILTER_VALIDATE_URL))
		{
			$external = true;
		}

		if ($url[0] != $slash && !$external)
		{
			$url = $slash . $url;
		}

		return $url;
	}

	/**
	 * Get Method Update Response from elementaction
	 *
	 * @param bool
	 *
	 * @return string
	 */
	public function getMethodUpdateResponse($status)
	{
		$response = null;
		if ($status)
		{
			$response = Craft::t('Methods updated.');
		}
		else
		{
			$response = Craft::t('Failed to update.');
		}

		return $response;
	}

	/**
	 * This service allows find a url that needs redirect
	 *
	 * @param string current request url
	 *
	 * @return SproutSeo_RedirectRecord
	 */
	public function getRedirect($url)
	{
		// check out normal and regex urls
		$redirect = sproutSeo()->redirects->findUrl($url);

		return $redirect;
	}

	/**
	 * This service allows find the structure id from the sprout seo settings
	 *
	 * @return int
	 */
	public function getStructureId()
	{
		$plugin   = craft()->plugins->getPlugin('sproutseo');
		$settings = $plugin->getSettings();

		return $settings->structureId;
	}

	/**
	 * Install default settings
	 *
	 * @param null $pluginName
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function installDefaultSettings($pluginName = null)
	{
		$structure = $this->createStructureRecord();

		// Add our default plugin settings
		$settings = '{"pluginNameOverride":"' . $pluginName . '", "structureId":"' . $structure->id . '"}';

		craft()->db->createCommand()->update('plugins', array(
			'settings' => $settings
		),
			'class=:class', array(':class' => 'SproutSeo')
		);

		return $structure->id;
	}

	/**
	 * Adds structure for redirects
	 */
	public function createStructureRecord()
	{
		// Add a new Structure for our Redirects
		$maxLevels            = 1;
		$structure            = new StructureModel();
		$structure->maxLevels = $maxLevels;

		craft()->structures->saveStructure($structure);

		return $structure;
	}

	/**
	 * Returns the value for the total404Redirects setting. Default is 1000.
	 *
	 * @param int $total
	 *
	 * @return int
	 */
	public function getTotal404Redirects($total = 1000)
	{
		$plugin      = craft()->plugins->getPlugin('sproutseo');
		$seoSettings = $plugin->getSettings();

		if (isset($seoSettings['total404Redirects']) && $seoSettings['total404Redirects'])
		{
			$total = $seoSettings['total404Redirects'];
		}

		return $total;
	}
}
