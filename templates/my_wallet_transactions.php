<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/17/2018
 * Time: 11:24
 */
$transactions = isset($args['transactions']) ? $args['transactions'] : [] ;

// records should be displayed on a page
$records_per_page = 30;

// include the pagination class
require_once wMyWallet_ROOT . '/vendor/stefangabos/zebra_pagination/Zebra_Pagination.php';

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


<table>
    <thead>
    <th>شماره تراکنش</th>
    <th>نوع تراکنش</th>
    <th>مقدار</th>
    <th>زمان</th>
    </thead>
    <tbody>

    <?php
        foreach ($transactions as $transaction){
            ?>
            <tr>
                <td><?php echo $transaction->id; ?></td>
                <td><?php echo ($transaction->type == 'subtraction') ? 'کاهش' : 'افزایش' ; ?></td>
                <td><?php echo ($transaction->amount) . get_woocommerce_currency_symbol(); ?>   </td>
                <td><?php echo ($transaction->created_at); //todo | convert date ?></td>
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

