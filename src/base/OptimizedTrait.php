<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\base;

use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\meta\SearchMetaType;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Field;
use craft\fields\Assets;
use PhpScience\TextRank\TextRankFacade;
use PhpScience\TextRank\Tool\StopWords\English;
use PhpScience\TextRank\Tool\StopWords\French;
use PhpScience\TextRank\Tool\StopWords\German;
use PhpScience\TextRank\Tool\StopWords\Italian;
use PhpScience\TextRank\Tool\StopWords\Norwegian;
use PhpScience\TextRank\Tool\StopWords\Spanish;
use yii\base\Exception;
use yii\base\InvalidConfigException;

trait OptimizedTrait
{
    /**
     * @var string|null
     */
    protected $optimizedTitle;

    /**
     * @var string|null
     */
    protected $appendTitleValue;

    /**
     * @var string|null
     */
    protected $optimizedDescription;

    /**
     * @var int|null
     */
    protected $optimizedImage;

    /**
     * @var string|null
     */
    protected $optimizedKeywords;

    /**
     * @var string|null
     */
    protected $canonical;

    /**
     * @return string|null
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function getOptimizedTitle()
    {
        if ($this->optimizedTitle || $this->getRawDataOnly()) {
            return $this->optimizedTitle;
        }

        $element = SproutSeo::$app->optimize->element;
        $elementMetadataField = SproutSeo::$app->optimize->elementMetadataField;

        $optimizedTitleFieldSetting = $elementMetadataField->optimizedTitleField ?? 'manually';

        $title = null;

        switch (true) {
            // Element Title
            case ($optimizedTitleFieldSetting === 'elementTitle'):
                $title = $element->title;
                break;

            // Manual Title
            case ($optimizedTitleFieldSetting === 'manually'):
                $title = $this->optimizedTitle;
                break;

            // Custom Field
            case (is_numeric($optimizedTitleFieldSetting)):
                $title = $this->getSelectedFieldForOptimizedMetadata($optimizedTitleFieldSetting);
                break;

            // Custom Value
            default:
                $title = Craft::$app->getView()->renderObjectTemplate($optimizedTitleFieldSetting, $element);
                break;
        }

        if ($title) {
            return $title ?: null;
        }

        return null;
    }
    /**
     * @param $value
     *
     * @throws Throwable
     */
    public function setOptimizedTitle($value)
    {
        $this->optimizedTitle = $value;
    }

    /**
     * @return string|null
     * @throws Exception
     * @throws Throwable
     */
    public function getOptimizedDescription()
    {
        if ($this->optimizedDescription || $this->getRawDataOnly()) {
            return $this->optimizedDescription;
        }

        $descriptionLength = SproutSeo::$app->settings->getDescriptionLength();
        $description = null;

        $element = SproutSeo::$app->optimize->element;
        $elementMetadataField = SproutSeo::$app->optimize->elementMetadataField;

        $optimizedDescriptionFieldSetting = $elementMetadataField->optimizedDescriptionField ?? 'manually';

        switch (true) {
            // Manual Description
            case ($optimizedDescriptionFieldSetting === 'manually'):
                $description = $this->optimizedDescription ?? null;
                break;

            // Custom Description
            case (is_numeric($optimizedDescriptionFieldSetting)):
                $description = $this->getSelectedFieldForOptimizedMetadata($optimizedDescriptionFieldSetting);
                break;

            // Custom Value
            default:
                $description = Craft::$app->view->renderObjectTemplate($optimizedDescriptionFieldSetting, $element);
                break;
        }
        
        // Just save the first 255 characters (we only output 160...)
        $description = mb_substr(trim($description), 0, 255);

        if ($description) {
            return mb_substr($description, 0, $descriptionLength) ?: null;
        }

        return null;
    }

    /**
     * @param $value
     */
    public function setOptimizedDescription($value)
    {
        $this->optimizedDescription = $value;
    }

