<?php defined('ABSPATH') or die; ?>
<div class="wrap" >
    <?php
    /**
     * Created by PhpStorm.
     * User: Almasvareh
     * Date: 12/18/2018
     * Time: 14:13
     */

    $errors = (isset($args['errors'])) ? $args['errors'] : [];


    foreach ($errors as $error){
        echo $error . '<br>';
    }
    ?>


    <!--  my wallet transactions button  -->
    <?php if(wMyWallet_short_code_used_page_exist('wMyWallet_my_wallet_transactions_page')){ ?>
        <a href="<?php echo wMyWallet_shortcode_used_page_link('wMyWallet_my_wallet_transactions_page'); ?>" >
            <button class="button button-primary">لیست تراکنش ها</button></a>
    <?php } ?>

    <!-- withdrawal requests -->
    <?php if(wMyWallet_short_code_used_page_exist('wMyWallet_my_withdrawal_requests_page')){ ?>
        <a href="<?php echo wMyWallet_shortcode_used_page_link('wMyWallet_my_withdrawal_requests_page'); ?>" >
            <button class="button button-primary">لیست درخواست های برداشت</button></a>
    <?php } ?>
    <br><br>


    <form method="post">
        <table>
            <thead>
            <th colspan="2">
                <span class="m-amout">
                <?php echo 'موجودی کیف پول: ' . wMyWallet_my_wallet_amount();  ?>
                </span>
            </th>

            </thead>
            <tbody>
            <tr>
                <td>
                    مبلغ
                    <?php if(wMyWallet_Options::get('withdrawal-min') > 0) { ?>
                     (حداقل<?php echo ' ' . wMyWallet_Options::get('withdrawal-min') . ' ' . wMyWallet_get_currency_symbol(); ?>)
                    <?php } ?>
                </td>
                <td>
                    <input name="amount" type="text"
                           value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <td>
                    نوع واریز
                </td>
                <td>
                    <select name="transfer_type" required >
                        <option> -- </option>
                        <option value="cart_to_cart"  <?php echo (isset($_POST['transfer_type']) and $_POST['transfer_type'] == 'cart_to_cart') ? "selected" : '' ; ?> >کارت به کارت(شامل کارمزد)</option>
                        <option value="permanent"  <?php echo (isset($_POST['transfer_type']) and $_POST['transfer_type'] == 'permanent') ? "selected" : '' ; ?> >وایز به حساب(حواله پایا، بدون کارمزد)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    شماره کارت
                </td>
                <td>
                    <input name="cart_number" type="text" value="<?php echo isset($_POST['cart_number']) ? $_POST['cart_number'] : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>
                    شماره حساب
                </td>
                <td>
                    <input name="account_number" type="text" value="<?php echo isset($_POST['account_number']) ? $_POST['account_number'] : ''; ?>">
                </td>
            </tr>
            <tr>
                <td>
                    توضیحات (اختیاری)
                </td>
                <td>
                    <textarea name="user_description" ><?php echo isset($_POST['user_description']) ? $_POST['user_description'] : ''; ?> </textarea>
                </td>
            </tr>
            <tr><td></td><td><button type="submit" name="submit" class="button button-primary" >درخواست برداشت</button></td></tr>
            </tbody>
        </table>


        <br>
        <br>

        <br>
        <br>

        <br>
        <br>
        <br>

    </form>

</div>