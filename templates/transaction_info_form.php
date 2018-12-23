<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/17/2018
 * Time: 16:58
 */
defined('ABSPATH') or die;
?>
<h1>جستجو در تراکنش ها</h1>
<form method="get">
    <input type="hidden" name="page"  value="wmywallet-transaction-info">
    شناسه تراکنش<input name="transaction_id"
                       value="<?php echo isset($_GET['transaction_id']) ? $_GET['transaction_id'] : '' ?>" type="text">

    <button class="button-primary" type="submit">مشاهده </button>
</form>
