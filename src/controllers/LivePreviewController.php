<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;

use barrelstrength\sproutseo\SproutSeo;
use craft\web\Controller;

use barrelstrength\sproutbase\app\seo\web\assets\livepreview\LivePreviewAsset;
use yii\web\Response;
use Craft;

class LivePreviewController extends Controller
{
    protected $allowAnonymous = true;

    public function actionSectionPreview(): Response
    {
        $this->requirePostRequest();
        $metadata = [];
        $post = Craft::$app->request->getBodyParams();
        $siteId = $post['siteId'] ?? Craft::$app->getSites()->getPrimarySite()->id;

        $post = $post['sproutseo']['metadata'];

        $plugin = Craft::$app->plugins->getPlugin('sprout-seo');
        $settings = $plugin->getSettings();

        if (is_array($post['optimizedImage'])) {
            $image = $post['optimizedImage'][0];
            $metadata['optimizedImage'] = $image;
            $metadata['twitterImage'] = $image;
            $metadata['ogImage'] = $image;
        }

        // lets update the title and description also on twitter and og
        $metadata['optimizedTitle'] = $post['optimizedTitle'];
        $metadata['optimizedDescription'] = $post['optimizedDescription'];
        $metadata['ogTitle'] = $post['optimizedTitle'];
        $metadata['twitterTitle'] = $post['optimizedTitle'];
        $metadata['ogDescription'] = $post['optimizedDescription'];
        $metadata['twitterDescription'] = $post['optimizedDescription'];

        // Meta details validation
        if (isset($post['enableMetaDetailsOpenGraph']) && $post['enableMetaDetailsOpenGraph']) {
            if (is_array($post['ogImage'])) {
                $image = $post['ogImage'][0];
                $metadata['ogImage'] = $image;
            }

            if ($post['ogDescription']) {
                $metadata['ogDescription'] = $post['ogDescription'];
            }

            if ($post['ogTitle']) {
                $metadata['ogTitle'] = $post['ogTitle'];
            }

            if ($post['ogType']) {
                $metadata['ogType'] = $post['ogType'];
            }
        }

        if (isset($post['enableMetaDetailsTwitterCard']) && $post['enableMetaDetailsTwitterCard']) {
            if (is_array($post['twitterImage'])) {
                $image = $post['twitterImage'][0];
                $metadata['twitterImage'] = $image;
            }

            if ($post['twitterDescription']) {
                $metadata['twitterDescription'] = $post['twitterDescription'];
            }

            if ($post['twitterTitle']) {
                $metadata['twitterTitle'] = $post['twitterTitle'];
            }

            if ($post['twitterCard']) {
                $metadata['twitterCard'] = $post['twitterCard'];
            }
        }

        if (isset($post['id'])) {
            // It's a SproutSEO section
            $sectionsRegistered = SproutSeo::$app->sitemaps->getUrlEnabledSectionTypesForSitemaps();
            $sitemapSection = SproutSeo::$app->sitemaps->getSitemapSectionById($post['id']);

            if (isset($sectionsRegistered[$sitemapSection->type])) {
                $sectionType = $sectionsRegistered[$sitemapSection->type];
                $uniqueKey = $sectionType->getId().'-'.$sitemapSection->urlEnabledSectionId;
                $elementSection = $sectionType->urlEnabledSections[$uniqueKey];
                // let's update the handle and the url
                $metadata['uri'] = $elementSection->sitemapSection->uri;
            }
        }

        SproutSeo::$app->optimize->updateMeta($metadata);
        SproutSeo::$app->optimize->rawMetadata = true;

        $prioritizedMetadata = SproutSeo::$app->optimize->getPrioritizedMetadataModel($siteId);

        $this->getView()->getTwig()->disableStrictVariables();
        $this->getView()->registerAssetBundle(LivePreviewAsset::class);

        $templatePath = Craft::getAlias('@sproutbase/app/seo/templates/');
        $originalTemplatesPath = Craft::$app->getView()->getTemplatesPath();

        Craft::$app->getView()->setTemplatesPath($templatePath);

        $rendered = $this->renderTemplate('_components/fields/elementmetadata/_preview', [
            'prioritizedMetadata' => $prioritizedMetadata
        ]);

        Craft::$app->getView()->setTemplatesPath($originalTemplatesPath);

        return $rendered;
    }

