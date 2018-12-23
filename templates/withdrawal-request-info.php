<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/19/2018
 * Time: 09:38
 */
defined('ABSPATH') or die;

$withdrawal = $args['withdrawal'];
?>


<table>
    <thead>
  <th></th>
    <th></th>
    </thead>
    <tbody>
    <?php
    foreach ((array)$withdrawal as $property => $value) {
        echo '<tr>';
        echo '<td> ' . $property . '</td>';
        echo '<td> ' . $value . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>


<form method="post">
    <textarea name="admin_description" ><?php echo $withdrawal->admin_description; ?></textarea>
    <br>
    <button class="button-primary" type="submit">ویرایش توضیحات</button>
</form>

<?php if ( $withdrawal->status == 'pending'){ ?>
    <hr>
    <form method="post">
        <input name="paid" value="2" type="hidden">
        <button type="submit"  class="button-primary"  >پرداخت شد</button>
    </form>
<?php } ?>
