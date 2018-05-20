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
use barrelstrength\sproutseo\enums\RedirectStatuses;
use barrelstrength\sproutseo\enums\RedirectMethods;
use barrelstrength\sproutseo\validators\StatusValidator;

use Craft;

class SetStatus extends ElementAction
{
    // Public Methods
    // =========================================================================
    /**
     * @var string|null The status elements should be set to
     */
    public $status;

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getTriggerHtml()
    {
        return Craft::$app->view->renderTemplate('sprout-base-seo/_components/elementactions/setstatus');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['status'], 'required'];
        $rules[] = [['status'], StatusValidator::class];

        return $rules;
    }

    /**
     * @inheritdoc
     *
     * @throws \yii\db\Exception
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $status = $this->status;

        // False by default
        $enable = 0;

        switch ($status) {
            case RedirectStatuses::ON:
                $enable = '1';
                break;
            case RedirectStatuses::OFF:
                $enable = '0';
                break;
        }

        $elementIds = $query->ids();

        foreach ($elementIds as $key => $redirectId) {
            $redirect = SproutSeo::$app->redirects->getRedirectById($redirectId);

            if ($redirect->method == RedirectMethods::PageNotFound) {
                $this->setMessage(Craft::t('sprout-seo', 'Unable to enable 404 method'));
                return false;
            }
        }

        // Update their statuses
        Craft::$app->db->createCommand()->update(
            '{{%elements}}',
            ['enabled' => $enable],
            ['in', 'id', $elementIds]
        )->execute();

        if ($status == RedirectStatuses::ON) {
            // Enable their locale as well
            Craft::$app->db->createCommand()->update(
                '{{%elements_sites}}',
                ['enabled' => $enable],
                ['and', ['in', 'elementId', $elementIds], 'siteId = :siteId'],
                [':siteId' => $query->siteId]
            )->execute();
        }

        // Clear their template caches
        Craft::$app->templateCaches->deleteCachesByElementId($elementIds);

        $this->setMessage(Craft::t('sprout-seo', 'Statuses updated.'));

        return true;
    }
}
