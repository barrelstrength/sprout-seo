<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\models\Settings;
use barrelstrength\sproutseo\models\SitemapSection;
use barrelstrength\sproutseo\sectiontypes\Entry;
use barrelstrength\sproutseo\sectiontypes\NoSection;
use barrelstrength\sproutseo\SproutSeo;
use craft\elements\db\ElementQuery;
use yii\base\Component;
use craft\db\Query;
use Craft;
use DateTime;
use craft\helpers\UrlHelper;

class XmlSitemap extends Component
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
        $sitemapIndexPages = [];
        $hasSingles = false;

        $totalElementsPerSitemap = $this->getTotalElementsPerSitemap();

        $urlEnabledSectionTypes = SproutSeo::$app->sitemaps->getUrlEnabledSectionTypesForSitemaps($siteId);

        foreach ($urlEnabledSectionTypes as $urlEnabledSectionType) {
            $urlEnabledSectionTypeId = $urlEnabledSectionType->getElementIdColumnName();

            foreach ($urlEnabledSectionType->urlEnabledSections as $urlEnabledSection) {
                $sitemapSection = $urlEnabledSection->sitemapSection;

                if ($sitemapSection->enabled) {
                    /**
                     * Get Total Elements for this URL-Enabled Section
                     *
                     * @var ElementQuery $query
                     */
                    $query = $urlEnabledSectionType->getElementType()::find();
                    $query->{$urlEnabledSectionTypeId}($urlEnabledSection->id);
                    $query->siteId($siteId);

                    $totalElements = $query->count();

                    // Is this a Singles Section?
                    $section = $urlEnabledSectionType->getById($urlEnabledSection->id);

                    if (isset($section->type) && $section->type === 'single') {
                        // only add this once
                        if ($hasSingles === false) {
                            $hasSingles = true;

                            // Add the singles at the beginning of our sitemap
                            array_unshift($sitemapIndexPages, UrlHelper::siteUrl().'sitemap-singles.xml');
                        }
                    } else {
                        $totalSitemaps = ceil($totalElements / $totalElementsPerSitemap);

                        $devMode = Craft::$app->config->getGeneral()->devMode;
                        $debugString = '';

                        if ($devMode) {
                            $debugString =
                                '?devMode=true'
                                .'&siteId='.$sitemapSection->siteId
                                .'&urlEnabledSectionId='.$sitemapSection->urlEnabledSectionId
                                .'&sitemapSectionId='.$sitemapSection->id
                                .'&type='.$sitemapSection->type
                                .'&handle='.$sitemapSection->handle;
                        }

                        // Build Sitemap Index URLs
                        for ($i = 1; $i <= $totalSitemaps; $i++) {

                            $sitemapIndexUrl = UrlHelper::siteUrl().'sitemap-'.$sitemapSection->uniqueKey.'-'.$i.'.xml'.$debugString;
                            $sitemapIndexPages[] = $sitemapIndexUrl;
                        }
                    }
                }
            }
        }

        // Fetching all Custom Sitemap defined in Sprout SEO
        $customSitemapSections = (new Query())
            ->select('id')
            ->from('{{%sproutseo_sitemaps}}')
            ->where('enabled = 1')
            ->andWhere('type=:type', [':type' => NoSection::class])
            ->andWhere('uri is not null')
            ->count();

        if ($customSitemapSections > 0) {
            $sitemapIndexPages[] = UrlHelper::siteUrl('sitemap-custom-pages.xml');
        }

        return $sitemapIndexPages;
    }

    /**
     * Prepares urls for a dynamic sitemap
     *
     * @param $sitemapKey
     * @param $pageNumber
     * @param $siteId
     *
     * @return array
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\Exception
     */
    public function getDynamicSitemapElements($sitemapKey, $pageNumber, $siteId)
    {
        $urls = [];

        $totalElementsPerSitemap = $this->getTotalElementsPerSitemap();

        $currentSitemapSites = $this->getCurrentSitemapSites();

        // Our offset should be zero for the first page
        $offset = ($totalElementsPerSitemap * $pageNumber) - $totalElementsPerSitemap;

        $enabledSitemapSections = $this->getEnabledSitemapSections($sitemapKey, $siteId);

        foreach ($enabledSitemapSections as $sitemapSection) {

            $urlEnabledSectionType = SproutSeo::$app->sitemaps->getUrlEnabledSectionTypeByType($sitemapSection->type);
            $sectionModel = $urlEnabledSectionType->getById($sitemapSection->urlEnabledSectionId);

            foreach ($currentSitemapSites as $site) {

                $globalMetadata = SproutSeo::$app->globalMetadata->getGlobalMetadata($site);

                $elementMetadataFieldHandle = null;
                $elements = [];

                if ($urlEnabledSectionType !== null) {

                    $query = $urlEnabledSectionType->getElementType()::find();

                    // Example: $query->sectionId(123)
                    $urlEnabledSectionColumnName = $urlEnabledSectionType->getElementIdColumnName();
                    $query->{$urlEnabledSectionColumnName}($sitemapSection->urlEnabledSectionId);

                    $query->offset($offset);
                    $query->limit($totalElementsPerSitemap);
                    $query->site($site);
                    $query->enabledForSite(true);

                    if ($urlEnabledSectionType->getElementLiveStatus()) {
                        $query->status($urlEnabledSectionType->getElementLiveStatus());
                    }

                    if ($sitemapKey === 'singles') {
                        if (isset($sectionModel->type) && $sectionModel->type === 'single') {
                            $elements = $query->all();
                        }
                    } else {
                        $elements = $query->all();
                    }
                }

                // Add each Element with a URL to the Sitemap
                foreach ($elements as $element) {

                    if ($elementMetadataFieldHandle === null) {
                        $elementMetadataFieldHandle = SproutSeo::$app->elementMetadata->getElementMetadataFieldHandle($element);
                    }

                    $robots = null;

                    // If we have an Element Metadata field, allow it to override robots
                    if ($elementMetadataFieldHandle) {
                        $metadata = $element->{$elementMetadataFieldHandle};

                        if (isset($metadata['enableMetaDetailsRobots']) && !empty($metadata['enableMetaDetailsRobots'])) {
                            $robots = $metadata['robots'] ?? null;
                            $robots = OptimizeHelper::prepareRobotsMetadataForSettings($robots);
                        }
                    }

                    $noIndex = $robots['noindex'] ?? $globalMetadata['robots']['noindex'] ?? null;
                    $noFollow = $robots['nofollow'] ?? $globalMetadata['robots']['nofollow'] ?? null;

                    if ($noIndex == 1 OR $noFollow == 1) {
                        SproutSeo::info(Craft::t('sprout-seo', 'Element ID {elementId} not added to sitemap. Element Metadata field `noindex` or `nofollow` settings are enabled.', [
                            'elementId' => $element->id
                        ]));
                        continue;
                    }

                    $canonicalOverride = $metadata['canonical'] ?? null;

                    if ($canonicalOverride !== null) {
                        SproutSeo::info(Craft::t('sprout-seo', 'Element ID {elementId} is using a canonical override and has not been included in the sitemap. Element URL: {elementUrl}. Canonical URL: {canonicalUrl}.', [
                            'elementId' => $element->id,
                            'elementUrl' => $element->getUrl(),
                            'canonicalUrl' => $canonicalOverride
                        ]));
                        continue;
                    }

                    if ($element->getUrl() === null) {
                        SproutSeo::info(Craft::t('sprout-seo', 'Element ID {elementId} not added to sitemap. Element does not have a URL.', [
                            'elementId' => $element->id
                        ]));
                        continue;
                    }

                    // Add each location indexed by its id
                    $urls[$element->id][] = [
                        'id' => $element->id,
                        'url' => $element->getUrl(),
                        'locale' => $site->language,
                        'modified' => $element->dateUpdated->format('Y-m-d\Th:m:s\Z'),
                        'priority' => $sitemapSection['priority'],
                        'changeFrequency' => $sitemapSection['changeFrequency'],
                    ];
                }

                // Reset our field handle for the next set of elements
                $elementMetadataFieldHandle = null;
            }
        }

        $urls = $this->getLocalizedSitemapStructure($urls);

        return $urls;
    }

    /**
     * Returns all sites to process for the current sitemap request
     *
     * @return array|\craft\models\Site[]
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getCurrentSitemapSites()
    {
        /**
         * @var Settings $pluginSettings
         */
        $pluginSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();

        $currentSite = Craft::$app->sites->getCurrentSite();
        $isMultisite = Craft::$app->getIsMultiSite();
        $isMultilingualSitemap = $pluginSettings->enableMultilingualSitemaps;

        // For multi-lingual sitemaps, get all sites in the Current Site group
        if ($isMultisite && $isMultilingualSitemap && in_array($currentSite->groupId, $pluginSettings->groupSettings, false)) {
            return Craft::$app->getSites()->getSitesByGroupId($currentSite->groupId);
        }

        // For non-multi-lingual sitemaps, get the current site
        if (!$isMultilingualSitemap && in_array($currentSite->id, array_filter($pluginSettings->siteSettings), false)) {
            return [$currentSite];
        }

        return [];
    }

    /**
     * @param $sitemapKey
     * @param $siteId
     *
     * @return array
     */
    protected function getEnabledSitemapSections($sitemapKey, $siteId): array
    {
        $query = (new Query())
            ->select('*')
            ->from('{{%sproutseo_sitemaps}}')
            ->where('enabled = 1 and urlEnabledSectionId is not null')
            ->andWhere('siteId = :siteId', [':siteId' => $siteId]);

        if ($sitemapKey == 'singles') {
            $query->andWhere('type = :type', [':type' => Entry::class]);
        } else {
            $query->andWhere('uniqueKey = :uniqueKey', [':uniqueKey' => $sitemapKey]);
        }

        $results = $query->all();

        $sitemapSections = [];
        foreach ($results as $result) {
            $sitemapSections[] = new SitemapSection($result);
        }

        return $sitemapSections;
    }

    /**
     * Returns all Custom Section URLs
     *
     * @param $siteId
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function getCustomSectionUrls($siteId)
    {
        $urls = [];

        // Fetch all Custom Sitemap defined in Sprout SEO
        $customSitemapSections = (new Query())
            ->select('uri, priority, changeFrequency, dateUpdated')
            ->from('{{%sproutseo_sitemaps}}')
            ->where('enabled = 1')
            ->andWhere('siteId = :siteId', [':siteId' => $siteId])
            ->andWhere('type=:type', [':type' => NoSection::class])
            ->all();

        foreach ($customSitemapSections as $customSitemapSection) {
            $customSitemapSection['url'] = null;
            // Adding each custom location indexed by its URL
            if (!UrlHelper::isAbsoluteUrl($customSitemapSection['uri'])) {
                $customSitemapSection['url'] = UrlHelper::siteUrl($customSitemapSection['uri']);
            }

            $modified = new DateTime($customSitemapSection['dateUpdated']);
            $customSitemapSection['modified'] = $modified->format('Y-m-d\Th:m:s\Z');

            $urls[$customSitemapSection['uri']] = $customSitemapSection;
        }

        $urls = $this->getLocalizedSitemapStructure($urls);

        return $urls;
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

        /**
         * Looping through all entries indexed by id
         */
        foreach ($stack as $id => $locations) {
            if (is_string($id)) {
                // Adding a custom location indexed by its URL
                $structure[] = $locations;
            } else {
                // Looping through each element and adding it as primary and creating its alternates
                /** @noinspection ForeachSourceInspection */
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
