<?php
namespace Craft;

class SproutSeo_TwitterCardSummaryLargeFieldModel extends BaseModel
{
    protected function defineAttributes()
    {
        return array(
            // fields for all twitter cards
            'twitterSite'                         => AttributeType::String,
            'twitterTitle'                        => AttributeType::String,
            'twitterCreator'                      => AttributeType::String,
            'twitterDescription'                  => AttributeType::String,
            // fields specific to the summary large card
            'twitterSummaryLargeImageImageSource' => AttributeType::String
        );
    }
}
