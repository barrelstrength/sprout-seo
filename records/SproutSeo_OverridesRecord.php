<?php
namespace Craft;

class SproutSeo_OverridesRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'sproutseo_overrides';
    }

    public function defineAttributes()
    {
        return array(
            'entryId'        => array(AttributeType::Number, 'required' => true),
            'title'          => array(AttributeType::String),
            'description'    => array(AttributeType::String),
            'keywords'       => array(AttributeType::String),
            
            'robots'         => array(AttributeType::String),
            'canonical'      => array(AttributeType::String),

            'region'         => array(AttributeType::String),
            'placename'      => array(AttributeType::String),
            'latitude'       => array(AttributeType::String),
            'longitude'      => array(AttributeType::String),

            'ogTitle'        => array(AttributeType::String),
            'ogType'         => array(AttributeType::String),
            'ogUrl'          => array(AttributeType::String),
            'ogImage'        => array(AttributeType::String),
            'ogSiteName'     => array(AttributeType::String),
            'ogDescription'  => array(AttributeType::String),
            'ogAudio'        => array(AttributeType::String),
            'ogVideo'        => array(AttributeType::String),
            'ogLocale'       => array(AttributeType::String),

            'twitterCard'    => array(AttributeType::String),
            'twitterSite'    => array(AttributeType::String),
            'ogDescription'  => array(AttributeType::String),
            'twitterCreator' => array(AttributeType::String),
            'twitterTitle'   => array(AttributeType::String),
            'twitterDescription' => array(AttributeType::String),
            'twitterImage'   => array(AttributeType::String),
        );
    }

    public function defineIndexes()
    {
        return array(
            array('columns' => array('entryId'), 'unique' => true),
        );
    }
}
