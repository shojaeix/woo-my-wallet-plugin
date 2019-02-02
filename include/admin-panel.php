<?php
/**
 * Created by Amin shojaei.
 * Email: shojaei.x@gmail.com
 * Date: 12/15/2018
 * Time: 16:03
 */

defined('ABSPATH') or die;

add_action('admin_menu', 'wMyWallet_add_admin_menu_pages');

function wMyWallet_add_admin_menu_pages()
{

    // main menu
    add_menu_page(null, 'کیف پول من',
        null, 'wmywallet-main-menu', null);

    // transaction info
    add_submenu_page('wmywallet-main-menu', 'تراکنش ها', 'تراکنش ها', 'manage_options'
        , 'wmywallet-transaction-info'
        , 'wMyWallet_transaction_info');

    // new transaction
    add_submenu_page('wmywallet-main-menu', '+ تراکنش کیف پول', 'تراکنش جدید', 'manage_options'
        , 'wmywallet-new-transaction-page'
        , 'wmywallet_new_transaction_page');





    // transaction info
    add_submenu_page('wmywallet-main-menu', 'لیست درخواست های برداشت', 'درخواست های برداشت'
        , 'manage_options'
        , 'wmywallet-withdarawal-requests-list'
        , 'wMywallet_withdrawal_requests_list');

    // transaction info
    add_submenu_page(null, 'جزئیات درخواست', null
        , 'manage_options'
        , 'wMyWallet-withdrawal-request-info'
        , 'wMyWallet_withdrawal_request_info');

    // wallets list
    add_submenu_page('wmywallet-main-menu', 'لیست حساب ها', 'مشاهده لیست حساب ها'
        , 'manage_options'
        , 'wmywallet-wallets-list'
        , 'wMywallet_wallets_list');

    // setting
    add_submenu_page('wmywallet-main-menu', 'تنظیمات کیف پول کاربران', 'تنظیمات'
        , 'manage_options'
        , 'wmywallet-main-options'
        , 'wMyWallet_main_options_page');



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

            if ($deposit_product_id != wMyWallet_Options::get('deposit-product-id')) {
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
        if (isset($_POST['withdrawal-min']) and (is_numeric($_POST['withdrawal-min']))) {
            $min = (int)htmlspecialchars($_POST['withdrawal-min']);
            if ($min != wMyWallet_Options::get('withdrawal-min')) {
                if ($min < 0) {
                    wMyWallet_show_admin_error('مقدار حداقل موچودی جهت درخواست برداشت نامعتبر است.');
                } else {
                    if ((bool)wMyWallet_Options::set('withdrawal-min', $min)) {
                        wMyWallet_show_admin_notice('حداقل موجودی جهت درخواست برداشت به ' . $min . ' تغییر کرد.');
                    } else {
                        wMyWallet_show_admin_error('بروزرسانی حداقل درخواست برداشت ناموفق بود.');
                    }
                }
            }
        }
        // set pages
        $pages_list = [
            'withdrawal_request_form_page' => 'صفحه درخواست برداشت',
            'my_wallet_transactions_page' => 'صفحه لیست تراکنش ها',
            'my_withdrawal_requests_page' => 'صفحه لیست درخواست های برداشت',
        ];
        foreach ($pages_list as $key => $title){
            if(isset($_POST[$key]) and (is_numeric($_POST[$key])  or $_POST[$key] == '' )){
                $page = (int)$_POST[$key];
                if(wMyWallet_Options::set($key,$page))
                {
                    wMyWallet_show_admin_notice("صفحه «" . $title . "» با موفقیت بروزرسانی شد.");
                }
            }
        }
        // referral options -----------------

        // set use-special-referral-code
        if(isset($_POST['use-special-referral-code'])) {
            if ($_POST['use-special-referral-code'] == 'on') {
                wMyWallet_Options::set('use-special-referral-code', true);
            } else if ($_POST['use-special-referral-code'] == 'off'){
                wMyWallet_Options::set('use-special-referral-code', false);
            }
        }
        // set inviter-award-on-user-first-order
        if(isset($_POST['inviter-award-on-user-first-order']) and is_numeric($_POST['inviter-award-on-user-first-order'])){
            wMyWallet_Options::set('inviter-award-on-user-first-order',(int)$_POST['inviter-award-on-user-first-order']);
        }
        // set invited-user-first-charge
        if(isset($_POST['invited-user-first-charge']) and is_numeric($_POST['invited-user-first-charge'])){
            wMyWallet_Options::set('invited-user-first-charge',(int)$_POST['invited-user-first-charge']);
        }
        // -----------------

    } catch (Exception $exception) {
        wMyWallet_show_admin_error($exception->getMessage());
    }

    $args = [];
    $view_options = [
        'withdrawal-min',
        'deposit-product-id',
        'withdrawal_request_form_page',
        'my_wallet_transactions_page',
        'my_withdrawal_requests_page',

        'use-special-referral-code',
        'inviter-award-on-user-first-order',
        'invited-user-first-charge',
    ];

    foreach ($view_options as $option){

        $args[$option] = wMyWallet_Options::get($option);
    }
    // show template
    wMyWallet_render_template('main-options', $args, false);
}

