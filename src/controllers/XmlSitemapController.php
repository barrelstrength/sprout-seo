<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;

use barrelstrength\sproutseo\helpers\SproutSeoOptimizeHelper;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutseo\models\SitemapSection;
use barrelstrength\sproutseo\SproutSeo;
use craft\web\Controller;
use craft\elements\Asset;
use Craft;

use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;


/**
 * Class XmlSitemapController
 */
class XmlSitemapController extends Controller
{
    public $allowAnonymous = ['renderXmlSitemap'];

    /**
     * Generates the proper xml
     *
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionRenderXmlSitemap()
    {
        // Get URL and remove .xml extension
        $url = Craft::$app->request->getFullPath();

        $settings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();
        $enableMultilingualSitemaps = false;

        $currentSite = Craft::$app->sites->getCurrentSite();
        $siteId = $currentSite->id;

        if (Craft::$app->getIsMultiSite()) {
            if ($settings->enableMultilingualSitemaps) {
                $enableMultilingualSitemaps = true;
            }
        }

        $sitemapSlug = substr($url, 0, -4);
        $segments = explode('-', $sitemapSlug);
        $sitemapSegment = array_pop($segments);

        // Extract the page number, if we have one.
        preg_match('/\d+/', $sitemapSegment, $match);
        $pageNumber = $match[0] ?? null;

        // Prepare Sitemap Index content
        $sitemapIndexItems = [];
        $elements = [];

        switch ($sitemapSlug) {
            // Generate Sitemap Index
            case 'sitemap':
                $sitemapIndexItems = SproutSeo::$app->xmlSitemap->getSitemapIndex($siteId);
                break;

            // Display Singles Sitemap
            case 'singles-sitemap':
                $elements = SproutSeo::$app->xmlSitemap->getDynamicSitemapElements('singles-sitemap', $pageNumber, $siteId, $enableMultilingualSitemaps);
                break;

            // Display Custom Section Sitemap
            case 'custom-sections-sitemap':
                $elements = SproutSeo::$app->xmlSitemap->getCustomSectionUrls();
                break;

            default:
                $sitemapHandle = $segments[1].':'.$segments[0];
                $elements = SproutSeo::$app->xmlSitemap->getDynamicSitemapElements($sitemapHandle, $pageNumber, $siteId, $enableMultilingualSitemaps);
        }

        header('Content-Type: text/xml');

        $templatePath = Craft::getAlias('@sproutbase/app/seo/templates/');
        Craft::$app->view->setTemplatesPath($templatePath);

        // sitemap index by default
        $template = '_special/sitemapindex';
        $params = [
            'sitemapIndexItems' => $sitemapIndexItems
        ];

        if ($sitemapSlug !== 'sitemap') {
            $template = '_special/sitemap-dynamic';
            $params = [
                'elements' => $elements
            ];
        }

        return $this->renderTemplate($template, $params);
    }
}
