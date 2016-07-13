<?php
namespace Craft;

class SproutSeo_RedirectSproutImportElementImporter extends BaseSproutImportElementImporter
{
	/**
	 * @return mixed
	 */
	public function defineModel()
	{
		return 'SproutSeo_RedirectModel';
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