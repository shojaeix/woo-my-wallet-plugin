
<?php defined('ABSPATH') or die; ?>
<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 2/2/2019
 * Time: 09:27
 */

?>
<div class="wrap">
    <?php
    /**
     * Created by PhpStorm.
     * User: Almasvareh
     * Date: 12/19/2018
     * Time: 08:50
     */
    $users = isset($args['users']) ? $args['users'] : [];


    // how many records should be displayed on a page?
    $records_per_page = 2;

    // include the pagination class
    //require_once wMyWallet_ROOT . '/vendor/stefangabos/zebra_pagination/Zebra_Pagination.php';

    // instantiate the pagination object
    $pagination = new Zebra_Pagination();

    $pagination->base_url($current_url,false);
    // the number of total records is the number of records in the array
    $pagination->records(count($users));

    // records per page
    $pagination->records_per_page($records_per_page);

    // here's the magic: we need to display *only* the records for the current page
    $users = array_slice(
        $users,
        (($pagination->get_page() - 1) * $records_per_page),
        $records_per_page
    );

?>

<div><span> لیست تمام کاربرانی که دارای موجودی کیف پول میباشند</span></div>
<br>
<table class="table table-bordered" border="1">
    <thead>
    <th>آی دی کاربر</th>
    <th>نام کاربری</th>
    <th>موجودی</th>
    <th>معرف</th>
    <th>کد معرف وارد شد</th>
    </thead>
    <tbody>
    <?php
        foreach($users as $user){
             echo '<tr> 
            <td>' . $user->ID . '</td>
            <td>' . $user->user_login . '</td>
            <td>' . $user->amount . '</td>
            <td>' . ((isset($user->inviter_nicename)) ? $user->inviter_nicename : '') . '</td>
            <td>' . ((isset($user->entered_inviter_code)) ? $user->entered_inviter_code : '') . '</td>
            </tr>';
        }
    ?>
    </tbody>
</table>
    <?php
    // render the pagination links
    $pagination->render();
    ?>
