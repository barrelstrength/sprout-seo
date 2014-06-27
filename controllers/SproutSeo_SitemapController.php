<?php
namespace Craft;

class SproutSeo_SitemapController extends BaseController
{
	/**
	 * Save Sitemap Info to the Database
	 *
	 * @return mixed Return to Page
	 */
	public function actionSaveSitemap()
	{
		$this->requireAjaxRequest();

		$sitemapSettings['id']              = craft()->request->getPost('id');
		$sitemapSettings['sectionId']       = craft()->request->getPost('sectionId');
		$sitemapSettings['url']             = craft()->request->getPost('url');
		$sitemapSettings['priority']        = craft()->request->getRequiredPost('priority');
		$sitemapSettings['changeFrequency'] = craft()->request->getRequiredPost('changeFrequency');
		$sitemapSettings['enabled']         = craft()->request->getRequiredPost('enabled');
		$sitemapSettings['ping']            = craft()->request->getPost('ping');

		$model = SproutSeo_SitemapModel::populateModel($sitemapSettings);

		$lastInsertId = craft()->sproutSeo_sitemap->saveSitemap($model);

		$this->returnJson(array(
			'lastInsertId' => $lastInsertId
		));

	}

	/**
	 * Save Custom Page Info
	 * 
	 * @return mixed redirect or status message
	 */
	public function actionSaveCustomPage()
	{
		// Require post request
		$this->requirePostRequest();

		// Hand off to model
		$customPage = new SproutSeo_SitemapModel();

		// Attributes
		$customPage->url              = craft()->request->getPost('url');
		$customPage->priority         = craft()->request->getPost('priority');
		$customPage->changeFrequency 	= craft()->request->getPost('changeFrequency');
		$customPage->enabled 	        = craft()->request->getPost('enabled');
		
		// @TODO - maybe add these as defaults to the model?  
		// We don't need this for the Custom URLs.
		$customPage->ping = 0;

		// SAVE CUSTOM PAGE - PASS TO SERVICE
		// @TODO clean up
		if (craft()->sproutSeo_sitemap->saveCustomPage($customPage))
		{
			craft()->userSession->setNotice(Craft::t('Custom page saved.'));
			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save custom page.'));
		}

	}

	/**
	 * Delete a Custom Page
	 * 
	 * @return json result
	 */
	public function actionDeleteCustomPage()
	{
    $this->requirePostRequest();
    $this->requireAjaxRequest();
    
    $id = craft()->request->getRequiredPost('id');
    $result = craft()->sproutSeo_sitemap->deleteCustomPageById($id);

    $this->returnJson(array('success' => $result));
	}
}

