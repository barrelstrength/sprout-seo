<?php
namespace Craft;

class SproutSeo_LivePreviewController extends BaseController
{
	protected $allowAnonymous = true;

	public function actionGetPrioritizedMetadata()
	{
		$this->requirePostRequest();

		$post     = craft()->request->getPost();
		$data     = $post['metadata'];
		$metadata = array();

		// prepare title value
		if (is_string($data['title']))
		{
			$metadata['optimizedTitle'] = $data['title'];
		}
		if (is_array($data['title']))
		{
			$metadata['title'] = craft()->templates->renderObjectTemplate($data['title']['template'], $data['title']['fields']);
		}

		// prepare description value
		if (is_string($data['description']))
		{
			$metadata['description'] = $data['description'];
		}

		if (is_array($data['description']))
		{
			$metadata['description'] = craft()->templates->renderObjectTemplate($data['description']['template'], $data['description']['fields']);
		}

		if (is_string($data['image']))
		{
			$metadata['optimizedImage'] = $data['image'];
		}

		// Facebook
		if (isset($data['ogTitle']))
		{
			$metadata['ogTitle'] = $data['ogTitle'];
		}

		if (isset($data['ogDescription']))
		{
			$metadata['ogDescription'] = $data['ogDescription'];
		}

		if (isset($data['ogImage']))
		{
			$metadata['ogImage'] = $data['ogImage'];
		}

		if (isset($data['ogType']))
		{
			$metadata['ogType'] = $data['ogType'];
		}

		//Twitter
		if (isset($data['twitterTitle']))
		{
			$metadata['twitterTitle'] = $data['twitterTitle'];
		}

		if (isset($data['twitterDescription']))
		{
			$metadata['twitterDescription'] = $data['twitterDescription'];
		}

		if (isset($data['twitterImage']))
		{
			$metadata['twitterImage'] = $data['twitterImage'];
		}

		if (isset($data['twitterCard']))
		{
			$metadata['twitterCard'] = $data['twitterCard'];
		}

		// We need to check wich scenario is calling the live-preview
		// check if the sproutseoSection value is true
		// if not we just need get the elementId

		// prepare image value

		sproutSeo()->optimize->updateMeta($metadata);
		sproutSeo()->optimize->rawMetadata = true;

		// default response without element metadata field. (global) level
		$response = array(
			'success'   => true,
			'optimized' => sproutSeo()->optimize->getMetadata($context)
		);

		if (isset($data['variableIdValue']) && isset($data['variableNameId']))
		{
			$context = sproutSeo()->optimize->getContextByElementVariable(
				$data['variableIdValue'], $data['variableNameId']
			);

			if ($context)
			{
				$response = array(
					'success'   => true,
					'optimized' => sproutSeo()->optimize->getMetadata($context)
				);
			}
			else
			{
				$response = array(
					'success' => false,
					'errors' => 'Unable to find '.$data['variableNameId'].':'.$data['variableIdValue'].''
				);
			}
			// we don't need to continue searching the section
			$this->returnJson($response);
		}
		else
		{
			//it's a SproutSEO section
		}

		$this->returnJson($response);
	}
}
