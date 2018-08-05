<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. or refer to LICENSE.md
 */
namespace tests\codeception\_support;

use Codeception\Module;
use yii\test\FixtureTrait;
/**
 * This helper is used to populate database with needed fixtures before any tests should be run.
 * For example - populate database with demo login user that should be used in acceptance and functional tests.
 * All fixtures will be loaded before suite will be starded and unloaded after it.
 */
class FixtureHelper extends Module
{
    /**
     * Redeclare visibility because codeception includes all public methods that not starts from "_"
     * and not excluded by module settings, in actor class.
     */
    use FixtureTrait 
    {
        loadFixtures as protected;
        fixtures as protected;
        globalFixtures as protected;
        unloadFixtures as protected;
        getFixtures as protected;
        getFixture as public;
    }
    /**
     * Method called before any suite tests run. Loads User fixture login user
     * to use in acceptance and functional tests.
     * @param array $settings
     */
    public function _beforeSuite($settings = [])
    {
        $this->loadFixtures();
    }
    /**
     * Method is called after all suite tests run
     */
    public function _afterSuite()
    {
        $this->unloadFixtures();
    }
    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'authAssignment' => [
                'class' => \tests\codeception\fixtures\AuthAssignmentFixture::className(),
                'dataFile' => '@tests/codeception/fixtures/data/auth_assignment.php',
            ],
            'account' => [
                'class' => \tests\codeception\fixtures\AccountFixture::className(),
                'dataFile' => '@tests/codeception/fixtures/data/account.php',
            ],
            'accountProfile' => [
                'class' => \tests\codeception\fixtures\AccountProfileFixture::className(),
                'dataFile' => '@tests/codeception/fixtures/data/account_profile.php',
            ],
            'oauthClient' => [
                'class' => \tests\codeception\fixtures\OauthClientsFixture::className(),
                'dataFile' => '@tests/codeception/fixtures/data/oauth_client.php',
            ],
            'feature' => [
                'class' => \tests\codeception\fixtures\FeatureFixture::className(),
                'dataFile' => '@tests/codeception/fixtures/data/feature.php',
            ],
            'package' => [
                'class' => \tests\codeception\fixtures\PackageFixture::className(),
                'dataFile' => '@tests/codeception/fixtures/data/package.php',
            ],
            'plan' => [
                'class' => \tests\codeception\fixtures\PlanFixture::className(),
                'dataFile' => '@tests/codeception/fixtures/data/plan.php',
            ],
            'planItem' => [
                'class' => \tests\codeception\fixtures\PlanItemFixture::className(),
                'dataFile' => '@tests/codeception/fixtures/data/plan_item.php',
            ],
            'shop' => [
                'class' => \tests\codeception\fixtures\ShopFixture::className(),
                'dataFile' => '@tests/codeception/fixtures/data/shop.php',
            ],
            'subscription' => [
                'class' => \tests\codeception\fixtures\SubscriptionFixture::className(),
                'dataFile' => '@tests/codeception/fixtures/data/subscription.php',
            ],
        ];
    }
}