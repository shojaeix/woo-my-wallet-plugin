<?php
/**
 * Created by Amin shojaei.
 * Email: shojaei.x@gmail.com
 * Date: 12/16/2018
 * Time: 09:04
 */

defined('ABSPATH') or die;
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
                    'name' => 'withdrawal_request_form_page',
                    'selected' => $args['withdrawal_request_form_page'],
            ]); ?>
            </td>
        </tr>
        <tr>
            <td>صفحه لیست تراکنش های کیف پول</td>
            <td><?php wp_dropdown_pages([
                    'show_option_none' => ' -- ',
                    'name' => 'my_wallet_transactions_page',
                    'selected' => $args['my_wallet_transactions_page'],
                ]); ?>
            </td>
        </tr>
        <tr>
            <td>صفحه لیست درخواست های برداشت</td>
            <td><?php wp_dropdown_pages([
                    'show_option_none' => ' -- ',
                    'name' => 'my_withdrawal_requests_page',
                    'selected' => $args['my_withdrawal_requests_page'],
                ]); ?>
            </td>
        </tr>

        <tr>
            <td colspan="2"><h3>تنظیمات زیر مجموعه گیری</h3></td>
        </tr>
        <tr>
            <td>شارژ اولیه کیف پول کاربرانی که با کد معرف ثبت نام میکنند(برای غیر فعال کردن 0 وارد کنید)</td>
            <td><input name="invited-user-first-charge"  value="<?php echo $args['invited-user-first-charge']; ?>"  type="text"><?php echo ' ' . wMyWallet_get_currency_symbol(); ?></td>
        </tr>
        <tr>
            <td>مژدگانی معرف پس از تکمیل اولین خرید زیر مجموعه(برای غیر فعال کردن 0 وارد کنید)</td>
            <td><input name="inviter-award-on-user-first-order" value="<?php echo $args['inviter-award-on-user-first-order']; ?>" type="text" ><?php echo ' ' . wMyWallet_get_currency_symbol(); ?></td>
        </tr>
        <tr>
            <td>برای هر کاربر یک کد زیر مجموعه گیری متفاوت با نام کاربری ایجاد شود</td>
            <td>
                <input name="use-special-referral-code" type="hidden" value="off" style="display: none">
               <input name="use-special-referral-code" type="checkbox" value="on"
                <?php if ($args['use-special-referral-code'] == true) {
                    echo 'checked';
                } ?>
                >
            </td>
        </tr>
        <br>
        <tr>
            <td>
                <button class="button button-primary" type="submit">بروزرسانی</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>


