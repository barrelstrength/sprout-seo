<?php
namespace Craft;

class SproutSeo_RedirectSproutImportElementImporter extends BaseSproutImportElementImporter
{
	/**
	 * @return mixed
	 */
	public function getModelName()
	{
		return 'SproutSeo_Redirect';
	}

	/**
	 * @return bool
	 * @throws Exception
	 * @throws \Exception
	 */
	public function save()
	{
		return sproutSeo()->redirects->saveRedirect($this->model);
	}
}