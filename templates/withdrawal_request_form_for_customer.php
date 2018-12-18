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
echo 'موجودی کیف پول: ' . wMyWallet_my_wallet_amount();
 ?>

<form method="post">
   مبلغ
    <br><input name="amount" type="number" value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>">
    <br>
    نوع واریز:

    <br><select name="transfer_type">
        <option> -- </option>
        <option value="cart_to_cart"  <?php echo (isset($_POST['transfer_type']) and $_POST['transfer_type'] == 'cart_to_cart') ? "selected" : '' ; ?> >کارت به کارت(شامل کارمزد)</option>
        <option value="permanent"  <?php echo (isset($_POST['transfer_type']) and $_POST['transfer_type'] == 'permanent') ? "selected" : '' ; ?> >وایز به حساب(حواله پایا، بدون کارمزد)</option>
    </select>
    <br>
    شماره کارت
    <br><input name="cart_number" type="text" value="<?php echo isset($_POST['cart_number']) ? $_POST['cart_number'] : ''; ?>">
    <br>
    شماره حساب
    <br><input name="account_number" type="text" value="<?php echo isset($_POST['account_number']) ? $_POST['account_number'] : ''; ?>">
    <br>
      توضیحات اختیاری
    <br><textarea name="user_description" ><?php echo isset($_POST['user_description']) ? $_POST['user_description'] : ''; ?> </textarea>
    <br>
    <br>
    <button type="submit" class="button-primary" >درخواست برداشت</button>
</form>

