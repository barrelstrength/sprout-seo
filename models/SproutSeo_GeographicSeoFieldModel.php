<?php
namespace Craft;

class SproutSeo_GeographicSeoFieldModel extends BaseModel
{

    protected function defineAttributes()
    {
        return array(
            'region'      => AttributeType::String,
            'placename'   => AttributeType::String,
            'latitude'    => AttributeType::String,
            'longitude'   => AttributeType::String
        );
    }

}
