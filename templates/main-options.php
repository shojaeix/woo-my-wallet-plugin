<?php
/**
 * Created by Amin shojaei.
 * Email: shojaei.x@gmail.com
 * Date: 12/16/2018
 * Time: 09:04
 */
if (!isset($args['deposit-product-id'])) {
    $args['deposit-product-id'] = '';
}
if(!isset($args['withdrawal-min'])){
    $args['withdrawal-min'] = '';
}
?>

<h1>گوی است دوای هرچه درد است</h1>
<form method="post">
    <table class="table">
        <tbody>
        <tr>
            <td><label>شماره محصول مخصوص جهت شارژ موجودی کیف پول</label></td>
            <td><input type="number" name="deposit-product-id" value="<?php echo $args['deposit-product-id']; ?>"></td>
        </tr>
        <tr>
            <td><label>حداقل موجودی جهت درخواست برداشت وجه</label></td>
            <td><input type="number" name="withdrawal-min" value="<?php echo $args['withdrawal-min']; ?>"></td>
        </tr>
        <tr>
            <td>
                <button class="button button-primary" type="submit">تایید</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>


