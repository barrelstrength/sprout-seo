<?php
namespace Craft;

class SproutSeo_SettingsController extends BaseController
{
	/**
	 * Save Settings to the Database
	 * 
	 * @return mixed Return to Page
	 */
	public function actionSettingsIndex()
	{	
		$settingsModel = new SproutSeo_SettingsModel;

		$settings = craft()->db->createCommand()
                         ->select('settings')
                         ->from('plugins')
                         ->where('class=:class', array(':class'=> 'SproutSeo'))
                         ->queryScalar();
    
    $settings = JsonHelper::decode($settings);
    $settingsModel->setAttributes($settings);
   
    $variables['settings'] = $settingsModel;
    
		// Load our template and with all of the variables we created
		$this->renderTemplate('sproutseo/settings', $variables);

	}

	/**
	 * Save Sprout SEO Settings
	 * 
	 * @return mixed redirect and variables
	 */
	public function actionSaveSettings()
	{
		$this->requirePostRequest();
		$settings = craft()->request->getPost('settings');

		if (craft()->sproutSeo_settings->saveSettings($settings))
		{
			craft()->userSession->setNotice(Craft::t('Settings saved.'));

			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save settings.'));

			// Send the settings back to the template
			craft()->urlManager->setRouteVariables(array(
				'settings' => $settings
			));
		}
	}
}
