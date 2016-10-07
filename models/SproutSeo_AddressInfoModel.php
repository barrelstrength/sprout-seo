<?php
namespace Craft;

/**
 * Class sproutSeo_AddressInfoModel
 *
 */
class SproutSeo_AddressInfoModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'                 => array(AttributeType::Number),
			'countryCode'        => array(AttributeType::String),
			'administrativeArea' => array(AttributeType::String),
			'locality'           => array(AttributeType::String),
			'dependentLocality'  => array(AttributeType::String),
			'postalCode'         => array(AttributeType::String),
			'sortingCode'        => array(AttributeType::String),
			'address1'           => array(AttributeType::String, 'required' => true),
			'address2'           => array(AttributeType::String)
		);
	}

	public function rules()
	{
		$rules = parent::rules();

		$rules[] = array('postalCode', 'validatePostalCode');

		return $rules;
	}

	public function validatePostalCode($attribute)
	{
		$postalCode = $this->{$attribute};
		
		if ($postalCode == null) return;

		$countryCode = $this->countryCode;

		if (!sproutSeo()->addressForm->validatePostalCode($countryCode, $postalCode))
    {
	    $postalName = sproutSeo()->addressForm->getPostalName($countryCode);

	    $params = array(
		    'postalName' => $postalName,
	    );

	    $this->addError($attribute, Craft::t("{postalName} is not a valid.", $params));
    }
	}

	/**
	 * Return the Address HTML for the appropriate region
	 *
	 * @return string
	 */
	public function getAddressHtml()
	{
		if (!$this->id)
		{
			return "";
		}

		$address = sproutSeo()->addressForm->getAddressWithFormat($this);

		return $address;
	}
}