// new transaction page
function wmywallet_new_transaction_page()
{

    if (!isset($_GET['user_id']) or !is_numeric($_GET['user_id'])) {

        // search for user
        if(isset($_GET['field'])){
            $field = $_GET['field'];

            // by ID
            if(is_numeric($field)){
                $user = get_user_by('ID', $field);

            }
            // by email
            if(!($user instanceof WP_User) and is_email($field)){
                echo 'search by email.';
                $user = get_user_by('email',$field);
            }
            // redirect if user found
            if($user instanceof WP_User){
                $user_id = $user->get('ID');
                wp_redirect(get_admin_url() . 'admin.php?page=wmywallet-new-transaction-page&user_id=' . $user_id);
            } else {
                wMyWallet_show_admin_error('شناسه وارد شده نامعتبر است.');
            }
        }
        // show choose user form if user not found
        if(!isset($user_id))
        {
            return wMyWallet_render_template('new_transction_choose_user_form', [], false);
        }

    }

    if(!isset($user_id))
    {
        $user_id = $_GET['user_id'];
    }

    $user = get_user_by('ID', $user_id);

    if (!$user instanceof WP_User) {
        wMyWallet_show_admin_error('ایدی کاربر نامعتبر است.');
        return wMyWallet_render_template('new_transction_choose_user_form', [], false);

    }
    //
    $args = [
        'user' => $user,
    ];

    // validation
    $validated = true;
    $validated_data = [];
    if (!isset($_POST['amount']) or !is_numeric($_POST['amount']) or (int)htmlspecialchars($_POST['amount']) <= 0) {
        if (isset($_POST['amount']))
            wMyWallet_show_admin_error('مقدار تراکنش نامعتبر است.');
        $validated = false;
    }

    if (!isset($_POST['type']) or !is_string($_POST['type']) or !in_array($_POST['type'], ['subtraction', 'addition'])) {
        if (isset($_POST['type']))
            wMyWallet_show_admin_error('نوع تراکنش نامعتبر است.');
        $validated = false;
    }

    if (!isset($_POST['description']) or !is_string($_POST['description']) or strlen($_POST['description'])<1) {
        if (isset($_POST['description']))
            wMyWallet_show_admin_error('لطفا حداقل ۱ حرف به عنوان توضیحات وارد کنید.');
        $validated = false;
    }

    if(isset($_POST['order_id'])){
        if(!is_numeric($_POST['order_id'])){
            wMyWallet_show_admin_error('مقدار وارد شده برای شماره سفارش نامعتبر است.');
            $validated = false;
        } else {
            // search for order
            try {
                $order = wc_get_order($_POST['order_id']);
            } catch (Exception $exception) {
                $order = null;
            }
            //  error
            if (!($order instanceof WC_Order)) {
                wMyWallet_show_admin_error('شماره سفارش وارد شده نامعتبر است.');
                $validated = false;
            } else {
                $validated_data['order_id'] = $_POST['order_id'];
            }
        }
    } else {
        $validated_data['order_id'] = 0;
    }
    // show form if not validated
    if (!$validated or (isset($_POST['edit']) and is_numeric($_POST['edit']) and (int)$_POST['edit'] == 2)) {
        return wMyWallet_render_template('new_transaction_form', $args, false);
    }



    $validated_data['amount'] = $_POST['amount'];
    $validated_data['type'] = $_POST['type'];
    $validated_data['description'] = $_POST['description'];

    // confirm
    $confirm = false;
    if (isset($_POST['confirm']) and is_numeric($_POST['confirm']) and (int)$_POST['confirm'] === 2) {
        $confirm = true;
    }
    // show confirm page if not confirmed
    $args['validated_data'] = $validated_data;

    $wallet = new wMyWallet_Wallet($user->ID);
    $args['wallet_amount'] = $wallet->get_amount();

    if (!$confirm) {
        return wMyWallet_render_template('new_transaction_confirm', $args, false); // show confirm page
    }

    // created new transaction if confirmed

    $old_amount = $wallet->get_amount();
    try {

        if ($validated_data['type'] == 'subtraction') {
            if ($wallet->minus_amount($validated_data['amount']) == false) {
                wMyWallet_show_admin_error('موجودی کیف پول کاربر کافی نیست.' . '(' . $wallet->get_amount() . ')');
                return wMyWallet_render_template('new_transaction_form', $args, false); // show confirm page
            }
        } else if ($validated_data['type'] == 'addition') {
            if ($wallet->add_amount($validated_data['amount']) == false) {
                wMyWallet_show_admin_error('خطا در هنگام افزایش موجودی کیف پول.');
                return wMyWallet_render_template('new_transaction_confirm', $args, false); // show confirm page
            }
        } else {
            throw new Exception('function: ' . __FUNCTION__ . ' LINE: ' . __LINE__);
        }

        $wallet->save();
    } catch (Exception $exception) {
        wMyWallet_show_admin_error($exception->getMessage());
    }
    $new_amount = $wallet->get_amount();
    // insert new transaction
    $transaction_id = wMyWallet_insert_new_transaction(
        $user->ID,
        $validated_data['amount'],
        $validated_data['type'],
        $old_amount,
        $new_amount,
        $validated_data['description'],
        null,
        $validated_data['order_id']
    );

    $transaction = wMyWallet_get_transaction($transaction_id);
    if($validated_data['order_id']){
        $transaction->order_link = get_edit_post_link($validated_data['order_id']);
    }

    $args = [
        'user' => $user,
        'transaction' => $transaction,

    ];

    wMyWallet_show_admin_notice('تراکنش با موفقیت انجام شد.');
    // show new transaction info
    return wMyWallet_render_template('transaction_info', $args, false);
}

