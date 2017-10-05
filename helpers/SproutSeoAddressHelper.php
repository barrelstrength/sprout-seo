<?php

namespace Craft;

require dirname(__FILE__) . '/../vendor/autoload.php';

use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;
use CommerceGuys\Addressing\Repository\CountryRepository;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Model\Address;

class SproutSeoAddressHelper
{
	/**
	 * @var
	 */
	protected $addressObj;

	/**
	 * @var
	 */
	protected $subdivisonObj;

	/**
	 * @var
	 */
	private $name;

	/**
	 * @var
	 */
	private $addressModel;

	/**
	 * @var
	 */
	protected $countryCode;

	/**
	 * @param                             $countryCode
	 * @param string                      $name
	 * @param SproutSeo_AddressModel|null $addressModel
	 */
	public function setParams($countryCode,
	                          $name = 'address',
	                          SproutSeo_AddressModel $addressModel = null)
	{
		$this->name               = $name;
		$this->addressModel   = ($addressModel == null) ? new SproutSeo_AddressModel : $addressModel;
		$this->countryCode        = $countryCode;
	}

	/**
	 * @return mixed
	 */
	public function getAddressFormHtml()
	{
		$countryCode = $this->countryCode;

		$addressRepo         = new AddressFormatRepository;
		$this->subdivisonObj = new SubdivisionRepository;
		$this->addressObj    = $addressRepo->get($countryCode);
		$format              = $this->addressObj->getFormat();

		// Remove unused format
		$format = preg_replace('/%recipient/', '', $format);
		$format = preg_replace('/%organization/', '', $format);

		// Insert line break based on the format
		//$format = nl2br($format);

		// More whitespace
		$format = preg_replace('/,/', '', $format);

		$format = preg_replace('/%addressLine1/', $this->addressLine('address1'), $format);
		$format = preg_replace('/%addressLine2/', $this->addressLine('address2'), $format);
		$format = preg_replace('/%dependentLocality/', $this->dependentLocality(), $format);
		$format = preg_replace('/%locality/', $this->locality(), $format);

		if (preg_match('/%sortingCode/', $format))
		{
			$format = preg_replace('/%sortingCode/', $this->sortingCode(), $format);
		}

		$format = preg_replace('/%administrativeArea/', $this->administrativeArea(), $format);
		$format = preg_replace('/%postalCode/', $this->postalCode(), $format);

		if ($this->addressModel->id != null)
		{
			$format .= $this->getAddressInfoInput();
		}

		return $format;
	}

	private function getAddressInfoInput()
	{
		return $this->renderTemplates('hidden', array(
			'name' => $this->name . '[id]',
			'value' => $this->addressModel->id
		));
	}

	public function displayAddressForm(SproutSeo_AddressModel $addressModel = null, $namespace = 'address')
	{
		$countryCode = $this->defaultCountryCode();

		if (isset($addressModel->countryCode))
		{
			$countryCode = $addressModel->countryCode;
		}

		$this->setParams($countryCode, $namespace, $addressModel);

		$countryInput = $this->countryInput();

		$form = $this->getAddressFormHtml();

		$html = $this->renderTemplates('form', array(
			'countryInput' => TemplateHelper::getRaw($countryInput),
			'form'         => TemplateHelper::getRaw($form),
			'actionUrl'    => UrlHelper::getActionUrl('sproutSeo/changeForm')
		));

		return TemplateHelper::getRaw($html);
	}

	/**
	 * @return string
	 */
	public function countryInput($hidden = null)
	{
		$countries = $this->getCountries();

		return $this->renderTemplates(
			'select',
			array(
				'class'              => 'sproutaddress-country-select',
				'label'              => $this->renderHeading('Country'),
				'name'               => $this->name . "[countryCode]",
				'inputName'          => 'countryCode',
				'options'            => $countries,
				'value'              => $this->countryCode,
				'nameValue'          => $this->name,
				'hidden'             => $hidden,
				'addressInfo'        => $this->addressModel
			)
		);
	}

