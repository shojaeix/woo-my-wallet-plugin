<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/17/2018
 * Time: 13:58
 */
$user = $args['user'];
?>
<br>
نام کاربر: <?php echo $user->user_nicename;
// todo | show user phone number ?>
<form method="post">

    <input name="user_id" value="<?php echo $user->ID; ?>" hidden>
<br>    مقدار <input name="amount" type="number" value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" >
<br>نوع تراکنش
    <select name="type" >
        <option> - </option>
        <option value="subtraction" >کاهش</option>
        <option value="addition" >افزایش</option>
    </select>
<br>
    توضیحات<input type="text" name="description" value="<?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?>" >
   <br> <button type="submit" class="button-primary">ایجاد</button>
</form>


<?php
// var_dump($user);
?>