    /**
     * @return mixed|string|null
     * @throws Exception
     * @throws Throwable
     */
    public function getOptimizedImage()
    {
        if ($this->optimizedImage || $this->getRawDataOnly()) {
            $imageId = $this->optimizedImage;
            if (is_array($this->optimizedImage)) {
                $imageId = $this->optimizedImage[0] ?? null;
            }
            return $imageId;
        }

        $optimizedImageId = $this->normalizeImageValue($this->optimizedImage);

        if ($optimizedImageId) {
            return $optimizedImageId;
        }

        return SproutSeo::$app->optimize->globals->identity['image'] ?? null;
    }

    /**
     * @param $value
     */
    public function setOptimizedImage($value)
    {
        if (is_array($value)) {
            $this->optimizedImage = $value[0] ?? null;
        } else {
            $this->optimizedImage = $value;
        }
    }

    /**
     * @return string|null
     * @throws InvalidConfigException
     */
    public function getOptimizedKeywords()
    {
        if ($this->optimizedKeywords || $this->getRawDataOnly()) {
            return $this->optimizedKeywords;
        }

        $keywords = $this->optimizedKeywords;

        $element = SproutSeo::$app->optimize->element;
        $elementMetadataField = SproutSeo::$app->optimize->elementMetadataField;

        $optimizedKeywordsFieldSetting = $elementMetadataField->optimizedKeywordsField ?? 'manually';

        switch (true) {
            // Manual Keywords
            case ($optimizedKeywordsFieldSetting === 'manually'):
                $keywords = $this->optimizedKeywords ?? null;
                break;

            // Auto-generate keywords from target field
            case (is_numeric($optimizedKeywordsFieldSetting)):
                $bigKeywords = $this->getSelectedFieldForOptimizedMetadata($optimizedKeywordsFieldSetting);
                $keywords = null;

                if ($bigKeywords) {
                    $textRankApi = new TextRankFacade();

                    $stopWordsMap = [
                        'en' => English::class,
                        'fr' => French::class,
                        'de' => German::class,
                        'it' => Italian::class,
                        'nn' => Norwegian::class,
                        'es' => Spanish::class
                    ];

                    $language = $element->getSite()->language;
                    $languagePrefixArray = explode('-', $language);

                    $stopWordsClass = $stopWordsMap['en'];

                    if (count($languagePrefixArray) > 0) {
                        $languagePrefix = $languagePrefixArray[0];

                        if (isset($stopWordsMap[$languagePrefix])) {
                            $stopWordsClass = $stopWordsMap[$languagePrefix];
                        }
                    }

                    $stopWords = new $stopWordsClass();

                    try {
                        $textRankApi->setStopWords($stopWords);

                        $rankedKeywords = $textRankApi->getOnlyKeyWords($bigKeywords);
                        $fiveKeywords = array_keys(array_slice($rankedKeywords, 0, 5));
                        $keywords = implode(',', $fiveKeywords);
                    } catch (\RuntimeException $e) {
                        // Cannot detect the language of the text, maybe to short.
                        $keywords = null;
                    }
                }

                break;
        }

        return $keywords;
    }
    /**
     * @param null $value
     */
    public function setOptimizedKeywords($value = null)
    {
        $this->optimizedKeywords = $value;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getCanonical()
    {
        if ($this->canonical || $this->getRawDataOnly()) {
            return $this->canonical;
        }

        return OptimizeHelper::getCanonical($this->canonical);
    }

    /**
     * @param $value
     */
    public function setCanonical($value)
    {
        $this->canonical = $value;
    }

    /**
     * @param $fieldId
     *
     * @return null
     */
    public function getSelectedFieldForOptimizedMetadata($fieldId)
    {
        $value = null;

        $element = SproutSeo::$app->optimize->element;

        if (is_numeric($fieldId)) {
            /**
             * @var Field $field
             */
            $field = Craft::$app->fields->getFieldById($fieldId);

            // Does the field exist on the element?
            if ($field && isset($element->{$field->handle})) {
                $elementValue = $element->{$field->handle};
                if (get_class($field) === Assets::class) {
                    $value = isset($elementValue[0]) ? $elementValue[0]->id : null;
                } else {
                    $value = $elementValue;
                }
            }
        }

        return $value;
    }
}
