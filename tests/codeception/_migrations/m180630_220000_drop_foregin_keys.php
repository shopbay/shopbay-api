<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
use yii\db\Schema;
use yii\db\Migration;
/**
 * Description of m180630_220000_drop_foregin_keys
 * This file is to drop foreign keys of certain tables, as some test requires to delete data 
 * and they might fail due to foreign key contraints
 * 
 * 
 * @author kwlok
 */
class m180630_220000_drop_foregin_keys extends Migration
{
    public function up()
    {
        //Foreign key constraints - account: account_profile, shop, shipping, shipping_order, payment_method, tax, theme, tutorial, ticket
        //Foreign key constraints - shop: order, shop_address, shipping_order
        //Foreign key constraints - order: item, order_address, shipping_order
        //Foreign key constraints - subscription: subscription_plan
        $this->dropForeignKey('s_account_profile_ibfk_1','s_account_profile');
        $this->dropForeignKey('s_shop_ibfk_1','s_shop');
        $this->dropForeignKey('s_shop_address_ibfk_1','s_shop_address');
        $this->dropForeignKey('s_order_ibfk_2','s_order');
        $this->dropForeignKey('s_order_address_ibfk_1','s_order_address');
        $this->dropForeignKey('s_item_ibfk_2','s_item');
        $this->dropForeignKey('s_shipping_ibfk_1','s_shipping');
        $this->dropForeignKey('s_shipping_order_ibfk_1','s_shipping_order');
        $this->dropForeignKey('s_shipping_order_ibfk_2','s_shipping_order');
        $this->dropForeignKey('s_shipping_order_ibfk_3','s_shipping_order');
        $this->dropForeignKey('s_payment_config_ibfk_1','s_payment_method');
        $this->dropForeignKey('s_tax_ibfk_1','s_tax');
        $this->dropForeignKey('s_theme_ibfk_1','s_theme');
        $this->dropForeignKey('s_tutorial_ibfk_1','s_tutorial');
        $this->dropForeignKey('s_ticket_ibfk_1','s_ticket');
        $this->dropForeignKey('s_subscription_plan_ibfk_1','s_subscription_plan');
        $this->dropForeignKey('s_subscription_plan_ibfk_2','s_subscription_plan');
        $this->dropForeignKey('s_subscription_plan_ibfk_3','s_subscription_plan');
        //Alternative 2: Change to foreign key constraint to "ON CASCADE" to work
    }

    public function down()
    {
        echo "m180630_220000_drop_foregin_keys does not support migration down.\n";
        return false;
    }
}
