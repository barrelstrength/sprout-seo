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
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 * Implements all attributes used in robots metadata
 */
class RobotsMetaType extends MetaType
{
//    /**
//     * @var string|null
//     */
//    protected $canonical;

    /**
     * @var string|null
     */
    protected $robots;

    public static function displayName(): string
    {
        return Craft::t('sprout-seo', 'Robots');
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes[] = 'canonical';
        $attributes[] = 'robots';

        return $attributes;
    }

    public function getRobots()
    {
        if ($this->robots || Craft::$app->getRequest()->getIsCpRequest()) {
            return $this->robots;
        }

        return SproutSeo::$app->optimize->globals['robots'] ?? null;
    }

    public function setRobots($value)
    {
        $this->robots = SproutSeo::$app->optimize->prepareRobotsMetadataValue($value);
    }

    public function getHandle(): string
    {
        return 'robots';
    }

    public function getIconPath(): string
    {
        return '@sproutbaseicons/search-minus.svg';
    }

    /**
     * @param Field $field
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml(Field $field): string
    {
        $robotsNamespace = $field->handle.'[metadata][robots]';
        $robots = SproutSeo::$app->optimize->prepareRobotsMetadataForSettings($this->robots);

        return Craft::$app->getView()->renderTemplate('sprout-seo/_components/fields/elementmetadata/blocks/robots', [
            'meta' => $this,
            'field' => $field,
            'robotsNamespace' => $robotsNamespace,
            'robots' => $robots
        ]);
    }

    public function showMetaDetailsTab(): bool
    {
        return SproutSeo::$app->optimize->elementMetadataField->showRobots;
    }
}
