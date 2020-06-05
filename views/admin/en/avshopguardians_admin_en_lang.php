<?php

$sLangName = 'English';

$aLang = [
    'charset' => 'UTF-8', // Supports DE chars like: ä, ü, ö, etc.

    /** Module settings */
    'SHOP_MODULE_AVSHOPGUARDIANS_API_KEY' => 'API Key',
    'HELP_SHOP_MODULE_AVSHOPGUARDIANS_API_KEY' => 'Copy this auto-generated key to Shopguardians once to setup the connection between your shop and shopguardians.',

    'SHOP_MODULE_AVSHOPGUARDIANS_ONLY_PARENTS' => 'Exclude variants',
    'SHOP_MODULE_AVSHOPGUARDIANS_ARTICLE_BLACKLIST' => 'Article number blacklist',
    'SHOP_MODULE_AVSHOPGUARDIANS_REMOVE_PARENTS_WITHOUT_VARIANTS' => 'Exclude parents with no active variants',
    'SHOP_MODULE_AVSHOPGUARDIANS_IGNORE_PARENT_STOCK' => 'Ignore stock of parent articles',

    'SHOP_MODULE_GROUP_avshopguardians_main' => 'General',
    'SHOP_MODULE_GROUP_avshopguardians_dataquality' => 'Data Quality Guard',
    'SHOP_MODULE_GROUP_avshopguardians_sales' => 'Sales Guardian',

    'SHOP_MODULE_AVSHOPGUARDIANS_OHS_DEVIATION_TRESHOLD' => 'Working hours standard deviation %',
    'HELP_SHOP_MODULE_AVSHOPGUARDIANS_OHS_DEVIATION_TRESHOLD' => 'Shopguardians determines the typical working hours of your shop. This expert setting sets the maximum deviation to the average, to exclude an hour from the alerts. Usually you dont need to change anything here.',
    'SHOP_MODULE_AVSHOPGUARDIANS_OHS_SAFETY_BUFFER_FACTOR' => 'Sensitivity Checkout-Monitoring (1-10)',
    'HELP_SHOP_MODULE_AVSHOPGUARDIANS_OHS_SAFETY_BUFFER_FACTOR' => 'Increase this factor, if you get often false-positives, reduce it for more early alerts. (Highest sensitivity: 1, lowest: 10)',

    'SHOP_MODULE_AVSHOPGUARDIANS_OHS_PAYMENTMETHOD_ACTIVITY_DAYS' => 'Limit in days to consider payment method active',
    'HELP_SHOP_MODULE_AVSHOPGUARDIANS_OHS_PAYMENTMETHOD_ACTIVITY_DAYS' => 'A payment method is considered active if an order was made at least XX days ago'
];