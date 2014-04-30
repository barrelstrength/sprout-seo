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

		$sitemapSettings['sectionId'] = craft()->request->getRequiredPost('sectionId');
		$sitemapSettings['priority'] = craft()->request->getRequiredPost('priority');
		$sitemapSettings['changeFrequency'] = craft()->request->getRequiredPost('changeFrequency');
		$sitemapSettings['enabled'] = craft()->request->getRequiredPost('enabled');
		$sitemapSettings['ping'] = craft()->request->getRequiredPost('ping');

		$model = SproutSeo_SitemapModel::populateModel($sitemapSettings);
		
		$success = craft()->sproutSeo->saveSitemap($model);
		$this->returnJson(array('success' => $success));

	}
}
