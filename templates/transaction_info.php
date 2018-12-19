<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/17/2018
 * Time: 15:29
 */
 $translate = [
    'amount' => 'مقدار',
    'type' => 'نوع تراکنش',
    'created_at' => 'تاریخ',
    'user_id' => 'شناسه کاربر',
    'old_amount' => 'موجودی کیف پول قبل از تراکنش',
    'new_amount' => 'موجودی جدید کیف پول',
    'description' => 'توضیحات تراکنش',
    'id' => 'شناسه تراکنش',
];
foreach ($args['transaction'] as $key=>$value){
    echo '<br>' . $translate[$key] . ' = ' . $value;
}
?>

