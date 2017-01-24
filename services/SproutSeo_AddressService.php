<?php

namespace Craft;


class SproutSeo_AddressService extends BaseApplicationComponent
{

	public function saveAddressByPost($namespace = 'address', int $modelId = null)
	{
		if (craft()->request->getPost($namespace) != null)
		{
			$addressInfo = craft()->request->getPost($namespace);

			if ($modelId != null)
			{
				$addressInfo['modelId'] = $modelId;
			}

			$addressInfoModel = SproutSeo_AddressModel::populateModel($addressInfo);

			if ($addressInfoModel->validate() == true && $this->saveAddress($addressInfoModel))
			{
				return $addressInfoModel->id;
			}
		}

		return false;
	}

	public function saveAddress(SproutSeo_AddressModel $model, $source = '')
	{
		$result = false;

		$record = new SproutSeo_AddressRecord;

		if (!empty($model->id))
		{
			$record = SproutSeo_AddressRecord::model()->findById($model->id);

			if (!$record)
			{
				throw new Exception(Craft::t('No entry exists with the ID “{id}”', array('id' => $model->id)));
			}
		}

		$attributes = $model->getAttributes();

		if (!empty($attributes))
		{
			foreach ($model->getAttributes() as $handle => $value)
			{
				$record->setAttribute($handle, $value);
			}
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		if ($record->validate())
		{
			try
			{
				if ($record->save())
				{
					if ($transaction && $transaction->active)
					{
						$transaction->commit();
					}

					$model->setAttributes($record->getAttributes());

					$result = true;

					$eventParams = array(
						'model'  => $model,
						'source' => $source
					);

					$event = new Event($this, $eventParams);

					sproutSeo()->onSaveAdderssInfo($event);
				}
			}
			catch (\Exception $e)
			{
				if ($transaction && $transaction->active)
				{
					$transaction->rollback();
				}

				throw $e;
			}

		}
		else
		{
			$model->addErrors($record->getErrors());
		}

		if (!$result)
		{
			if ($transaction && $transaction->active)
			{
				$transaction->rollback();
			}
		}

		return $result;
	}

	public function getAddressById($id)
	{
		if ($record = SproutSeo_AddressRecord::model()->findByPk($id))
		{
			return SproutSeo_AddressModel::populateModel($record);
		}
		else
		{
			return new SproutSeo_AddressModel();
		}
	}

	/**
	 * @param null $id
	 *
	 * @return int
	 */
	public function deleteAddressById($id = null)
	{
		$record = new SproutFields_AddressRecord();

		return $record->deleteByPk($id);
	}


	/**
	 * @param null $id
	 *
	 * @return int
	 */
	public function deleteAddressByModelId($id = null)
	{
		$record = new SproutFields_AddressRecord();

		$attributes = array(
			'modelId' => $id
		);

		return $record->deleteAllByAttributes($attributes);
	}
}