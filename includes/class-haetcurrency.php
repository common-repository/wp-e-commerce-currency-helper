<?php
class HaetCurrency {
   
    /**
     * activate the plugin
     */
    function init() {
            $this->getOptions();
            $this->createTables(true);
            $installed_shops = $this->getInstalledShops();
            if ( count($installed_shops)>0 )
                $this->importSettings ($installed_shops[0]['id']);
    }
    
    /**
     * deactivate the plugincr
     * remove table, but keep the settings
     */
    function disable() {
            $this->removeTables();
    }
    /**
     * creates or updates the currency-country table
     * @global object $wpdb
     * @param bool $debug 
     */
    function createTables($rewrite=false){
        global $wpdb;
        $currency_table_name = $wpdb->prefix.'haet_currency_list';
        if ( $rewrite ){
            $sql_result['drop'] = $wpdb->query( "DROP TABLE IF EXISTS `$currency_table_name`");
        }
        if ( !$wpdb->get_var( "SHOW TABLES LIKE '$currency_table_name'" ) ){
            include HAET_CURRENCY_PATH.'/includes/installation/currency_list.php';
        }
    }
    
    /**
     * delete the currency table
     * @global object $wpdb 
     */
    private function removeTables(){
        global $wpdb;
        $currency_table_name = $wpdb->prefix.'haet_currency_list';
        $wpdb->query( "DROP TABLE IF EXISTS `$currency_table_name`");
    }
    
    /**
     *  initialize and get Options
     * @return array $options
     */
    function getOptions() {
	$options = array(
            'bubble_theme' => 'grey',
            'thousands_separator' => ',',
            'decimal_separator' => '.',
            'currencycode' => 'USD',
            'decimal_places' => 2,
            'jquery_selector' => '',
            'shop' => ''
        );
        $haetcurrency_options = get_option('haetcurrency_options');
        if (!empty($haetcurrency_options)) {
                foreach ($haetcurrency_options as $key => $option)
                        $options[$key] = $option;
        }				
        update_option('haetcurrency_options', $options);
        return $options;
    }
    
    /**
     * detect all (known) installed shop extensions
     * @return array $shops
     */
    private function getInstalledShops(){
        
        $shops = array(
                    array(
                        'id' => 'wpsc',
                        'file' => 'wp-e-commerce/wp-shopping-cart.php',
                        'name' => 'WP e-Commerce'
                    ),
                    array(
                        'id' => 'edd',
                        'file' => 'easy-digital-downloads/easy-digital-downloads.php',
                        'name' => 'Easy Digital Downloads'
                    ),
                );
        $installed_shops = array();
        foreach($shops AS $shop){
            if(is_plugin_active( $shop['file'] )){
                $installed_shops[]=$shop;
            }
        }
        return $installed_shops;    
    }
    

    
    /**
     *  imports some settings from a detected shop plugin
     * @param string $shop_id 
     * @return string $importmessage
     */
    private function importSettings($shop_id){
        $options = $this->getOptions();
        $options['shop'] = $shop_id;
        switch ($shop_id){
            case 'edd':
                global $edd_options;
                                                  
                $options['thousands_separator'] = $edd_options['thousands_separator'];
                $options['decimal_separator'] = $edd_options['decimal_separator'];
                $options['currencycode'] = isset($edd_options['currency']) ? $edd_options['currency'] : 'USD';
                $options['jquery_selector'] = 'span.currency_assistant_price,td.edd_cart_item_price,.edd_cart_amount';
                break;
            case 'wpsc':
                global $wpdb;
                $options['thousands_separator'] = get_option('wpsc_thousands_separator');
                $options['decimal_separator'] = get_option('wpsc_decimal_separator');
                $currency_id = get_option('currency_type');
                $options['currencycode'] = $wpdb->get_var( 
                    $wpdb->prepare( 
                            "
                            SELECT code FROM ".WPSC_TABLE_CURRENCY_LIST."
                            WHERE id = %d
                            ",
                            $currency_id 
                    )
                );
                $options['jquery_selector'] = 'span.pricedisplay';
                break;
            default:
                $options['shop'] = '';
                $options['thousands_separator'] = ',';
                $options['decimal_separator'] = '.';
                $options['currencycode'] = 'USD';
                $options['jquery_selector'] = '';
                break;
        }
        update_option('haetcurrency_options', $options);
      
    }
    
