<?php
/*
 *  Made by Partydragen
 *  https://partydragen.com/
 *
 *  Donate settings page
 */

// Can the user view the panel?
if(!$user->handlePanelPageLoad('staffcp.donate.settings')) {
    require_once(ROOT_PATH . '/403.php');
    die();
}

define('PAGE', 'panel');
define('PARENT_PAGE', 'donate_settings');
define('PANEL_PAGE', 'donate_settings');
$page_title = $language->get('admin', 'general_settings');
require_once(ROOT_PATH . '/core/templates/backend_init.php');

// Deal with input
if (Input::exists()) {
    if (Token::check(Input::get('token'))) {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'paypal_email' => array(
				'max' => 128
			),
            'icon' => array(
				'max' => 64
			)
		));
						
		if($validation->passed()){
			// Update paypal email
			$paypal_email_id = $queries->getWhere('donate_settings', array('name', '=', 'paypal_email'));
            if(count($paypal_email_id)) {
                $queries->update('donate_settings', $paypal_email_id[0]->id, array(
                    'value' => Output::getClean(Input::get('paypal_email')),
                ));
            } else {
                $queries->create('donate_settings', array(
                    'name' => 'paypal_email',
                    'value' => Output::getClean(Input::get('paypal_email')),
                ));
            }
            
            $configuration->set('donate', 'currency', Output::getClean(Input::get('currency')));
            $configuration->set('donate', 'min_amount', Output::getClean(Input::get('min_amount')));
            $configuration->set('donate', 'reward_group', Output::getClean(Input::get('reward_group')));
            
            $configuration->set('donate', 'content', Output::getClean(Input::get('content')));
            $configuration->set('donate', 'success_content', Output::getClean(Input::get('success_content')));
            
			// Get link location
			if(isset($_POST['link_location'])){
				switch($_POST['link_location']){
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
			die();
        }
        
    } else {
        // Invalid token
        $errors = array($language->get('general', 'invalid_token'));
    }
}

$currency_list = array('USD', 'EUR', 'GBP', 'NOK', 'SEK', 'PLN', 'DKK', 'CAD', 'BRL', 'AUD');

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $mod_nav), $widgets);

// Retrieve PayPal Email
$paypal_email = $queries->getWhere('donate_settings', array('name', '=', 'paypal_email'));
$paypal_email = $paypal_email[0]->value;

$currency = $configuration->get('donate', 'currency');
$min_amount = $configuration->get('donate', 'min_amount');
$reward_group = $configuration->get('donate', 'reward_group');
$content = $configuration->get('donate', 'content');
$success_content = $configuration->get('donate', 'success_content');

// Retrieve Link Location from cache
$cache->setCache('nav_location');
$link_location = $cache->retrieve('donate_location');
				
// Retrieve Icon from cache
$cache->setCache('navbar_icons');
$icon = $cache->retrieve('donate_icon');

$group_list = array();
$groups = $queries->getWhere('groups', array('staff', '=', 0));
foreach($groups as $group) {
    $group_list[] = array(
        'id' => Output::getClean($group->id),
        'name' => Output::getClean($group->name)
    );
}

if(Session::exists('donate_success'))
	$success = Session::flash('donate_success');

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

$smarty->assign(array(
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
));

$template->addCSSFiles(array(
    (defined('CONFIG_PATH') ? CONFIG_PATH : '') . '/core/assets/plugins/ckeditor/plugins/spoiler/css/spoiler.css' => array()
));

$template->addJSFiles(array(
    (defined('CONFIG_PATH') ? CONFIG_PATH : '') . '/core/assets/plugins/ckeditor/plugins/spoiler/js/spoiler.js' => array(),
    (defined('CONFIG_PATH') ? CONFIG_PATH : '') . '/core/assets/plugins/ckeditor/ckeditor.js' => array()
));

$template->addJSScript(Input::createEditor('inputContent', true));
$template->addJSScript(Input::createEditor('inputSuccessContent', true));

$page_load = microtime(true) - $start;
define('PAGE_LOAD_TIME', str_replace('{x}', round($page_load, 3), $language->get('general', 'page_loaded_in')));

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/panel_navbar.php');

// Display template
$template->displayTemplate('donate/settings.tpl', $smarty);