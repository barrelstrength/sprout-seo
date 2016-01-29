<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160127_000000_sproutSeo_addDefaultSettings extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$plugin = craft()->plugins->getPlugin('sproutseo');
		$settings = $plugin->getSettings();

		if(is_null($settings->structureId))
		{
			$structureId = sproutSeo()->redirects->installDefaultSettings($settings->pluginNameOverride);
			SproutSeoPlugin::log('Successfully added structure', LogLevel::Info, true);

			// Find all currents redirects
			$redirects = craft()->db->createCommand()
				->select('*')
				->from('sproutseo_redirects')
				->queryAll();

			// Set structure to currents redirects
			foreach ($redirects as $key => $redirect)
			{
				$redirectModel = new SproutSeo_RedirectModel;
				$redirectModel->id     = $redirect["id"];
				$redirectModel->oldUrl = $redirect["oldUrl"];
				$redirectModel->newUrl = $redirect["newUrl"];
				$redirectModel->method = $redirect["method"];
				$redirectModel->regex  = $redirect["regex"];

				craft()->structures->appendToRoot($structureId, $redirectModel);
			}
		}

		return true;
	}
}