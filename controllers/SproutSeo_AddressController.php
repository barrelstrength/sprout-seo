<?php

namespace Craft;


class SproutSeo_AddressController extends BaseController
{
	protected $allowAnonymous = true;

	public function actionCountryInput()
	{
		$addressInfoId = craft()->request->getPost('addressInfoId');

		$addressInfoModel = sproutSeo()->addressInfo->getAddressById($addressInfoId);

		$countryCode = $addressInfoModel->countryCode;

		sproutSeo()->addressForm->setParams($countryCode, 'address');

		echo sproutSeo()->addressForm->countryInput();

		exit;
	}

	public function actionChangeForm()
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		$countryCode = craft()->request->getPost('countryCode');

		sproutSeo()->addressForm->setParams($countryCode, 'address');

		echo sproutSeo()->addressForm->getAddressFormHtml();
		exit;
	}

	public function actionUpdateAddressFormat()
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		$addressInfoId = null;

		if (craft()->request->getPost('addressInfoId') != null)
		{
			$addressInfoId = craft()->request->getPost('addressInfoId');

			$addressInfoModel = sproutSeo()->addressInfo->getAddressById($addressInfoId);
		}
		else
		{
			$addressInfoModel = new SproutSeo_AddressInfoModel();

			$addressInfoModel->countryCode = sproutSeo()->addressForm->defaultCountryCode();
		}

		$html = sproutSeo()->addressForm->getAddressWithFormat($addressInfoModel);

		if ($addressInfoId == null)
		{
			$html = "<p>" . Craft::t("No Address") . ".</p>";
		}

		$countryCode = $addressInfoModel->countryCode;

		sproutSeo()->addressForm->setParams($countryCode, 'address', '', $addressInfoModel);

		$countryCodeHtml = sproutSeo()->addressForm->countryInput();
		$formInputHtml   = sproutSeo()->addressForm->getAddressFormHtml();

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

		$source = '';

		if (craft()->request->getPost('source') != null)
		{
			$source = craft()->request->getPost('source');
		}

		$addressInfoModel = SproutSeo_AddressInfoModel::populateModel($formValues);

		if ($addressInfoModel->validate() == true)
		{
			if (sproutSeo()->addressInfo->saveAddressInfo($addressInfoModel, $source))
			{
				$html = sproutSeo()->addressForm->getAddressWithFormat($addressInfoModel);
				$countryCode = $addressInfoModel->countryCode;

				sproutSeo()->addressForm->setParams($countryCode, 'address', '', $addressInfoModel);
				$countryCodeHtml = sproutSeo()->addressForm->countryInput();
				$formInputHtml   = sproutSeo()->addressForm->getAddressFormHtml();

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

