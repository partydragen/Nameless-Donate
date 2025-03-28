<?php
/*
 *	Made by Partydragen
 *  https://partydragen.com/
 *  NamelessMC version 2.0.0-pr13
 *
 *  License: MIT
 *
 *  Donate page
 */

// Always define page name
define('PAGE', 'donate');
$page_title = 'Donate';
require_once(ROOT_PATH . '/core/templates/frontend_init.php');
$timeago = new TimeAgo(TIMEZONE);

if (isset($_GET['do'])) {
    if ($_GET['do'] == 'success') {
        $success_content = Settings::get('success_content', '', 'Donate');

        $success = Output::getPurified(Output::getDecoded($success_content));
    }
}

// Get latest donations
$latest_donations_list = [];
$latest_donations = DB::getInstance()->query('SELECT * FROM nl2_donate_payments WHERE status_id = 1 ORDER BY `created` DESC LIMIT 10')->results();
foreach ($latest_donations as $donation) {
    $target_user = new User($donation->user_id);
    if ($donation->user_id != 0 && $target_user->exists()) {
        $latest_donations_list[] = [
            'username' => $target_user->getDisplayname(),
            'avatar_url' => $target_user->getAvatar(),
            'profile_url' => $target_user->getProfileURL(),
            'amount' => Output::getClean($donation->amount),
            'currency' => Output::getClean($donation->currency),
            'date' => date(DATE_FORMAT, $donation->created),
            'date_rough' => $timeago->inWords($donation->created, $language)
        ];
    } else {
        $latest_donations_list[] = [
            'username' => $donate_language->get('general', 'anonymous'),
            'avatar_url' => $logo_image,
            'profile_url' => '#',
            'amount' => Output::getClean($donation->amount),
            'currency' => Output::getClean($donation->currency),
            'date' => date(DATE_FORMAT, $donation->created),
            'date_rough' => $timeago->inWords($donation->created, $language)
        ];
    }
}

$currency = Settings::get('currency', 'USD', 'Donate');
$min_amount = Settings::get('min_amount', '5.00', 'Donate');

$content = Settings::get('content', '', 'Donate');
if (!empty($content)) {
    $template->getEngine()->addVariable('CONTENT', Output::getPurified(Output::getDecoded($content)));
}

$template->getEngine()->addVariables([
    'DONATE' => $donate_language->get('general', 'donate'),
    'AMOUNT' => $donate_language->get('general', 'amount'),
    'MIN_AMOUNT' => $min_amount,
    'CURRENCY' => $currency,
    'DONATE_AS' => $donate_language->get('general', 'donate_as'),
    'ANONYMOUS' => $donate_language->get('general', 'anonymous'),
    'PROCESS_URL' => URL::build('/donate/process/', 'gateway=PayPal'),
    'LATEST_DONATIONS' => $donate_language->get('general', 'latest_donations'),
    'LATEST_DONATIONS_LIST' => $latest_donations_list
]);

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

if (Session::exists('donate_success'))
    $success = Session::flash('donate_success');

if (Session::exists('donate_error'))
    $errors[] = Session::flash('donate_error');

if (isset($success))
	$template->getEngine()->addVariables([
		'SUCCESS' => $success,
		'SUCCESS_TITLE' => $language->get('general', 'success')
	]);

if (isset($errors) && count($errors))
	$template->getEngine()->addVariables([
		'ERRORS' => $errors,
		'ERRORS_TITLE' => $language->get('general', 'error')
	]);
    
$template->assets()->include([
    DARK_MODE
        ? AssetTree::PRISM_DARK
        : AssetTree::PRISM_LIGHT,
    AssetTree::TINYMCE_SPOILER,
]);

$template->onPageLoad();

$template->getEngine()->addVariable('WIDGETS_LEFT', $widgets->getWidgets('left'));
$template->getEngine()->addVariable('WIDGETS_RIGHT', $widgets->getWidgets('right'));

require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');
	
// Display template
$template->displayTemplate('donate/donate');