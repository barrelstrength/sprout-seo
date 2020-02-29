<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\meta;

use barrelstrength\sproutseo\base\MetaType;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Field;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Implements all attributes used in search metadata
 */
class SearchMetaType extends MetaType
{
    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $keywords;

    public static function displayName(): string
    {
        return Craft::t('sprout-seo', 'Search');
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes[] = 'title';
        $attributes[] = 'description';
        $attributes[] = 'keywords';

        return $attributes;
    }

    /**
     * @param bool $appendTitle
     *
     * @return string|null
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function getTitle($appendTitle = true)
    {
        $appendTitleString = '';

        if ($appendTitle) {
            $appendTitleString = ' '.$this->getAppendTitleValue();
        }

        // In the CP we only save the raw data
        if ($this->title || Craft::$app->getRequest()->getIsCpRequest()) {
            return trim($this->title.$appendTitleString) ?: null;
        }

        // On the front-end, fall back to optimized values
        return trim($this->optimizedTitle.$appendTitleString) ?: null;
    }

    /**
     * @param $value
     */
    public function setTitle($value)
    {
        $this->title = $value;
    }

    /**
     * @return string|null
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function getAppendTitleValue()
    {
        if ($this->appendTitleValue || Craft::$app->getRequest()->getIsCpRequest()) {
            return $this->appendTitleValue;
        }

        $settings = SproutSeo::$app->optimize->globals->settings;

        $appendTitleValue = null;
        $globalAppendTitleValue = null;
        $appendTitleValueOnHomepage = $settings['appendTitleValueOnHomepage'];
        $seoDivider = $settings['seoDivider'];

        if ($appendTitleValueOnHomepage || Craft::$app->request->getPathInfo()) {
            $globalAppendTitleValue = $settings['appendTitleValue'];

            // @todo - migrate this to just use the {siteName} global that is supported below
            if ($globalAppendTitleValue === 'sitename') {
                $globalAppendTitleValue = Craft::$app->getSystemName();
            }
        }

        if ($globalAppendTitleValue) {
            // Add support for using {divider} and {siteName} in the Sitemap 'Append Meta Title' setting
            $appendTitleValue = Craft::$app->view->renderObjectTemplate($globalAppendTitleValue, [
                'siteName' => Craft::$app->getSystemName(),
                'divider' => $seoDivider
            ]);

            $appendTitleValue = $seoDivider.' '.$appendTitleValue;
        }

        $this->appendTitleValue = $appendTitleValue;

        return $appendTitleValue;
    }

    /**
     * @param $value
     */
    public function setAppendTitleValue($value)
    {
        $this->appendTitleValue = $value;
    }

    public function getDescription()
    {
        $descriptionLength = SproutSeo::$app->settings->getDescriptionLength();

        // In the CP we only save the raw data
        if ($this->description || Craft::$app->getRequest()->getIsCpRequest()) {
            return mb_substr($this->description, 0, $descriptionLength) ?: null;
        }

        // On the front-end, fall back to optimized values
        return mb_substr($this->optimizedDescription, 0, $descriptionLength) ?: null;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getKeywords()
    {
        // In the CP we only save the raw data
        if ($this->keywords || Craft::$app->getRequest()->getIsCpRequest()) {
            return $this->keywords;
        }

        // On the front-end, fall back to optimized values
        return $this->optimizedKeywords;
    }

    public function setKeywords($value)
    {
        $this->keywords = $value;
    }

    public function getHandle(): string
    {
        return 'search';
    }

    public function getIconPath(): string
    {
        return '@sproutbaseicons/search.svg';
    }

    public function showMetaDetailsTab(): bool
    {
        return SproutSeo::$app->optimize->elementMetadataField->showSearchMeta;
    }

    /**
     * @param Field $field
     *
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml(Field $field): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-seo/_components/fields/elementmetadata/blocks/search', [
            'meta' => $this,
            'field' => $field
        ]);
    }
}
