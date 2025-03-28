<?php
/*
 *  Made by Partydragen
 *  https://partydragen.com/
 *
 *  Donate payments page
 */

// Can the user view the panel?
if (!$user->handlePanelPageLoad('staffcp.donate.payments')) {
    require_once(ROOT_PATH . '/403.php');
    die();
}

define('PAGE', 'panel');
define('PARENT_PAGE', 'donate_payments');
define('PANEL_PAGE', 'donate_payments');
$page_title = $language->get('admin', 'general_settings');
require_once(ROOT_PATH . '/core/templates/backend_init.php');

if (!isset($_GET['payment']) && !isset($_GET['user'])) {
    $payments = DB::getInstance()->orderAll('donate_payments', 'created', 'DESC')->results();

    if (count($payments)) {
        $template_payments = [];

        foreach ($payments as $payment) {
            $payment_user = new User($payment->user_id);
            if ($payment->user_id != 0 && $payment_user->exists()) {
                $username = $payment_user->getDisplayname(true);
                $avatar = $payment_user->getAvatar();
                $style = $payment_user->getGroupStyle();
            } else {
                $username = $donate_language->get('general', 'anonymous');
                $avatar = '';
                $style = '';
            }

            switch ($payment->status_id) {
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

            $template_payments[] = [
                'user_link' => URL::build('/panel/donate/payments/', 'user=' . Output::getClean($payment->user_id)),
                'user_style' => $style,
                'user_avatar' => $avatar,
                'username' => $username,
                'uuid' => Output::getClean($payment->uuid),
                'status_id' => $payment->status_id,
                'status' => $status,
                'currency_symbol' => '$',
                'amount' => Output::getClean($payment->amount),
                'date' => date(DATE_FORMAT, $payment->created),
                'date_unix' => Output::getClean($payment->created),
                'link' => URL::build('/panel/donate/payments/', 'payment=' . Output::getClean($payment->id))
            ];
        }

        $template->getEngine()->addVariables([
            'USER' => $donate_language->get('admin', 'user'),
            'AMOUNT' => $donate_language->get('general', 'amount'),
            'STATUS' => $donate_language->get('admin', 'status'),
            'DATE' => $donate_language->get('admin', 'date'),
            'VIEW' => $donate_language->get('admin', 'view'),
            'ALL_PAYMENTS' => $template_payments
        ]);

        if (!defined('TEMPLATE_STORE_SUPPORT')) {
            $template->assets()->include([
                AssetTree::DATATABLES
            ]);

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
        $template->getEngine()->addVariable('NO_PAYMENTS', $donate_language->get('admin', 'no_payments'));

    $template_file = 'donate/payments';
} else if (isset($_GET['payment'])) {
    // View payment
    if (!isset($_GET['payment']) || !is_numeric($_GET['payment'])) {
        Redirect::to(URL::build('/panel/donate/payments'));
    }

    $payment = DB::getInstance()->get('donate_payments', ['id', '=', $_GET['payment']])->results();
    if (!count($payment)) {
        Redirect::to(URL::build('/panel/donate/payments'));
    }
    $payment = $payment[0];

    $payment_user = new User($payment->user_id);
    if ($payment->user_id != 0 && $payment_user->exists()) {
        $username = $payment_user->getDisplayname(true);
        $avatar = $payment_user->getAvatar();
        $style = $payment_user->getGroupStyle();
    } else {
        $username = $donate_language->get('general', 'anonymous');
        $avatar = '';
        $style = '';
    }

    switch ($payment->status_id) {
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

    $template->getEngine()->addVariables([
        'VIEWING_PAYMENT' => $donate_language->get('admin', 'viewing_payment', ['transaction' => Output::getClean($payment->transaction_id)]),
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
        'DATE_VALUE' => date(DATE_FORMAT, $payment->created)
    ]);

    $template_file = 'donate/payments_view';
} else if (isset($_GET['user'])) {
    // View user
    if (!isset($_GET['user']) || !is_numeric($_GET['user'])) {
        Redirect::to(URL::build('/panel/donate/payments'));
    }

    $target_user = new User($_GET['user']);
    if (!$target_user->exists()) {
        Redirect::to(URL::build('/panel/donate/payments'));
    }

    $payments = DB::getInstance()->get('donate_payments', ['user_id', '=', $target_user->data()->id])->results();
    if (count($payments)) {
        $username = $target_user->getDisplayname(true);
        $avatar = $target_user->getAvatar();
        $style = $target_user->getGroupStyle();

        $template_payments = [];
        foreach ($payments as $payment) {
            $template_payments[] = [
                'user_link' => URL::build('/panel/donate/payments/', 'user=' . $target_user->data()->id),
                'user_style' => $style,
                'user_avatar' => $avatar,
                'username' => $username,
                'currency' => Output::getPurified($payment->currency),
                'amount' => Output::getClean($payment->amount),
                'date' => date(DATE_FORMAT, $payment->created),
                'link' => URL::build('/panel/donate/payments', 'payment=' . Output::getClean($payment->id))
            ];
        }

        $template->getEngine()->addVariables([
            'USER' => $donate_language->get('admin', 'user'),
            'AMOUNT' => $donate_language->get('admin', 'amount'),
            'DATE' => $donate_language->get('admin', 'date'),
            'VIEW' => $donate_language->get('admin', 'view'),
            'USER_PAYMENTS' => $template_payments
        ]);

        if (!defined('TEMPLATE_STORE_SUPPORT')) {
            $template->assets()->include([
                AssetTree::DATATABLES
            ]);

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
        $template->getEngine()->addVariable('NO_PAYMENTS', $donate_language->get('admin', 'no_payments_for_user'));

    $template->getEngine()->addVariables([
        'VIEWING_PAYMENTS_FOR_USER' => $donate_language->get('admin', 'viewing_payments_for_user_x', ['user' => $target_user->getDisplayname(true)]),
        'BACK' => $language->get('general', 'back'),
        'BACK_LINK' => URL::build('/panel/donate/payments')
    ]);

    $template_file = 'donate/payments_user';
}

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

if (Session::exists('donate_success'))
    $success = Session::flash('donate_success');

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

$template->getEngine()->addVariables([
    'PARENT_PAGE' => PARENT_PAGE,
    'DASHBOARD' => $language->get('admin', 'dashboard'),
    'DONATE' => $donate_language->get('general', 'donate'),
    'PAGE' => PANEL_PAGE,
    'TOKEN' => Token::get(),
    'SUBMIT' => $language->get('general', 'submit'),
    'PAYMENTS' => $donate_language->get('admin', 'payments')
]);

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/panel_navbar.php');

// Display template
$template->displayTemplate($template_file);