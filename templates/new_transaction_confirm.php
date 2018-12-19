<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/17/2018
 * Time: 14:37
 */

$validated_data = $args['validated_data'];
$user = $args['user'];

// todo | show phone number
?>

<form method="post">
    شناسه کاربر<input name="user_id" readonly type="text" value="<?php echo $args['user']->ID; ?>">
    <br>
    نام کاربر <input readonly type="text" value="<?php echo $user->user_nicename; ?>" >
    <br>
    مقدار<input type="number" readonly name="amount" value="<?php echo $validated_data['amount']; ?>">
    <br>
    نوع تراکنش<input type="text" readonly name="type" value="<?php echo $validated_data['type']; ?>" >
    <br>
    توضیحات<input type="text" readonly name="description" value="<?php echo $validated_data['description']; ?>" >
    <br>
    <input type="hidden" name="confirm" value="2" >
    <button type="submit" class="button-primary"> تایید </button>
</form>


<?php

?>