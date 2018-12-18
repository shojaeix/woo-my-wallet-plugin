<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/18/2018
 * Time: 12:10
 */

?>

<table border="1">
    <thead>
    <th>شماره تراکنش</th>
    <th>شناسه کاربر</th>
    <th>نوع تراکنش</th>
    <th>مبلغ</th>
    <th>زمان</th>
    <th>توضیحات</th>
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
        echo '<td>' . $transaction->user_id . '</td>';
        echo '<td>' . $transaction->type . '</td>';
        echo '<td>' . $transaction->amount . '</td>';
        echo '<td>' . $transaction->created_at . '</td>';
        echo '<td>' . $transaction->description . '</td>';

        echo '</tr>';
    }
    ?>
    </tbody>
</table>