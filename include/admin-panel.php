<?php
/**
 * Created by Amin shojaei.
 * Email: shojaei.x@gmail.com
 * Date: 12/15/2018
 * Time: 16:03
 */

add_action('admin_menu', 'wMyWallet_add_admin_menu_pages');

function wMyWallet_add_admin_menu_pages(){

    // main menu
    add_menu_page( null, 'کیف پول من',
        null, 'wmywallet-main-menu', null );

    // new transaction
    add_submenu_page('wmywallet-main-menu','+ تراکنش کیف پول','تراکنش جدید','manage_options'
        ,'wmywallet-new-transaction-page'
        ,'wmywallet_new_transaction_page');

    // setting
    add_submenu_page('wmywallet-main-menu','تنظیمات کیف پول کاربران','تنظیمات'
        ,'manage_options'
        ,'wmywallet-main-options'
        ,'wMyWallet_main_options_page');

    // transaction info
    add_submenu_page('wmywallet-main-menu','مشاهده تراکنش','مشاهده تراکنش','manage_options'
        ,'wmywallet-transaction-info'
        ,'wMyWallet_transaction_info');

}

/**
 * Check for submited options and save then after validatewmywallet_transaction_info
 * Show main-option template
 * @throws Exception
 */
function wMyWallet_main_options_page()
{

    try {
        // apply new deposit-product-id if submited
        if (isset($_POST['deposit-product-id']) and is_numeric($_POST['deposit-product-id'])) {

            // cast input deposit-product-id to integer
            $deposit_product_id = (int)htmlspecialchars($_POST['deposit-product-id']);

            if($deposit_product_id != wMyWallet_Options::get('deposit-product-id')) {
                // deactive deposit product if it's zero
                if ($deposit_product_id === 0) {
                    wMyWallet_show_admin_notice('شارژ کیف پول غیر فعال شد.');
                    wMyWallet_Options::set('deposit-product-id', $deposit_product_id);
                } // apply new product
                else {
                    // get submited product id from db
                    $posts = wMyWallet_DBHelper::select('select * from ' . wMyWallet_DBHelper::wpdb()->prefix . 'posts '
                        . 'where ID=' . $deposit_product_id . ' and post_type=\'product\' and post_status=\'publish\'');
                    // show error if product id is invalid
                    if (!count($posts)) {
                        wMyWallet_show_admin_error('شناسه پست وارد شده نامعتبر است.');
                    } else {
                        // save id
                        wMyWallet_Options::set('deposit-product-id', $deposit_product_id);
                        wMyWallet_show_admin_notice('شماره محصول مخصوص جهت شارژ موجودی کیف پول بروزرسانی شد.');

                    }
                }
            }

        }
        // apply new value for minimum withdrawal request
        if (isset($_POST['withdrawal-min']) and is_numeric($_POST['withdrawal-min'])) {
            $min = (int)htmlspecialchars($_POST['withdrawal-min']);
            if($min != wMyWallet_Options::get('withdrawal-min')) {
                if ($min <= 0) {
                    wMyWallet_show_admin_error('مقدار حداقل موچودی جهت درخواست برداشت نامعتبر است.');
                } else {
                    if ((bool)wMyWallet_Options::set('withdrawal-min', $min)) {
                        wMyWallet_show_admin_notice('حداقل موجودی جهت درخواست برداشت به ' . $min . ' تغیر کرد.');
                    } else {
                        wMyWallet_show_admin_error('بروزرسانی حداقل درخواست برداشت ناموفق بود.');
                    }
                }
            }
        }
    } catch (Exception $exception) {
        wMyWallet_show_admin_error($exception->getMessage());
    }
    // show template
    wMyWallet_render_template('main-options', [
        'deposit-product-id' => wMyWallet_Options::get('deposit-product-id'),
        'withdrawal-min' => wMyWallet_Options::get('withdrawal-min'),
    ],false);
}