	/**
	 * @param $addressName
	 *
	 * @return string
	 */
	private function addressLine($addressName)
	{
		$value = $this->addressModel->$addressName;

		$addressLabel = $this->renderHeading('Address 1');

		if ($addressName == 'address2')
		{
			$addressLabel = $this->renderHeading('Address 2');
		}

		return $this->renderTemplates(
			'text',
			array(
				'fieldClass' => 'field-address-input',
				'label' => $addressLabel,
				'name'  => $this->name . "[$addressName]",
				'value' => $value,
				'inputName'   => $addressName,
				'addressInfo' => $this->addressModel
			)
		);
	}

	/**
	 * @return string
	 */
	private function sortingCode()
	{
		$value = $this->addressModel->sortingCode;

		return $this->renderTemplates(
			'text',
			array(
				'fieldClass' => 'field-address-input',
				'label' => $this->renderHeading('Sorting Code'),
				'name'  => $this->name . "[sortingCode]",
				'value' => $value,
				'inputName' => 'sortingCode',
				'addressInfo' => $this->addressModel
			)
		);
	}

	/**
	 * @return string
	 */
	private function locality()
	{
		$value = $this->addressModel->locality;

		return $this->renderTemplates(
			'text',
			array(
				'fieldClass' => 'field-address-input',
				'label' => $this->renderHeading($this->addressObj->getLocalityType()),
				'name'  => $this->name . "[locality]",
				'value' => $value,
				'inputName' => 'locality',
				'addressInfo' => $this->addressModel
			)
		);
	}

	/**
	 * @return string
	 */
	private function dependentLocality()
	{
		$value = $this->addressModel->dependentLocality;

		return $this->renderTemplates(
			'text',
			array(
				'fieldClass' => 'field-address-input',
				'label' => $this->renderHeading($this->addressObj->getDependentLocalityType()),
				'name'  => $this->name . "[dependentLocality]",
				'value' => $value,
				'inputName' => 'dependentLocality',
				'addressInfo' => $this->addressModel
			)
		);
	}

	/**
	 * @return string
	 */
	private function administrativeArea()
	{
		$value = $this->addressModel->administrativeArea;

		$options = array();

		if ($this->subdivisonObj->getAll($this->countryCode))
		{
			$states = $this->subdivisonObj->getAll($this->countryCode);
			if (!empty($states))
			{
				foreach ($states as $state)
				{
					$stateName           = $state->getName();
					$options[$stateName] = $stateName;
				}

				return $this->renderTemplates(
					'select',
					array(
						'fieldClass' => 'field-address-input',
						'label'   => $this->renderHeading($this->addressObj->getAdministrativeAreaType()),
						'name'    => $this->name . "[administrativeArea]",
						'options' => $options,
						'value'   => $value,
						'inputName' => 'administrativeArea',
						'addressInfo' => $this->addressModel
					)
				);
			}
		}
		else
		{
			return $this->renderTemplates(
				'text',
				array(
					'fieldClass' => 'field-address-input',
					'label' => $this->renderHeading($this->addressObj->getAdministrativeAreaType()),
					'name'  => $this->name . "[administrativeArea]",
					'value' => $value,
					'inputName' => 'administrativeArea',
					'addressInfo' => $this->addressModel
				)
			);
		}
	}

	/**
	 * @return string
	 */
	public function postalCode()
	{
		$value = $this->addressModel->postalCode;

		return $this->renderTemplates(
			'text',
			array(
				'fieldClass' => 'field-address-input',
				'label' => $this->renderHeading($this->addressObj->getPostalCodeType()),
				'name'  => $this->name . "[postalCode]",
				'value' => $value,
				'inputName' => 'postalCode',
				'addressInfo' => $this->addressModel
			)
		);
	}

