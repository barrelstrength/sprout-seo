# Changelog

## 4.0.0-beta.37 - 2019-04-20

### Changed
- Updated barrelstrength/sprout-base-redirects requirement v1.0.8
- Updated barrelstrength/sprout-base-sitemaps requirement v1.0.9
- Updated barrelstrength/sprout-base requirement v5.0.0

### Fixed
- Improved Postgres support
- Fixed javascript error on Internet Explorer ([#150])
- Fixed error when loading Redirects index page ([#152])

[#150]: https://github.com/barrelstrength/craft-sprout-seo/issues/150
[#152]: https://github.com/barrelstrength/craft-sprout-seo/issues/152

## 4.0.0-beta.36 - 2019-03-23

### Changed
- Updated barrelstrength/sprout-base-redirects requirement v1.0.7
- Updated barrelstrength/sprout-base-sitemaps requirement v1.0.8
- Updated barrelstrength/sprout-base requirement v4.0.8

### Fixed
- Fixed issue where Redirect Base URLs did not check `.env` variables ([#147])
- Fixed namespaces on migration classes

[#147]: https://github.com/barrelstrength/craft-sprout-seo/issues/147 

## 4.0.0-beta.35 - 2019-03-19

### Changed
- Updated barrelstrength/sprout-base-sitemaps requirement v1.0.7

### Fixed
- Fixed bug where Sitemap settings did not update properly
- Fixed typo in changelog headers

## 4.0.0-beta.34 - 2019-03-19

### Changed
- Updated barrelstrength/sprout-base-redirects requirement v1.0.6
- Updated barrelstrength/sprout-base-sitemaps requirement v1.0.6

### Fixed
- Fixed issue where saving a Redirect via the button would redirect to Dashboard
- Fixed issue where settings assets were not loaded in the right order

## 4.0.0-beta.33 - 2019-03-18

### Changed
- Updated barrelstrength/sprout-base-redirects requirement v1.0.5
- Updated barrelstrength/sprout-base-sitemaps requirement v1.0.5

### Fixed
- Fixed issue where Redirect New URL could return null
- Fixed issue where cpEditUrl was set incorrectly

## 4.0.0-beta.32 - 2019-03-18

### Changed
- Updated barrelstrength/sprout-base-redirects requirement v1.0.4
- Updated barrelstrength/sprout-base-sitemaps requirement v1.0.4

### Fixed
- Fixed settings compatibility across plugins

## 4.0.0-beta.31 - 2019-03-18

### Changed
- Updated settings to require Admin permission to edit
- Updated barrelstrength/sprout-base requirement v4.0.7

## 4.0.0-beta.30 - 2019-03-13

### Changed
- Updated barrelstrength/sprout-base-fields requirement v1.0.3

### Fixed
- Fixed bug where Administrative Area Input was not populated correctly ([#85][#85fields])

[#85fields]: https://github.com/barrelstrength/craft-sprout-fields/issues/85

## 4.0.0-beta.29 - 2019-03-01

### Fixed 
- Fixed bug where meta images could block Live Preview

## 4.0.0-beta.28 - 2019-02-26

### Changed
- Updated craftcms/cms requirement to v3.1.15
- Added barrelstrength/sprout-base-fields requirement v1.0.1

### Fixed
- Fixed display issues when reordering by Structure ([#132]) 

[#132]: https://github.com/barrelstrength/craft-sprout-seo/issues/132

## 4.0.0-beta.27 - 2019-02-23

### Fixed
- Updated method to set absolute URL to improve support for alternate root directories 

## 4.0.0-beta.26 - 2019-02-23

### Fixed
- Updated schema version to ensure latest migrations are triggered

## 4.0.0-beta.25 - 2019-02-23

### Changed
- Improved display for Redirect Element Index page when displaying long URLs

### Fixed 
- Updated to use declarative query conditions and improve support for Postgres ([#138])
- Added migration to clean up initial slashes that are no longer necessary in Old URL and New URL fields
- Fixed bug where a deleted Redirect did not redirect to index page properly

[#138]: https://github.com/barrelstrength/craft-sprout-seo/pull/138

## 4.0.0-beta.24 - 2019-02-22

### Changed
- Added improved validation so new Redirects will now delete and replace existing 404 Redirects ([#131])

## 4.0.0-beta.23 - 2019-02-22

### Changed
- Added improved validation so new Redirects will now replace existing 404 Redirects ([#131])

### Fixed
- Added support for redirecting to Absolute URLs ([#140])

[#131]: https://github.com/barrelstrength/craft-sprout-seo/issues/131
[#140]: https://github.com/barrelstrength/craft-sprout-seo/issues/140

## 4.0.0-beta.22 - 2019-02-14

### Fixed
- Added barrelstrength/sprout-base-import requirement v1.0.0

## 4.0.0-beta.21 - 2019-02-13

### Changed
- Added tag editor library (and other resources previously managed in Sprout Base)
- Updated settings to implement SproutSettingsInterface
- Updated barrelstrength/sprout-base requirement to v4.0.6
- Added barrelstrength/sprout-base-fields requirement v1.0.0

## 4.0.0-beta.20 - 2019-02-01

### Improved 
- Improved support for PostgreSQL

## 4.0.0-beta.19 - 2019-01-25

### Fixed 
- Fixed template path error 

## 4.0.0-beta.18 - 2019-01-25

### Fixed 
- Fixed syntax error in CHANGELOG.md

## 4.0.0-beta.17 - 2019-01-25

### Added
- Added initial support for Craft 3.1

### Changed
- Added support for siteId when running Delete 404 redirect job
- Updated Craft CMS requirement to v3.1.0
- Updated Sprout Base requirement to v4.0.5

### Fixed 
- Fixed some asset bundle namespaces

## 4.0.0-beta.16 - 2019-01-23

### Changed
- Updates version number to ensure Craft Plugin Store recognizes this release

## 4.0.0-beta.15 - 2019-01-23

### Changed
- Improved performance of Delete 404 job ([#130])
- Updated ElementImporter method signature to match base
- Updated Address Field to include updates from Sprout Base
- Improved support for Craft 2 to Craft 3 migration
- Added several assets back to repo that were previously stored in Sprout Base
- Updated barrelstrength/sprout-base to require v4.0.4

### Fixed
- Fixed issue where custom metadata variable was not available in templates ([#128])

[#128]: https://github.com/barrelstrength/craft-sprout-seo/issues/128
[#130]: https://github.com/barrelstrength/craft-sprout-seo/issues/130

## 4.0.0-beta.14 - 2018-12-07

### Fixed
- Fixed bug where switching Entry Types using a Metadata field could throw an error ([#123], [#124])
- Fixed bug where schema type can be null before an identity exists
- Improved support for PHP 7.2 ([#122])

[#123]: https://github.com/barrelstrength/craft-sprout-seo/issues/122
[#123]: https://github.com/barrelstrength/craft-sprout-seo/issues/123
[#124]: https://github.com/barrelstrength/craft-sprout-seo/pull/124

## 4.0.0-beta.13 - 2018-11-16

### Fixed
- Fixed CreativeWork Schema integration to reference Website Identity for author and creator schema 

## 4.0.0-beta.12 - 2018-11-14

### Changed
- Updated Sitemap URL Pattern column to display an info modal when URL Patterns include Twig tags
- Updated CreativeWork Schema integration to use Website Identity instead of Author for author and creator schema [#112] 
- Updated Sprout Base requirement to v4.0.2

[#112]: https://github.com/barrelstrength/craft-sprout-seo/issues/112

## 4.0.0-beta.11 - 2018-10-29

### Changed
- Updated Sprout Base requirement to v4.0.0

## 4.0.0-beta.10 - 2018-10-27

### Changed
- Updated Sprout Base requirement to v3.0.10

### Fixed
- Fixed error when switching entry type while using Metadata Field. [#117]

[#117]: https://github.com/barrelstrength/craft-sprout-seo/issues/117

## 4.0.0-beta.9 - 2018-10-22

### Changed
- Improved error message when accidentally adding a redirect that already exists [#106]
- Updated Sprout Base requirement to v3.0.6

### Fixed
- Fixed bug in Redirect "Save and add another" behavior [#107]
- Fixed bug in Craft 2 to Craft 3 migration of Globals metadata ([#102], [#113], [#115])

[#102]: https://github.com/barrelstrength/craft-sprout-seo/issues/102
[#106]: https://github.com/barrelstrength/craft-sprout-seo/issues/106
[#107]: https://github.com/barrelstrength/craft-sprout-seo/issues/107
[#113]: https://github.com/barrelstrength/craft-sprout-seo/issues/113
[#115]: https://github.com/barrelstrength/craft-sprout-seo/issues/115

## 4.0.0-beta.8 - 2018-10-18

### Fixed
- Fixed migration error related to the address table when migrating from Craft 2
- Fixed bug on Categories element class where `expiryDate` was not validated

## 4.0.0-beta.7 - 2018-08-23

### Changed
- Added maxMetaDescriptionLength to be a setting in the Control Panel ([#50])
- Updated Sprout Base requirement to v3.0.2

### Fixed
- Fixed error where Sitemap was only accessible to logged in users ([#99])
- Fixed error when saving Elements on German language sites due to incorrect stopword class ([#96]) 
- Fixed Globals Website Identity page "Your changes may not be saved" message even if you don't edit anything ([#76])

[#50]: https://github.com/barrelstrength/craft-sprout-seo/issues/50
[#76]: https://github.com/barrelstrength/craft-sprout-seo/issues/76
[#96]: https://github.com/barrelstrength/craft-sprout-seo/issues/96
[#99]: https://github.com/barrelstrength/craft-sprout-seo/issues/99

## 4.0.0-beta.6 - 2018-08-03

### Added
- Added Metadata field support for non URL-enabled Elements

### Fixed
- Fixed behavior where an error could be thrown on non URL-Enabled pages 

## 4.0.0-beta.5 - 2018-08-03

### Fixed
- Fixed bug in when rendering Sitemaps page when only one Site exists ([#94])

[#94]: https://github.com/barrelstrength/craft-sprout-seo/issues/94

## 4.0.0-beta.4 - 2018-08-02

### Changed
- Updated keyword generation to use php-science/textrank
- Removed crodas/text-rank 
- Added support for Stopwords in English, French, German, Italian, Norwegian, and Spanish

## 4.0.0-beta.3 - 2018-07-31

### Added
- Added Multi-Site support for Globals
- Added Multi-Site support for Sitemaps
- Added Multi-Site support for Redirects 
- Added support for obfuscated Sitemap Index URLs
- XML Sitemaps now exclude elements set to `noindex` and `nofollow`
- XML Sitemaps now exclude elements using a Canonical URL override

### Changed
- Element Metadata is now stored in the Craft content table
- Section Metadata has been removed in favor of page-specific Element Metadata and Template Metadata
- Max Meta Description Length setting can now be managed in the Control Panel 
- Structured Data Main Entity settings are now managed at the Metadata field level
- Meta Details settings are now managed at the Metadata field level
- OG Local now populates dynamically based on the Site locale setting
- Redirects automatically manage the starting slash with Old URL and New URL values
- Removed Open Graph ogAudio, ogVideo settings from the Open Graph Meta Details field
- Removed Twitter Card Twitter Player settings from the Twitter Card Meta Details field
- Removed _Meta Details->Open Graph->Image Transform_ field in favor of Global settings
- Removed _Meta Details->Twitter Card->Image Transform_ field in favor of Global settings

## 3.5.0 - 2018-07-28

### Changed
- Moved release feed to Github

## 3.4.3 - 2018-05-22

### Added
- Added support for Element Metadata field in Sections that are not URL-Enabled

## 3.4.2 - 2018-04-05

### Fixed
- Fixed bug where max description length was no taken in Element Metadata Field

## 3.4.1 - 2018-04-03

### Changed
- Updates description column types to handle longer Meta Description Lengths

### Fixed
- Fixed issue where Redirect Elements couldn&#039;t be edited

## 3.4.0 - 2018-02-23

### Fixed
- Fixed bug where `maxMetaDescriptionLength` was not always respected

## 3.3.9 - 2017-12-21

### Fixed
- Fixed bug where Global Metadata was being defined incorrectly

## 3.3.8 - 2017-12-19

### Fixed
- Fixed a bug that assumed some values exist when they may not exist

## 3.3.6 - 2017-12-14

### Added
- Added support for overriding Canonical URLs at Element Metadata field level
- Added support for Environment Variables in Canonical URL override field
- Added support for Environment Variables in Globals Name and Description settings
- Added a new `maxMetaDescriptionLength` config setting to update the max length of a meta description
- Added validation to Redirects to ensure Old URL&#039;s are unique

### Changed
- Improved display of long URLs on the redirects index page

## 3.3.5 - 2017-10-18

### Added
- Added `craft.sproutSeo.getGlobals` to easily access all Global Metadata in tempaltes
- Added support for Rich Text fields in Description and Keyword Element Metadata field settings

### Fixed
- Fixed bug when section handles used camelCase
- Fixed bug when creating a dynamic sitemap if a section had been deleted

## 3.3.4 - 2017-09-12

### Added
- Added Dynamic XML Sitemaps for all URL-Enabled Sections including Entries, Categories, and Craft Commerce Products
- Added _Enable Dynamic Sitemaps_ setting. Dynamic Sitemaps will default to disabled if you are updating and will default to enabled for new installations.
- Added _Total Elements Per Sitemap_ setting. Adjust the total elements per sitemap to facilitate managing large sitemaps, multilingual sitemaps, and sitemaps on servers with limited resources. Default: 500.
- Added 404 Redirect Element Type. Page Not Found errors can now be logged in your database, monitored, and updated to active Redirects by content admins.
- Added _Log 404 Redirects_ setting. Default: disabled.
- Added _404 Redirect Limit_ setting. Automatically purge the least recently updated 404 redirects from your database. Default: 500.
- Added Redirect count which keeps track of the number of times a Redirect or 404 is triggered.

### Changed
- Updated Custom Sections to support URLs for Sitemaps

### Fixed
- Fixed bug where Price Range Structured Data attribute displayed for more than &quot;Local Business&quot; metadata
- Fixed bug where field label SEO badge could display twice for custom patterns using the same custom variable in multiple settings
- Fixed bug where Redirects did not match UTF-8 characters in URLs

## 3.2.3 - 2017-07-19

### Fixed
- Fixed bug where the Website Identity image did not get removed after the image was deleted from Assets
- Fixed bug where the Element Metadata field caused an error when an Element did not have URLs enabled
- Fixed bug where `CommerceProductUrlEnabledSectionType` integration would throw error  when ResaveElement task was triggered

## 3.2.2 - 2017-06-20

### Added
- Added error message when templates use the deprecated `optimize` tag in templates

### Fixed
- Fixed bug where append title could not be updated
- Fixed bug where saving Section Metadata via the Sitemap tab saved incorrect handle value
- Fixed bug where ogLocale didn&#039;t always use the correct locale
- Improved support for compatibility with PHP 5.4 and lower

## 3.2.1 - 2017-03-29

### Fixed
- Fixed bug where Open Graph Image and Twitter Image could not be saved within Element Metadata Meta Details fields
- Fixed bug where canonical URL could default to the siteUrl in certain scenarios

## 3.2.0 - 2017-03-13

### Added
- Added support for Structured Data for Globals, Sections, and Element Metadata. Structured Data can be mapped to all supported Element Types such as Entries, Categories, Craft Commerce Products, and Sprout Email Campaign Emails. Basic schemas are provided for Creative Work, Event, Intangible, Organization, Person, Place, and Product data types and a flexible Schema API is provided for advanced mapping.
- Added support for Globals to generate Structured Data for Organization, Website, and Place schema mappings
- Added support for Main Entity of Page Structured Data for Sections and Element Metadata
- Added Structured Data Schema API
- Added Globals section to manage Knowledge Graph metadata and Structured Data
- Added Globals section to manage Verify Ownership tags
- Added Globals section to manage Social Profiles
- Added Globals section to manage Contact numbers
- Added support for Organization Website Identity schema
- Added Founding Date, Opening Hours, and Price Range for Local Business Schema
- Added support for Person Website Identity schema
- Added support for Gender in Person Website Identity schema: Male, Female, Custom
- Added Globals Address Field that supports over 200 address formats
- Added option to query Longitude and Latitude for an address via the Google Maps API
- Added craft.sproutSeo.contacts tag
- Added craft.sproutSeo.socialProfiles tag
- Added support for Append Site Name setting to be set to a custom value
- Added support for SEO Divider setting to be set to a custom value
- Added support to supress the global Title value from being added to the home page title metadata
- Added support for Google+ in publisher meta
- Added support for using {siteUrl} in Globals URL setting
- Added option to apply default image transforms to Open Graph and Twitter Card image metadata
- Added default transform options for Open Graph and Twitter Cards for common Rectangle and Square sizes
- Added URL-Enabled Sections and support for Entries, Categories, and Craft Commerce Products. URL-Enabled Sections facilitate the process of prioritizing metadata, mapping Structured Data, and generating XML sitemaps for all URL-Enabled Element Types.
- Added URL-Enabled Section API
- Added Custom Sections
- Added Active Metadata icons in the UI which indicate if a Section is setup to use an Element Metadata fieldtype, is included in the XML Sitemap, and supports fallback metadata for Search and Social Sharing
- Added support to resave all affected elements when an Entry Type or Category Group Field Layout are saved
- Added support to resave all affected elements after saving the Element Metadata field
- Added Element Metadata Field
- Added support to set default Title, Description, Image, and Keyword values based on existing fields
- Added support to set dynamic Title, Description, Image, and Keyword values based on existing fields
- Added support for users to manage metadata manually via Custom Fields (for Title, Description, Image, Keywords, and Main Entity Structured Data) or Meta Details blocks (for more comprehensive Search, Open Graph, Twitter Card, Geo, and Robots metadata)
- Added counters to Title and Description fields, including when selected as dynamic settings
- Added support to generate Keywords dynamically
- Added support to display an SEO badge in the Field Layout that indicates if a field is mapped to be used for SEO and provides additional info on the field&#039;s scope and level of priority
- Added support to display a SEO badge on SEO-enabled fields using &#039;Add Custom Format&#039; setting
- Added IPreviewableFieldtype support to display a status of the Element Metadata field in Element Index columns
- Added support for the Element Metadata field to be marked as required when settings use custom Meta fields via the &quot;Display Editable Field&quot; setting
- Added the `{% sproutseo &#039;optimize&#039; %}` tag, a more powerful way to manage Meta Tags and Structured Data in templates.
- Added Advanced setting to disable metadata rendering
- Added Advanced setting to enabled a custom `metadata` variable, and choose the variable name
- Added Advanced setting to disable Sitemap Custom Sections option and simplify UI
- Added Advanced setting to disable Meta Details options and simplify UI
- Added Advanced setting to update locale behavior if locales are being used for multi-site
- Added Advanced setting to optionally display field handles in Element Metadata field dropdowns
- Added support for Sprout Import import SEO data into all metadata fields

### Changed
- Renamed Defaults to Globals
- Removed Global Fallback meta data option. The Globals section will now serve as the Global Fallback.
- The `craft.sproutSeo.meta` tag `default` attribute has been deprecated. Use the `section` attribute instead.
- The `craft.sproutSeo.meta` tag `id` attribute has been deprecated. Use the Element Metadata field instead.
- Removed Sprout SEO 2 Meta Fields (Meta Basic, Meta Open Graph, Meta Twitter Card, Meta Geo, Meta Robots) in favor of new Element Metadata field
- Added a migration for the deprecated Twitter Photo Card to be migrated to use Summary Card with Large Image
- Redirects have been optimized to only get triggered if a request 404s
- Updated string values output in metadata to clean up any leading or trailing whitespace
- The `craft.sproutSeo.optimize()` tag has been deprecated. Use `{% sproutseo &#039;optimize&#039; %}`.

### Fixed
- Fixed bug where metadata values would not be saved with Entry Drafts
- Fixed bug where metadata values were not repopulated if field was required
- Fixed bug with Sprout Import Redirect Element integration

## 2.2.2 - 2016-10-20

### Fixed
- Fixed a bug where some Open Graph meta values could cause a validation error
- Fixed a bug where plugin would block yiic commands
- Fixed a bug where sitemap integrations could cause an error by returning null or empty

## 2.2.1 - 2016-04-20

### Fixed
- Fixed bug where the Meta Field Types would not display saved values on Craft Commerce Products
- Fixed migration error that could occur on older versions of MySQL
- Fixed deprecation error introduced in Craft 2.6.2779

## 2.2.0 - 2016-03-29

### Added
- Added Sitemap support for Categories
- Added Sitemap support for Craft Commerce Products
- Added sitemap integration support for third-party URL-enabled Elements using registerSproutSeoSitemap hook

### Fixed
- Fixed issue that could occur if somebody enabled a Section in the sitemap and then disabled URLs on that Section
- Fixed error that could occur when trying to save the Sitemap settings using the save shortcut command

## 2.1.1 - 2016-03-03

### Added
- Added PHP 7 compatibility

### Changed
- Updated Facebook locale to default to blank

### Fixed
- Fixed error that could occur if an image used in the SEO settings was independently deleted in Craft
- Fixed bug where updating some settings could break other settings

## 2.1.0 - 2016-02-04

### Added
- Redirects can now be reordered

### Changed
- Optimized database queries for determining if a redirect is necessary

### Fixed
- Updated SproutSeo_MetaModel id value to default to null to fix an error that could occur when saving to some MySQL databases

## 2.0.1 - 2015-12-02

### Added
- The entire Control Panel has been updated to work with Craft 2.5
- Added Plugin icon
- Added Plugin description
- Added link to documentation
- Added link to plugin settings
- Added link to release feed
- Added subnav in place of tabs for top level navigation
- Added Sprout Migrate support for Redirect Element Type
- Added status setting to Redirect form

### Changed
- Improved and standardized display of Sprout plugin info in footer
- Improved and simplified breadcrumbs
- Settings pages use sidebar in place of tabs
- Updated Redirect status and delete settings to appear before sidebar documentation

### Fixed
- Fixed global fallback setting display

## 1.1.1 - 2015-09-16

### Added
- Adds CSRF support on Redirect forms

### Changed
- Updates the Redirect Method field to be required
- Updates Redirect statuses from on/off to enabled/disabled
- Removes the option to sort by Test column on Redirect Element Index page

## 1.1.0 - 2015-09-14

### Added
- Added Redirect Element Type and the ability to create and manage 301 and 302 redirects
- Redirect Elements with regular expressions and capture groups
- Redirect Elements can be Enabled or Disabled
- Added bulk actions to update redirect methods and delete Redirect Elements

### Changed
- Improved a subset of internal APIs for better performance
- Improved some UI elements and copy throughout the control panel

### Fixed
- Fixes an output issue for the robots meta tag

## 1.0.5 - 2015-08-04

### Added
- Added craft.sproutSeo.getOptimizedMeta tag. Optimized meta data can now be customized in template.
- Added craft.sproutSeo.getDefaultByHandle tag
- Added craft.sproutSeo.divider tag
- Added SproutSeo_MetaDefaultsService
- Added SproutSeo_MetaDefaultsService
- Added several variables and methods to SproutSeo_MetaModel: locale, setMeta, getMetaTagName, getMetaTagData, getEntryOverride, getCodeOverride, getDefault, getGlobalFallback

### Changed
- Added SproutSeoMetaHelper and moves several helper methods into this class
- Updated robots checkboxes to use Craft form macros
- Updates prepareGeoPosition() method to return the default position if nothing better is found
- Added all og:image attributes to SproutSeo_OpenGraphFieldModel
- Improved error messaging and default behavior of ogImageSecure value in environments using SSL
- Renames curiously titled SproutSeo_MetaService::getDefaultByDefaultHandle() =&gt; SproutSeo_MetaService::getDefaultByHandle()
- Renames SproutSeo_MetaService::_prioritizeMetaValues() =&gt; SproutSeo_MetaService::getOptimizedMeta()
- Various code cleanup and improvements

### Fixed
- Fixed behavior of SproutSeoMetaHelper::prepareAssetUrls()
- Fixed behavior of SproutSeo_MetaService::prepareAppendedSiteName()
- Fixed bug in how several Meta Field Types were re-populating their data in their fields
- Removes data-saveshortcut behavior from Sitemap index

## 1.0.3 - 2015-06-24

### Fixed
- Fixed bug when saving Meta Fields without Robots Meta Field present

## 1.0.2 - 2015-06-23

### Changed
- Improved image override error messaging
- Improved migration logic

### Fixed
- Fixed several issues with Meta Fields not saving properly under certain conditions

## 1.0.0 - 2015-05-13

### Added
- Commercial Release

## 0.9.2 - 2015-05-11

### Added
- Meta Field Types now support multi-lingual content

### Changed
- Updated SEO Default titles to display name of Default
- Added logic to prevent a situation where a Section with URLs-enabled and null URLs could appear in the Sitemap output

### Fixed
- Fixed but where locale ID was being output twice in some URLs
- Fixes twitter:card `summary_large_image` meta content value

## 0.9.1 - 2015-04-07

### Added
- Added support for multi-language sitemaps
- Added support for images using Amazon S3
- Added support for overriding images
- Added support for environmentVariables in SEO Defaults, Field Types, and Sitemap Custom Pages
- Added support for CSRF Protection
- The `getSitemap()` tag can optionally just return &lt;url&gt; nodes
- Added example US English translation file
- Added character countdown to several fields that have recommended character limits

### Changed
- Removes deprecated &#039;template&#039; option in favor of &#039;default&#039; to reference Defaults by handle
- Allow Defaults to override Append Site Name setting
- Added Home icon to identify which Default is selected as the Global Fallback
- Robots meta tags now output as positive by default
- Sitemap now only displays live entries
- Improved field management between Defaults and Meta Field Types
- Improved formatting, code hinting, and doc blocks
- Various UI updates and copy tweaks

### Fixed
- Fixed error when duplicating an entry with a Meta Fields
- Fixed bug where geo fields might not be included in meta tags
- Fixed various bugs with Robots checkboxes
- Fixed call to deprecated method in Robots Field

## 0.8.2 - 2014-10-16

### Added
- Added support for absolute Twitter and Open Graph Asset paths

### Fixed
- Fixed issue where social field types could cause ResaveElements task to hang

## 0.8.1 - 2014-09-29

### Fixed
- Updated date format for &lt;lastmod&gt; tag to be ISO 8601 compliant
- Fixed bug that prevented user from editing a Custom Page URL

## 0.8.0 - 2014-09-14

### Added
- Create and generate an XML Sitemap from your Sections with URLs
- Add Sitemap Pages based on custom URLs
- Add support for multiple Twitter Cards (Summary Card, Summary Card with Large Image, Player Card, Photo Card)
- Add support for multiple Facebook Open Graph types (Article, Website)
- Twitter and Open Graph images are now integrated with Assets
- Added Meta: Twitter Card field type
- Added Meta: Open Graph field type
- Added option to select global Default
- Special characters are now escaped when output
- Added framework for unit testing

### Changed
- Updated `optimize()` tag to be more optimal
- Updated &quot;Fallbacks&quot; to be named &quot;Defaults&quot;
- Moved default plugin settings to the settings tab in the plugin
- Removed custom Global Fallback option
- Moved option to append sitename from global level to the Default level
- Improved organization of code
- Various minor code and UI improvements
- Updated Robots syntax to use a string instead of an array

### Fixed
- Fixed Robots Checkbox settings to now save properly

## 0.6.2 - 2014-03-25

### Fixed
- Fixed output of Custom Global Value setting

## 0.6.1 - 2014-03-18

### Added
- Added craft.sproutSeo.optimize() variable
- Deprecated craft.sproutSeo.define() variable

### Fixed
- Fixed bug preventing override fields from saving on more recent versions of Craft
- Fixed bug preventing fallback templates to be deleted on more recent versions of Craft

## 0.6.0 - 2013-12-29

### Added
- Private Beta
