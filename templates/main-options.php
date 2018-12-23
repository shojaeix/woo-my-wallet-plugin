<?php
/**
 * Created by Amin shojaei.
 * Email: shojaei.x@gmail.com
 * Date: 12/16/2018
 * Time: 09:04
 */

defined('ABSPATH') or die;
if (!isset($args['deposit-product-id'])) {
    $args['deposit-product-id'] = '';
}
if(!isset($args['withdrawal-min'])){
    $args['withdrawal-min'] = '';
}

?>

<h1>تنظیمات «پلاگین کیف پول من»</h1>
<form method="post">
    <table class="table">
        <tbody>
        <tr>
            <td><label>شناسه محصول شارژ کیف پول</label></td>
            <td><input type="number" name="deposit-product-id" value="<?php echo $args['deposit-product-id']; ?>"></td>
        </tr>
        <tr>
            <td><label>حداقل موجودی جهت درخواست برداشت وجه</label></td>
            <td><input type="number" name="withdrawal-min" value="<?php echo $args['withdrawal-min']; ?>"> <?php echo wMyWallet_get_currency_symbol(); ?></td>
        </tr>
        <tr><td><br></td></tr>
        <tr>
            <td colspan="2"><h3>صفحاتی که شورتکد هارا در آنها قرار داده اید انتخاب کنید.</h3></td>
        </tr>
        <tr>
            <td>صفحه درخواست برداشت</td>
            <td><?php wp_dropdown_pages([
                    'show_option_none' => ' -- ',
                    'name' => 'wMyWallet_withdrawal_request_form_page',
                    'selected' => $args['wMyWallet_withdrawal_request_form_page'],
            ]); ?>
            </td>
        </tr>
        <tr>
            <td>صفحه لیست تراکنش های کیف پول</td>
            <td><?php wp_dropdown_pages([
                    'show_option_none' => ' -- ',
                    'name' => 'wMyWallet_my_wallet_transactions_page',
                    'selected' => $args['wMyWallet_my_wallet_transactions_page'],
                ]); ?>
            </td>
        </tr>
        <tr>
            <td>صفحه لیست درخواست های برداشت</td>
            <td><?php wp_dropdown_pages([
                    'show_option_none' => ' -- ',
                    'name' => 'wMyWallet_my_withdrawal_requests_page',
                    'selected' => $args['wMyWallet_my_withdrawal_requests_page'],
                ]); ?>
            </td>
        </tr>
        <tr>
            <td>
                <button class="button button-primary" type="submit">تایید</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>