	/**
	 * @param SproutFields_AddressModel $model
	 *
	 * @return mixed
	 */
	public function getAddressWithFormat(SproutSeo_AddressModel $model)
	{
		$address                 = new Address();
		$addressFormatRepository = new AddressFormatRepository();
		$countryRepository       = new CountryRepository();
		$subdivisionRepository   = new SubdivisionRepository();

		$formatter = new DefaultFormatter($addressFormatRepository, $countryRepository, $subdivisionRepository);

		$address = $address
			->setCountryCode($model->countryCode)
			->setAdministrativeArea($model->administrativeArea)
			->setLocality($model->locality)
			->setPostalCode($model->postalCode)
			->setAddressLine1($model->address1)
			->setAddressLine2($model->address2);

		if ($model->dependentLocality != null)
		{
			$address->setDependentLocality($model->dependentLocality);
		}

		return $formatter->format($address);
	}

	/**
	 * @param $value
	 *
	 * @return array|bool
	 */
	public function validate($value)
	{
		if (!isset($value['countryCode']))
		{
			return true;
		}

		$countryCode = $value['countryCode'];

		if (empty($value['postalCode']) || !isset($value['postalCode']))
		{
			return true;
		}

		$addressFormatRepository = new AddressFormatRepository();

		$addressObj = $addressFormatRepository->get($countryCode);

		$postalName = $addressObj->getPostalCodeType();
		$errors     = array();

		if ($addressObj->getPostalCodePattern() != null)
		{
			$pattern = $addressObj->getPostalCodePattern();

			if (preg_match("/^" . $pattern . "$/", $value['postalCode']))
			{
				return true;
			}
			else
			{
				$errors['postalCode'] = Craft::t(ucwords($postalName) . ' is invalid.');
			}
		}

		if (!empty($errors))
		{
			return $errors;
		}

		return true;
	}

	public function validatePostalCode($countryCode, $postalCode)
	{
		$addressFormatRepository = new AddressFormatRepository();

		$addressObj = $addressFormatRepository->get($countryCode);

		$postalName = $addressObj->getPostalCodeType();

		if ($addressObj->getPostalCodePattern() != null)
		{
			$pattern = $addressObj->getPostalCodePattern();

			if (preg_match("/^" . $pattern . "$/", $postalCode))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	public function getPostalName($countryCode)
	{
		$addressFormatRepository = new AddressFormatRepository();

		$addressObj = $addressFormatRepository->get($countryCode);

		$postalName = $addressObj->getPostalCodeType();

		return ucwords($postalName);
	}

	/**
	 * @param $title
	 *
	 * @return null|string
	 */
	public function renderHeading($title)
	{
		return Craft::t(str_replace('_', ' ', ucwords($title)));
	}

	/**
	 * @return array
	 */
	public function getCountries()
	{
		$countries = array
		(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua And Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia And Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo',
			'CD' => 'Congo, Democratic Republic',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote D\'Ivoire',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands (Malvinas)',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island & Mcdonald Islands',
			'VA' => 'Holy See (Vatican City State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran, Islamic Republic Of',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle Of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KR' => 'Korea',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia, Federated States Of',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory, Occupied',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts And Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre And Miquelon',
			'VC' => 'Saint Vincent And Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome And Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia And Sandwich Isl.',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard And Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad And Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks And Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Viet Nam',
			'VG' => 'Virgin Islands, British',
			'VI' => 'Virgin Islands, U.S.',
			'WF' => 'Wallis And Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);

		return $countries;
	}

	/**
	 * @return string
	 */
	public function defaultCountryCode()
	{
		return 'US';
	}

	public function renderTemplates($template, $params, $folder = '')
	{
		if (empty($folder))
		{
			$class = (new \ReflectionClass($this))->getShortName();;
			$folder = str_replace('addresshelper', '', strtolower($class));
		}

		$path = "$folder/_fieldtypes/address/" . $template;

		$html = craft()->templates->render($path, $params);

		return $html;
	}
}