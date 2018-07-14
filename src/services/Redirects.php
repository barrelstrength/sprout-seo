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
        $redirect = Redirect::find()->id($redirectId);

        if ($siteId){
            $redirect->siteId($siteId);
        }

        return $redirect->one();
    }

    /**
     * Saves a redirect.
     *
     * @param Redirect $redirect
     *
     * @return bool
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     */
    public function saveRedirect(Redirect $redirect)
    {
        $isNewRedirect = !$redirect->id;

        // Event data
        if (!$isNewRedirect) {
            $redirectRecord = RedirectRecord::findOne($redirect->id);

            if (!$redirectRecord) {
                throw new Exception(Craft::t('sprout-seo', 'No redirect exists with the ID “{id}”', ['id' => $redirect->id]));
            }
        }

        $redirect->validate();

        if ($redirect->hasErrors()) {
            return false;
        }

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            $response = Craft::$app->getElements()->saveElement($redirect, false, false);

            if (!$response) {
                $transaction->rollBack();
                return false;
            }

            if ($isNewRedirect) {
                //Set the root structure
                Craft::$app->structures->appendToRoot(SproutSeo::$app->redirects->getStructureId(), $redirect);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Find a regex url using the preg_match php function and replace
     * capture groups if any using the preg_replace php function also check normal urls
     *
     * @param $absoluteUrl
     * @param $uri
     * @return Redirect $redirect
     * @throws \craft\errors\SiteNotFoundException
     */
    public function findUrl($absoluteUrl)
    {
        // $uri => /(*)
        // $uri => /es/(*)
        // $absoluteUrl => http://craft3b.test/(*)
        // $absoluteUrl => http://craft3b.test/es/(*)
        $currentSite = Craft::$app->getSites()->getCurrentSite();
        // http://craft3b.test
        // http://craft3b.test/es
        // http://docs.craft3b.test
        $baseSiteUrl = Craft::getAlias($currentSite->baseUrl);
        $baseSiteUrl = rtrim($baseSiteUrl,"/");

        $baseSiteId = $this->getBaseSiteBySiteId($currentSite->id)['id'] ?? null;

        if (!$baseSiteUrl){
            return null;
        }

        $redirects = Redirect::find()->baseUrlSiteId($baseSiteId)->all();

        $absoluteUrl = urldecode($absoluteUrl);

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

                if (preg_match($oldUrlPattern, $absoluteUrl)) {
                    // Replace capture groups if any
                    $redirect->newUrl = preg_replace($oldUrlPattern, $redirect->newUrl, $url);

                    return $redirect;
                }
            } else {
                if ($baseSiteUrl.$redirect->oldUrl == $absoluteUrl) {
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
            Craft::t('sprout-seo',RedirectMethods::Temporary ) => 'Temporary',
            Craft::t('sprout-seo',  RedirectMethods::PageNotFound) => 'Page Not Found'
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
            $response = Craft::t('sprout-seo', 'Methods updated.');
        } else {
            $response = Craft::t('sprout-seo', 'Failed to update.');
        }

        return $response;
    }

    /**
     * Add Slash
     *
     * @param $url
     *
     * @return string
     */
    public function addSlash($url)
    {
        $slash = '/';
        $external = false;
        //Check if the url is external
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $external = true;
        }

        if ($url[0] != $slash && !$external) {
            $url = $slash.$url;
        }

        return $url;
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

            $this->saveRedirect($redirect);
        } catch (\Exception $e) {
            SproutSeo::error('Unable to log redirect: '.$e->getMessage());
        }

        return true;
    }

    /**
     * Save a 404 redirect and check total404Redirects setting
     *
     * @param $url
     *
     * @return Redirect|null
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     */
    public function save404Redirect($url, $siteId = null)
    {
        $redirect = new Redirect();
        $plugin = Craft::$app->plugins->getPlugin('sprout-seo');
        $seoSettings = $plugin->getSettings();
        $currentSite = Craft::$app->getSites()->getCurrentSite();

        $redirect->oldUrl = $url;
        $redirect->newUrl = '/';
        $redirect->method = RedirectMethods::PageNotFound;
        $redirect->regex = 0;
        $redirect->enabled = 0;
        $redirect->count = 1;
        $redirect->siteId = $siteId;

        if (!SproutSeo::$app->redirects->saveRedirect($redirect)) {
            $redirect = null;
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
