<?php
namespace Craft;

class SproutSeo_RobotsSeoFieldModel extends BaseModel
{

    protected function defineAttributes()
    {
        return array(
            'canonical'   => AttributeType::String,
            'robots'   		=> AttributeType::String
        );
    }

}
