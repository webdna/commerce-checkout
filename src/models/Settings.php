<?php

namespace webdna\commerce\checkout\models;

use Craft;
use craft\base\Model;

/**
 * checkout settings
 */
class Settings extends Model
{
    public string $baseUrl = '';
    
    public string $cartUrl = '';
    
    public string $logoUrl = '';
    
    public mixed $brandColor = '';
    
    public bool $buttonText = false;
    
    public string $supportPhone = '';
    
    public mixed $policies = [];
}