// return transaction info
function wMyWallet_transaction_info()
{
    // return all transactions page if search field is empty
    if (!isset($_GET['search_value']))
        return wMyWallet_all_transactions_page();

    $search_value = $_GET['search_value'];
    $found = false;
    // search by transaction id
    if (is_numeric($search_value)) {
        $transaction = wMyWallet_get_transaction($search_value);
        $found = ($transaction != null);
        if (!$found) {
            wMyWallet_show_admin_error('تراکنشی با این شماره پیدا نشد.');
        }
    }
    if (!$found and !is_numeric($search_value)) {
        if (!is_numeric($search_value)) {
            $users = new WP_User_Query(array(
                'search' => '*' . esc_attr($search_value) . '*',
                'search_columns' => array(
                    'user_nicename',
                    'user_email',
                    'display_name',
                ),
            ));
            $users_found = $users->get_results();
            $count = count($users_found);
            if ($count === 1) {
                $found = true;
                $user = $users_found[0];
                $transaction = wMyWallet_get_transactions_by('user_id', $user->ID);
                if (!count($transaction)) {
                    wMyWallet_show_admin_error('تراکنشی برای کاربر پیدا نشد.');
                }
            } else if ($count > 1) {
                $found = true;
                $user_ids = [];
                foreach ($users_found as $user) {
                    array_push($user_ids, $user->ID);
                }
                $transaction = wMyWallet_get_transactions_by('user_id', $user_ids);
                wMyWallet_show_admin_error('<strong>توجه:</strong> نتیجه جستجو با این عبارت بیشتر از 1 کاربر است.');
            }
        }
    }

    // return form with error if not found
    if (!$found) {
        return wMyWallet_all_transactions_page();
    }

    // return single transaction page if transaction is not a array
    if (!is_array($transaction)) {
        if($transaction->order_id){
            $transaction->order_link = get_edit_post_link($transaction->order_id);
        }
        return
            wMyWallet_render_template('transaction_info', [
                'user' => get_user_by('id', 1),
                'transaction' => $transaction,
            ], false);
    } // return transactions lists if $transaction is array
    else {
        for ($i = 0; $i < count($transaction); $i++) {
            if (isset($transaction[$i]->order_id) and $transaction[$i] > 0) {
                $transaction[$i]->order_link = get_edit_post_link($transaction[$i]->order_id);
            }
        }
        $args = [
            'transactions' => $transaction,
        ];
        if (isset($users_found) and count($users_found) == 1) {
            $args['user'] = $user;
        }
        return
            ((!count($transaction)) ? wMyWallet_render_template('transaction_info_form', [], false) : '') .
            wMyWallet_render_template('all_transactions', $args, false);
    }
}

