<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/17/2018
 * Time: 13:58
 */
$user = $args['user'];
?>
<div class="" >

    <h1>ایجاد تراکنش جدید</h1>
    <form method="post">
        <table>
            <thead>
            <th></th>
            <th></th>
            </thead>
            <tbody>
            <tr>
                <td>نام کاربر:</td>
                <td><?php echo $user->user_nicename;
                    // todo | show user phone number ?></td>
            </tr>
            <tr>
                <td>مقدار:</td>
                <td><input name="amount" type="text" value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" >
                    <?php echo wMyWallet_get_currency_symbol(); ?></td>
            </tr>
            <tr>
                <td>نوع:</td>
                <td><select name="type" >
                        <option> - </option>
                        <option value="subtraction" <?php echo (isset($_POST['type']) and $_POST['type']=='subtraction') ? 'selected' : '' ; ?>>کاهش</option>
                        <option value="addition" <?php echo (isset($_POST['type']) and $_POST['type']=='addition') ? 'selected' : '' ; ?>>افزایش</option>
                    </select></td>
            </tr>
            <tr>
                <td>توضیحات:</td>
                <td><textarea type="text" name="description" ><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>

                </td>
            </tr>
            <tr>
                <td></td>
                <td> <button type="submit" class="button button-primary">ایجاد</button>
                    <button type="submit"  form="change_user_id" class="button button-cancel" >تغیر شناسه کاربر</button>
                </td>

            </tr>

            </tbody>
        </table>
        <input name="user_id" value="<?php echo $user->ID; ?>" hidden>
    </form>

    <form id="change_user_id" >
        <input type="hidden" name="page" value="wmywallet-new-transaction-page" >
    </form>

</div>
