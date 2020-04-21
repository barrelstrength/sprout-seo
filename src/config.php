<?php

/**
 * Sprout SEO config.php
 *
 * This file exists only as a template for the Sprout SEO settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'sprout-seo.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    // The name to display in the control panel in place of the plugin name
    'pluginNameOverride' => 'Sprout SEO',

    // The maximum number of characters to allow for Meta Description fields
    'maxMetaDescriptionLength' => 160,

    // Sprout SEO prepares and outputs all of your metadata in your template
    'enableRenderMetadata' => true,

    // Make a global `metadata` variable available to all of your templates
    'useMetadataVariable' => false,

    // The name of the metadata variable available to your templates
    // 'metadataVariable' => 'metadata'

    // Display field handle next to the field name in your Element Metadata field settings
    'displayFieldHandles' => false
];
