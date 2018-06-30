<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\elements\actions;

use barrelstrength\sproutseo\enums\RedirectMethods;
use barrelstrength\sproutseo\SproutSeo;
use craft\base\ElementAction;
use Craft;
use craft\elements\db\ElementQueryInterface;

class ChangePermanentMethod extends ElementAction
{
    /**
     * @inheritDoc IComponentType::getName()
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-seo', 'Update Method to 301');
    }

    /**
     * @param ElementQueryInterface $query
     *
     * @return bool|int
     * @throws \yii\base\Exception
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $elementIds = $query->ids();

        // Call updateMethods service
        $response = SproutSeo::$app->redirects->updateRedirectMethod($elementIds, RedirectMethods::Permanent);

        $message = SproutSeo::$app->redirects->getMethodUpdateResponse($response);

        $this->setMessage($message);

        return $response;
    }

    /**
     * @inheritDoc BaseElementAction::defineParams()
     *
     * @return array
     */
    protected function defineParams()
    {
        return [];
    }
}
