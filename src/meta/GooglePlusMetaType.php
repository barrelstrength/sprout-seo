<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\meta;

use barrelstrength\sproutseo\base\MetaType;
use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use yii\base\Exception;

/**
 * Implements all attributes used in Google Plus metadata
 *
 * @property null|string $googlePlusPage
 * @property array       $plusMetaTagData
 */
class GooglePlusMetaType extends MetaType
{
    public static function displayName(): string
    {
        return Craft::t('sprout-seo', 'Google Plus Meta');
    }

    public function getHandle(): string
    {
        return 'googlePlus';
    }

    public function hasMetaDetails(): bool
    {
        return false;
    }

    public function getPlusMetaTagData(): array
    {
        return $this->getGooglePlusPage();
    }

    /**
     * Returns the first Google+ Page found in the Social Profile settings
     *
     * @return mixed|null
     */
    public function getGooglePlusPage()
    {
        $googlePlusUrl = null;

        if (!SproutSeo::$app->optimize->globals->social) {
            return null;
        }

        foreach (SproutSeo::$app->optimize->globals->social as $key => $socialProfile) {
            if ($socialProfile['profileName'] === 'Google+') {
                // Get our first Google+ URL and bail
                $googlePlusUrl = $socialProfile['url'];
                break;
            }
        }

        return $googlePlusUrl;
    }

}
