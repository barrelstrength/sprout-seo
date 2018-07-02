<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;



use barrelstrength\sproutseo\SproutSeo;
use craft\web\Controller;

use Craft;


/**
 * Class XmlSitemapController
 */
class XmlSitemapController extends Controller
{
    /**
     * @inheritdoc
     */
    public $allowAnonymous = ['renderXmlSitemap'];

    /**
     * Generates an XML sitemapindex or sitemap
     *
     * @param null     $sitemapKey
     * @param int|null $pageNumber
     *
     * @return \yii\web\Response
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\Exception
     */
    public function actionRenderXmlSitemap($sitemapKey = null, int $pageNumber = null)
    {
        $currentSite = Craft::$app->sites->getCurrentSite();
        $siteId = $currentSite->id;

        // Prepare Sitemap Index content
        $sitemapIndexItems = [];
        $elements = [];

        switch ($sitemapKey) {
            // Generate Sitemap Index
            case '':
                $sitemapIndexItems = SproutSeo::$app->xmlSitemap->getSitemapIndex($siteId);
                break;

            // Display Singles Sitemap
            case 'singles':
                $elements = SproutSeo::$app->xmlSitemap->getDynamicSitemapElements('singles', $pageNumber, $siteId);
                break;

            // Display Custom Section Sitemap
            case 'custom-pages':
                $elements = SproutSeo::$app->xmlSitemap->getCustomSectionUrls($siteId);
                break;

            default:
                $elements = SproutSeo::$app->xmlSitemap->getDynamicSitemapElements($sitemapKey, $pageNumber, $siteId);
        }

        $headers = Craft::$app->getResponse()->getHeaders();
        $headers->set('Content-Type', 'text/xml');

        $templatePath = Craft::getAlias('@sproutbase/app/seo/templates/');
        Craft::$app->view->setTemplatesPath($templatePath);

        // Render a specific sitemap
        if ($sitemapKey) {
            return $this->renderTemplate('_components/sitemaps/sitemap', [
                'elements' => $elements
            ]);
        }

        // Render the sitemapindex if we no specific sitemap is defined
        return $this->renderTemplate('_components/sitemaps/sitemapindex', [
            'sitemapIndexItems' => $sitemapIndexItems
        ]);
    }
}
