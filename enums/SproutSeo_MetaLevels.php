<?php
namespace Craft;

/**
 * The Method class is an abstract class that defines the different meta levels available
 */
abstract class SproutSeo_MetaLevels extends BaseEnum
{
	// Constants
	// =========================================================================

	/**
	 * Lowest Priority
	 * globalFallbackMetaTagModel()
	 */
	const Global = 'global';

	/**
	 * metaTagsGroupMetaTagModel
	 */
	const MetaTagsGroup = 'metaTagsGroup';

	/**
	 * Highest Priority
	 * entryOverrideMetaTagModel
	 */
	const Entry = 'entry';

	/**
	 * codeOverrideMetaTagModel  --High--
	 */
	const Code = 'code';
}
