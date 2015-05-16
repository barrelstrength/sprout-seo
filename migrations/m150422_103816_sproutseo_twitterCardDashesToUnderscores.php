<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150422_103816_sproutseo_twitterCardDashesToUnderscores extends BaseMigration
{
	public function safeUp()
	{
        $updateDefaults = $this->_updateTwitterCard('defaults');
        $updateOverrides = $this->_updateTwitterCard('overrides');

        SproutSeoPlugin::log('Successfully updated `twitterCard` fields from `summary-large-image` to `summary_large_image`.', LogLevel::Info, true);

        return true;
	}

    private function _updateTwitterCard($table = null)
    {
        if(is_null($table))
        {
            throw new Exception(Craft::t('Cannot update a null table.'));
        }

        $result = craft()->db->createCommand()
            ->update(
                'sproutseo_' . $table,
                array(
                    'twitterCard' => 'summary_large_image'
                ),
                'twitterCard = :cardName',
                array(
                    ':cardName' => 'summary-large-image'
                )
            );

        return $result;
    }
}
