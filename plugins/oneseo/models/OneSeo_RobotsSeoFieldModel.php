<?php
namespace Craft;

class OneSeo_RobotsSeoFieldModel extends BaseModel
{

    protected function defineAttributes()
    {
        return array(
            'canonical'   => AttributeType::String,
            'robots'   		=> AttributeType::String
        );
    }

}
