<!DOCTYPE html>
<html class="mg-admin-html<?php if(!USER::isAuth() && (USER::access('admin_zone') == 0)): ?> auth-page<?php endif;?>">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" >
<!-- <link href="<?php echo SITE?>/mg-admin/design/css/reset.css" rel="stylesheet" type="text/css"> -->
<link href="<?php echo SITE?>/mg-admin/design/css/tipTip.css" rel="stylesheet" type="text/css">
<link href="<?php echo SITE?>/mg-admin/design/css/datepicker.css" rel="stylesheet" type="text/css">
<link href="<?php echo SITE?>/mg-admin/design/css/toggles.css" rel="stylesheet" type="text/css">
<!--  -->
<!-- <link href="<?php echo SITE?>/mg-admin/design/css/style-old.css" rel="stylesheet" type="text/css"> -->
<!--  -->
<link href="<?php echo SITE?>/mg-admin/design/css/vendors.min.css" rel="stylesheet">
<link href="<?php echo SITE?>/mg-admin/design/css/style.css?<?php echo filemtime(ADMIN_DIR.'/design/css/style.css') ?>" rel="stylesheet" type="text/css">

<?php
  if(unserialize(stripslashes(getOption('interface')))) {
    include_once ADMIN_DIR.'/design/css/user.css.php';
  } 
?>

<link rel="stylesheet" href="<?php echo SITE?>/mg-core/script/codemirror/lib/codemirror.css">
<link type="text/css" href="<?php echo SITE?>/mg-core/script/codemirror/addon/search/matchesonscrollbar.css" rel="stylesheet"/> 
<link type="text/css" href="<?php echo SITE?>/mg-core/script/codemirror/addon/dialog/dialog.css" rel="stylesheet"/>
<link type="text/css" href="<?php echo SITE?>/mg-core/script/codemirror/addon/scroll/simplescrollbars.css" rel="stylesheet"/>

<!--[if lte IE 9]>
    <link href="<?php echo SITE?>/mg-admin/design/css/ie.css" rel="stylesheet" type="text/css">
    <script src="<?php echo SITE?>/mg-core/script/css3-mediaqueries.js"></script>
<![endif]-->
<title>Панель управления | Moguta.CMS</title>

<?php 

if(USER::isAuth() && (USER::access('admin_zone') == 1)): ?>

 <?php MG::titlePage($lang['ADMIN_BAR']);?>

    <script>var phoneMask =  "<?php echo MG::getSetting('phoneMask');?>" </script>
    <script>var SITE = "<?php echo SITE; ?>";</script>
    <!-- <script src="<?php echo SITE?>/mg-core/script/jquery-1.10.2.min.js"></script> -->
    <script src="<?php echo SITE?>/mg-core/script/jquery-3.2.1.min.js"></script>
    <!-- <script src="<?php echo SITE?>/mg-core/script/jquery-migrate-3.0.0.js"></script> -->
    <!-- <script src="<?php echo SITE?>/mg-core/script/jquery-ui-1.10.3.custom.min.js"></script> -->
    <script src="<?php echo SITE?>/mg-core/script/jquery-ui.min.js"></script>

    <script src="<?php echo SITE?>/mg-core/script/vendors.min.js"></script>
    <script src="<?php echo SITE?>/mg-core/script/frontend.min.js"></script>

    <!-- <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script> -->
    <script src="<?php echo SITE?>/mg-core/script/jquery-ui.min.js"></script>
    <script src="<?php echo SITE?>/mg-core/script/admin/admin.js?protocol=<?php echo PROTOCOL; ?>&amp;mgBaseDir=<?php echo SITE; ?>&amp;currency=<?php echo MG::getSetting('currency'); ?>&amp;lang=<?php echo MG::getSetting('languageLocale');?>&amp;t=<?php echo filemtime(SITE_DIR.'/mg-core/script/admin/admin.js') ?>"></script>
    
