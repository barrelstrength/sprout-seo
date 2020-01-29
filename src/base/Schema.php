<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\base;

use barrelstrength\sproutbasefields\models\Address;
use barrelstrength\sproutbasefields\models\Phone as PhoneModel;
use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\models\Globals;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutseo\schema\ContactPointSchema;
use barrelstrength\sproutseo\schema\GeoSchema;
use barrelstrength\sproutseo\schema\ImageObjectSchema;
use barrelstrength\sproutseo\schema\MainEntityOfPageSchema;
use barrelstrength\sproutseo\schema\PostalAddressSchema;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Element;
use craft\helpers\Template as TemplateHelper;
use craft\helpers\UrlHelper;
use DateTime;

/**
 * Class Schema
 */
abstract class Schema
{
    /**
     * Defines whether to set the Schema's '@context' property
     *
     * @var bool
     */
    public $addContext = false;

    /**
     * Defines whether to set the Schema's 'mainEntityOfPage' property
     *
     * @var bool
     */
    public $isMainEntity = false;

    /**
     * The array of our Structured Data built using the addProperty methods
     * and can later convert this into JsonLD using the getJsonLd() method
     *
     * @var array
     */
    public $structuredData = [];

    /**
     * The Global Metadata values available to use when building the Structured Data
     *
     * @var Globals
     */
    public $globals;

    /**
     * The Matched Element or Primary Element of the schema
     *
     * @var Element
     */
    public $element;

    /**
     * The result after we optimize data from Globals and Element Metadata
     *
     * @var Metadata
     */
    public $prioritizedMetadataModel;

