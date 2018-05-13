<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\elements\actions;

use barrelstrength\sproutseo\SproutSeo;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use Craft;

class ChangeTemporaryMethod extends ElementAction
{
    /**
     * @inheritDoc IComponentType::getName()
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-seo', 'Update Method to 302');
    }

    /**
     * @inheritDoc IElementAction::isDestructive()
     *
     * @return bool
     */
    public function isDestructive()
    {
        return false;
    }

    /**
     * @param ElementQueryInterface $query
     *
     * @return bool|int
     * @throws \yii\db\Exception
     */
    public function performAction(ElementQueryInterface $query)
    {
        $elementIds = $query->ids();

        $response = false;

        // Call updateMethods service
        $response = SproutSeo::$app->redirects->updateMethods($elementIds, SproutSeo_RedirectMethods::Temporary);

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
