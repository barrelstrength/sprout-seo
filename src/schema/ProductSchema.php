<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\schema;

use Craft;
use craft\commerce\elements\Variant;

class ProductSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Product';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'Product';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType()
    {
        return false;
    }

    /**
     * @return array|null|void
     * @throws \Exception
     */
    public function addProperties()
    {
        parent::addProperties();

        $elementType = $this->element->getElementType();

        if ($elementType == 'Commerce_Product') {
            $this->addCommerceProductProperties();
        }
    }

    public function addCommerceProductProperties()
    {
        $identity = $this->globals['identity'];
        $element = $this->element;
        $seller = null;

        $websiteIdentity = [
            'Person' => WebsiteIdentityPersonSchema::class,
            'Organization' => WebsiteIdentityOrganizationSchema::class
        ];

        $primaryCurrencyIso = Craft::$app->commerce_paymentCurrencies->getPrimaryPaymentCurrencyIso();

        $offers = [];
        $identityType = $identity['@type'];

        if (isset($websiteIdentity[$identityType])) {
            // Determine if we have an Organization or Person Schema Type
            $schemaModel = $websiteIdentity[$identityType];

            $identitySchema = new $schemaModel();
            $identitySchema->globals = $this->globals;
            $seller = $identitySchema->getSchema();
        }

        /**
         * @var Variant $variant
         */
        foreach ($element->variants as $variant) {

            $offers[$variant->id]['@type'] = 'Offer';
            $offers[$variant->id]['sku'] = $variant->sku;
            $offers[$variant->id]['price'] = $variant->price;
            $offers[$variant->id]['priceCurrency'] = $primaryCurrencyIso;

            if ($variant->unlimitedStock == 1 || $variant->stock > 0) {
                $availability = 'https://schema.org/InStock';
            } else {
                $availability = 'https://schema.org/OutOfStock';
            }

            $offers[$variant->id]['availability'] = $availability;
            $offers[$variant->id]['seller'] = $seller;
        }

        $this->addProperty('offers', array_values($offers));
    }
}