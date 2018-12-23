<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/18/2018
 * Time: 15:24
 */
defined('ABSPATH') or die;

$requests = isset($args['requests']) ? $args['requests'] : [];


// how many records should be displayed on a page?
$records_per_page = 10;

// include the pagination class
//require_once wMyWallet_ROOT . '/vendor/stefangabos/zebra_pagination/Zebra_Pagination.php';

// instantiate the pagination object
$pagination = new Zebra_Pagination();

// the number of total records is the number of records in the array
$pagination->records(count($requests));

// records per page
$pagination->records_per_page($records_per_page);

// here's the magic: we need to display *only* the records for the current page
$requests = array_slice(
    $requests,
    (($pagination->get_page() - 1) * $records_per_page),
    $records_per_page
);

$statuses_trans = [
    'pending' => 'در انتظار پرداخت',
    'paid' => 'پرداخت شده',
    'rejected' => 'نامعتبرر ',
    'canceled' => 'لغو شده',

];
?>
<div class="wrap" >


    <!-- new withdrawal request button -->
    <?php
    if(wMyWallet_short_code_used_page_exist('wMyWallet_withdrawal_request_form_page')){ ?>
        <a href="<?php echo wMyWallet_shortcode_used_page_link('wMyWallet_withdrawal_request_form_page'); ?>" >
            <button class="button button-primary">درخواست برداشت </button></a>
    <?php } ?>

    <!--  my wallet transactions button  -->
    <?php if(wMyWallet_short_code_used_page_exist('wMyWallet_my_wallet_transactions_page')){ ?>
        <a href="<?php echo wMyWallet_shortcode_used_page_link('wMyWallet_my_wallet_transactions_page'); ?>" >
            <button class="button button-primary">لیست تراکنش ها</button></a>
    <?php } ?>

    <br><br>


    <table>
        <thead>
        <th>شماره درخواست</th>
        <th>وضعیت</th>
        <th>مبلغ<?php echo ' (' . wMyWallet_get_currency_symbol() . ')'; ?></th>
        <th>توضیحات شما</th>
        <th>تاریخ درخواست</th>
        <th>تاریخ واریز</th>
        </thead>
        <tbody>
        <?php
        foreach ($requests as $request){
            echo '<tr>';
            echo '<td> ' . $request->id . '</td>';
            echo '<td> ' . ((isset($statuses_trans[$request->status])) ? $statuses_trans[$request->status] : 'نامشخص' ) . '</td>';
            echo '<td>' . $request->amount . '</td>';
            echo '<td>' . $request->user_description . '</td>';
            echo '<td>' . wMyWallet_helical($request->created_at) . '</td>'; // todo | shamsi
            echo '<td>' . (isset($request->paid_at) ? wMyWallet_helical($request->paid_at) : '') . '</td>';

            echo '</tr>';
        }
        ?>
        </tbody>
    </table>

    <?php
    // render the pagination links
    $pagination->render();
    ?>
</div>