<?php if (preg_match('/MSIE\s(?P<v>\d+)/i', @$_SERVER['HTTP_USER_AGENT'], $B) && $B['v'] == 9) {     
 ?>
 <header class="ie-header">
            <div class="l-container clearfix">
               <div class="l-row">                           
                 <?php layout('topmenu'); ?>   
                 <?php layout('group'); ?>                           
                 <?php layout('language_select'); ?> 
                 <?php layout('currency_select'); ?>                           
                </div>
            </div>
            
            <div class="l-header__middle">
                <div class="l-container">
                    <div class="l-row">
                      
                            <a class="c-logo" href="<?php echo SITE ?>"><?php echo mgLogo(); ?></a>            
                            <div class="header_contacts">
                                <?php layout('contacts'); ?>
                            </div>

                            <div class="header_buttons">
                                    <?php if (MG::getSetting('printCompareButton') == 'true') { ?>
                                        <div class="max-767--hide">
                                            <?php layout('compare'); ?>
                                        </div>
                                    <?php } ?>
                                
                                   
                                <div class="header_auth">
                                    <?php layout('auth'); ?> 
                                </div>
                                <div class="header_cart">
                                    <?php layout('cart'); ?>                                                          
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <div class="l-header__bottom">
                <div class="l-container">
                    <div class="l-row">                       
                        <?php layout('leftmenu'); ?> 
                        <?php layout('search'); ?>                     
                    </div>
                </div>
            </div>
        </header>   
<?php }