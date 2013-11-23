<?php
namespace Craft;

class SproutSeo_SproutSeoSitemapRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'sproutseo_sitemap';
    }

    public function defineAttributes()
    {
        return array(
            'sectionId'             => array(AttributeType::String, 'required' => true),
            'url'                   => AttributeType::String,
            'status'                => AttributeType::String,
            'changeFrequency'       => AttributeType::String,
            'priority'              => AttributeType::String
        );
    }

    public function defineIndexes()
    {
        return array(
            array('columns' => array('sectionId'), 'unique' => true),
        );
    }
}