    /**
     * Defines our Schema's '@type' property
     *
     * @var string
     */
    protected $type;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getType();
    }

    /**
     * The Schema context
     *
     * @return string
     */
    final public function getContext()
    {
        return 'http://schema.org/';
    }

    /**
     * Human readable schema name. Admin user will select this schema by this name in the Control Panel.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Schema.org data type: http://schema.org/docs/full.html
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Determine if the Schema should be listed in the Main Entity dropdown.
     *
     * @return bool
     * @example Some schema, such as a PostalAddress may not ever be used as the Main Entity
     *          of the page, but are still be helpful to define to be used within other schema.
     *
     */
    public function isUnlistedSchemaType()
    {
        return false;
    }

    /**
     * Allow Schema definitions to add properties to the the Structured Data array
     * which will be processed and output as Schema
     *
     * @return null
     */
    abstract public function addProperties();

    /**
     * Convert Schema Map attributes to valid JSON-LD
     *
     * This method can return schema data for two different contexts.
     *
     * 1. As JSON-LD for your page
     * 2. As an array for use as a property of another schema
     *
     * By default $this->addContext is set to false, which will make this getSchema
     * method return the schema array without setting the @context property and
     * processing the array of data into JSON-LD. If $this->addContext is set to
     * true, the complete JSON-LD metadata will be returned. It's likely Custom
     * Schema integrations will only need to use the default, as Sprout SEO handles
     * outputting the JSON-LD, but, you never know!
     *
     * @return string|array
     */
    final public function getSchema()
    {
        $this->addProperties();
        $this->getSchemaOverrideType();

        if (empty($this->structuredData)) {
            return null;
        }

        if ($this->addContext) {
            // Add the @context tag for the full context
            $schema['@context'] = $this->getContext();
        }

        if (!empty($this->type)) {
            // If we have a schema override type, use it
            $schema['@type'] = $this->type;
        } else {
            // Grab the type after we process the attributes in case we need to set it dynamically
            $schema['@type'] = $this->getType();
        }

        foreach ($this->structuredData as $key => $value) {
            // Loop through each array attribute and build the schema
            // depending on what type of attribute 'value' is:
            // '@method' vs. 'value' vs. ???
            $schema[$key] = $value;
        }

        if ($this->addContext) {
            // Return the JSON-LD script tag and full context
            $output = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

            $output = '
<script type="application/ld+json">
'.$output.'
</script>';

            return TemplateHelper::raw($output);
        } else {
            // If context has already been established, just return the data
            return $schema;
        }
    }

    /**
     * Get the dynamic Schema Type Override or fallback to the defined type
     *
     * @return string
     */
    public function getSchemaOverrideType()
    {
        if (isset($this->prioritizedMetadataModel) &&
            $this->prioritizedMetadataModel->schemaOverrideTypeId !== null &&
            $this->prioritizedMetadataModel->schemaTypeId === get_class($this)
        ) {
            $this->type = $this->prioritizedMetadataModel->schemaOverrideTypeId;

            return $this->type;
        }

        return $this->getType();
    }

    /**
     * Allow our schema to define what a generic or fake object will look like
     *
     * @return null
     */
    public function getMockData()
    {
        return null;
    }

    /**
     * Add a property to our Structured Data array
     *
     * @param string       $propertyName
     * @param string|array $attributes
     */
    public function addProperty($propertyName, $attributes)
    {
        $this->structuredData[$propertyName] = $attributes;
    }

    /**
     * Remove a property from our Structured Data array
     *
     * @param string $propertyName
     */
    public function removeProperty($propertyName)
    {
        unset($this->structuredData[$propertyName]);
    }

    /**
     * Add a string to our Structured Data array.
     * If the property is not a string, don't add it.
     *
     * @param string $propertyName
     * @param string $string
     */
    public function addText($propertyName, $string)
    {
        if (is_string($string) && $string !== '') {
            $this->structuredData[$propertyName] = $string;
        }
    }

    /**
     * Add a boolean value to our Structured Data array.
     * If the property is not a boolean value, don't add it.
     *
     * @param string $propertyName
     * @param bool   $bool
     */
    public function addBoolean($propertyName, $bool)
    {
        if (is_bool($bool)) {
            $this->structuredData[$propertyName] = $bool;
        }
    }

    /**
     * Add a number to our Structured Data array.
     * If the property is not an integer or float, don't add it.
     *
     * @param string    $propertyName
     * @param int|float $number
     */
    public function addNumber($propertyName, $number)
    {
        if (is_int($number) || is_float($number)) {
            $this->structuredData[$propertyName] = $number;
        }
    }

    /**
     * Add a date to our Structured Data array.
     * If the property is not a date, don't add it.
     *
     * Format the date string into ISO 8601.
     *
     * https://schema.org/Date
     * https://en.wikipedia.org/wiki/ISO_8601
     *
     * @param string           $propertyName
     * @param string|\DateTime $date
     *
     * @throws \Exception
     */
    public function addDate($propertyName, $date)
    {
        $dateTime = $date;

        if (is_string($date)) {
            $dateTime = new DateTime($date);
        }

        $this->structuredData[$propertyName] = $dateTime->format('c');
    }

    /**
     * Add a URL to our Structured Data array.
     * If the property is not a valid URL, don't add it.
     *
     * @param string $propertyName
     * @param string $url
     */
    public function addUrl($propertyName, $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            // Valid URL
            $this->structuredData[$propertyName] = $url;
        } else {
            SproutSeo::info('Schema unable to add value. Value is not a valid URL.');
        }
    }

    /**
     * Add a telephone number to our Structured Data array.
     * If the property is not a string, don't add it.
     *
     * @param string $propertyName
     * @param string $phone
     */
    public function addTelephone($propertyName, $phone)
    {
        if (isset($phone['phone']) && isset($phone['country']) && !empty($phone['phone'])) {
            $phoneModel = new PhoneModel();
            $phoneModel->country = $phone['country'];
            $phoneModel->phone = $phone['phone'];
            $this->structuredData[$propertyName] = $phoneModel->getInternational();
        } else {
            SproutSeo::info('Schema unable to add value. Value is not a valid Phone.');
        }
    }

    /**
     * Add an email to our Structured Data array.
     * If the property is not a valid email, don't add it.
     *
     * Additionally, encode the email as HTML entities so it
     * doesn't appear in the output as plain text.
     *
     * @param string $propertyName
     * @param string $email
     */
    public function addEmail($propertyName, $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $emailString = $this->encodeHtmlEntities('mailto:'.$email);

            // Valid Email
            $this->structuredData[$propertyName] = $emailString;
        } else {
            SproutSeo::info('Schema unable to add value. Value is not a valid Email.');
        }
    }

    /**
     * Add an image to our Structured Data array as a SproutSeo_ImageObjectSchema.
     * If the property is not a valid URL or Asset ID, don't add it.
     *
     * @param string   $propertyName
     * @param int|null $imageId Accepts Image ID or URL
     *
     * @return null
     * @throws \Exception
     */
    public function addImage($propertyName, $imageId = null)
    {
        if ($imageId === null) {
            return null;
        }

        $image = [];

        if (UrlHelper::isFullUrl($imageId)) {
            $meta = $this->prioritizedMetadataModel;

            $image = [
                'url' => $imageId,
                'width' => $meta->ogImageWidth,
                'height' => $meta->ogImageHeight
            ];
        } else if (is_numeric($imageId)) {

            $imageAsset = Craft::$app->assets->getAssetById($imageId);

            if ($imageAsset !== null && $imageAsset->getUrl()) {

                $transform = $this->globals->settings['ogTransform'];

                $image = [
                    'url' => OptimizeHelper::getAssetUrl($imageAsset->id, $transform),
                    'width' => $imageAsset->getWidth(),
                    'height' => $imageAsset->getHeight()
                ];
            } else {
                return null;
            }
        }

        if (count($image)) {
            $imageObjectSchema = new ImageObjectSchema();
            $imageObjectSchema->element = $image;
            $imageObjectSchema->prioritizedMetadataModel = $this->prioritizedMetadataModel;

            $this->structuredData[$propertyName] = $imageObjectSchema->getSchema();
        }

        return null;
    }

    /**
     * Add a list of URLs to our Structured Data array.
     * If the property is not an array of URLs, don't add it.
     *
     * @param array $urls
     */
    public function addSameAs(array $urls = [])
    {
        if (count($urls)) {
            $sameAsList = [];

            foreach ($urls as $url) {
                $sameAsList[] = $url;
            }

            $this->structuredData['sameAs'] = array_values($sameAsList);
        }
    }

    /**
     * Add a list of contacts to our Structured Data array as a SproutSeo_ContactPointSchema
     * If the property is not an array of contacts, don't add it.
     *
     * @param array $contacts
     */
    public function addContactPoints($contacts)
    {
        if ($contacts) {
            $contactPoints = [];

            $contactPointSchema = new ContactPointSchema();

            foreach ($contacts as $contact) {
                $schema = $contactPointSchema;

                $schema->contact = $contact;

                $contactPoints[] = $schema->getSchema();
            }

            $this->structuredData['contactPoint'] = $contactPoints;
        }
    }

    /**
     * Add an Address to our Structured Data array as a SproutSeo_PostalAddressSchema
     * If the address ID is not found in our Globals, don't add it.
     *
     * @param $propertyName
     *
     * @return null
     */
    public function addAddress($propertyName)
    {
        $addressModel = $this->globals->addressModel;

        if ($addressModel === null) {
            return null;
        }

        $address = new PostalAddressSchema();

        if ($addressModel instanceof Address) {
            $address->addressCountry = $addressModel->countryCode;
            $address->addressLocality = $addressModel->locality;
            $address->addressRegion = $addressModel->administrativeAreaCode;
            $address->postalCode = $addressModel->postalCode;
            $address->streetAddress = $addressModel->address1.' '.$addressModel->address2;

            $this->structuredData[$propertyName] = $address->getSchema();
        }

        return null;
    }

    /**
     * Add longitude and latitude to our Structured Data array as a SproutSeo_GeoSchema
     * If longitude or latitude is not provided, don't add it.
     *
     * @param $propertyName
     * @param $latitude
     * @param $longitude
     *
     * @return null
     */
    public function addGeo($propertyName, $latitude, $longitude)
    {
        if (!$latitude || !$longitude) {
            return null;
        }

        $geo = new GeoSchema();

        $geo->latitude = $latitude;
        $geo->longitude = $longitude;

        $this->structuredData[$propertyName] = $geo->getSchema();

        return null;
    }

    /**
     * Add opening hours to our Structured Data array.
     * Opening hours must be in the correct array format.
     *
     * @param array $openingHours
     */
    public function addOpeningHours(array $openingHours)
    {
        $days = [
            0 => 'Su', 1 => 'Mo', 2 => 'Tu', 3 => 'We', 4 => 'Th', 5 => 'Fr', 6 => 'Sa'
        ];

        $index = 0;

        foreach ($openingHours as $key => $value) {
            $openingHours[$index] = $days[$index];

            if (isset($value['open']['time']) && $value['open']['time'] !== '') {
                $time = date('H:i', strtotime($value['open']['time']));
                $openingHours[$index] .= ' '.$time;
            }

            if (isset($value['close']['time']) && $value['close']['time'] !== '') {
                $time = date('H:i', strtotime($value['close']['time']));
                $openingHours[$index] .= '-'.$time;
            }

            // didn't work this day
            if (strlen($openingHours[$index]) === 2) {
                unset($openingHours[$index]);
            }

            $index++;
        }

        if (count(array_values($openingHours))) {
            // Prepare opening hours as one dimensional array
            $this->structuredData['openingHours'] = array_values($openingHours);
        }
    }

    /**
     * Add a Main Entity of Page to our Structured Data array of
     * type WebPage using the canonical URL.
     */
    public function addMainEntityOfPage()
    {
        $meta = $this->prioritizedMetadataModel;

        $mainEntity = new MainEntityOfPageSchema();
        $mainEntity->type = 'WebPage';
        $mainEntity->id = $meta->canonical;

        $mainEntity->prioritizedMetadataModel = $this->prioritizedMetadataModel;

        $this->structuredData['mainEntityOfPage'] = $mainEntity->getSchema();
    }

    /**
     * Returns a string converted to html entities
     * http://goo.gl/LPhtJ
     *
     * @param string $string Value to be encoded
     *
     * @return string Returns a string converted to html entities
     */
    public function encodeHtmlEntities($string)
    {
        $string = mb_convert_encoding($string, 'UTF-32', 'UTF-8');
        $t = unpack('N*', $string);
        $t = array_map(static static function($n) {
            return "&#$n;";
        }, $t);

        return implode('', $t);
    }
}
