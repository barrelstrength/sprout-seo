<?php
namespace Craft;

class SproutSeo_DefaultsController extends BaseController
{

    public function actionEditDefault()
    {
        // Set twitterSummaryImageSource
        // $variables['twitterSummaryImageSource'] = SproutSeo_MetaModel()->twitterSummaryImageSource;
        //
        // // Set elements
        // if ($variables['twitterSummaryImageSource'])
        // {
        //     $asset = craft()->elements->getElementById($variables['twitterSummaryImageSource']);
        //     $variables['elements'] = array($asset);
        // }
        // else
        // {
        //     $variables['elements'] = array();
        // }
        //
        // // Set elementType
        // $variables['elementType'] = craft()->elements->getElementType(ElementType::Asset);
        //
        // // Set assetsSourceExists
        // $sources = craft()->assets->findFolders();
        // $variables['assetsSourceExists'] = count($sources);
        //
        // // Set newAssetsSourceUrl
        // $variables['newAssetsSourceUrl'] = UrlHelper::getUrl('settings/assets/sources/new');

        return $this->renderTemplate('sproutSeo/defaults/_edit');
    }

    public function actionSaveDefault()
    {
        $this->requirePostRequest();

        $id = false; // we assume have a new item now

        $model = craft()->sproutSeo_meta->newMetaModel($id);

        $defaultFields = craft()->request->getPost('default_fields');

        // Convert Checkbox Array into comma-delimited String
        if (isset($defaultFields['robots']))
        {
            $defaultFields['robots'] = craft()->sproutSeo_meta->prepRobotsForDb($defaultFields['robots']);
        }

        $model->setAttributes($defaultFields);

        if (craft()->sproutSeo_meta->saveDefaultInfo($model))
        {
            craft()->userSession->setNotice(Craft::t('New default saved.'));
            $this->redirectToPostedUrl();
        }

        craft()->userSession->setError(Craft::t("Couldn't save the default."));

        // Send the field back to the template
        craft()->urlManager->setRouteVariables(array(
            'default' => $model
        ));
    }

    public function actionDeleteDefaults()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $this->returnJson(array(
            'success' => craft()->sproutSeo_meta->deleteDefault(craft()->request->getRequiredPost('id')) >= 0 ? true : false));
    }
}
