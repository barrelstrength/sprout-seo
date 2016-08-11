<?php
namespace Craft;

class SproutSeo_PersonSchemaMap extends BaseSproutSeoSchemaMap
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Person';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Person';
	}

	/**
	 * @return array|null
	 */
	public function getAttributes()
	{
		$person = $this->attributes['globals']['identity'];
		$socialProfiles = $this->attributes['globals']['social'];

		if (!$person)
		{
			return null;
		}

		$schema['name']                = isset($person['name']) ? $person['name'] : null;
		$schema['alternateEntityName'] = isset($person['alternateEntityName']) ? $person['alternateEntityName'] : null;
		$schema['description']         = isset($person['description']) ? $person['description'] : null;
		$schema['url']                 = isset($person['url']) ? $person['url'] : null;
		$schema['telephone']           = isset($person['telephone']) ? $person['telephone'] : null;
		$schema['email']               = isset($person['email']) ? $person['email'] : null;

		// @todo - consider renaming identity logo to the more generic "image" label
		if (isset($person['logo'][0]))
		{
			$logo = craft()->assets->getFileById($person['logo'][0]);

			if ($logo)
			{
				$logo = array(
					"url"    => SproutSeoOptimizeHelper::getAssetUrl($logo->id),
					"width"  => $logo->getWidth(),
					"height" => $logo->getHeight()
				);

				$imageObjectSchemaMap = new SproutSeo_ImageObjectSchemaMap(array(
					'image' => $logo
				), false);

				$schema['image'] = $imageObjectSchemaMap->getSchema();
			}
		}

		$schema['gender']              = isset($person['gender']) ? $person['gender'] : null;
		$schema['birthplace']          = isset($person['birthplace']) ? $person['birthplace'] : null;

		if (count($socialProfiles))
		{
			$profileUrls = array();

			foreach ($socialProfiles as $socialProfile)
			{
				$profileUrls[] = $socialProfile['url'];
			}

			$schema['sameAs'] = array_values($profileUrls);
		}


		return array_filter($schema);
	}
}