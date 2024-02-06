<?php

namespace webdna\commerce\checkout\behaviors;

use craft\commerce\elements\Order;
use craft\commerce\Plugin as Commerce;
use craft\events\ModelEvent;
use craft\helpers\ArrayHelper;
use RuntimeException;
use yii\base\Behavior;
use yii\base\InvalidConfigException;

/**
 * Order behavior.
 *
 * @property-read array $couponCodes
 */
class OrderBehavior extends Behavior
{
	/**
	 * @inheritdoc
	 */
	public function attach($owner)
	{
		if (!$owner instanceof Order) {
			throw new RuntimeException('OrderBehavior can only be attached to an Order element');
		}

		parent::attach($owner);
	}

	/**
	 * @return array
	 * @throws InvalidConfigException
	 */
	public function getIsBillingSameAsShipping(): bool
	{
		$same = true;
		
		if (!$this->owner->shippingAddress || !$this->owner->billingAddress) {
			return true;
		}
		
		if ($this->owner->shippingAddress->fullName != $this->owner->billingAddress->fullName) {
			$same = false;
		}
		if ($this->owner->shippingAddress->addressLine1 != $this->owner->billingAddress->addressLine1) {
			$same = false;
		}
		if ($this->owner->shippingAddress->countryCode != $this->owner->billingAddress->countryCode) {
			$same = false;
		}
		if ($this->owner->shippingAddress->postalCode != $this->owner->billingAddress->postalCode) {
			$same = false;
		}
		
		return $same;
	}

}
