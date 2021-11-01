<?php
/*
 *	Made by Partydragen
 *  https://partydragen.com/
 *  NamelessMC version 2.0.0-pr11
 *
 *  License: MIT
 *
 *  Donate page
 */
 
// Always define page name
define('PAGE', 'donate');
$page_title = 'Donate';
require_once(ROOT_PATH . '/core/templates/frontend_init.php');
$timeago = new Timeago(TIMEZONE);

if(isset($_GET['do'])) {
    if($_GET['do'] == 'success') {
        $success_content = $configuration->get('donate', 'success_content');
        
        $success = Output::getPurified(Output::getDecoded($success_content));
    }
}

// Get latest donations
$latest_donations_list = array();
$latest_donations = DB::getInstance()->query('SELECT * FROM nl2_donate_payments WHERE status_id = 1 ORDER BY `created` DESC LIMIT 10')->results();
foreach($latest_donations as $donation) {
    $target_user = new User($donation->user_id);
    if($donation->user_id != 0 && $target_user->data()) {
        $latest_donations_list[] = array(
            'username' => $target_user->getDisplayname(),
            'avatar_url' => $target_user->getAvatar(null, 128),
            'profile_url' => $target_user->getProfileURL(),
            'amount' => Output::getClean($donation->amount),
            'currency' => Output::getClean($donation->currency),
            'date' => date('d M Y, H:i', $donation->created),
            'date_rough' => $timeago->inWords(date('d M Y, H:i', $donation->created), $language->getTimeLanguage())
        );
    } else {
        $latest_donations_list[] = array(
            'username' => $donate_language->get('general', 'anonymous'),
            'avatar_url' => $logo_image,
            'profile_url' => '#',
            'amount' => Output::getClean($donation->amount),
            'currency' => Output::getClean($donation->currency),
            'date' => date('d M Y, H:i', $donation->created),
            'date_rough' => $timeago->inWords(date('d M Y, H:i', $donation->created), $language->getTimeLanguage())
        );
    }
}

$currency = $configuration->get('donate', 'currency');
$min_amount = $configuration->get('donate', 'min_amount');

$content = $configuration->get('donate', 'content');
if(!empty($content)) {
    $smarty->assign('CONTENT', Output::getPurified(Output::getDecoded($content)));
}

$smarty->assign(array(
    'DONATE' => $donate_language->get('general', 'donate'),
    'AMOUNT' => $donate_language->get('general', 'amount'),
    'MIN_AMOUNT' => $min_amount,
    'CURRENCY' => $currency,
    'DONATE_AS' => $donate_language->get('general', 'donate_as'),
    'ANONYMOUS' => $donate_language->get('general', 'anonymous'),
    'PROCESS_URL' => URL::build('/donate/process/', 'gateway=PayPal'),
    'LATEST_DONATIONS' => $donate_language->get('general', 'latest_donations'),
    'LATEST_DONATIONS_LIST' => $latest_donations_list
));

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $mod_nav), $widgets);

if(Session::exists('donate_success'))
    $success = Session::flash('donate_success');

if(Session::exists('donate_error'))
    $errors = array(Session::flash('donate_error'));

if(isset($success))
	$smarty->assign(array(
		'SUCCESS' => $success,
		'SUCCESS_TITLE' => $language->get('general', 'success')
	));

if(isset($errors) && count($errors))
	$smarty->assign(array(
		'ERRORS' => $errors,
		'ERRORS_TITLE' => $language->get('general', 'error')
	));

$page_load = microtime(true) - $start;
define('PAGE_LOAD_TIME', str_replace('{x}', round($page_load, 3), $language->get('general', 'page_loaded_in')));

$template->onPageLoad();
	
$smarty->assign('WIDGETS_LEFT', $widgets->getWidgets('left'));
$smarty->assign('WIDGETS_RIGHT', $widgets->getWidgets('right'));
	
require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');
	
// Display template
$template->displayTemplate('donate/donate.tpl', $smarty);