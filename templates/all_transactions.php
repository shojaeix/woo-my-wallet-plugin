<h1>لیست تمام تراکنش ها</h1>
<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/18/2018
 * Time: 12:10
 */

$types_trans = [
        'subtraction' => 'برداشت',
        'addition' => 'واریز',
];

?>
<div class="wrap" >
<table border="1" >
    <thead>
    <th>شماره تراکنش</th>
    <th>نام کاربر</th>
    <th>ایمیل</th>
    <th>نوع تراکنش</th>
    <th>مبلغ</th>
    <th>زمان</th>
    <th>توضیحات</th>
    <th ></th>
    </thead>
    <tbody>
    <?php
    $transactions = $args['transactions'];
     /**
     * @var $transaction stdClass
     */
    foreach ($transactions as $transaction){
        echo '<tr>';
        echo '<td>' . $transaction->id . '</td>';
        echo '<td>' . $transaction->user->user_nicename . '</td>';
        echo '<td>' . $transaction->user->user_email . '</td>';
        echo '<td>' . ((isset($types_trans[$transaction->type])) ? $types_trans[$transaction->type] : 'نامشخص' ). '</td>';
        echo '<td>' . $transaction->amount . '</td>';
        echo '<td>' . wMyWallet_helical($transaction->created_at) . '</td>';
        echo '<td>' . $transaction->description . '</td>';
        if(isset($transaction->order_link)){
            echo '<td>' . '<a href="' . $transaction->order_link . '" >مشاهده سفارش</a>' . '</td>';
        } else {
            echo '<td></td>';
        }
        echo '</tr>';
    }
    ?>
    </tbody>
</table></div>