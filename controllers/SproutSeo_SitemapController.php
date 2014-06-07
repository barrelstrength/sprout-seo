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

		$sitemapSettings['id'] = craft()->request->getPost('id');
		$sitemapSettings['sectionId'] = craft()->request->getPost('sectionId');
		$sitemapSettings['url'] = craft()->request->getPost('url');
		$sitemapSettings['priority'] = craft()->request->getRequiredPost('priority');
		$sitemapSettings['changeFrequency'] = craft()->request->getRequiredPost('changeFrequency');
		$sitemapSettings['enabled'] = craft()->request->getRequiredPost('enabled');
		$sitemapSettings['ping'] = craft()->request->getPost('ping');

		$model = SproutSeo_SitemapModel::populateModel($sitemapSettings);
		
		$lastInsertId = craft()->sproutSeo_sitemap->saveSitemap($model);
		$this->returnJson(array(
			'lastInsertId' => $lastInsertId)
		);

	}
}
