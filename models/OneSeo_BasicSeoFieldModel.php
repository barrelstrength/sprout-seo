<?php
namespace Craft;

class OneSeo_BasicSeoFieldModel extends BaseModel
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
