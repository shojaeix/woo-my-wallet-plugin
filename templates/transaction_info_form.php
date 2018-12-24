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
    شناسه تراکنش<input name="search_value" placeholder="شماره تراکنش یا نام، ایمیل یا نام کاربری کاربر" size="50"
                       value="<?php echo isset($_GET['search_value']) ? $_GET['search_value'] : '' ?>" type="text">

    <button class="button-primary" type="submit">مشاهده </button>
</form>
