<?php
namespace Craft;

/**
 *
 */
class BSDPluginHelper
{

	public static function getPluginName($plugin, $pluginName)
	{
		// The plugin name override
		$plugin = craft()->db->createCommand()
			->select('settings')
			->from('plugins')
			->where('class=:class', array(':class'=> $plugin->getClassHandle(__CLASS__)))
			->queryScalar();

		$plugin = json_decode( $plugin, true );
		$pluginNameOverride = $plugin['pluginNameOverride'];

		return ($pluginNameOverride) ? $pluginNameOverride : $pluginName;
	}

}
