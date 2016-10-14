<?php

namespace Craft;


class SproutSeo_AddressController extends BaseController
{
	/**
	 * @var SproutSeoAddressHelper $addressHelper
	 */
	protected $addressHelper;

	/**
	 * Allow anonymous actions as defined within this array
	 *
	 * @var array
	 */

	protected $allowAnonymous = array('actionGetAddressFormFields');

	public function init()
	{
		$this->addressHelper = new SproutSeoAddressHelper();

		parent::init();
	}

	public function actionCountryInput()
	{
		$addressInfoId = craft()->request->getPost('addressInfoId');

		$addressInfoModel = sproutSeo()->addressInfo->getAddressById($addressInfoId);

		$countryCode = $addressInfoModel->countryCode;

		$namespace = (craft()->request->getPost('namespace') != null)? craft()->request->getPost('namespace') : 'address';

		$this->addressHelper->setParams($countryCode, $namespace);

		echo $this->addressHelper->countryInput();

		exit;
	}

	public function actionChangeForm()
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		$countryCode = craft()->request->getPost('countryCode');
		$namespace   = (craft()->request->getPost('namespace') != null)? craft()->request->getPost('namespace') : 'address';

		$this->addressHelper->setParams($countryCode, $namespace);

		echo $this->addressHelper->getAddressFormHtml();
		exit;
	}

	/**
	 * Return all Address Form Fields for the selected Country
	 */

	public function actionGetAddressFormFields()
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
			$addressInfoModel = new SproutSeo_AddressModel();

			$addressInfoModel->countryCode = $this->addressHelper->defaultCountryCode();
		}

		$html = $this->addressHelper->getAddressWithFormat($addressInfoModel);

		if ($addressInfoId == null)
		{
			$html = "<p>" . Craft::t("No Address") . ".</p>";
		}

		$countryCode = $addressInfoModel->countryCode;

		$namespace = (craft()->request->getPost('namespace') != null)? craft()->request->getPost('namespace') : 'address';

		$this->addressHelper->setParams($countryCode, $namespace, '', $addressInfoModel);

		$countryCodeHtml = $this->addressHelper->countryInput();
		$formInputHtml   = $this->addressHelper->getAddressFormHtml();

		$this->returnJson(array(
			'html'            => $html,
			'countryCodeHtml' => $countryCodeHtml,
			'formInputHtml'   => $formInputHtml,
			'countryCode'     => $countryCode
		));
	}

	public function actionGetAddress()
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		$result = array(
			'result' => true,
			'errors' => array()
		);

		$addressInfo = craft()->request->getPost('addressInfo');
		$formValues  = craft()->request->getPost('formValues');
		$namespace   = (craft()->request->getPost('namespace') != null)? craft()->request->getPost('namespace') : 'address';

		$source = '';

		if (craft()->request->getPost('source') != null)
		{
			$source = craft()->request->getPost('source');
		}

		$addressInfoModel = SproutSeo_AddressModel::populateModel($formValues);

		if ($addressInfoModel->validate() == true)
		{
			$html = $this->addressHelper->getAddressWithFormat($addressInfoModel);
			$countryCode = $addressInfoModel->countryCode;

			$this->addressHelper->setParams($countryCode, $namespace, '', $addressInfoModel);
			$countryCodeHtml = $this->addressHelper->countryInput();
			$formInputHtml   = $this->addressHelper->getAddressFormHtml();

			$result['result'] = true;

			$result['html']            = $html;
			$result['countryCodeHtml'] = $countryCodeHtml;
			$result['formInputHtml']   = $formInputHtml;
			$result['countryCode']     = $countryCode;
		}
		else
		{
			$result['result'] = false;
			$result['errors'] = $addressInfoModel->getErrors();
		}

		$this->returnJson($result);
	}
}

