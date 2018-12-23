<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/23/2018
 * Time: 14:15
 */

if(count($args) == 0){
    $transactions_button = true;
    $withdrawal_requests = true;
    $withdrawal_request = true;
} else {
    $transactions_button = ((isset($args['transactions_button']) and $args['transactions_button']));
    $withdrawal_requests = ((isset($args['withdrawal_requests']) and $args['withdrawal_requests']));
    $withdrawal_request = ((isset($args['withdrawal_request']) and $args['withdrawal_request']));
}
?>

<!--  my wallet transactions button  -->
<?php if($transactions_button and wMyWallet_short_code_used_page_exist('wMyWallet_my_wallet_transactions_page')){ ?>
    <a href="<?php echo wMyWallet_shortcode_used_page_link('wMyWallet_my_wallet_transactions_page'); ?>" >
        <button class="button button-primary">مشاهده لیست تراکنش ها</button></a>
<?php } ?>

<!-- withdrawal requests -->
<?php if($withdrawal_requests and wMyWallet_short_code_used_page_exist('wMyWallet_my_withdrawal_requests_page')){ ?>
    <a href="<?php echo wMyWallet_shortcode_used_page_link('wMyWallet_my_withdrawal_requests_page'); ?>" >
        <button class="button button-primary">مشاهده لیست درخواست های برداشت</button></a>
<?php } ?>

<!-- new withdrawal request button -->
<?php
if($withdrawal_request and wMyWallet_short_code_used_page_exist('wMyWallet_withdrawal_request_form_page')){ ?>
    <a href="<?php echo wMyWallet_shortcode_used_page_link('wMyWallet_withdrawal_request_form_page'); ?>" >
        <button class="button button-primary">درخواست برداشت جدید</button></a>
<?php } ?>
