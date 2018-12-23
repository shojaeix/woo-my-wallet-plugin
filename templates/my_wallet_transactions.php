<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/17/2018
 * Time: 11:24
 */

defined('ABSPATH') or die;


$transactions = isset($args['transactions']) ? $args['transactions'] : [] ;

// records should be displayed on a page
$records_per_page = 30;

// include the pagination class
//require_once wMyWallet_ROOT . '/vendor/stefangabos/zebra_pagination/Zebra_Pagination.php';

// instantiate the pagination object
$pagination = new Zebra_Pagination();

// the number of total records is the number of records in the array
$pagination->records(count($transactions));

// records per page
$pagination->records_per_page($records_per_page);

// here's the magic: we need to display *only* the records for the current page
$transactions = array_slice(
    $transactions,
    (($pagination->get_page() - 1) * $records_per_page),
    $records_per_page
);
?>

<div class="wrap" >

    <!-- new withdrawal request button -->
    <?php if(wMyWallet_short_code_used_page_exist('wMyWallet_withdrawal_request_form_page')){ ?>
        <a href="<?php echo wMyWallet_shortcode_used_page_link('wMyWallet_withdrawal_request_form_page'); ?>" >
            <button class="button button-primary">درخواست برداشت</button></a>
    <?php } ?>

    <!-- withdrawal requests -->
    <?php if(wMyWallet_short_code_used_page_exist('wMyWallet_my_withdrawal_requests_page')){ ?>
        <a href="<?php echo wMyWallet_shortcode_used_page_link('wMyWallet_my_withdrawal_requests_page'); ?>" >
            <button class="button button-primary">لیست درخواست های برداشت</button></a>
    <?php } ?>
    <br><br>
    <table>
        <thead>
        <th>شماره تراکنش</th>
        <th>نوع تراکنش</th>
        <th>مبلغ <?php echo ' (' . wMyWallet_get_currency_symbol() . ')'; ?> </th>
        <th>زمان</th>
        <th>توضیحات</th>
        <th></th>
        </thead>
        <tbody>

        <?php
        foreach ($transactions as $transaction){
            $class = 'wMyWallet_' . $transaction->type . '_row'
            ?>

            <tr class="<?php echo $class; ?>">
                <td><?php echo $transaction->id; ?></td>
                <td><?php echo ($transaction->type == 'subtraction') ? 'برداشت' : 'واریز' ; ?></td>
                <td><?php echo ($transaction->amount) . ' ' . get_woocommerce_currency_symbol(); ?>   </td>
                <td><?php echo wMyWallet_helical($transaction->created_at); ?></td>
                <td><?php echo ($transaction->description); ?></td>

                <td><?php if(isset($transaction->order_link)){ ?>
                    <a href="<?php echo $transaction->order_link; ?>"><button class="button button-small" >مشاهده سفارش</button> </a>
                <?php } ?></td>
            </tr>

            <?php
        }
        ?>

        </tbody>
    </table>

    <?php
    // render the pagination links
    $pagination->render();
    ?>

</div>