    /**
     * tries to convert the bas-currency of this page to each currency from the 
     * currency-list table and blacklists all currencies returning an error
     * @global object $wpdb 
     */
    private function checkBlacklist(){
        global $wpdb; 
        $currencies = $wpdb->get_results( "SELECT currencycode FROM ".$wpdb->prefix."haet_currency_list GROUP BY currencycode ORDER BY currencycode LIMIT 220,30");
        $options=$this->getOptions();
        
        $blacklist = array();
        foreach($currencies AS $currency){
            //$jsonfile = wp_remote_get('http://www.google.com/ig/calculator?hl=en&q=100'.$options['currencycode'].'=?'.$currency->currencycode); 
            $jsonfile = wp_remote_get(
                    'https://exchange.p.mashape.com/exchange/?amt=100&from='.$options['currencycode'].'&to='.$currency->currencycode.'&accuracy=2&format=json'
                    ,array(
                        'headers'=>array('X-Mashape-Authorization'=>'2WiPmtcNU68hlDoPDR0QUrf0qqh0ZypX')        
                    )
                );
            
            $jsonarray = json_decode($jsonfile['body']);
            echo $currency->currencycode;
            if ( $jsonarray->result->{ strtoupper($jsonarray->query->final_currency)}->amount== 0){
                $blacklist[]=$currency->currencycode;
            }
        }
        $wpdb->query( "
                    UPDATE ".$wpdb->prefix."haet_currency_list SET available=1
                ");
        $wpdb->query( "
                    UPDATE ".$wpdb->prefix."haet_currency_list SET available=0 WHERE currencycode IN ( '".implode("','",$blacklist)."' ) 
                ");
    }
    
    /**
     * output the configuration page
     * @global object $wpdb 
     */
    function printAdminPage(){    
        if ( isset ( $_GET['tab'] ) ) 
            $tab=$_GET['tab']; 
        else 
            $tab='settings'; 
//TODO disclaimer page link
        $options = $this->getOptions();

        if( isset( $_GET['checkBlacklist'] )){
            $this->checkBlacklist();
            echo '<div class="updated"><p><strong>';
            _e("Currency list has been updated.", "haetcurrency");
            echo '</strong></p></div>';
        }
        if( isset( $_GET['importSettings'] )){
            $importmessage = $this->importSettings($_GET['importSettings']);
            echo '<div class="updated"><p><strong>';
            _e("Setting have been imported from your shop.", "haetcurrency");
            echo '</strong></p></div>';
            $options = $this->getOptions(); //reload new values
        }
        if (isset($_POST['update_haetcurrencySettings'])) { 
            if ($tab=='settings'){
                            if (isset($_POST['haet_thousands_separator'])) 
                                    $options['thousands_separator'] = $_POST['haet_thousands_separator'];
                            if (isset($_POST['haet_decimal_separator']))         
                                    $options['decimal_separator'] = $_POST['haet_decimal_separator'];#
                            if (isset($_POST['haet_currencycode']))  
                                    $options['currencycode'] = $_POST['haet_currencycode'];
                            if (isset($_POST['haet_jquery_selector']))  
                                    $options['jquery_selector'] = $_POST['haet_jquery_selector'];
            }else if ($tab=='theme'){
                            if (isset($_POST['haetcurrencytheme'])) {
                                    $options['bubble_theme'] = $_POST['haetcurrencytheme'];
                            }
            }
            update_option('haetcurrency_options', $options);


            echo '<div class="updated"><p><strong>';
            _e("Settings Updated.", "haetcurrency");
            echo '</strong></p></div>';	
        } 

        
        $tabs = array( 
                    'settings' => __('Settings','haetcurrency'),
                    'theme' => __('Theme','haetcurrency'),
                    //'currencies' => __('Currencies','haetcurrency'),
                    'more' => __('More','haetcurrency')
            );
        global $wpdb;
        $active_currencies = $wpdb->get_col( "SELECT currency FROM ".$wpdb->prefix."haet_currency_list WHERE available=1 GROUP BY currency ORDER BY currency" );

        wp_enqueue_style('jquerybubblestyle', HAET_CURRENCY_URL.'/css/jquery-bubble-popup-v3.css', false, '3.0', 'all' );
        
        include HAET_CURRENCY_PATH.'views/admin/settings.php';
    
    }
    
    /**
     * enqueue JS files 
     * translate and add ajax url
     */
    function scripts() {	
            $options=$this->getOptions();
            wp_enqueue_script('jquery');	
            wp_enqueue_script('jquerybubble',HAET_CURRENCY_URL.'/js/jquery-bubble-popup-v3.min.js', array('jquery'), '3.0', true);
            wp_enqueue_script('haetcurrency',HAET_CURRENCY_URL.'/js/haetcurrency.js', array('jquery'), '1.0', true);

            wp_localize_script( 
                'haetcurrency', 
                'HaetCurrency', 
                array( 
                    'ajaxurl' => admin_url( 'admin-ajax.php' ) ,
                    'loading' => __('loading currency conversion','haetcurrency'),
                    'theme' =>  $options['bubble_theme'],
                    'pluginurl' => HAET_CURRENCY_URL,
                    'selector' => $options["jquery_selector"]
                ) 
            );

    }
    
    /**
     * add styles
     */
    function styles() {	
            wp_enqueue_style('jquerybubblestyle', HAET_CURRENCY_URL.'/css/jquery-bubble-popup-v3.css', false, '3.0', 'all' );
    }
    
    
    /**
     * show the conversion tooltip 
     * @global object $wpdb
     */
    function ajaxShow(){
        global $wpdb;
        $success=true;
        $html = "";
        $money_str = $_POST['money_str'];
        if ( !$money_str || $money_str=='')
            $success = false;
        else { 
            $orig_money_str=$money_str;
            $options = $this->getOptions();
            $thousands_separator = $options['thousands_separator'];
            $decimal_separator = $options['decimal_separator'];
            $currency = $options['currencycode'];
            $target_currency = $this->getTargetCurrency();
            //$html.=$thousands_separator.$decimal_separator.$currency.$target_currency; 
            if( $currency!=$target_currency){
                $money_str=str_replace($thousands_separator,'',$money_str);
                if ($decimal_separator!='.')
                    $money_str=str_replace($decimal_separator,'.',$money_str);
                $money = preg_replace('/[^0-9\.]/','',$money_str);
                
                $jsonfile = wp_remote_get(
                    'https://exchange.p.mashape.com/exchange/?amt='.$money.'&from='.$currency.'&to='.$target_currency.'&accuracy=2&format=json'
                    ,array(
                        'headers'=>array('X-Mashape-Authorization'=>'2WiPmtcNU68hlDoPDR0QUrf0qqh0ZypX')        
                    )
                );
                
                $jsonarray = json_decode($jsonfile['body']);
                if ($jsonarray->result->{ strtoupper($jsonarray->query->final_currency)}->amount== 0 && $money!=0)
                    $success=false;   
                else {
                    $result_amount=number_format ( $jsonarray->result->{$jsonarray->query->final_currency}->amount, 2, $decimal_separator,  $thousands_separator);
                    $result_currency=$jsonarray->result->{$jsonarray->query->final_currency}->currency_symbol;
                    if(!$result_currency)
                        $result_currency=$jsonarray->result->{$jsonarray->query->final_currency}->currency;
                    if(!$result_currency)                        
                        $result_currency=$target_currency;
                	$html .= $orig_money_str.' &cong; '. $result_amount.' '.$result_currency.'<br>';
                	/*
                	{
                      "status": 200,
                      "query": {
                        "amount": "120",
                        "source_currency": "eur",
                        "final_currency": "usd"
                      },
                      "result": {
                        "USD": {
                          "amount": 162.5,
                          "currency_symbol": "&#36;",
                          "currency": "United States Dollar"
                        }
                      }
                    }*/
                }
            }
            $html.= '<a class="haet-change-currency" href="#">'.__('set conversion currency','haetcurrency').'</a>';
            
        }
        
        $response = json_encode( array( 
                'success' => $success,
                'html' => $html,
            ) 
        );

        header( "Content-Type: application/json" );
        echo $response;
        exit;
    }
      
    /**
     * print the select menu to change the target currency
     * @global object $wpdb 
     */
    function ajaxChangeCurrency(){
        $options = $this->getOptions();
        global $wpdb; 
        $html = '<select class="haet-currency-select">';
        $target_currency = $this->getTargetCurrency();
        
        $currencies = $wpdb->get_results( "SELECT currencycode,currency FROM ".$wpdb->prefix."haet_currency_list WHERE available=1 GROUP BY currencycode,currency ORDER BY currency" );
        foreach($currencies AS $currency){
            $html.= '<option value="'.$currency->currencycode.'" '.(($currency->currencycode==$target_currency)?'selected':'').'>'.$currency->currency.' ( '.$currency->currencycode.' )</option>';
        }
        $html.= '</select>';
        $response = json_encode( array( 
                'html' => $html
            ) 
        );

        header( "Content-Type: application/json" );
        echo $response;
        exit;
    }  
     
    /**
     * save the selected currency in a cookie
     * @global object $wpdb 
     */
    function ajaxSetCurrency(){
        global $wpdb;
        $currency = $_POST['currency'];
        setcookie('haet_target_currency', $currency, time()+3600*24*100);
        $response = json_encode( array( 
                'success' => true
            ) 
        );
        
        header( "Content-Type: application/json" );
        echo $response;
        exit;
    } 
      
    /**
     * get the target currency either by a previously saved cookie or by the users home country
     * @global object $wpdb
     * @return string ISO currency code
     */
    function getTargetCurrency(){
        global $wpdb;
        $target_currency=$_COOKIE['haet_target_currency'];
        if( !$target_currency){
            include("geoip/geoip.inc");

            $gi = geoip_open(HAET_CURRENCY_PATH. "includes/geoip/GeoIP.dat",GEOIP_STANDARD);

            $country = geoip_country_code_by_addr($gi, $_SERVER["REMOTE_ADDR"]);
            if(!$country)
                $country="US";
            
            $currency_table_name = $wpdb->prefix.'haet_currency_list';
            $target_currency = $wpdb->get_var( 
                $wpdb->prepare( 
                        "
                        SELECT currencycode FROM $currency_table_name
                        WHERE countrycode = %s
                        ",
                        $country
                )
            );

            geoip_close($gi);
            setcookie('haet_target_currency', $target_currency, time()+3600*24*100);
        }
        return $target_currency;
    }
       
    function filterPriceFormatting($price,$download_id){
        return '<span class="currency_assistant_price">'.$price.'</span>';
    }   
        
    //doesn't work with options beacause there are two seperators -> solved in jQuery
    function filterEDDCartPriceFormatting($cart_line,$download_id){
        $startpos = strpos($cart_line, 'edd-cart-item-separator');
        $startpos = strpos($cart_line,'</span>',$startpos)+7;
        $endpos = strpos($cart_line,'<span',$startpos);
        return substr($cart_line, 0, $startpos).'<span class="currency_assistant_price">'.substr($cart_line, $startpos,$endpos-$startpos).'</span>'.substr($cart_line, $endpos);

    }
}

?>