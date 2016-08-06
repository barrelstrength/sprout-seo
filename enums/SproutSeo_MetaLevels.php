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
	 * codeOverrideMetaTagModel
	 */
	const Code = 'code';

	/**
	 * Highest Priority
	 * entryOverrideMetaTagModel --High--
	 */
	const Entry = 'entry';
}