</head>
 <?php
   $oldIe = false;
   if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')||strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')||strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')){
     $oldIe = true;
   };

  if ($data['themeBackground'] == 'customBackground') {
    $style = "zoom: 1; background-image: url('".SITE."/uploads/customAdmin/".MG::getOption('customBackground')."');";
  }
  else{
    $style = "zoom: 1; background-image: url('".SITE."/mg-admin/design/images/bg_textures/".$data['themeBackground'].".png');";
  }

  if (MG::getOption('bgfullscreen') == 'true') {
    $style .= " background-position-x: center; 
    background-position-y: center; 
    background-repeat-x: no-repeat; 
    background-repeat-y: no-repeat; 
    background-attachment: fixed; 
    -moz-background-size: cover; 
    -webkit-background-size: cover; 
    -o-background-size: cover; 
    background-size: cover;"; 
  }

 ?>

<body class="mg-admin-body <?php if($oldIe): ?>old-ie<?php endif;?>" style="<?php echo $style; ?>">
    <!-- для обновления базы -->
    <input style="display:none;" id="updateDb" value="<?php echo MG::getSetting('DbVersion')==MG::getSetting('maxDbVersion')?'false':'true'; ?>">
    <?php 
    if($oldIe): ?>
        <div class="old-browser">
            <h1>ВНИМАНИЕ! Вы используете устаревший браузер Internet Explorer</h1>
            <p>Панель управления <b>MOGUTA.CMS</b> построена на передовых, современных технологиях и не поддерживает устаревшие браузеры Internet Explorer!.

                Настоятельно Вам рекомендуем выбрать и установить любой из современных браузеров. Это бесплатно и займет всего несколько минут.</p>
            <table class="brows">
                <tbody>
                    <tr>
                      <td width='120'></td>
                      <td><a href="http://www.google.com/chrome"><img src="<?php echo SITE?>/mg-admin/design/images/browsers/gc.jpg" alt="Google Chrome"></a></td>
                        <td><a href="http://www.mozilla.com/firefox/"><img src="<?php echo SITE?>/mg-admin/design/images/browsers/mf.jpg" alt="Mozilla Firefox"></a></td>
                        <td><a href="http://www.opera.com/download/"><img src="<?php echo SITE?>/mg-admin/design/images/browsers/op.jpg" alt="Opera Browser"></a></td>
                        <td><a href="http://www.apple.com/safari/download/"><img src="<?php echo SITE?>/mg-admin/design/images/browsers/as.jpg" alt="Apple Safari"></a></td>
                    </tr>
                    <tr class="brows_name">
                        <td></td>
                        <td><a href="http://www.google.com/chrome">Google Chrome</a></td>
                        <td><a href="http://www.opera.com/download/">Opera Browser</a></td>
                        <td><a href="http://www.mozilla.com/firefox/">Mozilla Firefox</a></td>                    
                        <td><a href="http://www.apple.com/safari/download/">Apple Safari</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </body>
    <?php 
    exit();
    endif;?>
    
    <div class="wrapper no-print">
      
        <!--
        <div class="notice-block top-position" style="height:30px;">     
            <div  class="message_information inform" style="background:#D8D8D8; margin: 0;border-bottom: 2px solid rgb(165, 161, 161);"> Желаете установить Moguta.CMS на свой хостинг? <b><a style="color:#28BB1D" href="https://moguta.ru">Скачать бесплатно!</a></b></div>
        </div>-->
        <?php
          if(MG::getSetting('notifInfo') != '' && !$_COOKIE['timeinfo_closed']) { 
            echo MG::getSetting('notifInfo');
          } else {
            if($newVersion){ ?> 
              <div id ="newVersion" class="message_information inform">
                  <?php echo($lang['NEW_VER'].' - '.$newVersion);?>
              </div>
            <?php }
          }

          if($fakeKey){ ?>
              <div style="background-color:#fffce5;text-align:center;padding:5px;" onclick="javascript:void(0);" class="message_information inform">
                <?php echo $fakeKey;?>          
              </div>
      		<?php }

          if (!$fakeKey && MG::getSetting('mpError') && $_COOKIE['section'] == 'marketplace') {
            $fakeKey = MG::getSetting('mpError');
          }
          ?>


        <?php 
          $adminLogo = MG::getOption('customAdminLogo');
          if (is_file(URL::getDocumentRoot().'uploads'.DS.'customAdmin'.DS.$adminLogo)) {

            $adminLogo = SITE.'/uploads/customAdmin/'.$adminLogo;
            $styleLogo = '';
          }
          else{

            $adminLogo = SITE.'/mg-admin/design/images/logo.svg';
            $styleLogo = 'style="height: 35px; wight:205px;"';
	
          }
        ?>

        <header class="header">
          <div class="header-top info-panel">
            <div class="row">
              <div class="large-12 columns">
                <div class="header-left fl-left clearfix"><a class="logo" target="_blank" href="<?php echo SITE?>/mg-admin/"><img <?php echo $styleLogo ?> src="<?php echo $adminLogo; ?>"><span class="success badge mg-version"><?php echo VER ?></span></a>
                  <ul class="buttons-list clearfix">
                    <?php echo $data['informerPanel']; ?>
                  </ul>
                </div>

                <div class="header-right fl-right clearfix">
                  <?php 
                  if (32 === strlen(MG::getSetting('licenceKey'))) {
                  ?>
                  <a href="javascript:void(0);" id="marketplace2" title="Каталог плагинов и шаблонов" class="tip-bottom go-market fl-left">
                      <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 201.387 201.387" style="enable-background:new 0 0 201.387 201.387;" xml:space="preserve" width="20px" height="20px">
                          <g>
                            <g>
                              <path d="M129.413,24.885C127.389,10.699,115.041,0,100.692,0C91.464,0,82.7,4.453,77.251,11.916    c-1.113,1.522-0.78,3.657,0.742,4.77c1.517,1.109,3.657,0.78,4.768-0.744c4.171-5.707,10.873-9.115,17.93-9.115    c10.974,0,20.415,8.178,21.963,19.021c0.244,1.703,1.705,2.932,3.376,2.932c0.159,0,0.323-0.012,0.486-0.034    C128.382,28.479,129.679,26.75,129.413,24.885z" fill="#FFFFFF"/>
                            </g>
                          </g>
                          <g>
                            <g>
                              <path d="M178.712,63.096l-10.24-17.067c-0.616-1.029-1.727-1.657-2.927-1.657h-9.813c-1.884,0-3.413,1.529-3.413,3.413    s1.529,3.413,3.413,3.413h7.881l6.144,10.24H31.626l6.144-10.24h3.615c1.884,0,3.413-1.529,3.413-3.413s-1.529-3.413-3.413-3.413    h-5.547c-1.2,0-2.311,0.628-2.927,1.657l-10.24,17.067c-0.633,1.056-0.648,2.369-0.043,3.439s1.739,1.732,2.97,1.732h150.187    c1.231,0,2.364-0.662,2.97-1.732S179.345,64.15,178.712,63.096z" fill="#FFFFFF"/>
                            </g>
                          </g>
                          <g>
                            <g>
                              <path d="M161.698,31.623c-0.478-0.771-1.241-1.318-2.123-1.524l-46.531-10.883c-0.881-0.207-1.809-0.053-2.579,0.423    c-0.768,0.478-1.316,1.241-1.522,2.123l-3.509,15c-0.43,1.835,0.71,3.671,2.546,4.099c1.835,0.43,3.673-0.71,4.101-2.546    l2.732-11.675l39.883,9.329l-6.267,26.795c-0.43,1.835,0.71,3.671,2.546,4.099c0.263,0.061,0.524,0.09,0.782,0.09    c1.55,0,2.953-1.062,3.318-2.635l7.045-30.118C162.328,33.319,162.176,32.391,161.698,31.623z" fill="#FFFFFF"/>
                            </g>
                          </g>
                          <g>
                            <g>
                              <path d="M102.497,39.692l-3.11-26.305c-0.106-0.899-0.565-1.72-1.277-2.28c-0.712-0.56-1.611-0.816-2.514-0.71l-57.09,6.748    c-1.871,0.222-3.209,1.918-2.988,3.791l5.185,43.873c0.206,1.737,1.679,3.014,3.386,3.014c0.133,0,0.27-0.009,0.406-0.024    c1.87-0.222,3.208-1.918,2.988-3.791l-4.785-40.486l50.311-5.946l2.708,22.915c0.222,1.872,1.91,3.202,3.791,2.99    C101.379,43.261,102.717,41.564,102.497,39.692z" fill="#FFFFFF"/>
                            </g>
                          </g>
                          <g>
                            <g>
                              <path d="M129.492,63.556l-6.775-28.174c-0.212-0.879-0.765-1.64-1.536-2.113c-0.771-0.469-1.696-0.616-2.581-0.406L63.613,46.087    c-1.833,0.44-2.961,2.284-2.521,4.117l3.386,14.082c0.44,1.835,2.284,2.964,4.116,2.521c1.833-0.44,2.961-2.284,2.521-4.117    l-2.589-10.764l48.35-11.626l5.977,24.854c0.375,1.565,1.775,2.615,3.316,2.615c0.265,0,0.533-0.031,0.802-0.096    C128.804,67.232,129.932,65.389,129.492,63.556z" fill="#FFFFFF"/>
                            </g>
                          </g>
                          <g>
                            <g>
                              <path d="M179.197,64.679c-0.094-1.814-1.592-3.238-3.41-3.238H25.6c-1.818,0-3.316,1.423-3.41,3.238l-6.827,133.12    c-0.048,0.934,0.29,1.848,0.934,2.526c0.645,0.677,1.539,1.062,2.475,1.062h163.84c0.935,0,1.83-0.384,2.478-1.062    c0.643-0.678,0.981-1.591,0.934-2.526L179.197,64.679z M22.364,194.56l6.477-126.293h143.701l6.477,126.293H22.364z" fill="#FFFFFF"/>
                            </g>
                          </g>
                          <g>
                            <g>
                              <path d="M126.292,75.093c-5.647,0-10.24,4.593-10.24,10.24c0,5.647,4.593,10.24,10.24,10.24c5.647,0,10.24-4.593,10.24-10.24    C136.532,79.686,131.939,75.093,126.292,75.093z M126.292,88.747c-1.883,0-3.413-1.531-3.413-3.413s1.531-3.413,3.413-3.413    c1.882,0,3.413,1.531,3.413,3.413S128.174,88.747,126.292,88.747z" fill="#FFFFFF"/>
                            </g>
                          </g>
                          <g>
                            <g>
                              <path d="M75.092,75.093c-5.647,0-10.24,4.593-10.24,10.24c0,5.647,4.593,10.24,10.24,10.24c5.647,0,10.24-4.593,10.24-10.24    C85.332,79.686,80.739,75.093,75.092,75.093z M75.092,88.747c-1.882,0-3.413-1.531-3.413-3.413s1.531-3.413,3.413-3.413    s3.413,1.531,3.413,3.413S76.974,88.747,75.092,88.747z" fill="#FFFFFF"/>
                            </g>
                          </g>
                          <g>
                            <g>
                              <path d="M126.292,85.333h-0.263c-1.884,0-3.413,1.529-3.413,3.413c0,0.466,0.092,0.911,0.263,1.316v17.457    c0,12.233-9.953,22.187-22.187,22.187s-22.187-9.953-22.187-22.187V88.747c0-1.884-1.529-3.413-3.413-3.413    s-3.413,1.529-3.413,3.413v18.773c0,15.998,13.015,29.013,29.013,29.013s29.013-13.015,29.013-29.013V88.747    C129.705,86.863,128.176,85.333,126.292,85.333z" fill="#FFFFFF"/>
                            </g>
                          </g>
                      </svg>
                      <span>Маркет</span>
                  </a>
                  <?php
                  }
                  ?>
                  <ul class="buttons-list clearfix">
                      <li>
                        <a class="tip-bottom not-use back-public" href="<?php echo SITE?>/" title="<?php echo($lang['BACK_TO_SITE']);?>">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 444.422 444.422 "  width="20px" height="20px">
                            <g fill="#fff">
                              <path d="M307.89 407.148c.453-.237.928-.453 1.381-.669-.173-.259-.345-.475-.496-.733-.259.41-.604.971-.885 1.402zM219.493 400.354c-35.786 0-69.134-10.268-97.586-27.762 25.605.259-19.047-38.957 11.713-44.975 32.27-6.342 45.859-43.249 35.527-42.689-10.311.539-29.94-5.328-43.746-22.671-13.827-17.472-25.928-13.482-40.186-10.742-12.468 2.459-27.826 38.525-31.644 47.995-9.707-18.594-16.286-39.022-19.263-60.614 15.833-8.283 41.675-22.218 51.015-29.919 13.762-11.411-4.595-16.027-6.86-22.908-2.243-6.881 0-22.908 0-38.827 0-16.07 32.054-16.07 43.422-32.054 8.175-11.411-6.795-36.584-16.135-50.217 31.558-24.289 70.946-38.892 113.743-38.892 11.346 0 22.412 1.186 33.219 3.128 3.171 19.716 9.772 46.83 22.52 53.905 20.643 11.433 45.622 36.606 54.854 41.135 9.189 4.573 20.557 43.53 18.314 59.535-1.618 11.303-15.639 23.749-21.377 35.721l95.343 59.406c6.73-20.708 10.44-42.732 10.44-65.661C432.806 95.645 337.075 0 219.515 0 101.889 0 6.223 95.645 6.223 213.249c0 117.625 95.666 213.313 213.292 213.313 4.465 0 8.822-.41 13.223-.669l-4.983-25.971c-2.761.13-5.479.432-8.262.432zm184.84-159.495c-4.034-16.006-11.842-31.45-28.452-43.379-11.368-8.024-20.643-43.444-13.741-54.919 4.12-6.838 14.841-12.856 22.736-16.609 13.827 26.079 21.722 55.76 21.722 87.297 0 9.405-.928 18.572-2.265 27.61z"/>
                              <path d="M349.133 334.455l89.066-17.753-223.128-139.023-5.263 3.667 49.677 258.094 48.491-76.727L355 431.222c8.93 12.899 25.324 17.062 36.692 9.275 11.303-7.809 13.331-24.634 4.465-37.555l-47.024-68.487z"/>
                            </g>
                          </svg>


                            <span>На сайт</span>
                        </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <?php
            $plugins = '';
            foreach ($pluginsList as $item) {
              if(PM::isHookInReg($item['folderName'])&& $item['Active']){ 
                $plugins .= '<li><a href="javascript:void(0)" class="'.$item['folderName'].'">'.$item['PluginName'].'</a></li>';
              } 
            }
          ?>
          <div class="header-nav">
            <div class="row">
              <div class="large-12 columns">
                <div class="top-menu clearfix">
                  <div class="menu-toggle"><span class="toggle-wrapper"><span class="toggle"></span></span><span class="text">Меню</span></div>
                  <ul class="nav-list main-list">
                    <?php if(USER::access('product') > 0) {?>  <li><a id="catalog" href="javascript:void(0);" title="<?php echo($lang['T_TIP_PROD']);?>"><i class="fa fa-shopping-cart" aria-hidden="true"></i> <?php echo($lang['PRODUCTS']);?></a></li><?php }?>
                    <?php if(USER::access('category') > 0) {?> <li><a id="category" href="javascript:void(0);" title="<?php echo($lang['T_TIP_CAT']);?>"><i class="fa fa-list-ol" aria-hidden="true"></i> <?php echo($lang['CATEGORIES']);?></a></li><?php }?>
                    <?php if(USER::access('page') > 0) {?> <li><a id="page" href="javascript:void(0);" title="<?php echo($lang['T_TIP_PAGE']);?>"><i class="fa fa-file-text-o" aria-hidden="true"></i> <?php echo($lang['PAGES']);?></a></li><?php }?>
                    <?php if(USER::access('order') > 0) {?> <li><a id="orders" href="javascript:void(0);" title="<?php echo($lang['T_TIP_ORDR']);?>"><i class="fa fa-shopping-basket" aria-hidden="true"></i> <?php echo($lang['ORDERS']);?></a>
                    </li><?php }?>
                    <?php if(USER::access('user') > 0) {?><li><a id="users" href="javascript:void(0);" title="<?php echo($lang['T_TIP_USER']);?>"><i class="fa fa-users" aria-hidden="true"></i> <?php echo($lang['USERS']);?></a></li><?php }?>
                    <?php if(USER::access('plugin') > 0) { ?><li class="<?php if($plugins != '') echo 'has-menu'; ?> plugins-list-menu"><a id="plugins" href="javascript:void(0);" title="<?php echo($lang['T_TIP_PLUG']);?>"><i class="fa fa-puzzle-piece" aria-hidden="true"></i> <?php echo($lang['PLUGINS']);?></a>
                      <?php 
                        if(USER::access('plugin') > 1) { 
                          if($plugins != '') {
                            echo '<ul class="sub-list plugins-dropdown-menu">';
                            echo $plugins;
                            echo '</ul>';
                          }
                        }
                      ?>
                    </li><?php } ?>
                    <?php if(USER::access('plugin') > 1) {?><li style="display: none;"><a id="marketplace" href="javascript:void(0);"></a></li><?php }?>
                    <?php if(USER::access('setting') > 0) {?> <li><a id="settings" href="javascript:void(0);" title="<?php echo($lang['T_TIP_SETT']);?>" class="tool-tip-bottom"><i class="fa fa-cogs" aria-hidden="true"></i> <?php echo($lang['SETTINGS']);?></a><span class="double-border"></span></li> <?php }?>
                    <?php if(USER::access('setting') > 0) {?> <li style="display: none;"><a style="display: none;" id="integrations" href="javascript:void(0);"></a></li> <?php }?>
                    <?php if(USER::access('order') > 0) {?> <li  style="display: none;"><a id="statistic" href="javascript:void(0);" title="<?php echo($lang['T_TIP_SETT']);?>" class="tool-tip-bottom"></a><span class="double-border"></span></li> <?php }?>
                  </ul>
                  <ul class="nav-list exit">
                    <li><a href="javascript:void(0);" title="<?php echo($lang['QUIT']);?>" class="logout-button"><i class="fa fa-sign-out" aria-hidden="true"></i> Выйти</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </header>

        <div class="notice-block row" style="display:block;">
            
            <div class="mailLoader fl-left" style="margin-right:10px;"></div>
            
            <?php if($fakeKey){ ?>
                <div id ="fakeKey" class="message_information inform button alert fl-left">
                    <?php echo($fakeKey);?>
                </div>
            <?php }?>
        </div>

        <div id="thisHostName" style="display:none"><?php echo SITE; ?></div>
        <div id="currency" style="display:none"><?php echo MG::getSetting('currency'); ?></div>
        <div id="color-theme" style="display:none"><?php echo $data['themeColor']; ?></div>
        <div id="bg-theme" style="display:none"><?php echo $data['themeBackground']; ?></div>
        <div id="staticMenu" style="display:none"><?php echo $data['staticMenu']; ?></div>
        <div id="protocol" style="display:none"><?php echo PROTOCOL; ?></div>
        <div id="currency-iso" style="display:none"><?php echo MG::getSetting('currencyShopIso'); ?></div>
        <div id="max-count-cart" style="display:none"><?php echo MAX_COUNT_CART; ?></div>
        <div id="enabledStorage" style="display:none"><?php echo MG::enabledStorage(); ?></div>         
        
        <div class="admin-center">
            <?php MG::printNotification(); ?>
            <?php
            ?>

            <div class="data">
                <!-- Контент раздела -->
            </div>
        </div>
        <div class="admin-h-height"></div>
    </div>

    <div class="block-print">
       <!-- В этот блок будет вставляться контент для печати -->
    </div>

    <?php 
      $versionText = '';
      $versionText = 'Витрина '.VER;
    ?>

    <footer class="footer no-print">
      <div class="row">
        <div class="small-7 columns">
          <div class="copy">&copy; Все права защищены <a href="https://moguta.ru?mg=admftr" target="_blank">Moguta.CMS™</a> <?php echo $versionText;?></div>
        </div>
        <div class="small-5 columns text-right"><a href="http://wiki.moguta.ru/panel-upravleniya?mg=admdoc" target="_blank"><i class="fa fa-info-circle" aria-hidden="true"></i> Документация</a></div>
      </div>
    </footer>
    <div class="updateDbLoaderPlace"></div>
    <div class="updateDbLoader">Идет фоновое обновление базы</div>

    </body>

    <?php else:?>

    <?php 
        if ($data['themeBackground'] == 'customBackground') {
          $style = "zoom: 1; background-image: url('".SITE."/uploads/customAdmin/".MG::getOption('customBackground')."');";
        }
        else{
          $style = "zoom: 1; background-image: url('".SITE."/mg-admin/design/images/bg_textures/".$data['themeBackground'].".png');";
        }

        if (MG::getOption('bgfullscreen') == 'true') {
          $style .= " background-position-x: center; 
          background-position-y: center; 
          background-repeat-x: no-repeat; 
          background-repeat-y: no-repeat; 
          background-attachment: fixed; 
          -moz-background-size: cover; 
          -webkit-background-size: cover; 
          -o-background-size: cover; 
          background-size: cover;"; 
        }

        $adminLogo = MG::getOption('customAdminLogo');
        if (is_file(URL::getDocumentRoot().'uploads'.DS.'customAdmin'.DS.$adminLogo)) {

          $adminLogo = SITE.'/uploads/customAdmin/'.$adminLogo;
        }
        else{

          $adminLogo = SITE.'/mg-admin/design/images/logo-black.svg';
        }
        
    ?>  
    </head>
    <body style="<?php echo $style; ?>">
        <div class="mg-enter">
            <?php if (MG::getSetting('trialVersionStart')):?>
        <div class="mg-error-public">
            <?php echo MG::getSetting('trialVersion').' Если Вы администратор сайта и у вас возникли вопросы обращайтесь на info@moguta.ru'?>
        </div>
        <?php endif;?>
            <div class="enter-header">
                <div class="enter-logo"><img src="<?php echo $adminLogo; ?>"></div>
            </div>
             <?php echo!empty($data['msgError'])?$data['msgError']:'' ?>
            <div class="enter-body">
                <h2>Вход в панель управления</h2>
                <div class="enter-form">                   
                    <form action="<?php echo SITE?>/enter" method="POST" class="login">
                        <ul class="login-list">
                            <li><input type="text" placeholder="Email" name="email" value="" class="login-input"></li>
                            <li><input type="password" placeholder="Пароль" name="pass" value="" class="pass-input"></li>
                        </ul>

                        <input type="hidden" name="location" value="/mg-admin" />
                        <?php echo !empty($data['checkCapcha']) ? $data['checkCapcha'] : '' ?>
                        <button type="submit" class="enter-button">Войти</button>
                    </form>
                </div>
                <div class="link-holder">
                    <a href="<?php echo SITE ?>/forgotpass" class="forgot-link">Забыли пароль?</a>
                    <a href="<?php echo SITE ?>" class="back_to_site">Вернуться на сайт</a>
                </div>
            </div>
        </div>
        <footer class="footer fixed no-print">
          <div class="row">
            <div class="small-7 columns">
              <div class="copy">&copy; Все права защищены Moguta.CMS™ <a href="https://moguta.ru?mg=admftr" target="_blank">moguta.ru</a></div>
            </div>
            <div class="small-5 columns text-right"><a href="http://wiki.moguta.ru/panel-upravleniya?mg=admdoc" target="_blank"><i class="fa fa-info-circle" aria-hidden="true"></i> Документация</a></div>
          </div>
        </footer>    
    </body>
<?php endif;?>
</html>
<!-- VER <?php echo VER;?> free -->
