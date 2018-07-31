<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\elements;

use barrelstrength\sproutseo\elements\actions\ChangePermanentMethod;
use barrelstrength\sproutseo\elements\actions\ChangeTemporaryMethod;
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
use yii\base\Model;

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

    public function init()
    {
        $this->setScenario(Model::SCENARIO_DEFAULT);

        parent::init();
    }

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
     * @inheritdoc
     */
    public function getSupportedSites(): array
    {
        // limit to just the one site this element is set to so that we don't propagate when saving
        return [$this->siteId];
    }

    /**
     * Returns the element's CP edit URL.
     *
     * @return null|string
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function getCpEditUrl()
    {
        $url = UrlHelper::cpUrl('sprout-seo/redirects/edit/'.$this->id);

        if (Craft::$app->getIsMultiSite() && $this->siteId != Craft::$app->getSites()->getCurrentSite()->id) {
            $url .= '/'.$this->getSite()->handle;
        }

        return $url;
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

        // Edit
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Edit::class,
            'label' => Craft::t('sprout-seo', 'Edit Redirect'),
        ]);

        // Change Permanent Method
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => ChangePermanentMethod::class,
            'successMessage' => Craft::t('sprout-seo', 'Redirects updated.'),
        ]);

        // Change Temporary Method
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => ChangeTemporaryMethod::class,
            'successMessage' => Craft::t('sprout-seo', 'Redirects updated.'),
        ]);

        // Delete
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('sprout-seo', 'Are you sure you want to delete the selected redirects?'),
            'successMessage' => Craft::t('sprout-seo', 'Redirects deleted.'),
        ]);

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
            case 'newUrl':

                return $this->newUrl === null ? '/' : $this->newUrl;

            case 'test':
                // no link for regex
                if ($this->regex) {
                    return ' - ';
                }
                // Send link for testing
                $site = Craft::$app->getSites()->getSiteById($this->siteId);
                $baseUrl = Craft::getAlias($site->baseUrl);
                $oldUrl = $baseUrl.$this->oldUrl;
                $link = "<a href='{$oldUrl}' target='_blank' class='go'>Test</a>";

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
        if ($this->oldUrl && !$this->regex) {
            $this->oldUrl = SproutSeo::$app->redirects->removeSlash($this->oldUrl);
        }

        if ($this->newUrl) {
            $this->newUrl = SproutSeo::$app->redirects->removeSlash($this->newUrl);

            // In case the value was a backslash: /
            if (empty($this->newUrl)) {
                $this->newUrl = null;
            }
        } else {
            $this->newUrl = null;
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

        $record->oldUrl = $this->oldUrl;
        $record->newUrl = $this->newUrl;
        $record->method = $this->method;
        $record->regex = $this->regex;
        $record->count = $this->count;

        $record->save(false);

        if ($isNew) {
            //Set the root structure
            Craft::$app->structures->appendToRoot(SproutSeo::$app->redirects->getStructureId(), $this);
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oldUrl'], 'required'],
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

    public function getAbsoluteNewUrl()
    {
        $baseUrl = Craft::getAlias($this->getSite()->baseUrl);

        // @todo - remove ltrim after we updating saving to skip beginning slashes
        $path = ltrim($this->newUrl, '/');

        return $baseUrl.$path;
    }

    /**
     * Add validation to unique oldUrl's
     *
     * @param $attribute
     */
    public function uniqueUrl($attribute)
    {
        $redirect = self::find()
            ->siteId($this->siteId)
            ->where(['oldUrl' => $this->$attribute])
            ->one();

        if ($redirect) {
            if ($redirect->id != $this->id) {
                $this->addError($attribute, 'This url already exists.');
            }
        }
    }
}
