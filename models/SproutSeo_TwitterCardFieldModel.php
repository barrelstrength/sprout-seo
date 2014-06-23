<?php
namespace Craft;

class SproutSeo_TwitterCardFieldModel extends BaseModel
{
    protected function defineAttributes()
    {
        return array(
            'twitterSite'        => AttributeType::String,
            'twitterTitle'       => AttributeType::String,
            'twitterCreator'     => AttributeType::String,
            'twitterDescription' => AttributeType::String
        );
    }
}
