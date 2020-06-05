<?php

$sLangName = 'Deutsch';

$aLang = [
    'charset' => 'UTF-8', // Supports DE chars like: ä, ü, ö, etc.

    /** Module settings */
    'SHOP_MODULE_AVSHOPGUARDIANS_API_KEY' => 'API Key',
    'HELP_SHOP_MODULE_AVSHOPGUARDIANS_API_KEY' => 'Diesen automatisch generierten Schlüssel musst du kopieren und einmalig bei Shopguardians hinterlegen, damit die Verbindung zu deinem Shop steht.',
    'SHOP_MODULE_AVSHOPGUARDIANS_ONLY_PARENTS' => 'Varianten ausschließen',
    'SHOP_MODULE_AVSHOPGUARDIANS_ARTICLE_BLACKLIST' => 'Artikelnummer-Blacklist',
    'SHOP_MODULE_AVSHOPGUARDIANS_REMOVE_PARENTS_WITHOUT_VARIANTS' => 'Vaterartikel ohne aktive Varianten ausschließen',
    'SHOP_MODULE_AVSHOPGUARDIANS_IGNORE_PARENT_STOCK' => 'Bestand von Vaterartikeln ignorieren',

    'SHOP_MODULE_GROUP_avshopguardians_main' => 'Allgemein',
    'SHOP_MODULE_GROUP_avshopguardians_dataquality' => 'Data Quality Guard',
    'SHOP_MODULE_GROUP_avshopguardians_sales' => 'Sales Guardian',

    'SHOP_MODULE_AVSHOPGUARDIANS_OHS_DEVIATION_TRESHOLD' => 'Verkaufszeiten Standardabweichung Grenzwert %',
    'HELP_SHOP_MODULE_AVSHOPGUARDIANS_OHS_DEVIATION_TRESHOLD' => 'Shopguardians ermittelt die typischen Verkaufszeiten in deinem Shop. Diese Experteneinstellung bestimmt die maximale Abweichung zum Mittelwert, um eine Stunde aus der Überwachung auszuschließen. Hier musst du i.d.R. nichts ändern.',
    'SHOP_MODULE_AVSHOPGUARDIANS_OHS_SAFETY_BUFFER_FACTOR' => 'Sensivität Checkout-Monitoring (1-10)',
    'HELP_SHOP_MODULE_AVSHOPGUARDIANS_OHS_SAFETY_BUFFER_FACTOR' => 'Faktor, mit der die durchschnittliche Distanz zwischen Bestellungen multipliziert wird. Erhöhe diesen Faktor, wenn du Fehlalarme bekommst, verringere ihn für frühere Alarme. (Höchste Sensivität: 1, Maximal: 10)',

    'SHOP_MODULE_AVSHOPGUARDIANS_OHS_PAYMENTMETHOD_ACTIVITY_DAYS' => 'Limit Tage für Zahlart-Aktivität',
    'HELP_SHOP_MODULE_AVSHOPGUARDIANS_OHS_PAYMENTMETHOD_ACTIVITY_DAYS' => 'Mit einer Zahlart muss mindestens vor dieser Anzahl Tagen bestellt worden sein, um die Zahlart für das Checkout-Monitoring zu aktivieren. Dies verhindert, dass du Meldungen über nicht mehr genutzte Zahlarten bekommst.'
];