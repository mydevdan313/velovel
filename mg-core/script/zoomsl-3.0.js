(function($) {
  $.fn.magnify = function(oOptions) {

    var oSettings = $.extend({     
        debug: false,
        speed: 100,
        lensLeft: 275,
        lensTop: 0,    
        lensWidth: 300,
        lensHeight: 300,
        onload: function(){}
      }, oOptions),
      $anchor,
      $container,
      $image,
      $lens,
      $traker,
      nMagnifiedWidth = 0,
      nMagnifiedHeight = 0,
      
      init = function(el) {
   
        $image = $(el);
        $image.css('cursor','crosshair');
        $anchor = $image.parents('a');        
        zoom($image.attr('data-magnify-src') || oSettings.src || $anchor.attr('href') || '');
      },
              
      zoom = function(sImgSrc, bAnchor) {       
        if (!sImgSrc) return;
        // Высчитываем разрешение большой картинки при увеличении
        var elImage = new Image();
     
        $(elImage).on({
          load: function() {            
            $image.css('display', 'block');         
            // Оборачиваем изображение контейнером для дальнейшей работы 
            if (!$image.parent('.magnify').length) {
              $image.wrap('<div class="magnify"></div>');        
            }
            
            $container = $image.parent('.magnify');
            // Создаем окно для вывода увеличенного изображения
            if ($image.prev('.magnify-lens').length) {
              $container.children('.magnify-lens').css('background-image', 'url(' + sImgSrc + ')');              
            } else {
              $image.before('<div class="magnify-lens loading" style="background:url(' + sImgSrc + ') no-repeat 0 0 white"></div>'); 
            }
            $lens = $container.children('.magnify-lens');  
            // стили для окна увеличения                  
            $lens.css({
                "position": "absolute",
                "width":  oSettings.lensWidth + "px",
                "height": oSettings.lensHeight + "px",
                "cursor": "none",
                "display": "none",
                "z-index": "100",
                "border": "#cccccc solid 1px",               
              });
          
            nMagnifiedWidth = elImage.width; // ширина большого изображения
            nMagnifiedHeight = elImage.height; // высота большого изображения
                       
            elImage = null;      
            // отложенный вызов функции, если задан
            oSettings.onload();
            $container.find('img').css('position','absolute');
            // Обработка движения мыши в контейнере
            $container.find('img').mousemove(function(e) {          
              // x/y кординаты курсора мыши            
              var oMagnifyOffset = $container.offset(),
			  oImgOffset = $(this).offset(),			
		
              // координаты относительно данного контейнера
               nX = e.pageX - oMagnifyOffset.left - (oImgOffset.left-oMagnifyOffset.left) ,
               nY = e.pageY - oMagnifyOffset.top - (oImgOffset.top-oMagnifyOffset.top) ; 
               
              // отображаем увеличение если курсор в области изображения
              if (nX<$container.width() && nY<$container.height() && nX>0 && nY>0) {
                $lens.fadeIn(oSettings.speed);
              } else {
                $lens.fadeOut(oSettings.speed);
              }
                  
      
              if ($lens.is(':visible')) {
                // если большое изображение подгружено и известно его разрешение      
                if (nMagnifiedWidth && nMagnifiedHeight) {
                    // устанавливаем границы для сдвига бекграунда большого изображения
                    var boundX = Math.round(nMagnifiedWidth - $lens.width()/1)*-1,
                        boundY = Math.round(nMagnifiedHeight - $lens.height()/1)*-1;
                    // Вычисляем текущее положение бекграунда в соответствии с положением курсора на изображении
                    var nRatioX = Math.round(nX/$image.width()*nMagnifiedWidth - $lens.width()/2)*-1,
                        nRatioY = Math.round(nY/$image.height()*nMagnifiedHeight - $lens.height()/2)*-1;
                    // не выходим за границу    
                    if(nRatioY>0){nRatioY=0}
                    if(nRatioY<boundY){nRatioY=boundY}
                    if(nRatioX>0){nRatioX=0}
                    if(nRatioX<boundX){nRatioX=boundX}

                    var sBgPos = nRatioX + 'px ' + nRatioY + 'px';                  
                }
                // позиционируем бекграунд окна
                $lens.css({
                  top:  oSettings.lensTop,
                  left: oSettings.lensLeft,
                  backgroundPosition: sBgPos || ''
                });
                            
              }
            }).mouseenter(function(e){
              // при  заходе курсора в область изображения 
              // пересчитываем данные об этом изображении
              // т.к. изображение могло быть подменено другими скриптами
              $(this).closest('.bx-viewport').css("overflow","visible");   
               $lens.css({       
                  "width":  oSettings.lensWidth + "px", 
                  "height": oSettings.lensHeight + "px",   
                  "background"	: 'url("'+$image.attr('data-magnify-src')+'") no-repeat 0 0 white',               
                });
                var elImage = new Image();
                elImage.src= $image.attr('data-magnify-src');
                nMagnifiedWidth = elImage.width; // ширина большого изображения
                nMagnifiedHeight = elImage.height; // высота большого изображения
                if(nMagnifiedWidth<oSettings.lensWidth){
                  $lens.css({               
                    "width":  nMagnifiedWidth + "px",                                
                  });
                }

                if(nMagnifiedHeight<oSettings.lensHeight){
                  $lens.css({
                    "height": nMagnifiedHeight + "px",                   
                  });
                }
            
             }).mouseout(function(e){
                $lens.fadeOut();
             });

            if ($anchor.length) {             
              $anchor.css('display', 'inline-block');          
              if (bAnchor || ($anchor.attr('href') && !($image.attr('data-magnify-src') || oSettings.src))) {
                $anchor.click(function(e) {
                  e.preventDefault();
                });
              }
            }

          },
          error: function() {
            // Clean up
            elImage = null;
            if (bAnchor) {
              if (oSettings.debug) console.log('error#1');
            } else {
              if (oSettings.debug) console.log('error#2 ' + $anchor.attr('href'));
              zoom($anchor.attr('href'), true);
            }
          }
        });

        elImage.src = sImgSrc;
      };

    return this.each(function() {     
      init(this);
    });

  };
}(jQuery));

//костыль для изменения overflow в плагине слайдера
$(document).ready(function(){
 	$('.bx-viewport').eq(0).css("overflow","visible"); 	
	//$('.main-product-slide li a img').css("position","static"); 	
});