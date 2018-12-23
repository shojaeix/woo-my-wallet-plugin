<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/17/2018
 * Time: 14:37
 */

defined('ABSPATH') or die;

$validated_data = $args['validated_data'];
$user = $args['user'];

// todo | show phone number
?>
<div class="wrap" >

    <h1>تایید مشخصات تراکنش جدید</h1>
    <form method="post">
        <table>
            <thead><th></th><th></th></thead>
            <tbody>
            <tr>
                <td>نام کاربر</td>
                <td><?php echo $user->user_nicename; ?></td>
            </tr><tr>
                <td>ایمیل کاربر</td>
                <td><?php echo $user->user_email; ?></td>
            </tr><tr>
                <td>شناسه کاربر</td>
                <td><input name="user_id" readonly type="text" value="<?php echo $args['user']->ID; ?>"></td>
            </tr><tr>
                <td>مقدار</td>
                <td><input type="text" readonly name="amount" value="<?php echo $validated_data['amount']; ?>"></td>
            </tr><tr>
                <td> نوع تراکنش</td>
                <td>
                    <input type="hidden"  name="type" value="<?php echo $validated_data['type']; ?>" >
                    <input type="text" readonly  value="<?php echo (in_array($validated_data['type'],['addition','subtraction'])) ? (($validated_data['type'] == 'addition') ? 'افزایش' : 'کاهش') : 'نامشخص' ; ?>" >
                </td>
            </tr><tr>
                <td>توضیحات</td>
                <td><input type="text" readonly name="description" value="<?php echo $validated_data['description']; ?>" ></td>
            </tr><tr>
                <td></td>
                <td>    <input type="hidden" name="confirm" value="2" >
                    <button type="submit" class="button-primary"> تایید </button>
                    <button type="submit" form="back" class="button-cancel"> ویرایش </button>
                </td>
            </tr>
            </tbody>
        </table>

    </form>

    <form id="back"  method="post" hidden action="<?php echo (get_admin_url() . 'admin.php?page=wmywallet-new-transaction-page&user_id=' . $args['user']->ID); ?>">
        <input type="text" readonly name="amount" value="<?php echo $validated_data['amount']; ?>">
        <input type="text" hidden name="description" value="<?php echo $validated_data['description']; ?>" >
        <input type="text" readonly name="type" value="<?php echo $validated_data['type']; ?>" >
        <input type="hidden" name="edit" value="2">
    </form>

</div>
