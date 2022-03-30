<?php

/*
  Plugin Name: Data Scrapper
  Plugin URI:
  Description:
  Version: 1.0.0
  Author URI:
  License: GPLv2
  Text Domain: datascrapper
 */

/**
 * Description of DataScrapper
 *
 * @author Neeraj Mourya <neerajmorya@gmail.com>
 */
class DataScrapper {
    public static $PLUGIN_DIR;
    public static $PLUGIN_DIR_URI;
    public static $TEXT_DOMAIN = "datascrapper";
    
    public function __construct() {
        //Setting ini
        ini_set("default_socket_timeout", 6000);
        //Defining the constants
        self::$PLUGIN_DIR = plugin_dir_path(__FILE__);
        self::$PLUGIN_DIR_URI = plugin_dir_url(__FILE__);


        //including files        
        require_once self::$PLUGIN_DIR . '/inc/scrapper/simplehtmldom_1_8_1/simple_html_dom.php';
        require_once self::$PLUGIN_DIR . '/inc/DSUtility.php';
        require_once self::$PLUGIN_DIR . '/inc/DSOptions.php';
        require_once self::$PLUGIN_DIR . '/inc/DSGastroScrapper.php';
        require_once self::$PLUGIN_DIR . '/inc/DSMoebelScrapper.php';
        require_once self::$PLUGIN_DIR . '/inc/DSActions.php';
                
        //Instantiates the classes
        DSActions::get_instance();
//        DSScrapper::get_instance();
    }
}
$datascrapper = new DataScrapper();