<?php

/**
 * SEO settings available in craft/config/sprout.php
 *
 * This file does nothing on its own. It provides documentation of the
 * default value for each config setting and provides an example of how to
 * override each setting in 'craft/config/sprout.php`
 *
 * To override default settings, copy the settings you wish to implement to
 * your 'craft/config/sprout.php' config file and make your changes there.
 *
 * Config settings files are multi-environment aware so you can have different
 * settings groups for each environment, just as you do for 'general.php'
 */
return [
    'sprout' => [
        'seo' => [
            // The maximum number of characters to allow for Meta Description fields
            'maxMetaDescriptionLength' => 160,

            // Sprout SEO prepares and outputs all of your metadata in your template
            'enableRenderMetadata' => true,

            // Make a global `metadata` variable available to all of your templates
            'useMetadataVariable' => false,

            // The name of the metadata variable available to your templates
            'metadataVariable' => 'metadata',

            // Display field handle next to the field name in your Element Metadata
            // field settings
            'displayFieldHandles' => false,
        ],
    ],
];
