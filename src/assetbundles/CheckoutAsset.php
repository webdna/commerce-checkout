<?php
/**
 * Checkout plugin
 */

namespace webdna\commerce\checkout\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;


class CheckoutAsset extends AssetBundle
{
	// Public Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->sourcePath = __DIR__.'/dist';

		$this->depends = [
			//CpAsset::class,
		];

		$this->js = [
			//'js/Checkout.js',
		];

		$this->css = [
			'css/Checkout.css',
		];

		parent::init();
	}
}
