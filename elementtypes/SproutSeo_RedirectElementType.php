<?php
namespace Craft;

/**
 * SproutSeo - Redirect element type
 */
class SproutSeo_RedirectElementType extends BaseElementType
{
	/**
	 * Returns the element type name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Redirects');
	}

	/**
	 * Returns whether this element type has content.
	 *
	 * @return bool
	 */
	public function hasContent()
	{
		return false;
	}

	/**
	 * @inheritDoc IElementType::hasStatuses()
	 *
	 * @return bool
	 */
	public function hasStatuses()
	{
		return true;
	}

	/**
	 * Returns whether this element type has titles.
	 *
	 * @return bool
	 */
	public function hasTitles()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function isLocalized()
	{
		return false;
	}

	/**
	 * Returns the attributes that can be shown/sorted by in table views.
	 *
	 * @param string|null $source
	 * @return array
	 */
	public function defineTableAttributes($source = null)
	{
		return array(
			'oldUrl' => Craft::t('Old Url'),
			'newUrl' => Craft::t('New Url'),
			'method' => Craft::t('Method'),
			'test'   => Craft::t('Test')
		);
	}

	/**
	 * Returns the attributes that can be sorted by in table views.
	 *
	 * @param string|null $source
	 * @return array
	 */
	public function defineSortableAttributes($source = null)
	{
		return array(
			'oldUrl' => Craft::t('Old Url'),
			'newUrl' => Craft::t('New Url'),
			'method' => Craft::t('Method')
		);
	}

	/**
	 * Returns this element type's sources.
	 *
	 * @param string|null $context
	 * @return array|false
	 */
	public function getSources($context = null)
	{
		$sources = array(
			'*' => array(
				'label'    => Craft::t('All redirects'),
			)
		);

		$methods = SproutSeo_RedirectMethods::getConstants();

		foreach ($methods as $code => $method)
		{
			$key = 'method:'.$method;
			$sources[$key] = array(
				'label'    => $method.' - '.$code,
				'criteria' => array('method' => $method)
			);
		}

		return $sources;
	}

	/**
	 * @inheritDoc IElementType::getAvailableActions()
	 *
	 * @param string|null $source
	 *
	 * @return array|null
	 */
	public function getAvailableActions($source = null)
	{
		$deleteAction = craft()->elements->getAction('Delete');

		$deleteAction->setParams(
			array(
				'confirmationMessage' => Craft::t('Are you sure you want to delete the selected redirects?'),
				'successMessage'      => Craft::t('Redirects deleted.'),
			)
		);

		$editAction = craft()->elements->getAction('Edit');
		$editAction->setParams(array(
			'label' => Craft::t('Edit Redirect'),
		));

		$changePermanentMethod = craft()->elements->getAction('SproutSeo_ChangePermanentMethod');
		$changeTemporaryMethod = craft()->elements->getAction('SproutSeo_ChangeTemporaryMethod');

		$setStatusAction = craft()->elements->getAction('SproutSeo_SetStatus');

		return array($deleteAction, $editAction, $changePermanentMethod, $changeTemporaryMethod, $setStatusAction);
	}

	/**
	 * Defines any custom element criteria attributes for this element type.
	 *
	 * @return array
	 */
	public function defineCriteriaAttributes()
	{
		return array(
			'id'     => AttributeType::Number,
			'oldUrl' => AttributeType::String,
			'newUrl' => AttributeType::String,
			'method' => AttributeType::Number
		);
	}

	public function defineSearchableAttributes()
	{
		return array('oldUrl', 'newUrl','method','regex');
	}

	/**
	 * @param BaseElementModel $element
	 * @param string           $attribute
	 *
	 * @return string
	 */
	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{
		switch ($attribute)
		{
			case 'method':
				$method = $element->$attribute;
				//Get method options
				$methods = array_flip(SproutSeo_RedirectMethods::getConstants());
				return $method.' - '.$methods[$method];

			case 'test':
				// Send link for testing
				$link = "<a href='{$element->oldUrl}' target='_blank' class='go'>Test</a>";

				if($element->regex)
				{
					$link = " - ";
				}
				return $link;

			default:
				return parent::getTableAttributeHtml($element, $attribute);
				break;
		}
	}

	/**
	 * Modifies an element query targeting elements of this type.
	 *
	 * @param DbCommand $query
	 * @param ElementCriteriaModel $criteria
	 * @return mixed
	 */
	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query
			->addSelect('redirects.oldUrl, redirects.newUrl, redirects.method, redirects.id, redirects.regex')
			->join('sproutseo_redirects redirects', 'redirects.id = elements.id');

		if ($criteria->id)
		{
			$query->andWhere(DbHelper::parseParam('redirects.id', $criteria->id, $query->params));
		}
		if ($criteria->method)
		{
			$query->andWhere(DbHelper::parseParam('redirects.method', $criteria->method, $query->params));
		}
		if ($criteria->oldUrl)
		{
			$query->andWhere(DbHelper::parseParam('redirects.oldUrl', $criteria->oldUrl, $query->params));
		}
		if ($criteria->newUrl)
		{
			$query->andWhere(DbHelper::parseParam('redirects.newUrl', $criteria->newUrl, $query->params));
		}
	}


	/**
	 * Populates an element model based on a query result.
	 *
	 * @param array $row
	 * @return array
	 */
	public function populateElementModel($row)
	{
		return SproutSeo_RedirectModel::populateModel($row);
	}

	/**
	 * Returns the HTML for an editor HUD for the given element.
	 *
	 * @param BaseElementModel $element
	 *
	 * @return string
	 */
	public function getEditorHtml(BaseElementModel $element)
	{
		$methodOptions = sproutSeo()->redirects->getMethods();
		// get template
		$html = craft()->templates->render('sproutseo/redirect/_editor', array(
			'redirect' => $element,
			'methodOptions' => $methodOptions
		));

		// Everything else
		$html .= parent::getEditorHtml($element);

		return $html;
	}

	/**
	 * @inheritdoc BaseElementType::saveElement()
	 *
	 * @return bool
	 */
	public function saveElement(BaseElementModel $element, $params)
	{
		// Route this through RedirectsService::saveRedirect() so the proper redirect events get fired.
		$redirect = new SproutSeo_RedirectModel();
		$redirect->id = $element->id;
		$redirect->oldUrl = $params['oldUrl'];
		$redirect->newUrl = $params['newUrl'];
		$redirect->method = $params['method'];
		$redirect->regex = $params['regex'];
		// send response
		return sproutSeo()->redirects->saveRedirect($redirect);;
	}
}
