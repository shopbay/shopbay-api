<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\models;

use Sii;
use CException;
/**
 * Description of SubscriptionTrait
 *
 * @author kwlok
 */
trait SubscriptionTrait 
{
    /*
     * Expecting params must be set up.
     */
    public $package;
    public $shop;//optional, if value is present, this is to change shop subscription; value is null is for a new subscription
    public $paymentNonce;//optional, the first time created payment method to pay subscription (for new customer)
    public $paymentToken;//optional, the selected payment method to pay subscription (for existing customer)
    /**
     * A method to prepare data for subscription
     * Parse object fields and populate field with correct values
     */
    public function prepareSubscription($rawBody)
    {
        $this->inputData = isset($rawBody)?$rawBody:[];
        if (!empty($this->inputData)){//prepare subscription data 
            
            foreach (['shop','package','paymentNonce'] as $value) {
                if (!array_key_exists($value, $this->inputData))
                    throw new CException(Sii::t('sii','Missing subscription data'));
            }
            
            $this->shop = $this->inputData['shop'];
            
            $this->paymentNonce = $this->inputData['paymentNonce'];

            if (isset($this->inputData['paymentToken']))
                $this->paymentToken = $this->inputData['paymentToken'];            
            
            $package = \Package::model()->findByPk($this->inputData['package']);
            
            if ($package==null)
                throw new CException(Sii::t('sii','Package not found'));
            
            if (!$package->existsPlan($this->id))
                throw new CException(Sii::t('sii','Plan not found'));
            
            $this->package = $package;
            logTrace(__METHOD__.' '.get_class($package).' attributes',$this->package->attributes); 
        }
    }    
}
