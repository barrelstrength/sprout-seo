<?php
namespace Craft;

/**
 * Class SproutSeo_MetaOverridesService
 *
 * @package Craft
 */
class SproutSeo_MetaOverridesService extends BaseApplicationComponent
{

	/**
	 * @param $attributes
	 */
	public function createOverride($attributes)
	{
		craft()->db->createCommand()
			->insert('sproutseo_overrides', $attributes);
	}

	/**
	 * @param $id
	 * @param $attributes
	 */
	public function updateOverride($id, $attributes)
	{
		craft()->db->createCommand()
			->update('sproutseo_overrides',
				$attributes,
				'id = :id', array(':id' => $id)
			);
	}

	/**
	 * @param null $id
	 * @return int
	 */
	public function deleteOverrideById($id = null)
	{
		$record = new SproutSeo_OverridesRecord;

		return $record->deleteByPk($id);
	}

	public function getOverrideByEntryId($entryId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_overrides')
			->where('entryId = :entryId', array(':entryId' => $entryId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		$model = SproutSeo_OverridesModel::populateModel($query);

		return $model;
	}

	/**
	 * @param $entryId
	 * @param $locale
	 * @return BaseModel|SproutSeo_BasicMetaFieldModel
	 */
	public function getBasicMetaFieldByEntryId($entryId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('id, title, description, keywords')
			->from('sproutseo_overrides')
			->where('entryId = :entryId', array(':entryId' => $entryId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		if (isset($query))
		{
			return SproutSeo_BasicMetaFieldModel::populateModel($query);
		}

		return new SproutSeo_BasicMetaFieldModel();
	}

	/**
	 * @param $entryId
	 * @param $locale
	 * @return BaseModel|SproutSeo_TwitterCardFieldModel
	 */
	public function getTwitterCardFieldByEntryId($entryId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('id, twitterCard, twitterSite, twitterTitle, twitterCreator,
			twitterDescription, twitterImage, twitterPlayerStream,
			twitterPlayerStreamContentType, twitterPlayerWidth,
			twitterPlayerHeight')
			->from('sproutseo_overrides')
			->where('entryId = :entryId', array(':entryId' => $entryId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		if (isset($query))
		{
			return SproutSeo_TwitterCardFieldModel::populateModel($query);
		}

		return new SproutSeo_TwitterCardFieldModel();
	}

	/**
	 * @param $entryId
	 * @param $locale
	 * @return BaseModel|SproutSeo_OpenGraphFieldModel
	 */
	public function getOpenGraphFieldByEntryId($entryId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('id, ogTitle, ogType, ogUrl, ogImage, ogAuthor, ogPublisher, ogSiteName, ogDescription, ogAudio, ogVideo, ogLocale')
			->from('sproutseo_overrides')
			->where('entryId = :entryId', array(':entryId' => $entryId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		if (isset($query))
		{
			return SproutSeo_OpenGraphFieldModel::populateModel($query);
		}

		return new SproutSeo_OpenGraphFieldModel();
	}

	/**
	 * @param $entryId
	 * @param $locale
	 * @return BaseModel|SproutSeo_GeographicMetaFieldModel
	 */
	public function getGeographicMetaFieldByEntryId($entryId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('region, placename, longitude, latitude')
			->from('sproutseo_overrides')
			->where('entryId = :entryId', array(':entryId' => $entryId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		if (isset($query))
		{
			return SproutSeo_GeographicMetaFieldModel::populateModel($query);
		}

		return new SproutSeo_GeographicMetaFieldModel();
	}

	/**
	 * @param $entryId
	 * @param $locale
	 * @return BaseModel|SproutSeo_RobotsMetaFieldModel
	 */
	public function getRobotsMetaFieldByEntryId($entryId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('canonical, robots')
			->from('sproutseo_overrides')
			->where('entryId = :entryId', array(':entryId' => $entryId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		if (isset($query))
		{
			return SproutSeo_RobotsMetaFieldModel::populateModel($query);
		}

		return new SproutSeo_RobotsMetaFieldModel();
	}
}