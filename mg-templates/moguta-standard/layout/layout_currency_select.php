<?php if(MG::getSetting('printCurrencySelector') == 'true'){ 
	$currencyActive = MG::getSetting('currencyActive');
	$currencyShopIso = MG::get('dbCurrency');?>
    <span class="currency-select">
        <svg class="icon icon--currency"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon--currency"></use></svg>
        <select name="userCustomCurrency">
            <?php foreach (MG::getSetting('currencyShort') as $k => $v){ 
              if(!in_array($k, $currencyActive) && $k != $currencyShopIso){continue;}?>
              <option value="<?php echo $k ?>" <?php if($k == $_SESSION['userCurrency']){echo 'selected';} ?>> <?php echo $v ?> </option>
            <?php } ?>
        </select>
    </span>
<?php } ?>