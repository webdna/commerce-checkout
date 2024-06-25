<?php

namespace webdna\commerce\checkout;

use Craft;
use craft\base\Plugin;
use craft\base\Model;
use craft\helpers\App;
use yii\base\Event;
use craft\web\UrlManager;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\commerce\elements\Order;
use yii\base\ModelEvent;
use webdna\commerce\checkout\models\Settings;
use webdna\commerce\checkout\behaviors\OrderBehavior;
use GuzzleHttp\Client;

/**
 * Checkout plugin
 *
 * @method static Checkout getInstance()
 * @author webdna <info@webdna.co.uk>
 * @copyright webdna
 * @license https://craftcms.github.io/license/ Craft License
 */
class Checkout extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    private bool $recaptchaVerified = false; // Guard to prevent duplicate verification
    
    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }
    
    public function init(): void
    {
        parent::init();
        
        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });
    }
    
    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }
    
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('checkout/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }
    
    private function attachEventHandlers(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $baseUrl = App::parseEnv($this->getSettings()->baseUrl);
                
                $event->rules[$baseUrl] = 'checkout/checkout/index';
                $event->rules["$baseUrl/shipping"] = 'checkout/checkout/shipping';
                $event->rules["$baseUrl/payment"] = 'checkout/checkout/payment';
                $event->rules["$baseUrl/confirmation"] = 'checkout/checkout/confirmation';
            }
        );
        
        /***
         * This seems to be working when the recaptcha is a sucess
         * but not on failure as it still  goes to the next screen...
         * **/
        
        Event::on(
            Order::class,
            Order::EVENT_BEFORE_VALIDATE,
            function (ModelEvent $event) {
                $order = $event->sender;
                
                $request = Craft::$app->request;
                $recaptchaResponse = $request->getParam('recaptchaResponse');
                $checkout = $request->getParam('checkout');
                $recaptchaSecret = App::parseEnv($this->getSettings()->recaptchaSecretKey);
                    
                // Verify reCAPTCHA only once
                if (!$this->recaptchaVerified) {
                    $recaptchaValid = $this->verifyRecaptcha($recaptchaResponse, $recaptchaSecret);
                    $this->recaptchaVerified = true;

                    // Conditional logic to validate the order
                    if ($checkout == 'true' && !$recaptchaValid) {
                        Craft::$app->session->setError('reCAPTCHA verification failed. Please try again.');
                        $event->isValid = false;  // Prevent saving the order
                    }
                }
            }
        );
        
        Event::on(
            Order::class,
            Order::EVENT_DEFINE_BEHAVIORS,
            function(DefineBehaviorsEvent $event) {
                $event->behaviors['commerce:checkout:order'] = OrderBehavior::class;
            }
        );
    }

    private function verifyRecaptcha($token, $secret)
    {
        $client = new Client();
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
            'form_params' => [
                'secret' => $secret,
                'response' => $token,
            ],
        ]);

        $body = json_decode((string) $response->getBody());

        Craft::info('reCAPTCHA Response Body: ' . json_encode($body), __METHOD__);
        if (isset($body->success) && $body->success) {
            Craft::info('reCAPTCHA Success: ' . $body->success, __METHOD__);
            return true;
        } else {
            if (isset($body->{'error-codes'})) {
                Craft::info('reCAPTCHA Error Codes: ' . implode(', ', $body->{'error-codes'}), __METHOD__);
            }
            return false;
        }
    }
}