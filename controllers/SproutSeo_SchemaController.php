<?php
namespace Craft;

class SproutSeo_SchemaController extends BaseController
{

	public function actionSchemaEditTemplate()
	{
		$segment = craft()->request->getSegment(3);
		$this->renderTemplate('sproutseo/schema/' . $segment, array());
	}
}
