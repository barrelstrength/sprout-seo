<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\services;


use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\migrations\InsertDefaultGlobalsBySite;
use barrelstrength\sproutseo\models\Globals;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Json;
use craft\models\Site;

/**
 * Class SproutSeo_GlobalMetadataService
 *
 * @package Craft
 *
 * @property array $transforms
 */
class GlobalMetadata extends Component
{
    /**
     * Get Global Metadata values
     *
     * @param Site|null $site
     *
     * @return Globals
     * @throws SiteNotFoundException
     * @throws \yii\base\Exception
     */
    public function getGlobalMetadata($site = null): Globals
    {
        $siteId = $site->id ?? null;

        if ($siteId) {
            $currentSite = Craft::$app->getSites()->getSiteById($siteId);
        } else {
            $currentSite = Craft::$app->getSites()->getCurrentSite();
        }

        $query = (new Query())
            ->select('*')
            ->from(['{{%sproutseo_globals}}']);

        if ($siteId) {
            $query->where(['siteId' => $siteId]);
        } else {
            $site = Craft::$app->getSites()->getPrimarySite();
            $query->where(['siteId' => $site->id]);
        }

        $results = $query->one();

        $results['meta'] = isset($results['meta']) ? Json::decode($results['meta']) : null;
        $results['identity'] = isset($results['identity']) ? Json::decode($results['identity']) : null;
        $results['contacts'] = isset($results['contacts']) ? Json::decode($results['contacts']) : null;
        $results['ownership'] = isset($results['ownership']) ? Json::decode($results['ownership']) : null;
        $results['social'] = isset($results['social']) ? Json::decode($results['social']) : null;
        $results['robots'] = isset($results['robots']) ? Json::decode($results['robots']) : null;
        $results['settings'] = isset($results['settings']) ? Json::decode($results['settings']) : null;

        if (!isset($results['identity']['url'])) {
            $results['identity']['url'] = $currentSite->baseUrl;
        }

        if (isset($results['settings']['ogTransform'])) {
            $results['meta']['ogTransform'] = $results['settings']['ogTransform'];
        }

        if (isset($results['settings']['twitterTransform'])) {
            $results['meta']['twitterTransform'] = $results['settings']['twitterTransform'];
        }

        return new Globals($results);
    }

    /**
     * Save Global Metadata to database
     *
     * @param string  $globalColumn
     * @param Globals $globals
     *
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function saveGlobalMetadata($globalColumn, $globals): bool
    {
        $values[$globalColumn] = $globals->getGlobalByKey($globalColumn, 'json');
        $values['siteId'] = $globals->siteId;

        // new site?
        $results = (new Query())
            ->select('*')
            ->from(['{{%sproutseo_globals}}'])
            ->where(['[[siteId]]' => $globals->siteId])
            ->one();

        if (!$results) {
            //save default settings
            $migration = new InsertDefaultGlobalsBySite([
                'siteId' => $globals->siteId,
            ]);

            ob_start();
            $migration->up();
            ob_end_clean();
        }

        Craft::$app->db->createCommand()->update('{{%sproutseo_globals}}',
            $values,
            ['siteId' => $globals->siteId]
        )->execute();

        $this->refreshMetaColumn($globals->siteId);

        return true;
    }

    /**
     * @param $siteId
     *
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function refreshMetaColumn($siteId)
    {
        $site = Craft::$app->getSites()->getSiteById($siteId);
        $globals = SproutSeo::$app->globalMetadata->getGlobalMetadata($site);

        $metadataArray = $this->prepareMetaColumnValues($globals);
        $metadata = new Metadata($metadataArray);
        $meta = Json::encode($metadata->getAttributes());

        Craft::$app->db->createCommand()->update('{{%sproutseo_globals}}', [
            'meta' => $meta
        ], [
            'siteId' => $globals->siteId
        ])->execute();
    }

    public function prepareMetaColumnValues(Globals $globals): array
    {
        $meta = $globals->meta;

        $identity = $globals->identity;
        $social = $globals->social;
        $robots = $globals->robots;
        $settings = $globals->settings;

        $optimizedTitle = $identity['name'] ?? null;
        $optimizedDescription = $identity['description'] ?? null;
        $optimizedImage = $identity['image'] ?? null;

        $meta['optimizedTitle'] = $optimizedTitle;
        $meta['optimizedDescription'] = $optimizedDescription;
        $meta['optimizedImage'] = $optimizedImage;

        $meta['title'] = $optimizedTitle;
        $meta['description'] = $optimizedDescription;
        $meta['keywords'] = $identity['keywords'] ?? null;
        $meta['robots'] = OptimizeHelper::prepareRobotsMetadataValue($robots);

        // @todo - Add location info
        $meta['region'] = '';
        $meta['placename'] = '';
        $meta['position'] = '';

        $meta['latitude'] = $identity['latitude'] ?? '';
        $meta['longitude'] = $identity['longitude'] ?? '';

        $meta['ogType'] = $settings['defaultOgType'] ?? 'article';
        $meta['ogSiteName'] = $identity['name'] ?? null;
        $meta['ogTitle'] = $optimizedTitle;
        $meta['ogDescription'] = $optimizedDescription;
        $meta['ogImage'] = $optimizedImage;
        $meta['ogLocale'] = null;
        $meta['ogPublisher'] = OptimizeHelper::getFacebookPage($social);

        $meta['twitterCard'] = $settings['defaultTwitterCard'] ?? 'summary';;
        $meta['twitterTitle'] = $optimizedTitle;
        $meta['twitterDescription'] = $optimizedDescription;
        $meta['twitterImage'] = $optimizedImage;

        $twitterProfileName = OptimizeHelper::getTwitterProfileName($social);
        $meta['twitterSite'] = $twitterProfileName;
        $meta['twitterCreator'] = $twitterProfileName;

        if (isset($settings['ogTransform'])) {
            $meta['ogTransform'] = $settings['ogTransform'];
        }

        if (isset($settings['twitterTransform'])) {
            $meta['twitterTransform'] = $settings['twitterTransform'];
        }

        $meta['ogType']  = (isset($settings['defaultOgType']) && $settings['defaultOgType'])
            ? $settings['defaultOgType']
            : 'article';
        $meta['twitterCard'] = (isset($settings['defaultTwitterCard']) && $settings['defaultTwitterCard'])
            ? $settings['defaultTwitterCard']
            : 'summary';

        return $meta;
    }

    /**
     * @return array
     */
    public function getTransforms(): array
    {
        $options = [
            '' => Craft::t('sprout-seo', 'None')
        ];

        $options[] = ['optgroup' => Craft::t('sprout-seo', 'Default Transforms')];

        $options['sproutSeo-socialSquare'] = Craft::t('sprout-seo', 'Square – 400x400');
        $options['sproutSeo-ogRectangle'] = Craft::t('sprout-seo', 'Rectangle – 1200x630 – Open Graph');
        $options['sproutSeo-twitterRectangle'] = Craft::t('sprout-seo', 'Rectangle – 1024x512 – Twitter Card');

        $transforms = Craft::$app->assetTransforms->getAllTransforms();

        if (count($transforms)) {
            $options[] = ['optgroup' => Craft::t('sprout-seo', 'Custom Transforms')];

            foreach ($transforms as $transform) {
                $options[$transform->handle] = $transform->name;
            }
        }

        return $options;
    }
}
