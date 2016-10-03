<?php
namespace Craft;

/**
 * Class SproutSeo_UrlEnabledSectionModel
 */
class SproutSeo_UrlEnabledSectionModel extends BaseModel
{
	/**
	 * URL-Enabled Section ID
	 *
	 * @var
	 */
	public $id;

	/**
	 * @var SproutSeoBaseUrlEnabledSectionType $type
	 */
	public $type;

	/**
	 * @var SproutSeo_MetadataModel $sectionMetadata
	 */
	public $sectionMetadata;

	/**
	 * The current locales URL Format for this URL-Enabled Section
	 *
	 * @var string
	 */
	public $urlFormat;

	/**
	 * The Element Model for the Matched Element Variable of the current page load
	 *
	 * @var BaseElementModel
	 */
	public $element;

	/**
	 * Get the URL format from the element via the Element Group integration
	 *
	 * @param $urlEnabledSection
	 * @param $urlEnabledSectionId
	 *
	 * @return \CDbDataReader|mixed|string
	 */
	public function getUrlFormat()
	{
		$locale = craft()->i18n->getLocaleById(craft()->language);

		$urlEnabledSectionUrlFormatTableName  = $this->type->getUrlEnabledSectionTableName();
		$urlEnabledSectionUrlFormatColumnName = $this->type->getUrlEnabledSectionUrlFormatColumnName();
		$urlEnabledSectionIdColumnName        = $this->type->getUrlEnabledSectionIdColumnName();

		$query = craft()->db->createCommand()
			->select($urlEnabledSectionUrlFormatColumnName)
			->from($urlEnabledSectionUrlFormatTableName)
			->where($urlEnabledSectionIdColumnName . '=:id', array(':id' => $this->id));

		if ($this->type->isLocalized())
		{
			$query->andWhere('locale=:locale', array(':locale' => $locale));
		}

		if ($query->queryScalar())
		{
			$this->urlFormat = $query->queryScalar();
		}

		return $this->urlFormat;
	}
}