    public function actionElementMetadataPreview()
    {
        $this->requirePostRequest();
        $metadata = [];
        $post = Craft::$app->request->getBodyParams();
        $fields = $post;
        $fieldSettings = [];
        $siteId = $post['siteId'] ?? Craft::$app->getSites()->getPrimarySite()->id;

        foreach ($post['fields'] as $key => $field) {
            if (isset($field['metadata'])) {
                $post = $field['metadata'];
                $fieldSettings = json_decode($post['sproutSeoSettings'], true);
                break;
            }
        }

        $metadata['optimizedImage'] = '';
        $metadata['optimizedTitle'] = '';
        $metadata['optimizedDescription'] = '';
        // lets update the title
        if ($fieldSettings['optimizedTitleField'] == 'elementTitle') {
            $metadata['optimizedTitle'] = $fields['title'] ?? '';
        }

        if (is_numeric($fieldSettings['optimizedTitleField'])) {
            $titleField = Craft::$app->fields->getFieldById($fieldSettings['optimizedTitleField']);

            if ($titleField) {
                $titleHandle = $titleField->handle;
                $metadata['optimizedTitle'] = $fields[$titleHandle] ?? '';
            }
        }
        // custom paterm
        if (!is_numeric($fieldSettings['optimizedTitleField']) && $fieldSettings['optimizedTitleField'] != 'manually' && $fieldSettings['optimizedTitleField'] != 'elementTitle') {
            $metadata['optimizedTitle'] = Craft::$app->getView()->renderObjectTemplate($fieldSettings['optimizedTitleField'], $fields['fields']);
        }
        //manually
        if (isset($post['optimizedTitle'])) {
            $metadata['optimizedTitle'] = $post['optimizedTitle'];
        }

        // lets update the decription
        if (is_numeric($fieldSettings['optimizedDescriptionField'])) {
            $descriptionField = Craft::$app->fields->getFieldById($fieldSettings['optimizedDescriptionField']);
            if ($descriptionField) {
                $descriptionHandle = $descriptionField->handle;

                $metadata['optimizedDescription'] = $fields['fields'][$descriptionHandle] ?? '';
            }
        }

        if (!is_numeric($fieldSettings['optimizedDescriptionField']) && $fieldSettings['optimizedDescriptionField'] != 'manually') {
            $metadata['optimizedDescription'] = Craft::$app->getView()->renderObjectTemplate($fieldSettings['optimizedDescriptionField'], $fields['fields']);
        }

        if (isset($post['optimizedDescription'])) {
            $metadata['optimizedDescription'] = $post['optimizedDescription'];
        }

        // lets update the image
        if (is_numeric($fieldSettings['optimizedImageField'])) {
            $imageField = Craft::$app->fields->getFieldById($fieldSettings['optimizedImageField']);

            if ($imageField) {
                $titleHandle = $imageField->handle;
                if (isset($fields[$titleHandle]) and is_array($fields[$titleHandle])) {
                    $metadata['optimizedImage'] = $fields[$titleHandle][0];
                }
            }
        }
        // custom format
        if (!is_numeric($fieldSettings['optimizedImageField']) && $fieldSettings['optimizedImageField'] != 'manually') {
            $metadata['optimizedImage'] = Craft::$app->getView()->renderObjectTemplate($fieldSettings['optimizedImageField'], $fields['fields']);
        }
        //manually
        if (isset($post['optimizedImage'])) {
            if (is_array($post['optimizedImage'])) {
                $metadata['optimizedImage'] = $post['optimizedImage'][0];
            }
        }

        if ($metadata['optimizedImage']) {
            $metadata['twitterImage'] = $metadata['optimizedImage'];
            $metadata['ogImage'] = $metadata['optimizedImage'];
        }

        // update others
        $metadata['ogTitle'] = $metadata['optimizedTitle'];
        $metadata['twitterTitle'] = $metadata['optimizedTitle'];
        $metadata['ogDescription'] = $metadata['optimizedDescription'];
        $metadata['twitterDescription'] = $metadata['optimizedDescription'];

        // Search meta detail!
        if ($fieldSettings['showSearchMeta'] && isset($post['enableMetaDetailsSearch']) && $post['enableMetaDetailsSearch']) {
            $metadata['optimizedTitle'] = $post['title'];
            $metadata['optimizedDescription'] = $post['description'];
        }
        // Meta details validation
        if ($fieldSettings['showOpenGraph'] && isset($post['enableMetaDetailsOpenGraph']) && $post['enableMetaDetailsOpenGraph']) {
            if (is_array($post['ogImage'])) {
                $image = $post['ogImage'][0];
                $metadata['ogImage'] = $image;
            }

            if ($post['ogDescription']) {
                $metadata['ogDescription'] = $post['ogDescription'];
            }

            if ($post['ogTitle']) {
                $metadata['ogTitle'] = $post['ogTitle'];
            }

            if ($post['ogType']) {
                $metadata['ogType'] = $post['ogType'];
            }
        }

        if ($fieldSettings['showTwitter'] && isset($post['enableMetaDetailsTwitterCard']) && $post['enableMetaDetailsTwitterCard']) {
            if (is_array($post['twitterImage'])) {
                $image = $post['twitterImage'][0];
                $metadata['twitterImage'] = $image;
            }

            if ($post['twitterDescription']) {
                $metadata['twitterDescription'] = $post['twitterDescription'];
            }

            if ($post['twitterTitle']) {
                $metadata['twitterTitle'] = $post['twitterTitle'];
            }

            if ($post['twitterCard']) {
                $metadata['twitterCard'] = $post['twitterCard'];
            }
        }

        $context = [];
        // Sprout SEO Element metadata field type
        $variablesNames = SproutSeo::$app->optimize->getVariableIdNames();

        foreach ($variablesNames as $key => $variableName) {
            if (isset($fields[$variableName])) {
                $context = SproutSeo::$app->optimize->getContextByElementVariable(
                    $fields[$variableName], $variableName
                );
            }
        }

        SproutSeo::$app->optimize->updateMeta($metadata);
        SproutSeo::$app->optimize->rawMetadata = true;

        if ($context) {
            SproutSeo::$app->optimize->urlEnabledSection = SproutSeo::$app->sitemaps->getUrlEnabledSectionsViaContext($context);
        }

        $prioritizedMetadata = SproutSeo::$app->optimize->getPrioritizedMetadataModel($siteId);

        $this->getView()->getTwig()->disableStrictVariables();
        Craft::$app->getView()->registerAssetBundle(LivePreviewAsset::class);

        $templatePath = Craft::getAlias('@sproutbase/app/seo/templates/');
        $originalTemplatesPath = Craft::$app->getView()->getTemplatesPath();

        Craft::$app->getView()->setTemplatesPath($templatePath);

        $rendered = $this->renderTemplate('_components/fields/elementmetadata/_preview', [
            'prioritizedMetadata' => $prioritizedMetadata
        ]);

        Craft::$app->getView()->setTemplatesPath($originalTemplatesPath);

        return $rendered;
    }
}
