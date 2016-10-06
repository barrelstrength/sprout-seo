<?php

namespace Craft;


class SproutSeo_AddressController extends BaseController
{
	protected $allowAnonymous = true;

	public function actionCountryInput()
	{
		$addressInfoId = craft()->request->getPost('addressInfoId');

		$addressInfoModel = sproutCommerce()->addressInfo->getAddressById($addressInfoId);

		$countryCode = $addressInfoModel->countryCode;

		sproutCommerce()->addressForm->setParams($countryCode, 'address');

		echo sproutCommerce()->addressForm->countryInput();

		exit;
	}

	public function actionChangeForm()
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		$countryCode = craft()->request->getPost('countryCode');

		sproutCommerce()->addressForm->setParams($countryCode, 'address');

		echo sproutCommerce()->addressForm->getAddressFormHtml();
		exit;
	}

	public function actionUpdateAddressFormat()
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		$addressInfoId = craft()->request->getPost('addressInfoId');

		$addressInfoModel = sproutCommerce()->addressInfo->getAddressById($addressInfoId);

		$html = sproutCommerce()->addressForm->getAddressWithFormat($addressInfoModel);

		$countryCode = $addressInfoModel->countryCode;

		sproutCommerce()->addressForm->setParams($countryCode, 'address', '', $addressInfoModel);

		$countryCodeHtml = sproutCommerce()->addressForm->countryInput();
		$formInputHtml   = sproutCommerce()->addressForm->getAddressFormHtml();

		$this->returnJson(array(
			'html'            => $html,
			'countryCodeHtml' => $countryCodeHtml,
			'formInputHtml'   => $formInputHtml,
			'countryCode'     => $countryCode
		));
	}

	public function actionSaveAddress()
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		$result = array(
			'result' => true,
			'errors' => array()
		);

		$addressInfo = craft()->request->getPost('addressInfo');
		$formValues  = craft()->request->getPost('formValues');

		$addressInfoModel = SproutCommerce_AddressInfoModel::populateModel($formValues);

		if ($addressInfoModel->validate() == true)
		{
			if (sproutCommerce()->addressInfo->saveAddressInfo($addressInfoModel))
			{
				$html = sproutCommerce()->addressForm->getAddressWithFormat($addressInfoModel);
				$countryCode = $addressInfoModel->countryCode;

				sproutCommerce()->addressForm->setParams($countryCode, 'address', '', $addressInfoModel);
				$countryCodeHtml = sproutCommerce()->addressForm->countryInput();
				$formInputHtml   = sproutCommerce()->addressForm->getAddressFormHtml();

				$result['result'] = true;

				$result['html']            = $html;
				$result['countryCodeHtml'] = $countryCodeHtml;
				$result['formInputHtml']   = $formInputHtml;
				$result['countryCode']     = $countryCode;
			}
		}
		else
		{
			$result['result'] = false;
			$result['errors'] = $addressInfoModel->getErrors();
		}


		$this->returnJson($result);
	}
}

