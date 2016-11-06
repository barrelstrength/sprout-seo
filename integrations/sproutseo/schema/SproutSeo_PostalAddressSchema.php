<?php
namespace Craft;

class SproutSeo_PostalAddressSchema extends SproutSeoBaseSchema
{
	public $addressCountry;
	public $addressLocality;
	public $addressRegion;
	public $postalCode;
	public $streetAddress;

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Postal Address';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'PostalAddress';
	}

	/**
	 * @return bool
	 */
	public function isUnlistedSchemaType()
	{
		return true;
	}

	/**
	 * @return array|null
	 */
	public function addProperties()
	{
		$this->addText('addressCountry', $this->addressCountry);
		$this->addText('addressLocality', $this->addressLocality);
		$this->addText('addressRegion', $this->addressRegion);
		$this->addText('postalCode', $this->postalCode);
		$this->addText('streetAddress', $this->streetAddress);
	}
}