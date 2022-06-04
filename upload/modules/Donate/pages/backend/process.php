<?php
/*
 *  Made by Partydragen
 *  https://partydragen.com/
 *
 *  Process donation
 */
$configuration = new Configuration('donate');

$paypal_email = DB::getInstance()->get('donate_settings', ['name', '=', 'paypal_email'])->results();
if (!count($paypal_email) || empty($paypal_email[0]->value)) {
    Session::flash('donate_error', $donate_language->get('general', 'donate_error'));
    Redirect::to(URL::build('/donate/'));
}
$paypal_email = $paypal_email[0]->value;

// Is user donating as anonymous?
if ($_POST['anonymous'] == 0 && $user->isLoggedIn()) {
	$user_id = $user->data()->id;
} else {
	$user_id = 0;
}

$currency = $configuration->get('currency');
$min_amount = $configuration->get('min_amount');

// Get amount
if (!isset($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] < $min_amount) {
    Session::flash('donate_error', $donate_language->get('general', 'invalid_amount'));
    Redirect::to(URL::build('/donate/'));
}
$amount = $_POST['amount'];
?>

<center><h1><?php echo $donate_language->get('general', 'processing'); ?></h1></center>

<form name="pay" action="https://www.paypal.com/donate" method="post" target="_top">
  <input type="hidden" name="cmd" value="_xclick">
  <input type="hidden" name="business" value="<?php echo $paypal_email; ?>" />
  <input type="hidden" name="currency_code" value="<?php echo $currency; ?>" />
  <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
  <input type="hidden" name="item_name" value="<?php echo SITE_NAME; ?>">
  <input type="hidden" name="custom" value="<?php echo $user_id; ?>">
  <input type="hidden" name="return" value="<?php echo rtrim(Util::getSelfURL(), '/') . URL::build('/donate/', 'gateway=PayPal&do=success'); ?>">
  <input type="hidden" name="cancel_return" value="<?php echo rtrim(Util::getSelfURL(), '/') . URL::build('/donate/', 'gateway=PayPal&do=cancelled'); ?>">
  <input type="hidden" name="rm" value="2">
  <input type="hidden" name="no_shipping" value="1">
  <input type="hidden" name="notify_url" value="<?php echo rtrim(Util::getSelfURL(), '/') . URL::build('/donate/listener/', 'gateway=PayPal'); ?>" />
</form>

<script type="text/javascript">
	document.pay.submit();
</script>
