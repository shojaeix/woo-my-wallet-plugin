<?php
/**
 * Created by PhpStorm.
 * User: Amin
 * Date: 2/21/2019
 * Time: 11:03 AM
 */

?>

<form method="post">

    <?php
    // add wp nonce field
    wp_nonce_field( 'wMyWallet-invite-friend');
    //++++ show errors
    foreach ($args['errors'] as $error){ ?>

        <div class="alert alert-danger" ><?php echo $error; ?></div>

    <?php }
    //---- end errors

    ?>
    <!-- form inputs -->
    <label for="invite_friend_form_name" >نام شما</label>
    <input name="wMyWallet_name" id="invite_friend_form_name" class="form-control" type="text">

    <label for="invite_friend_email" >ایمیل دوست</label>
    <input name="wMyWallet_friend_email" id="invite_friend_email"  class="form-control" type="text">

    <label for="invite_friend_phone_number"  class="rtl" >شماره موبایل دوست</label>
    <input name="wMyWallet_friend_phone_number" id="invite_friend_phone_number"  class="form-control" type="text">
    <!-- end inputs -->
    <!-- submit button -->
    <button type="submit" class="button button-primary">ارسال دعوتنامه</button>
    <!-- end submit button -->
</form>
