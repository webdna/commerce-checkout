<?php

namespace webdna\commerce\checkout\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;
use webdna\commerce\checkout\Checkout;
use craft\elements\Entry;
use craft\fields\data\ColorData;
use webdna\commerce\checkout\assetbundles\CheckoutAsset;
use craft\helpers\App;

/**
 * Checkout controller
 */
class CheckoutController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = true;

    /**
     * checkout/checkout action
     */
    public function actionIndex(): Response
    {
        $variables = $this->getVars();
        
        Craft::$app->getView()->registerAssetBundle(CheckoutAsset::class);
        
        return $this->renderTemplate('checkout/index', $variables, 'cp');
    }
    
    public function actionShipping(): Response
    {
        $variables = $this->getVars();
        
        Craft::$app->getView()->registerAssetBundle(CheckoutAsset::class);
        
        return $this->renderTemplate('checkout/shipping', $variables, 'cp');
    }
    
    public function actionPayment(): Response
    {
        $variables = $this->getVars();
        
        Craft::$app->getView()->registerAssetBundle(CheckoutAsset::class);
        
        return $this->renderTemplate('checkout/payment', $variables, 'cp');
    }
    
    public function actionConfirmation(): Response
    {
        $variables = $this->getVars();
        
        Craft::$app->getView()->registerAssetBundle(CheckoutAsset::class);
        
        return $this->renderTemplate('checkout/confirmation', $variables, 'cp');
    }
    
    
    private function getVars(): array
    {
        $settings = Checkout::getInstance()->settings;
        $variables = array_merge([], $settings->toArray());
        $variables['brandColor'] = new ColorData('#'.$variables['brandColor']);
        $variables['policies'] = Entry::find()->id($variables['policies'])->fixedOrder(true)->all();
        
        try {
            $variables['scripts'] = Craft::$app->getView()->renderTemplate(App::parseEnv($settings['scriptTemplate']), $variables, 'site');
        } catch (\Exception $e) {
            
        }
        
        return $variables;
    }
}
