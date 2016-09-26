<?php
namespace Craft;

class SproutSeo_SchemaController extends BaseController
{
	/**
	 * Load the Structured Data Edit Page
	 *
	 * @throws HttpException
	 */
	public function actionSchemaEditTemplate()
	{
		$segment = craft()->request->getSegment(3);
		$this->renderTemplate('sproutseo/schema/' . $segment, array());
	}
}
