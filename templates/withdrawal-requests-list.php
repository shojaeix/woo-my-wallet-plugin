<div class="wrap">
    <?php
    /**
     * Created by PhpStorm.
     * User: Almasvareh
     * Date: 12/19/2018
     * Time: 08:50
     */
    $requests = isset($args['widthrawals']) ? $args['widthrawals'] : [];


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

    ?>


    <table class="table" border >
        <thead>
        <th>شماره درخواست</th>
        <th>وضعیت</th>
        <th>مقدار</th>
        <th>توضیحات شما</th>
        <th>تاریخ درخواست</th>
        <th>تاریخ واریز</th>
        <th></th>
        </thead>
        <tbody>
        <?php
        foreach ($requests as $request){
        echo '<tr>';
        echo '<td> ' . $request->id . '</td>';
        echo '<td> ' . $request->status . '</td>';
        echo '<td>' . $request->amount . '</td>';
        echo '<td>' . $request->user_description . '</td>';
        echo '<td>' . wMyWallet_helical($request->created_at) . '</td>'; // todo | shamsi
        echo '<td>' . (isset($request->paid_at) ? wMyWallet_helical($request->paid_at) : '') . '</td>';
        ?>
        <td>
            <form>
                <input name="withdrawal_id" type="hidden" value="<?php echo $request->id; ?>">
                <input name="page" type="hidden" value="wMyWallet-withdrawal-request-info">
                <button class="button-primary" type="submit" >جزئیات</button>
            </form>

            <?php
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