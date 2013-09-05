<?php
namespace Craft;

class OneSeo_OneSeoFallbacksRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'oneseo_fallbacks';
    }

    protected function defineAttributes()
    {
        return array(
            'name'           => array(AttributeType::String, 'required' => true),
            'handle'         => array(AttributeType::String, 'required' => true),
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
        );
    }

    public function defineIndexes()
    {
        return array(
            array('columns' => array('name', 'handle'), 'unique' => true),
        );
    }

    /**
     * Create a new instance of the current class. This allows us to
     * properly unit test our service layer.
     *
     * @return BaseRecord
     */
    public function create()
    {
        $class = get_class($this);
        $record = new $class();

        return $record;
    }
}
