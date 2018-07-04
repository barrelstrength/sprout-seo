<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\elements;

use barrelstrength\sproutseo\enums\RedirectMethods;
use barrelstrength\sproutseo\SproutSeo;
use barrelstrength\sproutseo\elements\db\RedirectQuery;
use barrelstrength\sproutseo\records\Redirect as RedirectRecord;
use barrelstrength\sproutseo\elements\actions\SetStatus;


use Craft;
use craft\helpers\UrlHelper;
use craft\elements\actions\Delete;
use craft\elements\actions\Edit;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;

use yii\base\Exception;

/**
 * SproutSeo - Redirect element type
 */
class Redirect extends Element
{
    /**
     * @var string
     */
    public $oldUrl;

    /**
     * @var string
     */
    public $newUrl;

    /**
     * @var int
     */
    public $method;

    /**
     * @var bool
     */
    public $regex = false;

    /**
     * @var int
     */
    public $count = 0;

    public $siteId;

    /**
     * Returns the element type name.
     *
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-seo', 'Sprout SEO Redirects');
    }

    /**
     * @inheritDoc IElementType::hasStatuses()
     *
     * @return bool
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function getIsEditable(): bool
    {
        return true;
    }

    /**
     * Use the name as the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->oldUrl) {
            return (string)$this->oldUrl;
        }
        return (string)$this->id ?: static::class;
    }

    /**
     * Returns the element's CP edit URL.
     *
     * @return string|false
     */
    public function getCpEditUrl()
    {
        $site = Craft::$app->sites->getSiteById($this->siteId);
        return UrlHelper::cpUrl('sprout-seo/redirects/'.$site->handle.'/edit/'.$this->id);
    }

    /**
     * @inheritdoc
     *
     * @return RedirectQuery The newly created [[RedirectQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new RedirectQuery(static::class);
    }

    /**
     * Returns the attributes that can be shown/sorted by in table views.
     *
     * @param string|null $source
     *
     * @return array
     */
    public static function defineTableAttributes($source = null): array
    {
        return [
            'oldUrl' => Craft::t('sprout-seo', 'Old Url'),
            'newUrl' => Craft::t('sprout-seo', 'New Url'),
            'method' => Craft::t('sprout-seo', 'Method'),
            'count' => Craft::t('sprout-seo', 'Count'),
            'test' => Craft::t('sprout-seo', 'Test')
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [
            'oldUrl' => Craft::t('sprout-seo', 'Old Url'),
            'newUrl' => Craft::t('sprout-seo', 'New Url'),
            'method' => Craft::t('sprout-seo', 'Method'),
            'elements.dateCreated' => Craft::t('sprout-seo', 'Date Created'),
            'elements.dateUpdated' => Craft::t('sprout-seo', 'Date Updated'),
        ];

        return $attributes;
    }

    /**
     * Returns this element type's sources.
     *
     * @param string|null $context
     *
     * @return array
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout-seo', 'All redirects'),
                'structureId' => SproutSeo::$app->redirects->getStructureId(),
                'structureEditable' => true
            ]
        ];

        $methods = SproutSeo::$app->redirects->getMethods();

        foreach ($methods as $code => $method) {
            $key = 'method:'.$method;
            $sources[] = [
                'key' => $key,
                'label' => $method,
                'criteria' => ['method' => $method],
                'structureId' => SproutSeo::$app->redirects->getStructureId(),
                'structureEditable' => true
            ];
        }

        return $sources;
    }

    /**
     * @inheritDoc
     *
     * @param string|null $source
     *
     * @return array|null
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];
        // Set Status
        $actions[] = SetStatus::class;
        // Delete
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('sprout-seo', 'Are you sure you want to delete the selected redirects?'),
            'successMessage' => Craft::t('sprout-seo', 'Redirects deleted.'),
        ]);
        // Edit
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Edit::class,
            'label' => Craft::t('sprout-seo', 'Edit Redirect'),
        ]);

        //$changePermanentMethod = Craft::$app->elements->getAction('SproutSeo_ChangePermanentMethod');
        //$changeTemporaryMethod = Craft::$app->elements->getAction('SproutSeo_ChangeTemporaryMethod');

        return $actions;
    }

    public static function defineSearchableAttributes(): array
    {
        return ['oldUrl', 'newUrl', 'method', 'regex'];
    }

    /**
     * @param string $attribute
     *
     * @return string
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'test':
                // Send link for testing
                $link = "<a href='{$this->oldUrl}' target='_blank' class='go'>Test</a>";

                if ($this->regex) {
                    $link = ' - ';
                }

                return $link;
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * Returns the HTML for an editor HUD for the given element.
     *
     * @return string
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getEditorHtml(): string
    {
        $methodOptions = SproutSeo::$app->redirects->getMethods();
        // get template
        $html = Craft::$app->view->renderTemplate('sprout-base-seo/redirects/_editor', [
            'redirect' => $this,
            'methodOptions' => $methodOptions
        ]);

        // Everything else
        $html .= parent::getEditorHtml();

        return $html;
    }

    /**
     * Update "oldUrl" and "newUrl" to starts with a "/"
     *
     */
    public function beforeValidate()
    {
        if ($this->newUrl && $this->oldUrl) {
            if (!$this->regex) {
                $this->oldUrl = SproutSeo::$app->redirects->addSlash($this->oldUrl);
            }

            $this->newUrl = SproutSeo::$app->redirects->addSlash($this->newUrl);
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     * @throws Exception if reasons
     */
    public function afterSave(bool $isNew)
    {
        // Get the Redirect record
        if (!$isNew) {
            $record = RedirectRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid Redirect ID: '.$this->id);
            }
        } else {
            $record = new RedirectRecord();
            $record->id = $this->id;
        }
        // Route this through RedirectsService::saveRedirect() so the proper redirect events get fired.
        $record->siteId = $this->siteId;
        $record->oldUrl = $this->oldUrl;
        $record->newUrl = $this->newUrl;
        $record->method = $this->method;
        $record->regex = $this->regex;
        $record->count = $this->count;

        $record->save(false);

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oldUrl', 'newUrl'], 'required'],
            ['method', 'validateMethod'],
            ['oldUrl', 'uniqueUrl']
        ];
    }

    /**
     * Add validation so a user can't save a 404 in "enabled" status
     *
     * @param $attribute
     */
    public function validateMethod($attribute)
    {
        if ($this->enabled && $this->$attribute == RedirectMethods::PageNotFound) {
            $this->addError($attribute, 'Cannot enable a 404 Redirect. Update Redirect method.');
        }
    }

    /**
     * Add validation to unique oldUrl's
     *
     * @param $attribute
     */
    public function uniqueUrl($attribute)
    {
        $redirect = SproutSeo::$app->redirects->findUrl($this->$attribute);

        if ($redirect) {
            if ($redirect->id != $this->id) {
                $this->addError($attribute, 'This url already exists.');
            }
        }
    }
}
