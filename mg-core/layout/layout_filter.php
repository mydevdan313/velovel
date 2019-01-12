    <?php 
      $lang = MG::get('lang'); 
      ob_start();
    ?>

    <div class="mg-filter-head">  
    <div class="filter-preview"><div class="loader-search"></div><span></span></div>
    <!-- перебор характеристик и в зависимости от типа строится соответсвующий html код -->
    <?php  
      $data['property']['sorter']['option']['price_course|-1'] = lang('filterPrice_courseAsc');
      $data['property']['sorter']['option']['price_course|1'] = lang('filterPrice_courseDesc');
      $data['property']['sorter']['option']['id|1'] = lang('filterId');
      $data['property']['sorter']['option']['count_buy|1'] = lang('filterCount_buy');
      $data['property']['sorter']['option']['recommend|1'] = lang('filterRecommend');
      $data['property']['sorter']['option']['new|1'] = lang('filterNew');
      $data['property']['sorter']['option']['old_price|1'] = lang('filterOld_price');
      $data['property']['sorter']['option']['sort|-1'] = lang('filterSort');
      if (!MG::enabledStorage()) {
        $data['property']['sorter']['option']['count|1'] = lang('filterCountDesc');
        $data['property']['sorter']['option']['count|-1'] = lang('filterCountAsc');
      }
      $data['property']['sorter']['option']['title|-1'] = lang('filterTitleAsc');
      $data['property']['sorter']['option']['title|1'] = lang('filterTitleDesc');
      $data['property']['sorter']['label'] = lang('filterLabel1');
      $data['property']['price_course']['label1'] = lang('filterLabel2');
      $data['property']['price_course']['label2'] = lang('filterTo');
      // MG::loger($data['property']);
    ?>
    <?php foreach ($data['property'] as $name => $prop) {
      switch ($prop['type']) {
        case 'select': {
            if (!URL::isSection("mg-admin") && $name == 'sorter' && !empty($_SESSION['filters'])) {
              $prop['selected'] =  $_SESSION['filters'];
              $prop['value'] = $_SESSION['filters'];
            } ?>
            <div class="wrapper-field"><div class="filter-select"><div class="select"><span class="label-field"><?php echo $prop['label']?></span><select name="<?php echo $name?>" class="last-items-dropdown">
            <?php foreach ($prop['option'] as $value => $text) {
              $selected = ($prop['selected'] === $value."") ? 'selected="selected"' : ''; ?>
              <option value="<?php echo $value?>" <?php echo $selected?>><?php echo $text?></option>
            <?php } ?>
            </select></div>
            <?php if ($name == 'cat_id') {
              $checked = '';
              if ($_POST['insideCat']) {
                $checked = 'checked=checked';
              } ?>
              <div class="checkbox"><?php echo $lang['FILTR_PRICE7']?><input type="checkbox"  name="insideCat" <?php echo $checked?> /></div>
            <?php } ?>
            </div></div>
            <?php break;
          }

        case 'beetwen': {
            if ($prop['special'] == 'date') { ?>
              
             <div class="wrapper-field">
             <ul class="period-date">
               <li>
                  <span class="label-field"><?php echo $prop['label1']?></span> 
                  <input class="from-<?php echo $prop['class']?>" type="text" name="<?php echo $name?>[]" value="<?php echo date('d.m.Y', strtotime($prop['min']))?>">
                </li>
                <li>
                  <span class="label-field"><?php echo $prop['label2']?></span> 
                  <input class="to-<?php echo $prop['class']?>" type="text" name="<?php echo $name?>[]" value="<?php echo date('d.m.Y', strtotime($prop['max']))?>">
                </li>
             </ul>
             </div>
           

            <?php } else { ?>
              <div class="wrapper-field range-field">
                <div class="price-slider-wrapper">
                <ul class="price-slider-list">
                 <li><span class="label-field from"><?php echo $prop['label1']?></span><input type="text" id="minCost" class="price-input start-<?php echo $prop['class']?>  price-input" data-fact-min="<?php echo $prop['factMin']?>" name="<?php echo $name?>[]" value="<?php echo $prop['min']?>" /></li>
                 <li><span class="label-field to"><?php echo $prop['label2']?></span><input type="text" id="maxCost" class="price-input end-<?php echo $prop['class']?>  price-input" data-fact-max="<?php echo $prop['factMax']?>" name="<?php echo $name?>[]" value="<?php echo $prop['max']?>" /><span><?php echo MG::getSetting('currency')?></span></li>
                </ul>
                <div class="clear"></div>
                <div id="price-slider"></div>
              </div>
              </div>
            <?php } ?>

            <?php if (!empty($prop['special'])) { ?>
              <input type="hidden"  name="<?php echo $name?>[]" value="<?php echo $prop['special']?>" />
            <?php }
            break;
          }

        case 'hidden': { ?>
             <input type="hidden" name="<?php echo $name?>" value="<?php echo $prop['value']?>" class="price-input"/>
            <?php break;
          }

        case 'text': {
            if (!empty($prop['special'])) { ?>
              <div class="wrapper-field"><span class="label-field"><?php echo $prop['label']?>:</span> <input type="text" name="<?php echo $name?>[]" value="<?php echo $prop['value']?>" class="price-input"/></div>
              <input type="hidden"  name="<?php echo $name?>[]" value="<?php echo $prop['special']?>" />
            <?php }else{ ?>
              <div class="wrapper-field"><span class="label-field"><?php echo $prop['label']?>:</span> <input type="text" name="<?php echo $name?>" value="<?php echo $prop['value']?>" class="price-input"/></div>
            <?php }
            break;
          }

        default:
          break;
      }
    } ?>
    </div>

    <?php if(MG::getSetting('printSpecFilterBlock') == 'true'){ ?>
    <div class="mg-filter-body">
      <div class="mg-filter-item">
        <span class="mg-filter-title"><?php echo lang('filterSpecS'); ?></span>
        <ul> 
          <li>
            <label style="margin-bottom:8px;">
              <input type="checkbox" name="sale" value="1" class="mg-filter-prop-checkbox" <?php echo $_REQUEST['sale']?'checked':''; ?>>
              <span class="value-name"><?php echo lang('filterSaleS'); ?></span>
              <span class="cbox"> </span>
              <span class="unit"> </span>
            </label>
            <label style="margin-bottom:8px;">
              <input type="checkbox" name="new" value="1" class="mg-filter-prop-checkbox" <?php echo $_REQUEST['new']?'checked':''; ?>>
              <span class="value-name"><?php echo lang('filterNewS'); ?></span>
              <span class="cbox"> </span>
              <span class="unit"> </span>
            </label>
            <label style="margin-bottom:8px;">
              <input type="checkbox" name="recommend" value="1" class="mg-filter-prop-checkbox" <?php echo $_REQUEST['recommend']?'checked':''; ?>>
              <span class="value-name"><?php echo lang('filterRecommendS'); ?></span>
              <span class="cbox"> </span>
              <span class="unit"> </span>
            </label>
          </li>
        </ul>
      </div>
    </div>
    <?php } ?>
 
    <?php if(MG::get('controller')=='controllers_catalog' || $_REQUEST['mguniqueurl'] == 'catalog.php') { ?>
      <div class="mg-filter-body">
      <?php echo $data['propertyFilter']; ?>
      </div>
    <?php } ?>
    <?php if(MG::get('controller')=='controllers_users' || $_REQUEST['mguniqueurl'] == 'users.php') { ?>
      <div class="mg-filter-body">
     
      </div>
    <?php } ?>

    

    <div class="wrapper-field filter-buttons">
  <?php if($data['submit']){ ?>
    <input type="submit" value="<?php echo lang('filter'); ?>" class="filter-btn">
    <span class="refreshFilter" data-url="<?php echo SITE.URL::getClearUri() ?>"><?php echo lang('filterReset'); ?></span>
  <?php }else{ ?>
    <a class="filter-now"><span><?php echo lang('filter'); ?></span></a>
    <a href="javascript:void(0);" class="refreshFilter"><span><?php echo lang('filterReset'); ?></span></a> 
  <?php } ?>
    
    </div>
    
    <?php 
      $arReuestUrl = parse_url($_SERVER['REQUEST_URI']); 
      $html = ob_get_contents();
      ob_end_clean();
    ?>
    
    <form name="filter" class="filter-form" action="<?php echo $arReuestUrl['path']?>" data-print-res="<?php echo MG::getSetting('printFilterResult')?>"><?php echo str_replace(array('[', ']'), array('&#91;', '&#93;'), $html)?></form>