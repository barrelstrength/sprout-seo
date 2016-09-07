<?php
namespace Craft;

class SproutSeo_OrganizationSchemaMap extends BaseSproutSeoSchemaMap
{
	/**
	 * @var
	 */
	protected $type = "Organization";

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Organization';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	// Does syntax user a generic `object` or do we need to assume
	// we know specifically what the variable is called?
	//
	// Have some out of box helper methods like getFirst()
	// Do we really need the @methodName syntax? or do we just write this in PHP?
	public function getAttributes()
	{
		$schema         = $this->sitemapInfo['globals']['identity'];
		$socialProfiles = $this->sitemapInfo['globals']['social'];

		$jsonLd['name']                = isset($schema['name']) ? $schema['name'] : null;
		$jsonLd['alternateEntityName'] = isset($schema['alternateEntityName']) ? $schema['alternateEntityName'] : null;
		$jsonLd['description']         = isset($schema['description']) ? $schema['description'] : null;
		$jsonLd['url']                 = isset($schema['url']) ? $schema['url'] : null;
		$jsonLd['telephone']           = isset($schema['telephone']) ? $schema['telephone'] : null;
		$jsonLd['email']               = isset($schema['email']) ? $schema['email'] : null;

		if (isset($schema['logo'][0]))
		{
			$jsonLd['logo'] = $this->getSchemaImageById($schema['logo'][0]);
		}

		// Add Corporate Contacts
		$contacts = $this->sitemapInfo['globals']['contacts'];

		if ($contacts)
		{
			$contactPoints = array();

			foreach ($contacts as $contact)
			{
				$contactPointSchemaMap = new SproutSeo_ContactPointSchemaMap(array(
					'contact' => $contact
				), false);

				$contactPoints[] = $contactPointSchemaMap->getSchema();
			}

			$jsonLd['contactPoint'] = $contactPoints;
		}

		$organization['organizationSubTypes']    = array();
		$organization['organizationSubTypes'][0] = isset($schema['organizationSubTypes'][0]) ? $schema['organizationSubTypes'][0] : null;
		$organization['organizationSubTypes'][1] = isset($schema['organizationSubTypes'][1]) ? $schema['organizationSubTypes'][1] : null;
		$organization['organizationSubTypes'][2] = isset($schema['organizationSubTypes'][2]) ? $schema['organizationSubTypes'][2] : null;

		// Set the right value for @type
		foreach ($organization['organizationSubTypes'] as $org)
		{
			if ($org != '')
			{
				$this->type = $org;
			}
		}

		if (isset($schema['organizationSubTypes'][0]) && $schema['organizationSubTypes'][0] == 'LocalBusiness')
		{
			$days         = array(0 => "Su", 1 => "Mo", 2 => "Tu", 3 => "We", 4 => "Th", 5 => "Fr", 6 => "Sa");
			$index        = 0;
			$openingHours = array();

			$schema['openingHours'] = isset($schema['openingHours']) ? $schema['openingHours'] : null;

			foreach ($schema['openingHours'] as $key => $value)
			{
				$openingHours[$index] = $days[$index];

				if (isset($value['open']['time']) && $value['open']['time'] != '')
				{
					$time = DateTime::createFromString($value['open']);
					$openingHours[$index] .= " " . $time->format('H:m');
				}

				if (isset($value['close']['time']) && $value['close']['time'] != '')
				{
					$time = DateTime::createFromString($value['close']);
					$openingHours[$index] .= "-" . $time->format('H:m');
				}

				// didn't work this day
				if (strlen($openingHours[$index]) == 2)
				{
					unset($openingHours[$index]);
				}

				$index++;
			}

			// Prepare opening hours as one dimensional array
			$jsonLd['openingHours'] = array_values($openingHours);
		}

		$jsonLd['organizationFounder'] = isset($schema['organizationFounder']) ? $schema['organizationFounder'] : null;

		if (isset($schema['foundingDate']['date']))
		{
			$foundingDate           = DateTime::createFromString($schema['foundingDate']);
			$jsonLd['foundingDate'] = $foundingDate->format('Y-m-d');
		}

		$structuredData['foundingLocation'] = isset($schema['foundingLocation']) ? $schema['foundingLocation'] : null;

		// Add Social Profile Links
		if (count($socialProfiles))
		{
			$profileUrls = array();

			foreach ($socialProfiles as $socialProfile)
			{
				$profileUrls[] = $socialProfile['url'];
			}

			$schema['sameAs'] = array_values($profileUrls);
		}

		return array_filter($jsonLd);
	}
}