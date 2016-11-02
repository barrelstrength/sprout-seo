<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160901_000005_sproutSeo_updateSectionMetadataInformation extends BaseMigration
{
	/**
	 * Let's dance!
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName    = "sproutseo_metadata_sections";
		$enableCustom = false;

		if (craft()->db->tableExists($tableName))
		{
			// Find all Section Metadata Sections and set all the rows as custom pages
			$rows = craft()->db->createCommand()
				->select('*')
				->from($tableName)
				->queryAll();

			$enableMetaDetails = false;


			$metaInfo = array(
				'ogType'       ,
				'ogSiteName'   ,
				'ogAuthor'     ,
				'ogPublisher'  ,
				'ogUrl'        ,
				'ogTitle'      ,
				'ogDescription',
				'ogImage'      ,
				'ogImageSecure',
				'ogImageWidth' ,
				'ogImageHeight',
				'ogImageType'  ,
				'ogAudio'      ,
				'ogVideo'      ,
				'ogLocale'     ,
				'twitterCard'                   ,
				'twitterSite'                   ,
				'twitterCreator'                ,
				'twitterUrl'                    ,
				'twitterTitle'                  ,
				'twitterDescription'            ,
				'twitterImage'                  ,
				'twitterPlayer'                 ,
				'twitterPlayerStream'           ,
				'twitterPlayerStreamContentType',
				'twitterPlayerWidth'            ,
				'twitterPlayerHeight'           ,
			);

			foreach ($rows as $row)
			{
				// let's validate any possible duplicate handle
				$urlEnabledSectionTypes = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypes();

				foreach ($urlEnabledSectionTypes as $urlEnabledSectionTypeKey => $urlEnabledSectionType)
				{
					foreach ($urlEnabledSectionType->urlEnabledSections as $urlEnabledSectionKey => $urlEnabledSection)
					{
						$sectionMetadata = $urlEnabledSection->sectionMetadata;

						if (isset($sectionMetadata->name))
						{
							if (isset($sectionMetadata->handle))
							{
								$handle = $sectionMetadata->handle;

								if ($row['handle'] == $handle)
								{
									$row['handle'] = 'customSection' . ucfirst($row['handle']);
									break 2;
								}
							}
						}
					}
				}

				if (!$enableMetaDetails)
				{
					foreach ($metaInfo as $meta)
					{
						if (isset($row[$meta]) && $row[$meta])
						{
							$enableMetaDetails = true;
						}
					}
				}

				$pluginSettings = craft()->db->createCommand()
				->select('*')
				->from('plugins')
				->where('class=:class', array(':class' => 'SproutSeo'))
				->queryRow();

				$pluginSettings = json_decode($pluginSettings['settings'], true);
				$pluginSettings['twitterTransform'] = '';
				$pluginSettings['ogTransform']      = '';

				if ($enableMetaDetails)
				{
					$pluginSettings['enableMetaDetailsFields'] = 1;
				}

				craft()->db->createCommand()->update('plugins', array(
					'settings' => json_encode($pluginSettings)
				),
					'class=:class', array(':class' => 'SproutSeo')
				);

				craft()->db->createCommand()->update($tableName,
					array('isCustom' => 1, 'handle' => $row['handle'], 'priority' => '0.5'),
					'id = :id',
					array(':id' => $row['id'])
				);

				$enableCustom = true;
			}

			$pluginSettings = craft()->db->createCommand()
			->select('*')
			->from('plugins')
			->where('class=:class', array(':class' => 'SproutSeo'))
			->queryRow();

			$pluginSettings = json_decode($pluginSettings['settings'], true);

			if ($enableCustom)
			{
				// Plugin settings
				$pluginSettings['enableCustomSections'] = 1;

				craft()->db->createCommand()->update('plugins', array(
					'settings' => json_encode($pluginSettings)
				),
					'class=:class', array(':class' => 'SproutSeo')
				);
			}

			// Move globalFallback to globals

			$globalFallback = craft()->db->createCommand()
				->select('*')
				->from($tableName)
				->where('globalFallback = 1')
				->queryRow();

			if ($globalFallback)
			{
				$identity       = array();
				$values['meta'] = json_encode($globalFallback);

				$identity['name']                 = $globalFallback['title'];
				$identity['alternateName']        = "";
				$identity['logo']                 = "";
				$identity['email']                = "";
				$identity['telephone']            = "";
				$identity['logo']                 = "";
				$identity['@type']                = "Organization";
				$identity['foundingDate']         = "";
				$identity['openingHours']         = "";
				$identity['gender']               = "";
				$identity['description']          = $globalFallback['description'];
				$identity['keywords']             = $globalFallback['keywords'];
				$identity['url']                  = $globalFallback['url'];
				$identity['organizationSubTypes'] = "";

				if (is_numeric($globalFallback['twitterImage']))
				{
					$identity['logo'] = array($globalFallback['twitterImage']);
				}

				if (is_numeric($globalFallback['ogImage']))
				{
					$identity['logo'] = array($globalFallback['ogImage']);
				}

				if ($globalFallback['robots'])
				{
					$robotsArray    = explode(",", $globalFallback['robots']);
					$robotsSettings = array();

					foreach ($robotsArray as $key => $value)
					{
						$robotsSettings[$value] = 1;
					}

					$robots = array(
						'noindex'      => 0,
						'nofollow'     => 0,
						'noarchive'    => 0,
						'noimageindex' => 0,
						'noodp'        => 0,
						'noydir'       => 0,
					);

					foreach ($robots as $key => $value)
					{
						if (isset($robotsSettings[$key]))
						{
							$robots[$key] = 1;
						}
					}

					$values['robots'] = json_encode($robots);
				}

				$settings = array(
					'seoDivider'         => $pluginSettings['seoDivider'],
					'appendTitleValue'   => $enableCustom ? 1 : "",
					'imageTransform'     => ""
				);

				if ($globalFallback['ogType'] || $globalFallback['twitterCard'])
				{
					$settings['defaultOgType']      = $globalFallback['ogType'];
					$settings['defaultTwitterCard'] = $globalFallback['twitterCard'];
				}

				$values['identity'] = json_encode($identity);
				$values['settings'] = json_encode($settings);

				if ($globalFallback['twitterSite'])
				{
					$username = $globalFallback['twitterSite'];
					$username = str_replace("@", "", $username);
					$twitterUrl  = "http://twitter.com/".$username;

					if($twitterUrl)
					{
						$social = array(array('profileName'=>'Twitter', 'url'=>$twitterUrl));

						$values['social'] = json_encode($social);
					}
				}

				$result = craft()->db->createCommand()->update('sproutseo_metadata_globals',
					$values,
					'id=:id',
					array(':id' => 1)
				);
			}

			// Migrate Sitemap info
			$sitemapTable = "sproutseo_sitemap";

			$sitemaps = craft()->db->createCommand()
				->select('*')
				->from($sitemapTable)
				->queryAll();

			foreach ($sitemaps as $sitemap)
			{
				$locale = craft()->i18n->getLocaleById(craft()->language);

				// support for sections (entries) and categories
				if ($sitemap['elementGroupId'] && $sitemap['type'] == 'sections')
				{
					$section = craft()->db->createCommand()
					->select('*')
					->from('sections')
					->where('id = :id', array(':id' => $sitemap['elementGroupId']))
					->queryRow();

					$section18n = craft()->db->createCommand()
					->select('urlFormat')
					->from('sections_i18n')
					->where('sectionId = :id and locale = :locale', array(
						':id' => $sitemap['elementGroupId'],
						':locale' => $locale
						)
					)
					->queryRow();

					if ($section && $section18n)
					{
						// Create a new row in sections
						craft()->db->createCommand()->insert($tableName, array(
							'urlEnabledSectionId' => $sitemap['elementGroupId'],
							'isCustom'            => 0,
							'enabled'             => $sitemap['enabled'],
							'type'                => 'entries',
							'name'                => $section['name'],
							'handle'              => $section['handle'],
							'url'                 => $section18n['urlFormat'],
							'priority'            => $sitemap['priority'],
							'changeFrequency'     => $sitemap['changeFrequency']
						));
					}
				}

				if ($sitemap['elementGroupId'] && $sitemap['type'] == 'categories')
				{
					$category = craft()->db->createCommand()
					->select('*')
					->from('categorygroups')
					->where('id = :id', array(':id' => $sitemap['elementGroupId']))
					->queryRow();

					$category18n = craft()->db->createCommand()
					->select('urlFormat')
					->from('categorygroups_i18n')
					->where('groupId = :id and locale = :locale', array(
						':id' => $sitemap['elementGroupId'],
						':locale' => $locale
						)
					)
					->queryRow();

					if ($category && $category18n)
					{
						// Create a new row in sections
						craft()->db->createCommand()->insert($tableName, array(
							'urlEnabledSectionId' => $sitemap['elementGroupId'],
							'isCustom'            => 0,
							'enabled'             => $sitemap['enabled'],
							'type'                => 'categories',
							'name'                => $category['name'],
							'handle'              => $category['handle'],
							'url'                 => $category18n['urlFormat'],
							'priority'            => $sitemap['priority'],
							'changeFrequency'     => $sitemap['changeFrequency']
						));
					}
				}
			}

			$this->dropTableIfExists($sitemapTable);

			// We no longer need the Global Fallback column
			if (craft()->db->columnExists($tableName, 'globalFallback'))
			{
				$this->dropColumn($tableName, 'globalFallback');
			}
		}
		else
		{
			SproutSeoPlugin::log("Table {$tableName} does not exists", LogLevel::Error, true);
		}

		return true;
	}
}