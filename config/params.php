<?php
return [
    /**
     * configuraion for local information 
     */
    'SITE_NAME' => 'API',
    /**
     * configuration for domain
     */
    'HOST_DOMAIN' => readConfig('domain','host'),    
    'MERCHANT_DOMAIN' => readConfig('domain','merchant'),    
];
