# Changelog

## 4.6.9 - 2021-07-17

### Fixed
- Fixed Element Metadata save behavior when drafts are saved ([#241])

[#241]: https://github.com/barrelstrength/craft-sprout-seo/issues/241

## 4.6.8 - 2021-03-29

### Fixed
- Fixed Project Config support in settings migration ([#227], [#229])

[#227]: https://github.com/barrelstrength/craft-sprout-seo/issues/227
[#229]: https://github.com/barrelstrength/craft-sprout-seo/issues/229

## 4.6.7 - 2021-02-11

### Changed
- Updated `barrelstrength/sprout-base-redirects` requirement v1.5.4

### Fixed
- Fixed issue where updating a Redirect could delete it ([#233])

[#233]: https://github.com/barrelstrength/craft-sprout-seo/issues/233

## 4.6.6 - 2021-02-10

### Fixed
- Fixed migration check for migrations table pluginId and pluginHandle columns

## 4.6.5 - 2020-11-16

### Changed
- Updated `barrelstrength/sprout-base-redirects` requirement to v1.5.3

### Fixed
- Fixed redirect permissions issue ([#226])

[#226]: https://github.com/barrelstrength/craft-sprout-seo/issues/226

## 4.6.4 - 2020-10-22

### Changed
- Updated `barrelstrength/sprout-base-fields` requirement to v1.4.5

### Fixed
- Fixed 'Undefined index: metadataVariable' migration error [#217]
- Fixed condition where open graph URL overrides may not get processed correctly [#218]
- Improved namespace support ([#222], [#223])
- Fixed settings page rendering error introduced in Craft 3.5 [#221]

[#217]: https://github.com/barrelstrength/craft-sprout-seo/issues/217
[#218]: https://github.com/barrelstrength/craft-sprout-seo/issues/218
[#221]: https://github.com/barrelstrength/craft-sprout-seo/issues/221
[#222]: https://github.com/barrelstrength/craft-sprout-seo/issues/222
[#223]: https://github.com/barrelstrength/craft-sprout-seo/issues/223

## 4.6.3 - 2020-05-21

### Changed
- Updated `barrelstrength/sprout-base-uris` requirement v1.2.1

### Fixed
- Fixed bug where URL Enabled Sections could throw an error if any Section existed without a Field Layout ([#210], [#213])

[#210]: https://github.com/barrelstrength/craft-sprout-seo/issues/210
[#213]: https://github.com/barrelstrength/craft-sprout-seo/issues/213

## 4.6.2 - 2020-05-21

### Added
- Added placeholder value to '404 Redirect Limit' setting

### Changed
- Added minimum column width for Old URL on Redirect Element Index page
- Minor updates to primary and Element Metadata nav components
- Updated `barrelstrength/sprout-base-redirects` requirement v1.5.2
- Updated `barrelstrength/sprout-base-fields` requirement to v1.4.2
- Updated `giggsey/libphonenumber-for-php` requirement to v8.12.4

## 4.6.1 - 2020-05-16

### Changed
- Improved saving of new Redirects if matching 404 exists ([#26][#26-redirects])
- Improved display of Base URL on Redirect edit page
- Updated `barrelstrength/sprout-base-redirects` requirement v1.5.1

### Fixed
- Fixed address field spacing ([#181])
- Fixed Website Identity settings display bug ([#198])

[#26-redirects]: https://github.com/barrelstrength/craft-sprout-redirects/issues/26
[#181]: https://github.com/barrelstrength/craft-sprout-seo/issues/181
[#198]: https://github.com/barrelstrength/craft-sprout-seo/issues/198

## 4.6.0 - 2020-04-28

### Changed
- Updated `barrelstrength/sprout-base` requirement v6.0.0

### Fixed
- Fixed migration issue when multiple Sprout plugins are installed
- Fixed fallback scenario when migrating settings

[#203]: https://github.com/barrelstrength/craft-sprout-seo/issues/203

## 4.5.0 - 2020-04-27

### Added
- Added example config file `src/config.php`
- Added `barrelstrength\sproutbase\base\SproutDependencyTrait`
- Added `barrelstrength\sproutbase\base\SproutDependencyInterface`
- Added `barrelstrength\sproutbase\records\Settings`
- Added `barrelstrength\sproutbase\migrations\Install::safeDown()`
- Added support for config overrides in base settings models

### Changed
- Improved uninstall migration
- Updated `barrelstrength/sprout-base` requirement v5.2.0
- Updated `barrelstrength/sprout-base-fields` requirement v1.4.0
- Updated `barrelstrength/sprout-base-redirects` requirement v1.4.0
- Updated `barrelstrength/sprout-base-sitemaps` requirement v1.3.0
- Updated `barrelstrength/sprout-base-uris` requirement v1.1.0

### Fixed
- Fixed bug in migration when using alias in Site settings

### Removed
- Removed `barrelstrength\sproutbaseredirects\services\getPluginSettings()`
- Removed `barrelstrength\sproutbase\services\Settings::getPluginSettings()`
- Removed `barrelstrength\sproutbase\base\BaseSproutTrait`

## 4.4.4 - 2020-04-09

### Changed
- Updated `barrelstrength/sprout-base-fields` requirement to v1.3.4

### Fixed
- Fixed saving address in Postgres ([#101][#101-sprout-base-fields])

[#101-sprout-base-fields]: https://github.com/barrelstrength/craft-sprout-fields/issues/101

## 4.4.3 - 2020-03-14

### Changed
- Updated reference to optimized values on MetaType classes to use Metadata model
- Added `barrelstrength\sproutseo\base\MetaType::$metadata`
- Added `barrelstrength\sproutseo\helpers\OptimizeHelper::getSelectedFieldForOptimizedMetadata()`
- Added `barrelstrength\sproutseo\meta\SearchMetaType::$appendTitleValue`
- Added `barrelstrength\sproutseo\meta\RobotsMetaType::$canonical`

### Fixed
- Fixed bug where an Element Metadata field Meta Image mapped to an existing field was set to the Element Metadata field ID instead of the respective Field ID ([#194])
- Fixed issue where Open Graph article meta tags could be set for non-article Open Graph Types
- Fixed `siteId` argument when setting matched element

### Removed
- Removed use of `OptimizedTrait` in `barrelstrength\sproutseo\base\MetaType`
- Removed `barrelstrength\sproutseo\base\MetaType::$rawDataOnly`
- Removed `barrelstrength\sproutseo\base\OptimizedTrait::$appendTitleValue`
- Removed `barrelstrength\sproutseo\base\OptimizedTrait::getSelectedFieldForOptimizedMetadata()`

[#194]: https://github.com/barrelstrength/craft-sprout-seo/issues/194

## 4.4.2 - 2020-03-08

### Added
- Added Enable Redirects setting to turn Redirect behavior on or off ([#20][#20-sproutredirects])

### Changed
- Updated Redirects and Sitemaps sidebar navs to display or hide based on the their respective enabled/disabled setting
- Improved handling of calculated Metadata when saving Element Metadata field ([#194])
- Updated Metadata Model to support a `$rawDataOnly` argument
- Removed 'Enable Globals' setting. Use Sprout Redirects and Sprout Sitemaps if limited features are needed. 
- Updated `barrelstrength/sprout-base-redirects` to v1.3.2
- Updated `barrelstrength/sprout-base-sitemaps` to v1.2.1

### Fixed
- Fixed issue where canonical and other optimized fields were getting saved when they shouldn't have been ([#194])
- Fixed javascript error in Element Metadata Field when Meta Details settings are not enabled ([#194])
- Fixed bug where secondary sites redirected back to the main site ([#24][#24-sproutbaseredirects])
- Fixed 404 Redirect priority when matching a redirect

[#24-sproutbaseredirects]: https://github.com/barrelstrength/craft-sprout-redirects/issues/24
[#20-sproutredirects]: https://github.com/barrelstrength/craft-sprout-redirects/issues/20
[#194]: https://github.com/barrelstrength/craft-sprout-seo/issues/194

## 4.4.1.2 - 2020-03-03

###
- Fixed bug where Metadata model could not exist on new entries
- Fixed bug where `getRawData` could be called on Metadata model if it did not exists

## 4.4.1.1 - 2020-03-03

### Fixed
- Fixed bug where Globals image migration could fail if no image was present ([#193])
- Improved performance of Craft 2 to Craft 3 Element Metadata migration

[#193]: https://github.com/barrelstrength/craft-sprout-seo/issues/193

## 4.4.1 - 2020-03-03

> {warning} Custom Schema integrations will require additional updates in how they access and retrieve metadata. See [Updating to Sprout SEO v4.4.0](https://sprout.barrelstrengthdesign.com/docs/seo/installing-and-updating-craft-3.html#upgrading-to-sprout-seo-4-4) for details.

### Added
- Added Meta Types to handle setting and getting all meta attributes
- Added multi-site support for `craft.sproutSeo.getGlobalMetadata()` ([#184])
- Added multi-site support for `craft.sproutSeo.getSocialProfiles()` ([#184])
- Added `barrelstrength\sproutseo\base\MetaType`
- Added `barrelstrength\sproutseo\base\OptimizedTrait`
- Added `barrelstrength\sproutseo\base\MetaImageTrait`
- Added `barrelstrength\sproutseo\base\SchemaTrait`
- Added `barrelstrength\sproutseo\meta\GeoMetaType`
- Added `barrelstrength\sproutseo\meta\GooglePlusMetaType`
- Added `barrelstrength\sproutseo\meta\OpenGraphMetaType`
- Added `barrelstrength\sproutseo\meta\RobotsMetaType`
- Added `barrelstrength\sproutseo\meta\SchemaMetaType`
- Added `barrelstrength\sproutseo\meta\TwitterMetaType`
- Added `barrelstrength\sproutseo\services\ElementMetadata::getRawMetadataFromElement()`
- Added `barrelstrength\sproutseo\models\Metadata::setOptimizedProperties()`
- Added `barrelstrength\sproutseo\models\Metadata::getMetaTypes()`
- Added `barrelstrength\sproutseo\models\Metadata::setMetaTypes()`
- Added `barrelstrength\sproutseo\models\Metadata::getRawData()`

### Changed
- Updated Element Metadata field to always normalize data and return a Metadata model ([#192])
- Updated Metadata model to delegate responsibility of specific Metadata Types to Meta Type subclasses ([#192])
- Updated Meta Details field to use Metadata model and Meta Type classes to handle assignment and retrieval of metadata properties as well as several settings ([#192])
- Updated logical flow of `barrelstrength\sproutseo\services\Optimize::getPrioritizedMetadataModel()`
- Updated Template Overrides to now support `optimizedTitle`, `optimizedDescription`, and `optimizedImage` config settings ([#92])
- Updated Globals to save data directly without additionally processing `sproutseo_globals.meta` column ([#93])
- Moved `barrelstrength\sproutseo\models\Metadata::getMetaTagName()` to specific Meta Type classes
- Moved logic for Meta Image Relation fields into templates  
- Renamed `barrelstrength\sproutseo\services\Optimize::$elementMetadata` => `barrelstrength\sproutseo\services\Optimize::$elementMetadataField`
- Updated method signature of `barrelstrength\sproutseo\services\Optimize::getMetadata()`
- Updated `appendTitleValue` behavior to no longer parse the token `sitename` ([#180])
- Updated landing page styles to support Craft 3.4
- Improved support for translations in templates
- Updated `barrelstrength/sprout-base-fields` to require v1.3.3
- Updated `barrelstrength/sprout-base` to require v5.1.2

### Fixed
- Improved compatibility with PHP 7.0 ([#189], [#191]) 
- Improved managing Global and Element metadata for multi-site ([#193])

### Removed
- Removed database column `sproutseo_globals.meta`, this value is now calculated from raw settings
- Removed `barrelstrength\sproutseo\controllers\GlobalMetadata::populateGlobalMetadata()`
- Removed `barrelstrength\sproutseo\web\twig\variables\SproutSeoVariable\getGlobalRobots()`
- Removed `barrelstrength\sproutseo\services\Optimize::$matchedElement` in favor of `barrelstrength\sproutseo\services\Optimize::$element`
- Removed `barrelstrength\sproutseo\services\Optimize::getUri()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::prepareCanonical()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::prepareGeoPosition()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::prepareRobotsMetaValue()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::prepareRobotsMetadataForSettings()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::prepareAssetUrls()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::getImageId()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::getSelectedTransform()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::getTwitterProfileName()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::getFacebookPage()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::getGooglePlusPage()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::prepareAppendedTitleValue()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::updateOptimizedAndAdvancedMetaValues()`
- Removed `barrelstrength\sproutseo\helpers\OptimizeHelper::getDefaultFieldTypeSettings()`
- Removed `barrelstrength\sproutseo\fields\ElementMetadata::$metadata`
- Removed `barrelstrength\sproutseo\fields\ElementMetadata::$showMainEntity`
- Removed `barrelstrength\sproutseo\fields\ElementMetadata::$values`
- Removed `barrelstrength\sproutseo\fields\ElementMetadata::getSelectedFieldForOptimizedMetadata()`
- Removed `barrelstrength\sproutseo\fields\ElementMetadata::setMetaDetailsValues()`
- Removed `barrelstrength\sproutseo\fields\ElementMetadata::prepareExistingValuesForPage()`
- Removed `barrelstrength\sproutseo\services\ElementMetadata::getElementMetadata()`

[#92]: https://github.com/barrelstrength/craft-sprout-seo/issues/92
[#93]: https://github.com/barrelstrength/craft-sprout-seo/issues/93
[#180]: https://github.com/barrelstrength/craft-sprout-seo/issues/180
[#184]: https://github.com/barrelstrength/craft-sprout-seo/issues/184
[#189]: https://github.com/barrelstrength/craft-sprout-seo/issues/189
[#191]: https://github.com/barrelstrength/craft-sprout-seo/issues/191
[#192]: https://github.com/barrelstrength/craft-sprout-seo/issues/192
[#193]: https://github.com/barrelstrength/craft-sprout-seo/issues/193

## 4.3.3 - 2020-02-10

### Fixed
- Fixed bug where incorrect type existed when retrieving metadata ([#187])
- Fixed bug where appended title value could return null instead of string ([#187])
- Fixed bug where Editable Field did not process address ID value correctly

[#187]: https://github.com/barrelstrength/craft-sprout-seo/issues/187

## 4.3.2 - 2020-02-07

### Fixed
- Fixed error when loading metadata ([#186])

[#186]: https://github.com/barrelstrength/craft-sprout-seo/issues/186

## 4.3.1 - 2020-02-07

### Added
- Updated `barrelstrength/sprout-base-redirects` to v1.3.1

## 4.3.0 - 2020-02-05

### Added
- Added autofocus to Custom Pages URI input field
- Added `sproutbaseredirects/elements/Redirect::pluralDisplayName()`

### Changed
- Updated Element Metadata field to use Tabs layout instead of Matrix-block style layout
- Updated Redirect Element Index to support Craft 3.4
- Updated models to use `defineRules()` method
- Refactored asset management and in-template javascript into assets files
- Updated `barrelstrength/sprout-base-fields` to v1.3.0
- Updated `barrelstrength/sprout-base-redirects` to v1.3.0
- Updated `barrelstrength/sprout-base-sitemaps` to v1.2.0

## 4.2.11 - 2020-01-18

### Changed
- Updated `barrelstrength/sprout-base-sitemaps` requirement v1.1.3

### Fixed
- Fixed minutes value in XML Sitemap output

## 4.2.10 - 2020-01-16

### Updated
- Updated `barrelstrength/sprout-base-fields` to v1.2.2

### Fixed 
- Fixed error in address table migration ([#182])

[#182]: https://github.com/barrelstrength/craft-sprout-seo/issues/182

## 4.2.9 - 2020-01-09

### Updated
- Updated Title Character setting to use auto-suggest field
- Updated Append Title Value setting to use auto-suggest field
- Updated how Website Identity address to be stored as `identity.address`
- Updated Website Identity Address to not use `sproutfields_addresses` table
- Updated address to use `sprout-base-fields/_components/fields/formfields/address/input`
- Updated `barrelstrength\sproutfields\fields\Address::hasContentColumn` to return false. Addresses are now stored only in the `sproutfields_adddresses` table.
- Added `barrelstrength\sproutbasefields\models\Address::getCountryCode()`
- Updated `barrelstrength\sproutbasefields\services\Address::deleteAddressById()` to require address ID
- Improved fallbacks for Address Field's default country and language
- Moved methods from `barrelstrength\sproutbasefields\helpers\AddressHelper` to `barrelstrength\sproutbasefields\services\Address`
- Updated `barrelstrength\sproutbasefields\helpers\AddressHelper` to `barrelstrength\sproutbasefields\services\AddressFormatter`
- Added property `barrelstrength\sproutbasefields\events\OnSaveAddressEvent::$address`
- Deprecated property `barrelstrength\sproutbasefields\events\OnSaveAddressEvent::$model`
- Renamed `barrelstrength\sproutbasefields\services\Address::getAddress()` => `getAddressFromElement()`
- Renamed data attribute `addressid` => `address-id`
- Renamed data attribute `defaultcountrycode` => `default-country-code`
- Renamed data attribute `showcountrydropdown` => `show-country-dropdown`
- Updated and standardized shared logic, validation, and response for fields Email, Phone, and Url 
- Updated dynamic email validation to exclude check for unique email setting
- Updated `barrelstrength/sprout-base-fields` to v1.2.0
- Updated `commerceguys/addressing` to v1.0.6
- Updated `giggsey/libphonenumber-for-php` to v8.11.1

### Fixed
- Fixed display issue with Gibraltar addresses
- Fixed bug where Address input fields did not display in edit modal after Address was cleared

### Removed
- Removed `identity.addressId`
- Removed Address asset bundle from GlobalsAsset, it is included in the Address template
- Removed `barrelstrength\sproutfields\fields\Address::serializeValue()`
- Removed `barrelstrength\sproutbasefields\helpers\AddressHelper`
- Removed `barrelstrength\sproutbasefields\controllers\actionDeleteAddress()`
- Removed `commerceguys/intl`

## 4.2.8 - 2019-12-18

### Changed
- Ensure Sitemap sections default to false when initially created
- Improved error messages when XML Sitemaps are not enabled
- Reorganized assets and build script to support ES6
- Updated barrelstrength/sprout-base-sitemaps requirement v1.1.2

### Fixed
- Fixed bug when updating a Sitemap
- Fixed broken link on plugin settings page ([#11][11-sprout-sitemaps])

[11-sprout-sitemaps]: https://github.com/barrelstrength/craft-sprout-sitemaps/issues/11

## 4.2.7.1 - 2019-12-10

### Changed
- Added method heading in Redirect sources sidebar 
- Updated barrelstrength/sprout-base-redirects requirement to v1.2.2

### Fixed
- Added missing columns to Install migration [#19]
- Fixed `dateLastUsed` column type
- Fixed database prefix errors [#1][1pull-sprout-base-redirects]

[#19]: https://github.com/barrelstrength/craft-sprout-redirects/issues/19
[1pull-sprout-base-redirects]: https://github.com/barrelstrength/craft-sprout-base-redirects/pull/1

## 4.2.6 - 2019-11-22

### Fixed
- Fixed bug where database migrations did not get triggered

## 4.2.5 - 2019-11-22

### Changed
- Updated barrelstrength/sprout-base-redirects requirement to v1.2.1

### Fixed
- Fixed support for database prefixes when finding URLs [#18][18-sprout-base-redirects]

[18-sprout-base-redirects]: https://github.com/barrelstrength/craft-sprout-redirects/issues/18

## 4.2.4 - 2019-11-18

> {tip} This release is a recommended upgrade. Updates include improvements to the redirect workflow including how query strings are handled, managing excluded URLs from tracking, performance improvements around finding and cleaning up 404 Redirects, and several bug fixes include a potential security issue.

### Added
- Added 'Redirect Match Strategy' setting to control how query strings are handled when matching incoming redirects ([#6][6-sprout-redirects], [#16][16-sprout-redirects])
- Added 'Query String Strategy' setting to control if a query string is appended or removed when redirecting to a new URL ([#6][6-sprout-redirects], [#16][16-sprout-redirects])
- Added 'Clean Up Probability' setting to control the frequency that 404 Redirect cleanup tasks are triggered
- Added Last Remote IP Address, Last Referrer, Last User Agent, and Date Last Used fields to Redirect Elements ([#7][7-sprout-redirects], [#10][10-sprout-redirects])
- Added 'Track Remote IP' setting to enable/disable whether IP Address is stored in the database
- Added 'Excluded URL Patterns' setting to filter URL patterns you don't wish to log as 404 Redirects
- Added 'Add to Excluded URLs' Element Action to quickly add one or more 404 Redirects to the 'Excluded URL Patterns' setting

### Changed
- Improved performance when finding a match for an incoming URL
- Added the Redirect 'Data Last Used' field as default table attribute on the Element Index page ([#7sproutredirects])
- Updated Redirect 'RegEx' field to be named 'Match Strategy' with the strategies `Exact Match` and `Regular Expression`
- Improved validation when saving New URLs to avoid an edge case
- Updated barrelstrength/sprout-base-redirects requirement to v1.2.0
- Updated barrelstrength/sprout-base requirement to v5.0.8

### Fixed
- Fixed open redirect vulnerability (thanks to Liam Stein) ([#176])
- Fixes bug where 404s could be matched before active redirects when matching regex URL patterns

[6-sprout-redirects]: https://github.com/barrelstrength/craft-sprout-redirects/issues/6
[7-sprout-redirects]: https://github.com/barrelstrength/craft-sprout-redirects/issues/7
[10-sprout-redirects]: https://github.com/barrelstrength/craft-sprout-redirects/issues/10
[16-sprout-redirects]: https://github.com/barrelstrength/craft-sprout-redirects/issues/16
[#176]: https://github.com/barrelstrength/craft-sprout-seo/issues/176

## 4.2.3 - 2019-09-23

### Changed
- Updated barrelstrength/sprout-base-sitemaps requirement v1.1.1

### Fixed
- Updated `published_time` to use postDate instead of dateCreated ([#169])
- Fixed bug where user may be unable to create new SEO Metadata field using Free Edition ([#172])
- Fixed error for multilingual setups when no groups are activated ([#1][1-pull-sprout-base-sitemaps])

[#169]: https://github.com/barrelstrength/craft-sprout-seo/issues/169 
[#172]: https://github.com/barrelstrength/craft-sprout-seo/issues/172
[1-pull-sprout-base-sitemaps]: https://github.com/barrelstrength/craft-sprout-base-sitemaps/pull/1/files

## 4.2.2 - 2019-08-16

### Changed
- Updated barrelstrength/sprout-base requirement v5.0.7
- Updated barrelstrength/sprout-base-sitemaps requirement v1.1.0

## 4.2.1 - 2019-08-14

### Changed
- Updated barrelstrength/sprout-base-redirects requirement v1.1.2

### Fixed
- Fixed bug where Redirects could be assigned incorrect structureId during migration
- Fixed bug where migration could fail if sproutseo_globals table already exists
- Fixed js console warning when Craft is only configured with a single Site
- Fixed bug where incorrect return type hint was used

## 4.2.0 - 2019-08-06

### Added
- Added ability to sort Redirects by Count
- Added hard delete support for Redirect Elements
- Added logging for Redirects that fail to save

### Changed
- Updated 'All Redirects' Element index listing to only show 301 and 302 Redirects, and exclude 404 Redirects
- Improved performance of Delete 404 task during large cleanup tasks 
- Updated barrelstrength/sprout-base-redirects requirement v1.1.1

### Fixed
- Fixed bug where 404 Redirect cleanup job was not working
- Fixed redirect behavior after deleting Redirect from edit page

## 4.1.3 - 2019-08-07

### Fixed
- Fixed CHANGELOG syntax

## 4.1.2 - 2019-08-07

### Fixed
- Fixed message category bug ([#170])

[#170]: https://github.com/barrelstrength/craft-sprout-seo/issues/170

## 4.1.1 - 2019-07-14

### Changed
- Updated barrelstrength/sprout-base-fields requirement v1.0.9
- Updated barrelstrength/sprout-base-import requirement v1.0.5

## 4.1.0 - 2019-07-09

### Added
- Added support for free, Sprout SEO Lite Edition
- Added support for Craft 3.2 allowAnonymous updates
 
### Changed
- Updated craftcms/cms requirement to v3.2.0
- Updated barrelstrength/sprout-base-fields requirement to 1.0.8
- Updated barrelstrength/sprout-base requirement v5.0.5

### Fixed
- Fixed `Unable to set Metadata::url` error on migration from Craft 2 ([#164])
- Removed find coordinates address helper ([#162])

[#162]: https://github.com/barrelstrength/craft-sprout-seo/issues/162
[#164]: https://github.com/barrelstrength/craft-sprout-seo/issues/164

## 4.0.0 - 2019-06-11

### Changed
- Updated barrelstrength/sprout-base-fields requirement v1.0.7

### Fixed
- Fixed display issue with SEO badge on field labels
- Fixed bug retrieving meta image in some scenarios ([#156])

[#156]: https://github.com/barrelstrength/craft-sprout-seo/issues/156

## 4.0.0-beta.39 - 2019-04-28

### Changed
- Updated barrelstrength/sprout-base-redirects requirement v1.0.10

### Fixed
- Fixed migration but where settings may not exist
- Improved Postgres support

## 4.0.0-beta.38 - 2019-04-24

### Changed
- Updated barrelstrength/sprout-base-redirects requirement v1.0.9
- Updated barrelstrength/sprout-base-uris requirement v1.0.4

### Fixed
- Improved logic for which Elements get re-saved after saving a Field Layout ([#134])
- Fixed bug where Redirect order was not determined by Structure order ([#146])

[#134]: https://github.com/barrelstrength/craft-sprout-seo/issues/134
[#146]: https://github.com/barrelstrength/craft-sprout-seo/issues/146

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
- Fixed bug where Administrative Area Input was not populated correctly ([#85][85-sprout-fields])

[85-sprout-fields]: https://github.com/barrelstrength/craft-sprout-fields/issues/85

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
