<?php
namespace Craft;

class SproutSeo_SeoDataController extends BaseController
{
    /**
     * Save Fallback Info to the Datbase
     * 
     * @return mixed Return to Page
     */
    public function actionSaveFallbacks()
    {
        $this->requirePostRequest();

        $id = false; // we assume have a new item now

        $model = craft()->sproutSeo->newModel($id);

        $fallback = craft()->request->getPost('fallback');

        // Convert Checkbox Array into comma-delimited String
        if (isset($fallback['robots']))
        {
            $fallback['robots'] = craft()->sproutSeo->prepRobots($fallback['robots']);
        }

        $attributes = craft()->request->getPost('fallback');
        $model->setAttributes($attributes);

        if (craft()->sproutSeo->saveFallbackInfo($model))
        {
			craft()->userSession->setNotice(Craft::t('Item saved.'));
			$this->redirectToPostedUrl();
        } 

        
        craft()->userSession->setError(Craft::t("Couldn't save."));
        
        // Send the field back to the template
        craft()->urlManager->setRouteVariables(array(
        	'fallback' => $model
        ));

    }

    public function actionDeleteFallbacks()
    {
    	$this->requirePostRequest();
    	$this->requireAjaxRequest();
    		
    	$this->returnJson(array(
    			'success' => craft()->sproutSeo->deleteFallback(craft()->request->getRequiredPost('id')) >= 0 ? true : false));
    }

}
