<?php
/*
 *  Made by Partydragen
 *  https://partydragen.com/
 *
 *  Donate payments page
 */

// Can the user view the panel?
if(!$user->handlePanelPageLoad('staffcp.donate.payments')) {
    require_once(ROOT_PATH . '/403.php');
    die();
}

define('PAGE', 'panel');
define('PARENT_PAGE', 'donate_payments');
define('PANEL_PAGE', 'donate_payments');
$page_title = $language->get('admin', 'general_settings');
require_once(ROOT_PATH . '/core/templates/backend_init.php');

if(!isset($_GET['payment']) && !isset($_GET['user'])) {
    $payments = $queries->orderAll('donate_payments', 'created', 'DESC');
    
	if(count($payments)){
		$template_payments = array();

		foreach($payments as $payment){
            $payment_user = new User($payment->user_id);
			if($payment->user_id != 0 && $payment_user->data()){
                $username = $payment_user->getDisplayname(true);
				$avatar = $payment_user->getAvatar();
				$style = $payment_user->getGroupClass();
			} else {
                $username = $donate_language->get('general', 'anonymous');
				$avatar = '';
				$style = '';
			}
            
            switch($payment->status_id) {
                case 0;
                    $status = '<span class="badge badge-warning">Pending</span>';
                break;
                case 1;
                    $status = '<span class="badge badge-success">Complete</span>';
                break;
                case 2;
                    $status = '<span class="badge badge-primary">Refunded</span>';
                break;
                case 3;
                    $status = '<span class="badge badge-info">Changeback</span>';
                break;
                default:
                    $status = '<span class="badge badge-danger">Unknown</span>';
                break;
            }

			$template_payments[] = array(
				'user_link' => URL::build('/panel/donate/payments/', 'user=' . Output::getClean($payment->user_id)),
				'user_style' => $style,
				'user_avatar' => $avatar,
				'username' => $username,
				'uuid' => Output::getClean($payment->uuid),
                'status_id' => $payment->status_id,
                'status' => $status,
				'currency_symbol' => '$',
				'amount' => Output::getClean($payment->amount),
				'date' => date('d M Y, H:i', $payment->created),
				'date_unix' => Output::getClean($payment->created),
				'link' => URL::build('/panel/donate/payments/', 'payment=' . Output::getClean($payment->id))
			);
		}

		$smarty->assign(array(
			'USER' => $donate_language->get('admin', 'user'),
			'AMOUNT' => $donate_language->get('general', 'amount'),
            'STATUS' => $donate_language->get('admin', 'status'),
			'DATE' => $donate_language->get('admin', 'date'),
			'VIEW' => $donate_language->get('admin', 'view'),
			'ALL_PAYMENTS' => $template_payments
		));

		$template->addCSSFiles(array(
			(defined('CONFIG_PATH') ? CONFIG_PATH : '') . '/custom/panel_templates/Default/assets/css/dataTables.bootstrap4.min.css' => array()
		));

		$template->addJSFiles(array(
			(defined('CONFIG_PATH') ? CONFIG_PATH : '') . '/core/assets/plugins/dataTables/jquery.dataTables.min.js' => array(),
			(defined('CONFIG_PATH') ? CONFIG_PATH : '') . '/custom/panel_templates/Default/assets/js/dataTables.bootstrap4.min.js' => array()
		));

		$template->addJSScript('
			$(document).ready(function() {
				$(\'.dataTables-payments\').dataTable({
					responsive: true,
                    order: [[ 3, "desc" ]],
					language: {
						"lengthMenu": "' . $language->get('table', 'display_records_per_page') . '",
						"zeroRecords": "' . $language->get('table', 'nothing_found') . '",
						"info": "' . $language->get('table', 'page_x_of_y') . '",
						"infoEmpty": "' . $language->get('table', 'no_records') . '",
						"infoFiltered": "' . $language->get('table', 'filtered') . '",
						"search": "' . $language->get('general', 'search') . '",
						"paginate": {
							"next": "' . $language->get('general', 'next') . '",
							"previous": "' . $language->get('general', 'previous') . '"
						}
					}
				});
			});
		');

	} else
        $smarty->assign('NO_PAYMENTS', $donate_language->get('admin', 'no_payments'));
    
    $template_file = 'donate/payments.tpl';
} else if(isset($_GET['payment'])) {
	// View payment
    if (!isset($_GET['payment']) || !is_numeric($_GET['payment'])) {
        Redirect::to(URL::build('/panel/donate/payments'));
        die();
    }
        
    $payment = $queries->getWhere('donate_payments', array('id', '=', $_GET['payment']));
	if(!count($payment)) {
		Redirect::to(URL::build('/panel/donate/payments'));
		die();
	}
    $payment = $payment[0];
    
    $payment_user = new User($payment->user_id);
	if($payment->user_id != 0 && $payment_user->data()){
        $username = $payment_user->getDisplayname(true);
		$avatar = $payment_user->getAvatar();
		$style = $payment_user->getGroupClass();
	} else {
        $username = $donate_language->get('general', 'anonymous');
		$avatar = '';
		$style = '';
	}
            
    switch($payment->status_id) {
        case 0;
            $status = '<span class="badge badge-warning">Pending</span>';
        break;
        case 1;
            $status = '<span class="badge badge-success">Complete</span>';
        break;
        case 2;
            $status = '<span class="badge badge-primary">Refunded</span>';
        break;
        case 3;
            $status = '<span class="badge badge-info">Changeback</span>';
        break;
        default:
            $status = '<span class="badge badge-danger">Unknown</span>';
        break;
    }
    
	$smarty->assign(array(
		'VIEWING_PAYMENT' => str_replace('{x}', Output::getClean($payment->transaction_id), $donate_language->get('admin', 'viewing_payment')),
		'BACK' => $language->get('general', 'back'),
		'BACK_LINK' => URL::build('/panel/donate/payments'),
		'USER_LINK' => URL::build('/panel/donate/payments/', 'user=' . Output::getClean($payment->user_id)),
		'AVATAR' => $avatar,
		'STYLE' => $style,
        'USER' => $donate_language->get('admin', 'user'),
		'USER_NAME' => $username,
        'TRANSACTION' => $donate_language->get('admin', 'transaction'),
		'TRANSACTION_VALUE' => Output::getClean($payment->transaction_id),
        'PAYMENT_METHOD' => $donate_language->get('admin', 'payment_method'),
		'PAYMENT_METHOD_VALUE' => 'PayPal',
        'STATUS' => $donate_language->get('admin', 'status'),
		'STATUS_VALUE' => $status,
		'AMOUNT' => $donate_language->get('general', 'amount'),
		'AMOUNT_VALUE' => Output::getClean($payment->amount),
        'FEE' => $donate_language->get('admin', 'fee'),
		'FEE_VALUE' => Output::getClean($payment->fee),
		'CURRENCY_SYMBOL' => Output::getClean('$'),
		'CURRENCY_ISO' => Output::getClean($payment->currency),
		'DATE' => $donate_language->get('admin', 'date'),
		'DATE_VALUE' => date('d M Y, H:i', $payment->created)
	));

	$template_file = 'donate/payments_view.tpl';
} else if(isset($_GET['user'])) {
	// View user
    if (!isset($_GET['user']) || !is_numeric($_GET['user'])) {
        Redirect::to(URL::build('/panel/donate/payments'));
        die();
    }
    
    $target_user = new User($_GET['user']);
    if(!$target_user->data()) {
        Redirect::to(URL::build('/panel/donate/payments'));
        die();
    }
        
    $payments = $queries->getWhere('donate_payments', array('user_id', '=', $target_user->data()->id));
	if(count($payments)){
        $username = $target_user->getDisplayname(true);
        $avatar = $target_user->getAvatar();
        $style = $target_user->getGroupClass();

		$template_payments = array();
		foreach($payments as $payment){
			$template_payments[] = array(
				'user_link' => URL::build('/panel/donate/payments/', 'user=' . $target_user->data()->id),
				'user_style' => $style,
				'user_avatar' => $avatar,
				'username' => $username,
				'currency' => Output::getPurified($payment->currency),
				'amount' => Output::getClean($payment->amount),
				'date' => date('d M Y, H:i', $payment->created),
				'link' => URL::build('/panel/donate/payments', 'payment=' . Output::getClean($payment->id))
			);
		}

		$smarty->assign(array(
			'USER' => $donate_language->get('admin', 'user'),
			'AMOUNT' => $donate_language->get('admin', 'amount'),
			'DATE' => $donate_language->get('admin', 'date'),
			'VIEW' => $donate_language->get('admin', 'view'),
			'USER_PAYMENTS' => $template_payments
		));

		if(!defined('TEMPLATE_DONATE_SUPPORT')){
			$template->addCSSFiles(array(
				(defined('CONFIG_PATH') ? CONFIG_PATH : '') . '/custom/panel_templates/Default/assets/css/dataTables.bootstrap4.min.css' => array()
			));

			$template->addJSFiles(array(
				(defined('CONFIG_PATH') ? CONFIG_PATH : '') . '/core/assets/plugins/dataTables/jquery.dataTables.min.js' => array(),
				(defined('CONFIG_PATH') ? CONFIG_PATH : '') . '/custom/panel_templates/Default/assets/js/dataTables.bootstrap4.min.js' => array()
			));

			$template->addJSScript('
				$(document).ready(function() {
					$(\'.dataTables-payments\').dataTable({
						responsive: true,
						order: [[ 3, "desc" ]],
						language: {
							"lengthMenu": "' . $language->get('table', 'display_records_per_page') . '",
							"zeroRecords": "' . $language->get('table', 'nothing_found') . '",
							"info": "' . $language->get('table', 'page_x_of_y') . '",
							"infoEmpty": "' . $language->get('table', 'no_records') . '",
							"infoFiltered": "' . $language->get('table', 'filtered') . '",
							"search": "' . $language->get('general', 'search') . '",
							"paginate": {
								"next": "' . $language->get('general', 'next') . '",
								"previous": "' . $language->get('general', 'previous') . '"
							}
						}
					});
				});
			');
		}

	} else
		$smarty->assign('NO_PAYMENTS', $donate_language->get('admin', 'no_payments_for_user'));
    
	$smarty->assign(array(
		'VIEWING_PAYMENTS_FOR_USER' => str_replace('{x}', $target_user->getDisplayname(true), $donate_language->get('admin', 'viewing_payments_for_user_x')),
		'BACK' => $language->get('general', 'back'),
		'BACK_LINK' => URL::build('/panel/donate/payments')
	));

	$template_file = 'donate/payments_user.tpl';
}

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $mod_nav), $widgets);

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
	'PAYMENTS' => $donate_language->get('admin', 'payments')
));

$page_load = microtime(true) - $start;
define('PAGE_LOAD_TIME', str_replace('{x}', round($page_load, 3), $language->get('general', 'page_loaded_in')));

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/panel_navbar.php');

// Display template
$template->displayTemplate($template_file, $smarty);