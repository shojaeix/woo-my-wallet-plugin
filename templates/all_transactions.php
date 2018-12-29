<h1>لیست تراکنش ها</h1>
<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/18/2018
 * Time: 12:10
 */

defined('ABSPATH') or die;

$types_trans = [
        'subtraction' => 'برداشت',
        'addition' => 'واریز',
];
if(isset($args['user'])){
    $user = $args['user'];
}
?>
<div class="wrap" >

    <?php if(isset($user)){ echo "
    نام کاربر: $user->display_name <br>
    شناسه کاربر: $user->ID <br>
   
    ایمیل: $user->user_email <br><br>
    "; } ?>
    <!--
<table border="1" >
    <thead>
    <th>شماره تراکنش</th>
    <th>نام کاربر</th>
    <th>شناسه کاربری</th>
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
        echo '<td>' . $transaction->user->ID . '</td>';
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
</table>
-->

</div>

<table class="widefat fixed" cellspacing="0">
    <thead>
    <tr>
        <th id="columnname" class="manage-column column-columnname num" scope="col">شماره تراکنش</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">نام کاربر</th>
        <th id="columnname" class="manage-column column-columnname num" scope="col">شناسه کاربری</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">ایمیل</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">نوع تراکنش</th>
        <th id="columnname" class="manage-column column-columnname num" scope="col">مبلغ</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">زمان</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">توضیحات</th>
        <th ></th>


    </tr>
    </thead>

    <tfoot>

    </tfoot>

    <tbody>
    <?php

    foreach ($transactions as $transaction){
        if($transaction->id % 2) {
            echo '<tr>';
        } else {
            echo '<tr  class="alternate">';
        }
        echo '<td class="column-columnname">' . $transaction->id . '</td>';
        echo '<td class="column-columnname">' . $transaction->user->user_nicename . '</td>';
        echo '<td class="column-columnname">' . $transaction->user->ID . '</td>';
        echo '<td class="column-columnname">' . $transaction->user->user_email . '</td>';
        echo '<td class="column-columnname">' . ((isset($types_trans[$transaction->type])) ? $types_trans[$transaction->type] : 'نامشخص' ). '</td>';
        echo '<td class="column-columnname">' . $transaction->amount . '</td>';
        echo '<td class="column-columnname">' . wMyWallet_helical($transaction->created_at) . '</td>';
        echo '<td class="column-columnname">' . $transaction->description . '</td>';
        if(isset($transaction->order_link)){
            echo '<td class="column-columnname">' . '<a href="' . $transaction->order_link . '" >مشاهده سفارش</a>' . '</td>';
        } else {
            echo '<td class="column-columnname"></td>';
        }
        echo '</tr>';
    }

    ?>
    </tbody>
</table>