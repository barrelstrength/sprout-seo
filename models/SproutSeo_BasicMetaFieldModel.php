<?php
namespace Craft;

class SproutSeo_BasicMetaFieldModel extends BaseModel
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
