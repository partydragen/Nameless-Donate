<?php
/*
 *  Made by Partydragen
 *  https://partydragen.com/
 *
 *  Donate settings page
 */

// Can the user view the panel?
if (!$user->handlePanelPageLoad('staffcp.donate.settings')) {
    require_once(ROOT_PATH . '/403.php');
    die();
}

define('PAGE', 'panel');
define('PARENT_PAGE', 'donate_settings');
define('PANEL_PAGE', 'donate_settings');
$page_title = $language->get('admin', 'general_settings');
require_once(ROOT_PATH . '/core/templates/backend_init.php');
$configuration = new Configuration('donate');

// Deal with input
if (Input::exists()) {
    if (Token::check(Input::get('token'))) {
		$validation = Validate::check($_POST, [
			'paypal_email' => [
				Validate::MAX => 128
			],
            'icon' => [
				Validate::MAX => 64
			]
		]);

		if ($validation->passed()) {
			// Update paypal email
			$paypal_email_id = DB::getInstance()->get('donate_settings', ['name', '=', 'paypal_email'])->results();
            if (count($paypal_email_id)) {
                DB::getInstance()->update('donate_settings', $paypal_email_id[0]->id, [
                    'value' => Input::get('paypal_email'),
                ]);
            } else {
                DB::getInstance()->insert('donate_settings', [
                    'name' => 'paypal_email',
                    'value' => Input::get('paypal_email'),
                ]);
            }

            $configuration->set('currency', Input::get('currency'));
            $configuration->set('min_amount', Input::get('min_amount'));
            $configuration->set('reward_group', Input::get('reward_group'));

            $configuration->set('content', Input::get('content'));
            $configuration->set('success_content', Input::get('success_content'));

			// Get link location
			if (isset($_POST['link_location'])) {
				switch ($_POST['link_location']) {
					case 1:
					case 2:
					case 3:
					case 4:
						$location = $_POST['link_location'];
					break;
                    default:
                        $location = 1;
				}
			} else
				$location = 1;

			// Update Link location cache
			$cache->setCache('nav_location');
			$cache->store('donate_location', $location);

			// Update Icon cache
			$cache->setCache('navbar_icons');
			$cache->store('donate_icon', Input::get('icon'));

			Session::flash('donate_success', $donate_language->get('admin', 'settings_updated_successfully'));
			Redirect::to(URL::build('/panel/donate/settings/'));
        } else {
            // Validation errors
            $errors = $validation->errors();
        }
    } else {
        // Invalid token
        $errors[] = $language->get('general', 'invalid_token');
    }
}

$currency_list = ['USD', 'EUR', 'GBP', 'NOK', 'SEK', 'PLN', 'DKK', 'CAD', 'BRL', 'AUD'];

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

// Retrieve PayPal Email
$paypal_email = DB::getInstance()->get('donate_settings', ['name', '=', 'paypal_email'])->results();
$paypal_email = $paypal_email[0]->value;

$currency = $configuration->get('currency');
$min_amount = $configuration->get('min_amount');
$reward_group = $configuration->get('reward_group');
$content = $configuration->get('content');
$success_content = $configuration->get('success_content');

// Retrieve Link Location from cache
$cache->setCache('nav_location');
$link_location = $cache->retrieve('donate_location');

// Retrieve Icon from cache
$cache->setCache('navbar_icons');
$icon = $cache->retrieve('donate_icon');

$group_list = [];
$groups = DB::getInstance()->get('groups', ['staff', '=', 0])->results();
foreach ($groups as $group) {
    $group_list[] = [
        'id' => Output::getClean($group->id),
        'name' => Output::getClean($group->name)
    ];
}

if (Session::exists('donate_success'))
	$success = Session::flash('donate_success');

if (isset($success))
	$smarty->assign([
		'SUCCESS' => $success,
		'SUCCESS_TITLE' => $language->get('general', 'success')
	]);

if (isset($errors) && count($errors))
	$smarty->assign([
		'ERRORS' => $errors,
		'ERRORS_TITLE' => $language->get('general', 'error')
	]);

$smarty->assign([
	'PARENT_PAGE' => PARENT_PAGE,
	'DASHBOARD' => $language->get('admin', 'dashboard'),
	'DONATE' => $donate_language->get('general', 'donate'),
	'PAGE' => PANEL_PAGE,
	'TOKEN' => Token::get(),
	'SUBMIT' => $language->get('general', 'submit'),
	'SETTINGS' => $donate_language->get('admin', 'settings'),
    'PAYPAL_EMAIL' => $donate_language->get('admin', 'paypal_email'),
    'PAYPAL_EMAIL_VALUE' => Output::getClean($paypal_email),
    'MIN_AMOUNT' => $donate_language->get('general', 'min_amount'),
    'MIN_AMOUNT_VALUE' => Output::getClean($min_amount),
    'CURRENCY' => $donate_language->get('general', 'currency'),
    'CURRENCY_LIST' => $currency_list,
    'CURRENCY_VALUE' => Output::getClean($currency),
    'GROUPS' => $group_list,
    'REWARD_GROUP' => $donate_language->get('admin', 'reward_group'),
    'REWARD_GROUP_VALUE' => Output::getClean($reward_group),
    'CONTENT' => $language->get('admin', 'content'),
    'CONTENT_VALUE' => (isset($_POST['content']) ? Output::getClean(Input::get('content')) : Output::getClean(Output::getDecoded($content))),
    'SUCCESS_CONTENT' => $donate_language->get('admin', 'success_content'),
    'SUCCESS_CONTENT_VALUE' => (isset($_POST['success_content']) ? Output::getClean(Input::get('success_content')) : Output::getClean(Output::getDecoded($success_content))),
	'LINK_LOCATION' => $donate_language->get('admin', 'link_location'),
	'LINK_LOCATION_VALUE' => $link_location,
	'LINK_NAVBAR' => $language->get('admin', 'page_link_navbar'),
	'LINK_MORE' => $language->get('admin', 'page_link_more'),
	'LINK_FOOTER' => $language->get('admin', 'page_link_footer'),
	'LINK_NONE' => $language->get('admin', 'page_link_none'),
	'ICON' => $donate_language->get('admin', 'icon'),
	'ICON_EXAMPLE' => htmlspecialchars($donate_language->get('admin', 'icon_example')),
	'ICON_VALUE' => Output::getClean(htmlspecialchars_decode($icon)),
    'NONE' => $language->get('general', 'none'),
]);

$template->assets()->include([
    AssetTree::TINYMCE,
]);

$template->addJSScript(Input::createTinyEditor($language, 'inputContent'));
$template->addJSScript(Input::createTinyEditor($language, 'inputSuccessContent'));

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/panel_navbar.php');

// Display template
$template->displayTemplate('donate/settings.tpl', $smarty);