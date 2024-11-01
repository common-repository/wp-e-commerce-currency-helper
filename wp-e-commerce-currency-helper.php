<?php
/*
Plugin Name: WP E-Commerce Currency Conversion Assistant
Plugin URI:  http://haet.at/wp-e-commerce-currency-helper/
Description: convert all price fields in your store to any currency
Version: 1.5
Author: haet webdevelopment
Author URI: http://haet.at
License: GPL
*/

/*  Copyright 2014 haet (email : contact@haet.at) */

define( 'HAET_CURRENCY_PATH', plugin_dir_path(__FILE__) );
define( 'HAET_CURRENCY_URL', plugin_dir_url(__FILE__) );
define( 'HAET_CURRENCY_NAME', basename(__FILE__) );


require HAET_CURRENCY_PATH . 'includes/class-haetcurrency.php';
load_plugin_textdomain('haetcurrency', false, dirname( plugin_basename( __FILE__ ) ) . '/translations' );



if (class_exists("HaetCurrency")) {
    $wp_haetcurrency = new HaetCurrency();
}

//Actions and Filters	
if (isset($wp_haetcurrency)) {
    add_action('admin_menu', 'add_haetcurrency_adminpage');
    register_activation_hook( __FILE__, array(&$wp_haetcurrency, 'init'));
    add_action( 'plugins_loaded', array(&$wp_haetcurrency, 'createTables') );
    register_deactivation_hook( __FILE__, array(&$wp_haetcurrency, 'disable'));
    add_action( 'wp_enqueue_scripts',array(&$wp_haetcurrency, 'scripts'));
    add_action( 'wp_print_styles',array(&$wp_haetcurrency, 'styles'));
    add_action( 'wp_ajax_nopriv_haet-currency-show', array(&$wp_haetcurrency, 'ajaxShow') );
    add_action( 'wp_ajax_haet-currency-show', array(&$wp_haetcurrency, 'ajaxShow') );
    add_action( 'wp_ajax_nopriv_haet-currency-change', array(&$wp_haetcurrency, 'ajaxChangeCurrency') );
    add_action( 'wp_ajax_haet-currency-change', array(&$wp_haetcurrency, 'ajaxChangeCurrency') );
    add_action( 'wp_ajax_nopriv_haet-setcurrency', array(&$wp_haetcurrency, 'ajaxSetCurrency') );
    add_action( 'wp_ajax_haet-setcurrency', array(&$wp_haetcurrency, 'ajaxSetCurrency') );
    
    //price formatting filter for EDD 
    add_filter('edd_download_price',array(&$wp_haetcurrency, 'filterPriceFormatting'),50,2);
    //add_filter('edd_cart_item',array(&$wp_haetcurrency, 'filterEDDCartPriceFormatting'),50,2);
}



function add_haetcurrency_adminpage() {
    global $wp_haetcurrency;
    if (!isset($wp_haetcurrency)) {
            return;
    }
    if (function_exists('add_options_page')) {
        $page = add_options_page(__('Configure Currency Conversion Assistant Pro','haetcurrency'), __('Currency Converter','haetcurrency'), 'manage_options', basename(__FILE__), array(&$wp_haetcurrency, 'printAdminPage'));
        add_action( 'admin_print_styles-' . $page, array(&$wp_haetcurrency, 'styles') );
    }
}
	
	




?>