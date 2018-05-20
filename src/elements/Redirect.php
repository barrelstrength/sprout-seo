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
use yii\base\ErrorHandler;
use yii\base\Exception;

/**
 * SproutSeo - Redirect element type
 */
class Redirect extends Element
{
    public $oldUrl;

    public $newUrl;

    public $method;

    public $regex = false;

    public $count = 0;

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
     * Returns whether this element type has content.
     *
     * @return bool
     */
    public static function hasContent(): bool
    {
        return false;
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
     * Returns whether this element type has titles.
     *
     * @return bool
     */
    public static function hasTitles(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public static function isLocalized(): bool
    {
        return false;
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
    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function __toString()
    {
        try {
            // @todo - For some reason the Title returns null possible Craft3 bug
            return $this->oldUrl;
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
        }
    }

    /**
     * Returns the element's CP edit URL.
     *
     * @return string|false
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpurl('sprout-seo/redirects/'.$this->id);
    }

    /**
     * @inheritdoc
     *
     * @return RedirectQuery The newly created [[RedirectQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new RedirectQuery(get_called_class());
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
     * Returns the attributes that can be sorted by in table views.
     *
     * @param string|null $source
     *
     * @return array
     */
    public function defineSortableAttributes($source = null)
    {
        return [
            'oldUrl' => Craft::t('sprout-seo', 'Old Url'),
            'newUrl' => Craft::t('sprout-seo', 'New Url'),
            'method' => Craft::t('sprout-seo', 'Method'),
            'count' => Craft::t('sprout-seo', 'Count')
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
     * @return array|false
     * @throws \ReflectionException
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
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
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
        $html .= parent::getEditorHtml($this);

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
     * @param $params
     */
    public function validateMethod($attribute, $params)
    {
        if ($this->enabled && $this->$attribute == RedirectMethods::PageNotFound) {
            $this->addError($attribute, 'Cannot enable a 404 Redirect. Update Redirect method.');
        }
    }

    /**
     * Add validation to unique oldUrl's
     *
     * @param $attribute
     * @param $params
     */
    public function uniqueUrl($attribute, $params)
    {
        $redirect = SproutSeo::$app->redirects->findUrl($this->$attribute);

        if ($redirect) {
            if ($redirect->id != $this->id) {
                $this->addError($attribute, 'This url already exists.');
            }
        }
    }
}
