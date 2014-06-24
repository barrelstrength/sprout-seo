<?php
namespace Craft;

class SproutSeo_OpenGraphFieldModel extends BaseModel
{
    protected function defineAttributes()
    {
        return array(
            'ogTitle'        => AttributeType::String,
            'ogType'         => AttributeType::String,
            'ogUrl'          => AttributeType::String,
            'ogImage'        => AttributeType::String,
            'ogSiteName'     => AttributeType::String,
            'ogDescription'  => AttributeType::String,
            'ogAudio'        => AttributeType::String,
            'ogVideo'        => AttributeType::String,
            'ogLocale'       => AttributeType::String,
        );
    }
}
