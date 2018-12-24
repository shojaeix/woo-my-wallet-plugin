<h1>مشخصات تراکنش</h1>
<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/17/2018
 * Time: 15:29
 */

$transaction = $args['transaction'];
?>

<table>
    <thead>
    <th></th>
    <th></th>
    </thead>
    <tbody>
    <tr>
        <td>شناسه تراکنش</td>
        <td><?php echo $transaction->id; ?></td>
    </tr>
    <tr>
        <td>مقدار</td>
        <td><?php echo $transaction->amount; ?></td>
    </tr>
    <tr>
        <td>نوع تراکنش</td>
        <td><?php echo $transaction->type; ?></td>
    </tr>
    <tr>
        <td>تاریخ</td>
        <td><?php echo wMyWallet_helical($transaction->created_at); ?></td>
    </tr>
    <tr>
        <td>شناسه کاربر</td>
        <td><?php echo $transaction->user_id; ?></td>
    </tr>
    <tr>
        <td>موجودی کیف پول قبل از تراکنش</td>
        <td><?php echo $transaction->old_amount; ?></td>
    </tr>
    <tr>
        <td>موجودی جدید کیف پول</td>
        <td><?php echo $transaction->new_amount; ?></td>
    </tr>
    <tr>
        <td>توضیحات تراکنش</td>
        <td><?php echo $transaction->description; ?></td>
    </tr>
    <tr>
        <td>شماره تراکنش مرتبط</td>
        <td><?PHP
            echo(isset($transaction->order_id) ? $transaction->order_id : ' - ');
            if (isset($transaction->order_link)) {
                ?>
                <a href="<?php echo $transaction->order_link; ?>">مشاهده سفارش</a>
                <?PHP
            }
            ?></td>
    </tr>


    </tbody>
</table>

