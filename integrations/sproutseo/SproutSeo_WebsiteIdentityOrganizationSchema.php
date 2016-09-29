<?php
namespace Craft;

class SproutSeo_WebsiteIdentityOrganizationSchema extends SproutSeoBaseSchema
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

	/**
	 * @return bool
	 */
	public function isUnlistedSchemaType()
	{
		return true;
	}

	// Does syntax user a generic `object` or do we need to assume
	// we know specifically what the variable is called?
	//
	// Have some out of box helper methods like getFirst()
	// Do we really need the @methodName syntax? or do we just write this in PHP?
	public function addProperties()
	{
		$schema         = $this->globals['identity'];
		$socialProfiles = $this->globals['social'];

		$this->setOrganizationType($schema);

		$this->addText('name', $schema['name']);
		$this->addText('alternateName', $schema['alternateName']);
		$this->addText('description', $schema['description']);
		$this->addUrl('url', $schema['url']);
		$this->addTelephone('telephone', $schema['telephone']);
		$this->addEmail('email', $schema['email']);
		$this->addImage('logo', $schema['logo'][0]);

		// Add Corporate Contacts
		$contacts = $this->globals['contacts'];
		$this->addContactPoints($contacts);

		if (isset($schema['organizationSubTypes'][0]) && $schema['organizationSubTypes'][0] == 'LocalBusiness')
		{
			$openingHours = isset($schema['openingHours']) ? $schema['openingHours'] : null;
			$this->addOpeningHours($openingHours);
		}

		$this->addDate('foundingDate', $schema['foundingDate']['date']);

		//$jsonLd['foundingLocation'] = isset($schema['foundingLocation']) ? $schema['foundingLocation'] : null;

		$urls = array_column($socialProfiles, 'url');
		$this->addSameAs($urls);
	}

	/**
	 * Process the selected Organization Type setting and update this schema type
	 *
	 * @param $schema
	 */
	protected function setOrganizationType($schema)
	{
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
	}
}