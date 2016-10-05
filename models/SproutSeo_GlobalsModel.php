<?php
namespace Craft;

class SproutSeo_GlobalsModel extends BaseModel
{
	/**
	 * @var null
	 */
	public $globalKey = null;

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'id'        => AttributeType::Number,
			'locale'    => AttributeType::Locale,
			'meta'      => AttributeType::Mixed,
			'identity'  => AttributeType::Mixed,
			'ownership' => AttributeType::Mixed,
			'contacts'  => AttributeType::Mixed,
			'social'    => AttributeType::Mixed,
			'robots'    => AttributeType::Mixed,
			'settings'  => AttributeType::Mixed,
		);
	}

	/**
	 * Factory to return schema of any type
	 *
	 * @param        $target
	 * @param string $format
	 *
	 * @return string
	 */
	public function getGlobalByKey($target, $format = 'array')
	{
		if ($target)
		{
			$this->globalKey = $target;
		}

		$targetMethod = 'get' . ucfirst($target);

		$schema = $this->{$targetMethod}();

		if ($format == 'json')
		{
			return JsonHelper::encode($schema);
		}

		return $schema;
	}

	/**
	 * @return null|string
	 */
	public function getWebsiteIdentityType()
	{
		$this->getGlobalByKey('identity');

		return $this->identity['@type'] != '' ? $this->identity['@type'] : 'Organization';
	}

	/**
	 * @return mixed
	 */
	protected function getMeta()
	{
		return $this->meta;
	}

	/**
	 * @return array
	 */
	protected function getIdentity()
	{
		return $this->{$this->globalKey};
	}

	/**
	 * @return array
	 */
	protected function getContacts()
	{
		$contacts = $this->{$this->globalKey};

		$contactPoints = array();

		if (count($contacts))
		{
			foreach ($contacts as $contact)
			{
				$contactPoints[] = array(
					'@type'       => 'ContactPoint',
					'contactType' => isset($contact['contactType']) ? $contact['contactType'] : $contact[0],
					'telephone'   => isset($contact['telephone']) ? $contact['telephone'] : $contact[1]
				);
			}
		}

		return $contactPoints;
	}

	/**
	 * @return array
	 */
	protected function getSocial()
	{
		$profiles = $this->{$this->globalKey};

		$profileLinks = array();

		if (count($profiles))
		{
			foreach ($profiles as $profile)
			{
				$profileLinks[] = array(
					'profileName' => isset($profile['profileName']) ? $profile['profileName'] : $profile[0],
					'url'         => isset($profile['url']) ? $profile['url'] : $profile[1]
				);
			}
		}

		return $profileLinks;
	}

	/**
	 * @return mixed
	 */
	protected function getOwnership()
	{
		$ownership = $this->{$this->globalKey};

		return $ownership;
	}

	/**
	 * @return mixed
	 */
	protected function getRobots()
	{
		$robots = $this->{$this->globalKey};

		return $robots;
	}

	/**
	 * @return mixed
	 */
	protected function getSettings()
	{
		$settings = $this->{$this->globalKey};

		return $settings;
	}

	/**
	 * @return null|string
	 */
	public function isLocalBusiness()
	{
		$identity = $this->getGlobalByKey('identity');

		if (isset($identity['organizationSubTypes'][0]) and $identity['organizationSubTypes'][0] == 'LocalBusiness')
		{
			return true;
		}

		return false;
	}
}
