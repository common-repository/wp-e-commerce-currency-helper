

<div class=wrap>
    <h2><?php _e('Live Currency Conversion Assistant','haetcurrency'); ?></h2>
    <div id="" class="icon32"><img src="<?php echo HAET_CURRENCY_URL;?>images/icon.png"><br></div>
    <h2 class="nav-tab-wrapper">
    <?php
        foreach( $tabs as $el => $name ){
            $class = ( $el == $tab ) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=".HAET_CURRENCY_NAME."&tab=$el'>$name</a>";
        }
    ?>
    </h2>
    
        



        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<?php 
        switch ( $tab ){
            case 'settings':
?>
            <h3><?php _e('Import settings','haetcurrency'); ?></h3>
            <?php _e('Live Currency Conversion Assistant comes with settings for some popular Wordpress Shop Plugins.','haetcurrency'); ?><br/><br/>
            <?php _e('Following shop plugins were detected on your wordpress installation:','haetcurrency'); ?><br/><br/>
            <?php 
                $shops = $this->getInstalledShops();
                foreach($shops AS $shop){
                    echo '<a class="button" href="/wp-admin/options-general.php?page='.HAET_CURRENCY_NAME.'&importSettings='.$shop['id'].'">'.__('Import settings from','haetcurrency').' '.$shop['name'].'</a>';
                }
            ?>
            <br/>
            <img src="<?php echo HAET_CURRENCY_URL;?>images/arrow_bottom_right.png" style="float:left; margin-left:50px;">
            <table style="">
                <tbody>
                    <tr valign="top">
                        <td><label for="haet_thousands_separator"><?php _e('thousand separator','haetcurrency'); ?></label></td>
                        <td><input type="text" class="" size="1" maxlength="1" name="haet_thousands_separator" id="haet_thousands_separator" value="<?php echo $options["thousands_separator"];?>"></td>
                    </tr>
                    <tr valign="top">
                        <td><label for="haet_decimal_separator"><?php _e('Decimal separator','haetcurrency'); ?></label></td>
                        <td><input type="text" class="" size="1" maxlength="1" name="haet_decimal_separator" id="haet_decimal_separator" value="<?php echo $options["decimal_separator"];?>"></td>
                    </tr>
                    <tr valign="top">
                        <td><label for="haet_currencycode"><?php _e('Shop currency (ISO-Code)','haetcurrency'); ?></label></td>
                        <td><input type="text" class="" size="3" maxlength="3" name="haet_currencycode" id="haet_currencycode" value="<?php echo $options["currencycode"];?>"></td>
                    </tr>
                    <tr valign="top">
                        <td><label for="haet_jquery_selector"><?php _e('jQuery selector','haetcurrency'); ?></label></td>
                        <td><input type="text" class="" name="haet_jquery_selector" id="haet_jquery_selector" value="<?php echo $options["jquery_selector"];?>"></td>
                    </tr>
                </tbody>
            </table>
            <div class="submit">
                <input type="submit" class="button-primary" name="update_haetcurrencySettings" value="<?php _e('Update Settings', 'haetcurrency') ?>" />
            </div>
<?php 
            break;
            case 'theme':
?>
            <h3><?php _e('Bubble Theme','haetcurrency'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <td>
                            
                                <?php  
                                    $themes=array("all-azure","all-black","all-blue","all-green","all-grey","all-orange","all-violet","all-yellow","azure","black","blue","green","grey","orange","violet","yellow");
                                    foreach ($themes as $theme)
                                        echo '
                                                <div class="jquerybubblepopup jquerybubblepopup-'.$theme.'" style="display:inline-block; width:150px; height:60px; position:relative">
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="background-image:url('.HAET_CURRENCY_URL.'jquery-bubble-popup/jquerybubblepopup-themes/'.$theme.'/top-left.png);" class="jquerybubblepopup-top-left"></td> 
                                                                <td style="background-image:url('.HAET_CURRENCY_URL.'jquery-bubble-popup/jquerybubblepopup-themes/'.$theme.'/top-middle.png);" class="jquerybubblepopup-top-middle"></td>
                                                                <td style="background-image:url('.HAET_CURRENCY_URL.'jquery-bubble-popup/jquerybubblepopup-themes/'.$theme.'/top-right.png);" class="jquerybubblepopup-top-right"></td> 
                                                            </tr>
                                                            <tr>
                                                                <td style="background-image:url('.HAET_CURRENCY_URL.'jquery-bubble-popup/jquerybubblepopup-themes/'.$theme.'/middle-left.png);" class="jquerybubblepopup-middle-left"></td>
                                                                <td class="jquerybubblepopup-innerHtml" style="width:100px;">
                                                                    <input type="radio" id="haetcurrencytheme" name="haetcurrencytheme" '.(($options['bubble_theme']==$theme)?'checked':'').' value="'.$theme.'">
                                                                    '.$theme.'
                                                                </td>
                                                                <td style="background-image: url(&quot;'.HAET_CURRENCY_URL.'jquery-bubble-popup/jquerybubblepopup-themes/'.$theme.'/middle-right.png&quot;); vertical-align: top;" class="jquerybubblepopup-middle-right">
                                                                    <img class="jquerybubblepopup-tail" alt="" src="'.HAET_CURRENCY_URL.'jquery-bubble-popup/jquerybubblepopup-themes/'.$theme.'/tail-right.png">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="background-image:url('.HAET_CURRENCY_URL.'jquery-bubble-popup/jquerybubblepopup-themes/'.$theme.'/bottom-left.png);" class="jquerybubblepopup-bottom-left"></td>
                                                                <td style="background-image:url('.HAET_CURRENCY_URL.'jquery-bubble-popup/jquerybubblepopup-themes/'.$theme.'/bottom-middle.png);" class="jquerybubblepopup-bottom-middle"></td>
                                                                <td style="background-image:url('.HAET_CURRENCY_URL.'jquery-bubble-popup/jquerybubblepopup-themes/'.$theme.'/bottom-right.png);" class="jquerybubblepopup-bottom-right"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                ';
                                ?>

                            
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="submit">
                <input type="submit" class="button-primary" name="update_haetcurrencySettings" value="<?php _e('Update Settings', 'haetcurrency') ?>" />
            </div>
<?php 
            break;
            case 'currencies':
?>
            <h3><?php _e('Blacklist currencies','haetcurrency'); ?></h3>
            <?php _e('This plugin makes use of appnema.com Conversion API. This API supports many currencies, but not all. You can check and regenerate the blacklist from time to time.','haetcurrency'); ?><br/><br/>
            <?php _e('Currently active currencies in your system are: ','haetcurrency'); ?><br/>
            <span class="description">
                <?php echo implode(', ',$active_currencies); ?>
            </span>
            <br/><br/><a class="button" href="/wp-admin/options-general.php?page=<?php echo HAET_CURRENCY_NAME; ?>&checkBlacklist=true"><?php _e('check list with Google conversion API','haetcurrency'); ?></a>
<?php 
        break;
        case 'more':
?>
            <p>
                Get other Wordpress plugins by <a href="http://haet.at/blog/" target="_blank">haet.webdevelopment</a> 
            </p>
            <br/>
            <p>
                This plugin uses the appnema.com Conversion API <a target="_blank" href="http://www.appnema.com/">appnema.com</a><br> 
                currency icons by <a target="_blank" href="http://pinvoke.com">pinvoke</a><br> 
                jquery bubble popup by <a href="http://www.maxvergelli.com/jquery-bubble-popup/" target="_blank">Max Vergelli</a><br>
                This product includes GeoLite data created by MaxMind, available from <a href="http://maxmind.com/" target="_blank">http://maxmind.com</a>
            </p>
<?php    
           break;
       }//switch      
?>

        </form>
    
</div>
<p><a target="_blank" href="http://haet.at/wp-e-commerce-currency-helper/">go to the plugin website</a></p>


