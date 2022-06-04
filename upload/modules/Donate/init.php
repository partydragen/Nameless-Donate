<?php 
/*
 *	Made by Partydragen
 *  NamelessMC version 2.0.0-pr13
 *
 *  License: MIT
 *
 *  Donate module initialisation file
 */

// Initialise Donate language
$donate_language = new Language(ROOT_PATH . '/modules/Donate/language', LANGUAGE);

// Initialise module
require_once(ROOT_PATH . '/modules/Donate/module.php');
$module = new Donate_Module($language, $donate_language, $pages, $user, $navigation, $cache);