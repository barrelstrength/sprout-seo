<?php
namespace Craft;

class SproutSeo_MetaController extends BaseController
{
    /**
     * Save Template Info to the Datbase
     * 
     * @return mixed Return to Page
     */
    public function actionSaveTemplates()
    {
        $this->requirePostRequest();

        $id = false; // we assume have a new item now

        $model = craft()->sproutSeo_meta->newModel($id);
        
        $templateFields = craft()->request->getPost('template_fields');

        // Convert Checkbox Array into comma-delimited String
        if (isset($templateFields['robots']))
        {
            $templateFields['robots'] = craft()->sproutSeo_meta->prepRobotsForDb($templateFields['robots']);
        }

        $model->setAttributes($templateFields);

        if (craft()->sproutSeo_meta->saveTemplateInfo($model))
        {
			craft()->userSession->setNotice(Craft::t('Item saved.'));
			$this->redirectToPostedUrl();
        } 

        
        craft()->userSession->setError(Craft::t("Couldn't save."));
        
        // Send the field back to the template
        craft()->urlManager->setRouteVariables(array(
        	'template' => $model
        ));

    }

    public function actionDeleteTemplates()
    {
    	$this->requirePostRequest();
    	$this->requireAjaxRequest();
    		
    	$this->returnJson(array(
    			'success' => craft()->sproutSeo_meta->deleteTemplate(craft()->request->getRequiredPost('id')) >= 0 ? true : false));
    }

}
