jQuery(document).ready(function($){ 
    $(HaetCurrency.selector).mouseenter(function(){showCurrencyTooltip($(this),'','','');});
    $('[id^=edd_price_option_]').parent('label').mouseenter(function(){showCurrencyTooltip($(this),' - ','','');});
    $('li.edd-cart-item').mouseenter(function(){showCurrencyTooltip($(this),'','','span,a');});


    function showCurrencyTooltip($el,seperator_from,seperator_to,remove_elements){

        
        if ( $el.hasClass("currency-bubble-loaded")===false ){
            var direction='left';
            if($el.hasClass('edd_cart_amount'))
                direction='right';
            $el.addClass("currency-bubble-loaded");
            $el.CreateBubblePopup({
                position : 'top',
                align	 : direction,
                innerHtml: HaetCurrency.loading+"...",
                tail:{align:direction,hidden: false},
                themeName: HaetCurrency.theme,
                selectable: true,  
                themePath: HaetCurrency.pluginurl+'jquery-bubble-popup/jquerybubblepopup-themes/'
            }).ShowBubblePopup();
            
            
            var text = $el.text();
            
            if(remove_elements!=''){
                var $item = $el.clone();
                $(remove_elements,$item).remove();
                text = $item.text();
            }
            
            if(seperator_from!='' || seperator_to!=''){
                var pos_from = 0;
                var pos_to = text.length;
                if(seperator_from!='')
                    pos_from = text.lastIndexOf(seperator_from);
                if( pos_from == -1 )
                    pos_from=0;
                if(seperator_to!='')
                    pos_to = text.indexOf(seperator_to,pos_from);
                if( pos_to == -1 )
                    pos_to=text.length;
                text=text.substr(pos_from,pos_to-pos_from);
            }
            jQuery.post(
                HaetCurrency.ajaxurl,
                {
                    action : 'haet-currency-show',
                    money_str: text
                },
                function( response ) {
                   
                        $el.SetBubblePopupInnerHtml(response['html']);
                        var $bubble= $('#'+$el.GetBubblePopupID());
                        //console.log($el.GetBubblePopupID());
                        $bubble
                            //.find('.jquerybubblepopup-innerHtml').html(response['html'])
                            .find('a.haet-change-currency').live('click',function(){  
                            var $link = $(this);
                            $el.FreezeBubblePopup();
                            jQuery.post(
                                HaetCurrency.ajaxurl,
                                {
                                    action : 'haet-currency-change'
                                },
                                function( response ) {
                                    $link.replaceWith(response['html']);  
                                    $bubble.find('select').live('change',function(){
                                        $el.UnfreezeBubblePopup();
                                        jQuery.post(
                                            HaetCurrency.ajaxurl,
                                            {
                                                action : 'haet-setcurrency',
                                                currency: $(this).val()
                                            },
                                            function( response ) {
                                                $(".currency-bubble-loaded").removeClass("currency-bubble-loaded").RemoveBubblePopup();
                                            }
                                        );
                                    });   
                                }
                            );
                            
                            return false;
                        });
                    
                }
            );
        } 

    }
    
    
    

});