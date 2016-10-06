<?php

namespace Craft;


class SproutSeo_AddressInfoService extends BaseApplicationComponent
{

	public function saveAddressInfo(SproutCommerce_AddressInfoModel $model)
	{
		$result = false;

		$record = new SproutCommerce_AddressInfoRecord;

		if (!empty($model->id))
		{
			$record = SproutCommerce_AddressInfoRecord::model()->findById($model->id);

			if (!$record)
			{
				throw new Exception(Craft::t('No entry exists with the ID “{id}”', array('id' => $model->id)));
			}
		}

		if (!empty($model->getAttributes()))
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
		if ($record = SproutCommerce_AddressInfoRecord::model()->findByPk($id))
		{
			return SproutCommerce_AddressInfoModel::populateModel($record);
		}
		else
		{
			return new SproutCommerce_AddressInfoModel();
		}
	}
}