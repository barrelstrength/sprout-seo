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

		$redirectRecord->oldUrl	= $redirect->oldUrl;
		$redirectRecord->newUrl = $redirect->newUrl;
		$redirectRecord->method = $redirect->method;
		$redirectRecord->regex = $redirect->regex;

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
	 * @param int $method value to update
	 * @return bool
	 */
	public function updateMethods($ids, $newMethod)
	{
		$resp = craft()->db->createCommand()->update(
				'sproutseo_redirects',
				array('method' => $newMethod),
				array('in', 'id', $ids)
			);

		return $resp;
	}

	/**
	 * Find a url
	 *
	 * @param string $url
	 * @return SproutSeo_RedirectRecord $redirect
	 */
	public function findUrl($url)
	{
		$criteria = new \CDbCriteria();
		$criteria->condition = 'oldUrl =:url';
		$criteria->params = array(':url'=>$url);
		$criteria->limit = 1;

		return SproutSeo_RedirectRecord::model()->find($criteria);
	}

	/**
	 * Find a regex url
	 *
	 * @param string $url
	 * @return SproutSeo_RedirectRecord $redirect
	 */
	public function findRegexUrl($url)
	{
		$criteria = new \CDbCriteria();
		$criteria->addCondition('regex = true');
		$criteria->addCondition(':url regexp oldUrl');
		$criteria->params = array(':url'=>$url);
		$criteria->limit = 1;

		return SproutSeo_RedirectRecord::model()->find($criteria);
	}

	/**
	 * Get methods
	 *
	 * @return array
	 */
	public function getMethods()
	{
		$methods = array_flip(SproutSeo_RedirectMethods::getConstants());
		$newMethods = array();
		foreach ($methods as $key => $value) {
			$newMethods[$key] = $key.' - '.$value;
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
		$slash = '/';
		if($url[0] != $slash)
		{
			$url = $slash.$url;
		}

		return $url;
	}

	/**
	 * Get Method Update Response from elementaction
	 *
	 * @param bool
	 * @return string
	 */
	public function getMethodUpdateResponse($status)
	{
		$response = null;
		if($status)
		{
			$response = Craft::t('Methods updated.');
		}
		else
		{
			$response = Craft::t('Failed to update.');
		}

		return $response;
	}
}
