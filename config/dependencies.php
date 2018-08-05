<?php
/**
 * This file set aliases and import module dependencies
 * @see bootstrapYii1Engine() for its loading
 */
$root = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..';

$depends = [
    'base'=>[
    //----------------------
    // Alias mapping
    //----------------------
        'common' => 'shopbay-kernel', //actual folder name
        'api' => 'shopbay-api',
    ],
    //---------------------------
    // Common modules / resources
    //---------------------------
    'module'=>[
        'common' => [
            'import'=>[
                'components.*',
                'services.WorkflowManager',
                'widgets.SWidget',
                'widgets.sloader.SLoader',
                'widgets.stooltip.SToolTip',
                'widgets.SButtonColumn',
                'widgets.simagemanager.behaviors.*',
                'controllers.*',
                'models.*',
                'extensions.*',
            ],
        ],
        'rights' => [
            'import'=>[
                'components.*',
            ],
        ],
        'accounts' => [
            'config'=> [
                'serviceMode'=>'api',
            ],
            'import'=>[
                'components.*',
                'users.Role',
                'users.Task',
                'users.WebUser',
            ],
        ],        
        'images' => [
            'import'=>[
                'components.*',
                'components.Img',
            ],
            'config'=> [
                 'createOnDemand'=>true, // requires apache mod_rewrite enabled
            ],
        ],
        'tasks'=> [
            'import'=> [
                'models.*',
                'behaviors.WorkflowBehavior',
            ],
        ],
        'messages'=> [
            'import'=> [
                'models.Message',
            ],
        ],
        'shops' => [
            'import'=> [
                'models.ShopSetting',
                'behaviors.ShopBehavior',
                'behaviors.ShopConfigBehavior',
            ],
        ],
        'questions'=> [
            'import'=> [
                'models.Question',
                'models.QuestionForm',
                'behaviors.QuestionableBehavior',
            ],
        ],
        'likes' => [
            'import'=> [
                'models.LikeForm',
                'models.Like',
                'behaviors.LikableBehavior',
            ],
        ],
        'comments' => [
            'import'=> [
                'models.CommentForm',
                'models.Comment',
                'behaviors.CommentableBehavior',
            ],
        ],
        'payments'=> [
            'import'=> [
                'components.*',
            ],            
        ],
        'notifications'=> [],
        'analytics' => [
            'import'=> [
                'models.*',
                'components.ChartFactory',
            ],
        ],
        'search' => [
            'import'=> [
                'behaviors.SearchableBehavior',
            ],
        ],
        'tickets' => [
            'import'=>[
                'models.Ticket',
            ],
        ],
        'tutorials' => [
            'import'=>[
                'models.Tutorial',
            ],
        ],
        'help'=> [],
        'plans' => [
            'config'=>[
                'serviceMode'=>'api',
            ],
            'import'=>[
                'models.*',
            ],
        ],        
        'billings' => [
            'config'=>[
                'serviceMode'=>'api',
                'paymentGateway'=>'common.modules.payments.plugins.braintreeRecurringBilling.components.BraintreeRecurringBillingGateway',
            ],            
            'import'=>[
                'models.*',
            ],
        ],    
        'media' => [
            'import'=>[
                'models.*',
            ],
        ],       
        'customers' => [
            'import'=>[
                'models.Customer',
                'models.CustomerAccount',
            ],
        ],
        'pages' => [
            'import'=>[
                'models.Page',
            ],
        ],
        //plain modules contains components/behaviors/models without controllers/views
        'activities'=> [
            'import'=> [
                'models.Activity',
                'behaviors.ActivityBehavior',
            ],
        ],
        'orders' => [
            'import'=> [
                'models.ShippingOrder',
                'models.OrderItemForm',
            ],
        ],
        'items' => [],
        'news'=> [
            'import'=> [
                'models.News',
            ],
        ],
        'brands' => [],
        'campaigns' => [],
        'products' => [],
        'shippings' => [],
        'taxes' => [],
        'inventories' => [],
    ],
    //----------------------
    // Local modules
    // Format: local module name
    //----------------------
    'local'=>[],
];
// The app directory path, e.g. /path/to/shopbay-app
$appPath = dirname(dirname(__FILE__));

loadDependencies($root,$depends,$appPath);

return $depends;