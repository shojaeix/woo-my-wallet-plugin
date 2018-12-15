<?php

// ckeck for duplicate
if(!isset($wMyWallet_functions_loaded) or !$wMyWallet_functions_loaded){

    function wMyWallet_insert_new_transaction($user_id,$amount,$type,$old_amount,$new_amount,$created_at = null){
        // create created_at if is null
        if($created_at == null){
            $datetime = new DateTime('now',new DateTimeZone('Asia/Tehran'));
            $created_at = wMyWallet_datetime_to_string($datetime);
        }
        // convert created at to string if it's Object
        else if ($created_at instanceof DateTime){
            $created_at = wMyWallet_datetime_to_string($created_at);
        }
        // validate type
        if(!in_array($type, ['debptor', 'creditor'])){
            throw new Exception('invalid type');
        }

        $data = [
            'user_id' => $user_id,
            'amount' => $amount,
            'type' => $type,
            'old_amount' => $old_amount,
            'new_amount' => $new_amount,
            'created_at' => $created_at,
        ];

        wMyWallet_DBHelper::insert('transactions',$data);
    }

    function wMyWallet_get_wallet_transactions(){}

    function wMyWallet_get_all_transactions(){}


}