<?php
namespace Craft;

class SproutSeo_SitemapController extends BaseController
{
	/**
	 * Load the Sitemap index page
	 *
	 * @throws HttpException
	 */
	public function actionSitemapIndex()
	{
		$this->renderTemplate('sproutSeo/sitemap/index');
	}

	/**
	 * Load the Sitemap edit page
	 *
	 * @throws HttpException
	 */
	public function actionEditSitemap()
	{
		$this->renderTemplate('sproutSeo/sitemap/_edit');
	}

	/**
	 * Save Sitemap Info to the Database
	 *
	 * @throws HttpException
	 */
	public function actionSaveSitemap()
	{
		$this->requireAjaxRequest();

		$sitemapSettings['id']              = craft()->request->getPost('id');
		$sitemapSettings['elementGroupId']  = craft()->request->getPost('elementGroupId');
		$sitemapSettings['url']             = craft()->request->getPost('url');
		$sitemapSettings['priority']        = craft()->request->getRequiredPost('priority');
		$sitemapSettings['changeFrequency'] = craft()->request->getRequiredPost('changeFrequency');
		$sitemapSettings['enabled']         = craft()->request->getRequiredPost('enabled');

		$model = SproutSeo_SitemapModel::populateModel($sitemapSettings);

		$lastInsertId = sproutSeo()->sitemap->saveSitemap($model);

		$this->returnJson(array(
			'lastInsertId' => $lastInsertId
		));
	}
}