<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\elements\Redirect;
use barrelstrength\sproutseo\enums\RedirectMethods;
use barrelstrength\sproutseo\SproutSeo;
use barrelstrength\sproutseo\jobs\Delete404;
use barrelstrength\sproutseo\records\Redirect as RedirectRecord;
use barrelstrength\sproutseo\models\Settings;

use Craft;
use craft\db\Query;
use craft\models\Site;
use yii\base\Component;


use yii\base\Exception;


class Redirects extends Component
{
    /**
     * Returns a Redirect by its ID.
     *
     * @param          $redirectId
     * @param int|null $siteId
     *
     * @return Redirect|null
     */
    public function getRedirectById($redirectId, int $siteId = null)
    {
        $redirect = Redirect::find()
            ->id($redirectId);

        if ($siteId) {
            $redirect->siteId($siteId);
        }

        return $redirect->one();
    }

    /**
     * Find a regex url using the preg_match php function and replace
     * capture groups if any using the preg_replace php function also check normal urls
     *
     * Example: $absoluteUrl
     *   https://website.com
     *   https://website.com/es
     *   https://es.website.com
     *
     * @param      $absoluteUrl
     * @param Site $site
     *
     * @return Redirect|null
     */
    public function findUrl($absoluteUrl, $site)
    {
        $absoluteUrl = urldecode($absoluteUrl);
        $baseSiteUrl = Craft::getAlias($site->baseUrl);

        $redirects = Redirect::find()
            ->siteId($site->id)
            ->all();

        if (!$redirects) {
            return null;
        }

        /**
         * @var Redirect $redirect
         */
        foreach ($redirects as $redirect) {
            if ($redirect->regex) {
                // Use backticks as delimiters as they are invalid characters for URLs
                $oldUrlPattern = '`'.$redirect->oldUrl.'`';

                $currentPath = preg_replace('`^'.$baseSiteUrl.'`', '', $absoluteUrl);

                if (preg_match($oldUrlPattern, $currentPath)) {
                    // Replace capture groups if any
                    $redirect->newUrl = preg_replace($oldUrlPattern, $redirect->newUrl, $currentPath);
                    return $redirect;
                }
            } else {
                if ($baseSiteUrl.$redirect->oldUrl === $absoluteUrl) {
                    return $redirect;
                }
            }
        }

        return null;
    }

    /**
     * Get Redirect methods
     *
     * @return array
     */
    public function getMethods()
    {
        $methods = [
            Craft::t('sprout-seo', RedirectMethods::Permanent) => 'Permanent',
            Craft::t('sprout-seo', RedirectMethods::Temporary) => 'Temporary',
            Craft::t('sprout-seo', RedirectMethods::PageNotFound) => 'Page Not Found'
        ];
        $newMethods = [];

        foreach ($methods as $key => $value) {
            $value = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value);
            $newMethods[$key] = $key.' - '.$value;
        }

        return $newMethods;
    }

    /**
     * Update the current method in the record
     *
     * @param $ids
     * @param $newMethod
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function updateRedirectMethod($ids, $newMethod)
    {
        $response = Craft::$app->db->createCommand()->update(
            '{{%sproutseo_redirects}}',
            ['method' => $newMethod],
            ['in', 'id', $ids]
        )->execute();

        return $response;
    }

    /**
     * Get Method Update Response from elementaction
     *
     * @param bool
     *
     * @return string
     */
    public function getMethodUpdateResponse($status)
    {
        $response = null;
        if ($status) {
            $response = Craft::t('sprout-seo', 'Redirect method updated.');
        } else {
            $response = Craft::t('sprout-seo', 'Unable to update Redirect method.');
        }

        return $response;
    }

    /**
     * Remove Slash from URI
     *
     * @param string $uri
     *
     * @return array
     */
    public function removeSlash($uri)
    {
        $slash = '/';

        if (isset($uri[0]) && $uri[0] == $slash) {
            $uri = ltrim($uri, $slash);
        }

        return $uri;
    }

    /**
     * This service allows find the structure id from the sprout seo settings
     *
     * @return int
     */
    public function getStructureId()
    {
        /**
         * @var Settings $pluginSettings
         */
        /** @noinspection OneTimeUseVariablesInspection */
        $pluginSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();

        return $pluginSettings->structureId;
    }

    /**
     * Logs a redirect when a match is found
     *
     * @todo - escape this log data when we output it
     *         https://stackoverflow.com/questions/13199095/escaping-variables
     *
     * @param $redirectId int
     *
     * @return bool
     * @throws \Throwable
     */
    public function logRedirect($redirectId)
    {
        $log = [];

        try {
            $log['redirectId'] = $redirectId;
            $log['referralURL'] = Craft::$app->request->getReferrer();
            $log['ipAddress'] = $_SERVER['REMOTE_ADDR'];
            $log['dateCreated'] = date('Y-m-d h:m:s');

            SproutSeo::warning('404 - Page Not Found: '.json_encode($log));

            $redirect = $this->getRedirectById($redirectId);
            ++$redirect->count;

            Craft::$app->elements->saveElement($redirect, true, false);

        } catch (\Exception $e) {
            SproutSeo::error('Unable to log redirect: '.$e->getMessage());
        }

        return true;
    }

    /**
     * Save a 404 redirect and check total404Redirects setting
     *
     * @param      $absoluteUrl
     * @param Site $site
     *
     * @return Redirect|null
     * @throws Exception
     * @throws \Throwable
     */
    public function save404Redirect($absoluteUrl, $site)
    {
        $redirect = new Redirect();
        $seoSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();

        $baseUrl = Craft::getAlias($site->baseUrl);

        $baseUrlMatch = mb_substr($absoluteUrl, 0, strlen($baseUrl)) === $baseUrl;

        if (!$baseUrlMatch) {
            return null;
        }

        // Strip the base URL from our Absolute URL
        // We need to do this because the Base URL can contain
        // subfolders that are included in the path and we only
        // want to store the path value that doesn't include
        // the Base URL
        $uri = substr($absoluteUrl, strlen($baseUrl));

        $redirect->oldUrl = $uri;
        $redirect->newUrl = '/';
        $redirect->method = RedirectMethods::PageNotFound;
        $redirect->regex = 0;
        $redirect->enabled = 0;
        $redirect->count = 0;
        $redirect->siteId = $site->id;

        if (!Craft::$app->elements->saveElement($redirect, true, false)) {
            return null;
        }

        // delete new one
        if (isset($seoSettings['total404Redirects']) && $seoSettings['total404Redirects'] && $redirect) {
            $count = Redirect::find()->where('method=:method and sproutseo_redirects.id!=:redirectId', [
                ':method' => RedirectMethods::PageNotFound,
                ':redirectId' => $redirect->id
            ])
                ->count();

            if ($count >= $seoSettings['total404Redirects']) {
                $totalToDelete = $count - $seoSettings['total404Redirects'];

                $delete404 = new Delete404();
                $delete404->totalToDelete = $totalToDelete <= 0 ? 1 : $totalToDelete + 1;
                $delete404->redirectIdToExclude = $redirect->id ?? null;

                // Call the delete redirects job
                Craft::$app->queue->push($delete404);
            }
        }

        return $redirect;
    }
}
