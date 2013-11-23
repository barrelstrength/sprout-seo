<?php
namespace Craft;

class SproutSeo_BasicSeoFieldModel extends BaseModel
{

    protected function defineAttributes()
    {
        return array(
            'title'              => AttributeType::String,
            'description'        => AttributeType::String,
            'keywords'           => AttributeType::String
        );
    }

}
