<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\SproutSeo;
use yii\base\Component;
use craft\db\Query;
use yii\web\NotFoundHttpException;
use Craft;
use DateTime;
use craft\helpers\UrlHelper;
use craft\helpers\Template as TemplateHelper;

class Sitemap extends Component
{
    /**
     * Prepares sitemaps for a sitemapindex
     *
     * @param $siteId
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function getSitemapIndex($siteId = null)
    {
        $sitemapIndexItems = [];
        $hasSingles = false;

        $totalElementsPerSitemap = $this->getTotalElementsPerSitemap();

        $urlEnabledSectionTypes = SproutSeo::$app->sectionMetadata->getUrlEnabledSectionTypes($siteId);

        foreach ($urlEnabledSectionTypes as $urlEnabledSectionType) {
            $urlEnabledSectionTypeId = $urlEnabledSectionType->getIdColumnName();

            foreach ($urlEnabledSectionType->urlEnabledSections as $urlEnabledSection) {
                $sectionMetadata = $urlEnabledSection->sectionMetadata;

                if ($sectionMetadata->enabled) {
                    // Get Total Elements for this URL-Enabled Section
                    $query = $urlEnabledSectionType->getElementType()::find();
                    $query->{$urlEnabledSectionTypeId}($urlEnabledSection->id);
                    $query->siteId = $siteId;

                    $totalElements = $query->total();

                    // Is this a Singles Section?
                    $section = $urlEnabledSectionType->getById($urlEnabledSection->id);

                    if (isset($section->type) && $section->type === 'single') {
                        // only add this once
                        if ($hasSingles === false) {
                            $hasSingles = true;

                            // Add the singles at the beginning of our sitemap
                            array_unshift($sitemapIndexItems, UrlHelper::siteUrl().'singles-sitemap.xml');
                        }
                    } else {
                        $totalSitemaps = ceil($totalElements / $totalElementsPerSitemap);

                        // Build Sitemap Index URLs
                        for ($i = 1; $i <= $totalSitemaps; $i++) {
                            $elementTableName = $urlEnabledSectionType->getElementTableName();
                            $sitemapHandle = strtolower($sectionMetadata->handle.'-'.$elementTableName);

                            $sitemapIndexUrl = UrlHelper::siteUrl().$sitemapHandle.'-sitemap'.$i.'.xml';

                            $sitemapIndexItems[] = $sitemapIndexUrl;
                        }
                    }
                }
            }
        }

        // Fetching all Custom Section Metadata defined in Sprout SEO
        $customSectionMetadata = (new Query())
            ->select('id')
            ->from('{{%sproutseo_metadata_sections}}')
            ->where('enabled = 1')
            ->andWhere('uri is not null and isCustom = 1')
            ->count();

        if ($customSectionMetadata > 0) {
            $sitemapIndexItems[] = UrlHelper::siteUrl('custom-sections-sitemap.xml');
        }

        return $sitemapIndexItems;
    }

    /**
     * Prepares urls for a dynamic sitemap
     *
     * @param      $sitemapHandle
     * @param      $pageNumber
     * @param      $siteId
     * @param bool $enableMultilingualSitemaps
     *
     * @return array
     * @throws NotFoundHttpException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getDynamicSitemapElements($sitemapHandle, $pageNumber, $siteId, $enableMultilingualSitemaps = false)
    {
        $urls = [];
        $seoSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();
        // Get the Seo Sites enabled on Sprout SEO
        $sitesIds = Craft::$app->getIsMultiSite() ? $seoSettings->siteSettings : Craft::$app->getSites()->getAllSiteIds();
        $totalElementsPerSitemap = $this->getTotalElementsPerSitemap();
        // We could have sections with the same handle but diferent siteId we just need to check one and then
        // check the siteId in the section table in the getLocalizedSitemapStructure function
        // We need to do it this way because the site could be enabled in the settings but disabled for the section
        $uniqueSitemapHandles = [];

        // Our offset should be zero for the first page
        $offset = ($totalElementsPerSitemap * $pageNumber) - $totalElementsPerSitemap;

        $query = (new Query())
            ->select('*')
            ->from('{{%sproutseo_metadata_sections}}')
            ->where('enabled = 1 and urlEnabledSectionId is not null')
            ->andWhere('siteId = :siteId', [':siteId' => $siteId]);

        if ($sitemapHandle == 'singles-sitemap') {
            $query->andWhere('type = :type', [':type' => 'entries']);
        } else {
            $query->andWhere('handle = :handle', [':handle' => $sitemapHandle]);
        }

        $enabledSitemaps = $query->all();

        if (empty($enabledSitemaps)) {
            throw new NotFoundHttpException();
        }

        // Fetching settings for each enabled section in Sprout SEO
        foreach ($enabledSitemaps as $key => $sitemapSettings) {
            $uniqueId = $sitemapSettings['type'].$sitemapSettings['handle'];

            if (isset($uniqueSitemapHandles[$uniqueId]) && !$enableMultilingualSitemaps) {
                // we already add this section we just need one, lets validate the siteIds in getLocalizedSitemapStructure so go ahead with the next iteration
                continue;
            }
            $uniqueSitemapHandles[$uniqueId] = 1;
            // let's remove empty or disabled sites
            $sitesIds = array_filter($sitesIds);
            foreach ($sitesIds as $siteId) {
                $site = Craft::$app->getSites()->getSiteById((int)$siteId);

                if (!$this->isSiteSectionEnabled($enabledSitemaps, $sitemapSettings['type'], $sitemapSettings['handle'], $siteId) && !$enableMultilingualSitemaps) {
                    // This site is not enabled so don't added to sitemap
                    continue;
                }

                $urlEnabledSectionType = SproutSeo::$app->sectionMetadata->getUrlEnabledSectionTypeByType($sitemapSettings['type']);

                $elements = [];

                if ($urlEnabledSectionType != null) {
                    $urlEnabledSectionTypeId = $urlEnabledSectionType->getIdColumnName();

                    $query = $urlEnabledSectionType->getElementType()::find();

                    $query->{$urlEnabledSectionTypeId}($sitemapSettings['urlEnabledSectionId']);

                    $query->offset($offset);
                    $query->limit($totalElementsPerSitemap);
                    //@todo - enabled is not defined
                    //$query->enabled(true);
                    $query->site($site);

                    $elements = $query->all();
                }

                foreach ($elements as $element) {
                    // @todo - Confirm this is necessary
                    // Confirm that this check/logging is necessary
                    // Catch null URLs, log them, and prevent them from being output to the sitemap
                    if (null === $element->getUrl()) {
                        SproutSeo::info('Element ID '.$element->id.' does not have a URL.');

                        continue;
                    }

                    // Add each location indexed by its id
                    $urls[$element->id][] = [
                        'id' => $element->id,
                        'url' => $element->getUrl(),
                        'locale' => $site->language,
                        'modified' => $element->dateUpdated->format('Y-m-d\Th:m:s\Z'),
                        'priority' => $sitemapSettings['priority'],
                        'changeFrequency' => $sitemapSettings['changeFrequency'],
                    ];
                }
            }
        }

        $urls = $this->getLocalizedSitemapStructure($urls);

        return $urls;
    }

    /**
     * @param $enabledSitemaps
     * @param $type
     * @param $handle
     * @param $siteId
     *
     * @return bool
     */
    private function isSiteSectionEnabled($enabledSitemaps, $type, $handle, $siteId)
    {
        foreach ($enabledSitemaps as $enabledSitemap) {
            if ($enabledSitemap['type'] == $type && $enabledSitemap['handle'] == $handle && $enabledSitemap['siteId'] == $siteId) {
                // All sections are enabled so we don't need to check if enabled
                return true;
            }
        }

        return false;
    }

