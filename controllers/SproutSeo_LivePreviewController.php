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
		$response = array('success'=>false,'errors'=>'Not defined');

		// prepare title value
		if (is_string($data['title']))
		{
			$metadata['title'] = $data['title'];
		}
		if (is_array($data['title']))
		{
			$metadata['title'] = craft()->templates->renderObjectTemplate($data['title']['template'], $data['title']['fields']);
		}
		// lets update the title also on og
		$metadata['ogTitle']      = $metadata['title'];
		$metadata['twitterTitle'] = $metadata['title'];

		// prepare description value
		if (is_string($data['description']))
		{
			$metadata['description'] = $data['description'];
		}

		if (is_array($data['description']))
		{
			$metadata['description'] = craft()->templates->renderObjectTemplate($data['description']['template'], $data['description']['fields']);
		}
		// lets update the description also on twitter
		$metadata['ogDescription']      = $metadata['description'];
		$metadata['twitterDescription'] = $metadata['description'];

		if (isset($data['image']) && is_string($data['image']))
		{
			$metadata['optimizedImage'] = $data['image'];
			$metadata['ogImage'] = $data['image'];
			$metadata['twitterImage'] = $data['image'];
		}

		// Meta details
		if (isset($data['enableMetaDetailsSearch']) &&
				isset($data['enableMetaDetailsOpenGraph']) &&
				isset($data['enableMetaDetailsTwitterCard']) &&
				isset($data['enableMetaDetailsGeo']) &&
				isset($data['enableMetaDetailsRobots']))
		{
			$metadata['enableMetaDetailsSearch']      = $data['enableMetaDetailsSearch'];
			$metadata['enableMetaDetailsOpenGraph']   = $data['enableMetaDetailsOpenGraph'];
			$metadata['enableMetaDetailsTwitterCard'] = $data['enableMetaDetailsTwitterCard'];
			$metadata['enableMetaDetailsGeo']         = $data['enableMetaDetailsGeo'];
			$metadata['enableMetaDetailsRobots']      = $data['enableMetaDetailsRobots'];
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

		sproutSeo()->optimize->updateMeta($metadata);
		sproutSeo()->optimize->rawMetadata = true;
		$context = array();

		// Sprout SEO Element metadata field type
		if (isset($post['variableIdValue']) && isset($post['variableNameId']))
		{
			$context = sproutSeo()->optimize->getContextByElementVariable(
				$post['variableIdValue'], $post['variableNameId']
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
					'errors' => 'Unable to find '.$post['variableNameId'].':'.$post['variableIdValue'].''
				);
			}
			// we don't need to continue searching the section
			$this->returnJson($response);
		}
		else if (isset($post['section']) && isset($post['sectionId']))
		{
			//it's a SproutSEO section
			$section = sproutSeo()->sectionMetadata->getSectionMetadataById($post['sectionId']);
			$metadata['section'] = $section->type.':'.$section->handle;
			sproutSeo()->optimize->updateMeta($metadata);
			$response = array(
				'success'   => true,
				'optimized' => sproutSeo()->optimize->getMetadata($context)
			);
		}

		$this->returnJson($response);
	}
}
