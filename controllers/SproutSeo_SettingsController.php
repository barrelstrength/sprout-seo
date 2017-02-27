<?php
namespace Craft;

class SproutSeo_SettingsController extends BaseController
{
	/**
	 * Loads the Settings Index page
	 *
	 * @throws HttpException
	 */
	public function actionSettingsIndex()
	{
		$settingsModel = new SproutSeo_SettingsModel;

		$settings = craft()->db->createCommand()
			->select('settings')
			->from('plugins')
			->where('class=:class', array(':class' => 'SproutSeo'))
			->queryScalar();

		$settings = JsonHelper::decode($settings);
		$settingsModel->setAttributes($settings);

		$settingsTemplate = craft()->request->getSegment(3);

		$this->renderTemplate('sproutseo/settings/' . $settingsTemplate, array(
			'settings' => $settingsModel
		));
	}

	/**
	 * Saves Plugin Settings
	 *
	 * @throws HttpException
	 */
	public function actionSaveSettings()
	{
		$this->requirePostRequest();
		$settings = craft()->request->getPost('settings');

		if (sproutSeo()->settings->saveSettings($settings))
		{
			craft()->userSession->setNotice(Craft::t('Settings saved.'));

			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save settings.'));

			craft()->urlManager->setRouteVariables(array(
				'settings' => $settings
			));
		}
	}
}