    /**
     * Returns all Custom Section URLs
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function getCustomSectionUrls()
    {
        $urls = [];

        // Fetch all Custom Section Metadata defined in Sprout SEO
        $customSectionMetadata = (new Query())
            ->select('uri, priority, changeFrequency, dateUpdated')
            ->from('{{%sproutseo_metadata_sections}}')
            ->where('enabled = 1')
            ->andWhere('uri is not null and isCustom = 1')
            ->all();

        foreach ($customSectionMetadata as $customSection) {
            $customSection['url'] = null;
            // Adding each custom location indexed by its URL
            if (!UrlHelper::isAbsoluteUrl($customSection['uri'])) {
                $customSection['url'] = UrlHelper::siteUrl($customSection['uri']);
            }

            $modified = new DateTime($customSection['dateUpdated']);
            $customSection['modified'] = $modified->format('Y-m-d\Th:m:s\Z');
            // @todo - parseEnvironmentString was removed
            $urls[$customSection['uri']] = $customSection;
        }

        $urls = $this->getLocalizedSitemapStructure($urls);

        return $urls;
    }

    /**
     * Returns all URLs for a given sitemap or the rendered sitemap itself
     *
     * @param array|null $options
     *
     * @return \Twig_Markup
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSitemap(array $options = null)
    {
        $urls = [];

        $enabledSitemaps = (new Query())
            ->select('*')
            ->from(['{{%sproutseo_metadata_sections}}'])
            ->where('enabled = 1 and urlEnabledSectionId is not null')
            ->all();

        // Fetching settings for each enabled section in Sprout SEO
        foreach ($enabledSitemaps as $key => $sitemapSettings) {
            // Fetching all enabled locales
            foreach (Craft::$app->getSites()->getAllSites() as $site) {
                $urlEnabledSectionType = SproutSeo::$app->sectionMetadata->getUrlEnabledSectionTypeByType($sitemapSettings['type']);

                $elements = [];

                if ($urlEnabledSectionType != null) {
                    $urlEnabledSectionTypeId = $urlEnabledSectionType->getIdColumnName();

                    $elementType = $urlEnabledSectionType->getElementType();
                    $elementQuery = $elementType::find();

                    $elementQuery->{$urlEnabledSectionTypeId} = $sitemapSettings['urlEnabledSectionId'];

                    $elementQuery->limit = null;
                    $elementQuery->enabledForSite = true;
                    $elementQuery->siteId = $site->id;

                    $elements = $elementQuery->all();
                }
                foreach ($elements as $element) {
                    // @todo - Confirm this is necessary
                    // Confirm that this check/logging is necessary
                    // Catch null URLs, log them, and prevent them from being output to the sitemap
                    if (null === $element->getUrl()) {
                        SproutSeo::info('Element ID '.$element->id.' does not have a URL.');

                        continue;
                    }

                    // Add each location indexed by its id
                    $urls[$element->id][] = [
                        'id' => $element->id,
                        'url' => $element->getUrl(),
                        'locale' => $site->language,
                        'modified' => $element->dateUpdated->format('Y-m-d\Th:m:s\Z'),
                        'priority' => $sitemapSettings['priority'],
                        'changeFrequency' => $sitemapSettings['changeFrequency'],
                    ];
                }
            }
        }

        // Fetching all Custom Section Metadata defined in Sprout SEO
        $customSectionMetadata = (new Query())
            ->select('uri, priority, changeFrequency, dateUpdated')
            ->from(['{{%sproutseo_metadata_sections}}'])
            ->where('enabled = 1')
            ->andWhere('uri is not null and isCustom = 1')
            ->all();

        foreach ($customSectionMetadata as $customSection) {
            // Adding each custom location indexed by its URL
            $modified = new DateTime($customSection['dateUpdated']);
            $customSection['modified'] = $modified->format('Y-m-d\Th:m:s\Z');
            // @todo - parseEnvironmentString was removed
            $urls[$customSection['uri']] = $customSection;
        }

        $urls = $this->getLocalizedSitemapStructure($urls);

        // Rendering the template and passing in received options
        $path = Craft::$app->view->getTemplatesPath();

        Craft::$app->view->setTemplatesPath(Craft::getAlias('@barrelstrength/sproutseo/templates/'));

        $source = Craft::$app->view->renderTemplate('_special/sitemap', [
            'elements' => $urls,
            'options' => is_array($options) ? $options : [],
        ]);

        Craft::$app->view->setTemplatesPath($path);

        return TemplateHelper::raw($source);
    }

    /**
     * Returns an array of localized entries for a sitemap from a set of URLs indexed by id
     *
     * The returned structure is compliant with multiple locale google sitemap spec
     *
     * @param array $stack
     *
     * @return array
     */
    protected function getLocalizedSitemapStructure(array $stack)
    {
        // Defining the containing structure
        $structure = [];

        // Looping through all entries indexed by id
        foreach ($stack as $id => $locations) {
            if (is_string($id)) {
                // Adding a custom location indexed by its URL
                $structure[] = $locations;
            } else {
                // Looping through each element and adding it as primary and creating its alternates
                foreach ($locations as $index => $location) {
                    // Add secondary locations as alternatives to primary
                    if (count($locations) > 1) {
                        $structure[] = array_merge($location, ['alternates' => $locations]);
                    } else {
                        $structure[] = $location;
                    }
                }
            }
        }

        return $structure;
    }

    /**
     * Returns the value for the totalElementsPerSitemap setting. Default is 500.
     *
     * @param int $total
     *
     * @return int
     */
    public function getTotalElementsPerSitemap($total = 500)
    {
        $plugin = Craft::$app->plugins->getPlugin('sprout-seo');
        $seoSettings = $plugin->getSettings();

        if (isset($seoSettings['totalElementsPerSitemap']) && $seoSettings['totalElementsPerSitemap']) {
            $total = $seoSettings['totalElementsPerSitemap'];
        }

        return $total;
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
}
