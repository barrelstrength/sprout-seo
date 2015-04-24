<?php
namespace Craft;

class SproutSeo_SitemapController extends BaseController
{
	/**
	 * Show Sitemap index
	 *
	 * @return mixed Return to Page
	 */
	public function actionSitemapIndex()
	{
		$this->renderTemplate('sproutSeo/sitemap/index');
	}

	/**
	 * Create a new page for the sitemap
	 *
	 * @return mixed Return to Page
	 */
	public function actionEditSitemap()
	{
		$this->renderTemplate('sproutSeo/sitemap/_edit');
	}

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

		$lastInsertId = sproutSeo()->sitemap->saveSitemap($model);
		$this->returnJson(array(
				'lastInsertId' => $lastInsertId)
		);
	}

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

		$this->returnJson(array('success' => $result));
	}
}