// all transactions page
function wMyWallet_all_transactions_page()
{
    $transactions = wMyWallet_get_all_transactions();

    $count = count($transactions);
    for($i=0; $i<$count; $i++){

        if(isset($transactions[$i]->order_id) and is_numeric($transactions[$i]->order_id) and $transactions[$i]->order_id > 0)
        {
            $transactions[$i]->order_link = get_edit_post_link($transactions[$i]->order_id);
        }
    }

    $args = [
        'transactions' => $transactions,
    ];
    // return tranasction_info_form + all_transaction templates
    wMyWallet_render_template('transaction_info_form', [], false);
    echo '<br>';
    wMyWallet_render_template('all_transactions', $args, false);
}

// withdrawal requests page
function wMywallet_withdrawal_requests_list()
{
    wMyWallet_render_template('withdrawal-requests-list', [
        'widthrawals' => wMyWallet_get_all_withdrawal_requests(),
    ], false);
}

function wMyWallet_withdrawal_request_info()
{
    $widthrawal_id = (isset($_GET['withdrawal_id']) and is_numeric($_GET['withdrawal_id'])) ? (int)$_GET['withdrawal_id'] : null;

    if ($widthrawal_id == null) {
        wMyWallet_show_admin_error('آیدی درخواست وارد نشده است.');
        return;
    }

    $rows = wMyWallet_DBHelper::select('
    select * from ' . wMyWallet_widthrawal_requests_table_name() . ' where id=' . $widthrawal_id);
    $widthrawal = (count($rows)) ? $rows[0] : null;

    if (is_null($widthrawal)) {
        wMyWallet_show_admin_error('آیدی درخواست وارد شده نامعتبر است.' . $widthrawal_id);
        return;
    }

    // update admin description if needed
    if (isset($_POST['admin_description'])) {
        $new_admin_description = htmlspecialchars($_POST['admin_description']);
        if ($new_admin_description != $widthrawal->admin_description) {
            $widthrawal->admin_description = $new_admin_description;
            wMyWallet_update_withdrawal_request($widthrawal_id, [
                'admin_description' => $new_admin_description,
            ]);
        }
    }

    // update admin description if needed
    if (isset($_POST['paid']) and is_numeric($_POST['paid']) and (int)$_POST['paid'] === 2) {
        if ($widthrawal->status != 'paid') {
            $widthrawal->status = 'paid';
            $widthrawal->paid_at = wMyWallet_datetime_to_string(new DateTime());
            wMyWallet_update_withdrawal_request($widthrawal_id, [
                'status' => 'paid',
                'paid_at' => $widthrawal->paid_at,
            ]);
        }
    }
    wMyWallet_render_template('withdrawal-request-info',
        [
            'withdrawal' => $widthrawal,
        ], false);
}

/**
 * Show all wallets with an inventory of more than 0
 */
function wMywallet_wallets_list(){
    // get wallets amount
    $query = "Select meta_value,user_id 
    from " . wMyWallet_DBHelper::wpdb()->prefix . "usermeta 
    where meta_key='" . wMyWallet_DBHelper::prefix . "amount' and meta_value>0";
    $amounts = wMyWallet_DBHelper::select($query);
    // get users from db
    $users = [];
    $users_ids = [];
    foreach($amounts as $amount){
        $user = get_user_by('ID',$amount->user_id);
        $user->amount = $amount->meta_value;
        array_push($users_ids,$amount->user_id);
        $users[$user->ID] = $user;
    }
    // get users inviter_name
    $prefix = wMyWallet_DBHelper::wpdb()->prefix;

    $user_id_condition = '(';
    for($i=0; isset($users_ids[$i]); $i++)
    {
        $user_id_condition .= $prefix . "usermeta.user_id=" . $users_ids[$i];
        if(isset($users_ids[$i+1])) {
            $user_id_condition .= ' OR ';
        }
    }
    $user_id_condition .= ')';
    $inviters_query = "
        select " . $prefix . "users.*, " . $prefix . "usermeta.*, " . $prefix . "usermeta.user_id as invited_user_id
        from " . $prefix . "users join " . $prefix . "usermeta
        on " . $prefix . "usermeta.meta_value=" . $prefix . "users.ID
        where " . $prefix . "usermeta.meta_key='inviter' and " . $prefix . "usermeta.meta_value>0 and " . $user_id_condition;
    $inviters = wMyWallet_DBHelper::select($inviters_query);
    foreach($inviters as $inviter){
        //
        if(!isset($users[$inviter->invited_user_id])) continue;
        //
        $users[$inviter->invited_user_id]->inviter_nicename = $inviter->user_nicename;
        $users[$inviter->invited_user_id]->inviter_id = $inviter->user_id;
    }
    // get users entered inviter code
     $entered_inviter_codes_query = "
        select " . $prefix . "usermeta.*
        from " . $prefix . "usermeta
        where " . $prefix . "usermeta.meta_key='entered_inviter_code' 
        and " . $user_id_condition;
    $entered_inviter_codes = wMyWallet_DBHelper::select($entered_inviter_codes_query);
    foreach($entered_inviter_codes as $eic){
        //
        if(!isset($users[$eic->user_id])) continue;
        //
        $users[$eic->user_id]->entered_inviter_code = $eic->meta_value;

    }

     // render template
    wMyWallet_render_template('wallets_list',[
        'users' => $users,
    ],false);
}


// add inviter info to user profile page on admin side
add_action( 'edit_user_profile', 'extra_user_profile_fields' );
function extra_user_profile_fields( $user ) {
    $inviter_data = get_userdata(get_user_meta( $user->ID, 'inviter',true ));
    ?>
    <h3><?php _e("اطلاعات معرف", "blank"); ?></h3>

    <table class="form-table">
        <tr>
            <th><label for="inviter_nicename"><?php _e("معرف"); ?></label></th>
            <td>
                <input disabled type="text" name="inviter_nicename" id="inviter_nicename" value="<?php echo esc_attr($inviter_data->user_nicename); ?>" class="regular-text" /><br />
                 <span class="description"><?php _e("نام معرف این کاربر"); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="inviter_nicename"><?php _e("ایمیل معرف"); ?></label></th>
            <td>
                <input disabled type="text" name="inviter_nicename" id="inviter_nicename" value="<?php echo esc_attr($inviter_data->user_email); ?>" class="regular-text" /><br />
            </td>
        </tr>

    </table>
<?php }