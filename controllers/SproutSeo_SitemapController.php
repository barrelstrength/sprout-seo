<?php
namespace Craft;

class SproutSeo_SitemapController extends BaseController
{
	protected $allowAnonymous = true;

	/**
	 * Generates the proper xml
	 *
	 * @throws HttpException
	 */
	public function actionIndex()
	{
		$url = craft()->request->getPath();

		$segments = explode('-', $url);

		$variables = array();

		#header('Content-Type: text/xml');
		// Rendering the template and passing in received options
		$path = craft()->templates->getTemplatesPath();

		craft()->templates->setTemplatesPath(dirname(__FILE__) . '/../templates/');
		//@todo add criteria by number at the end
		//https://craftcms.stackexchange.com/a/16474/4798

		foreach ($segments as $segment)
		{
			switch ($segment)
			{
				case 'sitemap.xml':
					$sitemap = sproutSeo()->sitemap->getSitemapIndex();

					$template = "sitemap/_frontend/sitemap";
					$variables = array(
						'sitemap' => $sitemap
					);
					break;
				case 'category':
					# code...
					break;
				case 'product':
					# code...
					break;
				case 'entry':
					# code...
					break;
			}
		}

		$this->renderTemplate($template, $variables);
	}
}
