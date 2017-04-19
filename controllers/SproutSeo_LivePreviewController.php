<?php
namespace Craft;

class SproutSeo_LivePreviewController extends BaseController
{
	protected $allowAnonymous = true;

	// public function actionParseSeoValue()
	// {
	//   $this->requireAjaxRequest();

	//   $post     = craft()->request->getPost();

	//   $template = $post['template'];
	//   $object   = $post['dataObject'];

	//   $this->returnJson(craft()->templates->renderObjectTemplate($template, $object));
	// }

	// public function actionGetImageUrl()
	// {
	//   $this->requireAjaxRequest();

	//   $post    = craft()->request->getPost();

	//   $imageId = $post['imageId'];

	//   $this->returnJson(craft()->assets->getFileById($imageId)->getUrl());
	// }

	public function actionGetPrioritizedMetadata()
	{
		$this->requirePostRequest();

		sproutSeo()->optimize->rawMetadata = true;

		// default response without element metadata field. (global) level
		$response = array(
			'success'   => true,
			'optimized' => sproutSeo()->optimize->getMetadata($context)
		);

		$registeredUrlEnabledSectionsTypes = craft()->plugins->call('registerSproutSeoUrlEnabledSectionTypes');

		foreach ($registeredUrlEnabledSectionsTypes as $plugin => $urlEnabledSectionTypes)
		{
			foreach ($urlEnabledSectionTypes as $urlEnabledSectionType)
			{
				// Let's get the optimized metadata model
				$idVariableName = $urlEnabledSectionType->getIdVariableName();

				$idVariableValue = craft()->request->getPost($idVariableName, null);

				if ($idVariableValue)
				{
					// example: entry, category, etc.
					$elementType = $urlEnabledSectionType->getMatchedElementVariable();
					$locale      = craft()->i18n->getLocaleById(craft()->language);
					$elementById = craft()->elements->getElementById($idVariableValue,$urlEnabledSectionType->getElementType(), $locale->id);

					if ($elementById)
					{
						$context = array(
							$elementType => $elementById
						);

						$response = array(
							'success'   => true,
							'optimized' => sproutSeo()->optimize->getMetadata($context)
						);
					}
					else
					{
						$response = array(
							'success' => false,
							'errors' => 'The '.$idVariableValue.' element id does not exists'
						);
					}
					// we don't need to continue searching the section
					$this->returnJson($response);
				}
				else
				{
					$response = array(
						'success' => false,
						'errors' => 'The '.$idVariableName.' field value is missing in the post request.'
					);
				}

			}
		}

		$this->returnJson($response);
	}
}




