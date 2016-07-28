<?php
namespace Craft;

/**
 * The Method class is an abstract class that defines the different meta levels available
 */
abstract class SproutSeo_MetaLevels extends BaseEnum
{
	// Constants
	// =========================================================================
	const Global = 'global'; //globalFallbackMetaTagModel --Less--
	const MetaTagsGroup = 'metaTagsGroup'; //metaTagsGroupMetaTagModel --
	const Code = 'code'; //codeOverrideMetaTagModel(***Priority) --
	const Entry = 'entry'; //entryOverrideMetaTagModel --High--
}
