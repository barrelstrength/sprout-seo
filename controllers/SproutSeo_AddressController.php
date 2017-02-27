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
	protected $allowAnonymous = array('actionGetAddressFormFields', 'actionDeleteAddress');

	/**
	 * Initialize the Address Field Helper
	 */
	public function init()
	{
		$this->addressHelper = new SproutSeoAddressHelper();

		parent::init();
	}

	/**
	 * Display the Country Input for the selected Country
	 */
	public function actionCountryInput()
	{
		$addressInfoId = craft()->request->getPost('addressInfoId');

		$addressInfoModel = sproutSeo()->address->getAddressById($addressInfoId);

		$countryCode = $addressInfoModel->countryCode;

		$namespace = (craft()->request->getPost('namespace') != null) ? craft()->request->getPost('namespace') : 'address';

		$this->addressHelper->setParams($countryCode, $namespace);

		echo $this->addressHelper->countryInput();

		exit;
	}

	/**
	 * Update the Address Form HTML
	 */
	public function actionChangeForm()
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		$countryCode = craft()->request->getPost('countryCode');
		$namespace   = (craft()->request->getPost('namespace') != null) ? craft()->request->getPost('namespace') : 'address';

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

			$addressInfoModel = sproutSeo()->address->getAddressById($addressInfoId);
		}
		else
		{
			$addressInfoModel = new SproutSeo_AddressModel();

			$addressInfoModel->countryCode = $this->addressHelper->defaultCountryCode();
		}

		$html = $this->addressHelper->getAddressWithFormat($addressInfoModel);

		if ($addressInfoId == null)
		{
			$html = "";
		}

		$countryCode = $addressInfoModel->countryCode;

		$namespace = (craft()->request->getPost('namespace') != null) ? craft()->request->getPost('namespace') : 'address';

		$this->addressHelper->setParams($countryCode, $namespace, $addressInfoModel);

		$countryCodeHtml = $this->addressHelper->countryInput();
		$formInputHtml   = $this->addressHelper->getAddressFormHtml();

		$this->returnJson(array(
			'html'            => $html,
			'countryCodeHtml' => $countryCodeHtml,
			'formInputHtml'   => $formInputHtml,
			'countryCode'     => $countryCode
		));
	}

	/**
	 * Get an address
	 */
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
		$namespace   = (craft()->request->getPost('namespace') != null) ? craft()->request->getPost('namespace') : 'address';

		$source = '';

		if (craft()->request->getPost('source') != null)
		{
			$source = craft()->request->getPost('source');
		}

		$addressInfoModel = SproutSeo_AddressModel::populateModel($formValues);

		if ($addressInfoModel->validate() == true)
		{
			$html        = $this->addressHelper->getAddressWithFormat($addressInfoModel);
			$countryCode = $addressInfoModel->countryCode;

			$this->addressHelper->setParams($countryCode, $namespace, $addressInfoModel);
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

	/**
	 * Delete an address
	 */
	public function actionDeleteAddress()
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		$addressId        = null;
		$addressInfoModel = null;

		if (craft()->request->getPost('addressInfoId') != null)
		{
			$addressId        = craft()->request->getPost('addressInfoId');
			$addressInfoModel = sproutSeo()->address->getAddressById($addressId);
		}

		$result = array(
			'result' => true,
			'errors' => array()
		);

		try
		{
			$response = false;

			if (isset($addressInfoModel->id) && $addressInfoModel->id)
			{
				$addressRecord = new SproutSeo_AddressRecord;
				$response      = $addressRecord->deleteByPk($addressInfoModel->id);
			}

			$globals = craft()->db->createCommand()
				->select('*')
				->from('sproutseo_metadata_globals')
				->queryRow();

			if ($globals && $response)
			{
				$identity = $globals['identity'];
				$identity = json_decode($identity, true);

				if ($identity['addressId'] != null)
				{
					$identity['addressId'] = "";
					$globals['identity']   = json_encode($identity);

					craft()->db->createCommand()->update('sproutseo_metadata_globals',
						$globals,
						'id=:id',
						array(':id' => 1)
					);
				}
			}
		}
		catch (Exception $e)
		{
			$result['result'] = false;
			$result['errors'] = $e->getMessage();
		}

		$this->returnJson($result);
	}

	/**
	 * Find the longitude and latitude of an address
	 */
	public function actionQueryAddress()
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		$addressInfo = null;

		if (craft()->request->getPost('addressInfo') != null)
		{
			$addressInfo = craft()->request->getPost('addressInfo');
		}

		$result = array(
			'result' => false,
			'errors' => array()
		);

		try
		{
			$data = array();

			if ($addressInfo)
			{
				$addressInfo = str_replace("\n", " ", $addressInfo);
				// Get JSON results from this request
				$geo = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($addressInfo) . '&sensor=false');

				// Convert the JSON to an array
				$geo = json_decode($geo, true);

				if ($geo['status'] == 'OK')
				{
					$data['latitude']  = $geo['results'][0]['geometry']['location']['lat'];
					$data['longitude'] = $geo['results'][0]['geometry']['location']['lng'];

					$result = array(
						'result' => true,
						'errors' => array(),
						'geo'    => $data
					);
				}
			}
		}
		catch (Exception $e)
		{
			$result['result'] = false;
			$result['errors'] = $e->getMessage();
		}

		$this->returnJson($result);
	}
}

