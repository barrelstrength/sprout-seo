<?php
namespace Craft;

class SproutSeo_SchemaModel extends BaseModel
{
	public $schemaId = null;
	public $type = null;

	protected function defineAttributes()
	{
		return array(
			'meta'      => AttributeType::Mixed,
			'identity'  => AttributeType::Mixed,
			'contacts'  => AttributeType::Mixed,
			'social'    => AttributeType::Mixed,

			// @todo - move to a meta tag model
			'ownership' => AttributeType::Mixed,
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
	public function getSchema($target, $format = 'array')
	{
		if ($target)
		{
			$this->schemaId = $target;
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
	 * Returns correct values for final Json-LD
	 * Validated on: https://search.google.com/structured-data/testing-tool
	 *
	 * @param $target
	 *
	 * @return string
	 */
	public function getJsonLd($target)
	{
		$schema = $this->getSchema($target);
		$jsonLd = array();
		// Don't add null values to jsonLd
		switch ($target)
		{
			case 'identity':
				$jsonLd['@type'] = $schema['@type'];
				$jsonLd['@context'] = $schema['@context'];
				$jsonLd['name']          = isset($schema['name']) ? $schema['name'] : null;
				$jsonLd['description']   = isset($schema['description']) ? $schema['description'] : null;
				$jsonLd['url']           = isset($schema['url']) ? $schema['url'] : null;

				if (isset($schema['logo'][0]))
				{
					$logo = craft()->assets->getFileById($schema['logo'][0]);

					if ($logo)
					{
						$img = $schema['@type'] == 'Person' ? 'image' : 'logo';
						$jsonLd[$img] = array(
							"@type" => "ImageObject",
							"url" => SproutSeoOptimizeHelper::getAssetUrl($logo->id),
							"width" => $logo->getWidth(),
							"height" => $logo->getHeight()
						);
					}
				}

				if ($schema['@type'] == 'Organization')
				{
					if (isset($schema['foundingDate']['date']))
					{
						$foundingDate = DateTime::createFromString($schema['foundingDate']);
						$jsonLd['foundingDate'] = $foundingDate->format('Y-m-d');
					}

					$days = array(0=>"Su", 1=>"Mo", 2=>"Tu", 3=>"We", 4=>"Th", 5=>"Fr", 6=>"Sa");
					$index = 0;
					$openingHours = array();

					if (isset($schema['organizationSubTypes'][0]) && $schema['organizationSubTypes'][0] == 'LocalBusiness')
					{
						foreach ($schema['openingHours'] as $key => $value)
						{
							$openingHours[$index] = $days[$index];

							if (isset($value['open']['time']) && $value['open']['time'] != '')
							{
								$time = DateTime::createFromString($value['open']);
								$openingHours[$index] .= " ".$time->format('H:m');
							}

							if (isset($value['close']['time']) && $value['close']['time'] != '')
							{
								$time = DateTime::createFromString($value['close']);
								$openingHours[$index] .= "-".$time->format('H:m');
							}
							// didn't work this day
							if (strlen($openingHours[$index]) == 2)
							{
								unset($openingHours[$index]);
							}

							$index++;
						}

						$jsonLd['openingHours'] = $openingHours;
					}

					$jsonLd['alternateEntityName'] = isset($schema['alternateEntityName']) ? $schema['alternateEntityName'] : null;
					#For what @type is keywords needed?
					#$jsonLd['keywords']            = isset($schema['keywords']) ? $schema['keywords'] : null;
					#$jsonLd['organizationFounder'] = isset($schema['organizationFounder']) ? $schema['organizationFounder'] : null;
					#$jsonLd['foundingLocation']    = isset($schema['foundingLocation']) ? $schema['foundingLocation'] : null;

					// Set the right value for @type
					foreach ($schema['organizationSubTypes'] as $org)
					{
						if ($org != '')
						{
							$jsonLd['@type'] = $org;
						}
					}

				}
				else if($schema['@type'] == 'Person')
				{
					//Person
					$jsonLd['gender']     = isset($schema['gender']) ? $schema['gender'] : null;
					$jsonLd['birthplace'] = isset($schema['birthplace']) ? $schema['birthplace'] : null;
				}

				break;
			case 'contacts':
				$jsonLd = $schema;
				break;
			case 'social':
				$index = 0;
				foreach ($schema as $key => $value)
				{
					$jsonLd[$index] = $value['url'];
					$index++;
				}

				break;
		}

		return $jsonLd;
	}


	// Supported Schema Types
	// =========================================================================

	public function getType()
	{
		$this->getSchema('identity');

		return $this->type != '' ? $this->type : 'Organization' ;
	}

	protected function getMeta()
	{
		return $this->meta;
	}

	protected function getIdentity()
	{
		$structuredData = $this->prepareSchemaObject();

		$schema = $this->{$this->schemaId};

		$structuredData['name']                = isset($schema['name']) ? $schema['name'] : null;
		$structuredData['description']         = isset($schema['description']) ? $schema['description'] : null;
		$structuredData['url']                 = isset($schema['url']) ? $schema['url'] : null;
		$structuredData['logo']                = isset($schema['logo']) ? $schema['logo'] : null;
		$structuredData['keywords']            = isset($schema['keywords']) ? $schema['keywords'] : null;
		$structuredData['alternateEntityName'] = isset($schema['alternateEntityName']) ? $schema['alternateEntityName'] : null;

		$structuredData['organizationSubTypes']    = array();
		$structuredData['organizationSubTypes'][0] = isset($schema['organizationSubTypes'][0]) ? $schema['organizationSubTypes'][0] : null;
		$structuredData['organizationSubTypes'][1] = isset($schema['organizationSubTypes'][1]) ? $schema['organizationSubTypes'][1] : null;
		$structuredData['organizationSubTypes'][2] = isset($schema['organizationSubTypes'][2]) ? $schema['organizationSubTypes'][2] : null;

		$structuredData['organizationFounder'] = isset($schema['organizationFounder']) ? $schema['organizationFounder'] : null;
		$structuredData['foundingDate']        = isset($schema['foundingDate']) ? $schema['foundingDate'] : null;
		$structuredData['foundingLocation']    = isset($schema['foundingLocation']) ? $schema['foundingLocation'] : null;

		$structuredData['openingHours'] = isset($schema['openingHours']) ? $schema['openingHours'] : null;

		//Person
		$structuredData['gender']     = isset($schema['gender']) ? $schema['gender'] : null;
		$structuredData['birthplace'] = isset($schema['birthplace']) ? $schema['birthplace'] : null;

		return $structuredData;
	}

	protected function getOrganization()
	{
	}

	protected function getPerson()
	{
	}

	protected function getWebsite()
	{
	}

	protected function getPlace()
	{
	}

	protected function getContacts()
	{
		$contacts = $this->{$this->schemaId};

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

	protected function getSocial()
	{
		$profiles = $this->{$this->schemaId};

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

	protected function getOwnership()
	{
		$ownership = $this->{$this->schemaId};

		return $ownership;
	}

	// Custom Schema Types
	// =========================================================================

	public function getSchemaMap($object, $mapId)
	{
	}


	// Protected Methods
	// =========================================================================

	protected function prepareSchemaObject()
	{
		$this->type = $this->{$this->schemaId}['@type'];

		return array(
			"@context" => "http://schema.org",
			"@type"    => $this->type
		);
	}
}
