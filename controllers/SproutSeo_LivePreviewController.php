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
    $entry = craft()->entries->getEntryById(2);    

    $context = array(
      'entry' => $entry
    );

    sproutSeo()->optimize->rawMetadata = true;

    $this->returnJson(sproutSeo()->optimize->getMetadata($context));
  }
}




