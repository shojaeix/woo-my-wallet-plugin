<?php
/**
 * Created by PhpStorm.
 * User: Almasvareh
 * Date: 12/12/2018
 * Time: 14:35
 */

if (!isset($wMyWallet_helper_functions_loaded) or !$wMyWallet_helper_functions_loaded) {

    function wMyWallet_datetime_to_string(DateTime $dateTime)
    {
        return $dateTime->format('Y-m-d H:i:s');
    }

    function wMyWallet_string_to_datetime(string $string) : DateTime
    {
        return DateTime::createFromFormat('Y-m-d H:i:s');
    }

    function doLog($text)
    {
        echo "<br>" . 'doLog("' . $text . '")';
        $filename = "dolog.log";
        $fh = fopen($filename, "a") or die("Could not open log file.");
        fwrite($fh, date("d-m-Y, H:i") . " - $text\n") or die("Could not write file!");
        fclose($fh);
    }

    function wMyWallet_get_datetime_string_to_show(DateTime $dateTime){
        return $dateTime->format('Y-m-d H:i:s');
        //return jdate('Y-m-d H:i:s')
    }

    /**
     * Require template from templates directory and pass $args array to it.
     * @param $template_name
     * @param array $args
     * @throws Exception throw if template file not exists. template_name.php file should exist in templates directory.
     */
    function wMyWallet_render_template($template_name,array $args = []){
        if(!is_file(wMyWallet_ROOT . '/templates/' . $template_name . '.php'))
            throw new Exception('Template ' . $template_name . ' not found.');

        require wMyWallet_ROOT . '/templates/' . $template_name . '.php';
    }

    function wMyWallet_show_admin_error($text){
        ?>
        <div class="error notice">
            <p><?php echo $text; ?></p>
        </div>
        <?php
    }

    function wMyWallet_show_admin_notice($text){
        ?>
        <div class="updated notice">
            <p><?php echo $text; ?></p>
        </div>
        <?php
    }
    function wMyWallet_show_admin_notice_pan($text){
        ?>
        <div class="update-nag notice">
            <p><?php echo $text; ?></p>
        </div>
        <?php
    }
}