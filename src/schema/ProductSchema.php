<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\schema;

use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Product;


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

        if (get_class($this->element) === Product::class) {
            $this->addProductProperties();
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function addProductProperties()
    {
        $identity = $this->globals['identity'];

        /**
         * @var Product $element
         */
        $element = $this->element;
        $seller = null;

        $websiteIdentity = [
            'Person' => WebsiteIdentityPersonSchema::class,
            'Organization' => WebsiteIdentityOrganizationSchema::class
        ];

        $primaryCurrencyIso = Commerce::getInstance()->getPaymentCurrencies()->getPrimaryPaymentCurrencyIso();

        $offers = [];
        $identityType = $identity['@type'];

        if (isset($websiteIdentity[$identityType])) {
            // Determine if we have an Organization or Person Schema Type
            $schemaModel = $websiteIdentity[$identityType];

            /**
             * @var WebsiteIdentityOrganizationSchema|WebsiteIdentityPersonSchema $identitySchema
             */
            $identitySchema = new $schemaModel();
            $identitySchema->globals = $this->globals;
            $seller = $identitySchema->getSchema();
        }

        foreach ($element->getVariants() as $variant) {

            $offers[$variant->id]['@type'] = 'Offer';
            $offers[$variant->id]['sku'] = $variant->sku;
            $offers[$variant->id]['price'] = $variant->price;
            $offers[$variant->id]['priceCurrency'] = $primaryCurrencyIso;

            if ($variant->hasUnlimitedStock == 1 || $variant->stock > 0) {
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