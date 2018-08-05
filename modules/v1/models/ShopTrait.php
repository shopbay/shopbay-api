<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\models;
/**
 * Description of ShopTrait
 *
 * @author kwlok
 */
trait ShopTrait 
{
    /**
     * @return array language keys
     */
    public function getLanguageKeys()
    {
        return $this->shop->getLanguageKeys();
    }
    /**
     * @return string language attribute
     */
    public function getLanguage()
    {
        return $this->shop->language;
    }
}
