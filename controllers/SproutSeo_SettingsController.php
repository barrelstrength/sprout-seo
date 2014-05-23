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
		// Create any variables you want available in your template
		// $variables['items'] = craft()->pluginName->getAllItems();
		$settings = craft()->db->createCommand()
                         ->select('settings')
                         ->from('plugins')
                         ->where('class=:class', array(':class'=> 'SproutSeo'))
                         ->queryScalar();

    $variables['settings'] = JsonHelper::decode($settings);
    
		// Load a particular template and with all of the variables you've created
		$this->renderTemplate('sproutseo/settings', $variables);

	}

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
