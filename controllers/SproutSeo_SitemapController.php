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

	/**
	 * Save a Custom Sitemap Page
	 *
	 * @throws HttpException
	 */
	public function actionSaveCustomPage()
	{
		$this->requirePostRequest();

		$customPage                  = new SproutSeo_SitemapModel();
		$customPage->url             = craft()->request->getPost('url');
		$customPage->priority        = craft()->request->getPost('priority');
		$customPage->changeFrequency = craft()->request->getPost('changeFrequency');
		$customPage->enabled         = craft()->request->getPost('enabled');

		// Save the Custom Page
		if (sproutSeo()->sitemap->saveCustomPage($customPage))
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
	 * Deletes a Custom Page Record
	 *
	 * @return json success object
	 */
	public function actionDeleteCustomPage()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$id     = craft()->request->getRequiredPost('id');
		$result = sproutSeo()->sitemap->deleteCustomPageById($id);

		$this->returnJson(array(
			'success' => $result
		));
	}
}