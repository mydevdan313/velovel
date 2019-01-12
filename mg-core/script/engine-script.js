/**
 * В этом файле собран весь JS скрипт необходимый для функционирования компонентов сайта.
 */
;(function(u) {var I,e=typeof define=='function'&&typeof define.amd=='object'&&define.amd&&define,J=typeof exports=='object'&&exports,q=typeof module=='object'&&module,h=typeof require=='function'&&require,o=2147483647,p=36,i=1,H=26,B=38,b=700,m=72,G=128,C='-',E=/^xn--/,t=/[^ -~]/,l=/\x2E|\u3002|\uFF0E|\uFF61/g,s={overflow:'Overflow: input needs wider integers to process','not-basic':'Illegal input >= 0x80 (not a basic code point)','invalid-input':'Invalid input'},v=p-i,g=Math.floor,j=String.fromCharCode,n;function y(K) {throw RangeError(s[K])}function z(M,K) {var L=M.length;while(L--) {M[L]=K(M[L])}return M}function f(K,L) {return z(K.split(l),L).join('.')}function D(N) {var M=[],L=0,O=N.length,P,K;while(L<O) {P=N.charCodeAt(L++);if((P&63488)==55296&&L<O) {K=N.charCodeAt(L++);if((K&64512)==56320) {M.push(((P&1023)<<10)+(K&1023)+65536)}else{M.push(P,K)}}else{M.push(P)}}return M}function F(K) {return z(K,function(M) {var L='';if(M>65535) {M-=65536;L+=j(M>>>10&1023|55296);M=56320|M&1023}L+=j(M);return L}).join('')}function c(K) {return K-48<10?K-22:K-65<26?K-65:K-97<26?K-97:p}function A(L,K) {return L+22+75*(L<26)-((K!=0)<<5)}function w(N,L,M) {var K=0;N=M?g(N/b):N>>1;N+=g(N/L);for(;N>v*H>>1;K+=p) {N=g(N/v)}return g(K+(v+1)*N/(N+B))}function k(L,K) {L-=(L-97<26)<<5;return L+(!K&&L-65<26)<<5}function a(X) {var N=[],Q=X.length,S,T=0,M=G,U=m,P,R,V,L,Y,O,W,aa,K,Z;P=X.lastIndexOf(C);if(P<0) {P=0}for(R=0;R<P;++R) {if(X.charCodeAt(R)>=128) {y('not-basic')}N.push(X.charCodeAt(R))}for(V=P>0?P+1:0;V<Q;) {for(L=T,Y=1,O=p;;O+=p) {if(V>=Q) {y('invalid-input')}W=c(X.charCodeAt(V++));if(W>=p||W>g((o-T)/Y)) {y('overflow')}T+=W*Y;aa=O<=U?i:(O>=U+H?H:O-U);if(W<aa) {break}Z=p-aa;if(Y>g(o/Z)) {y('overflow')}Y*=Z}S=N.length+1;U=w(T-L,S,L==0);if(g(T/S)>o-M) {y('overflow')}M+=g(T/S);T%=S;N.splice(T++,0,M)}return F(N)}function d(W) {var N,Y,T,L,U,S,O,K,R,aa,X,M=[],Q,P,Z,V;W=D(W);Q=W.length;N=G;Y=0;U=m;for(S=0;S<Q;++S) {X=W[S];if(X<128) {M.push(j(X))}}T=L=M.length;if(L) {M.push(C)}while(T<Q) {for(O=o,S=0;S<Q;++S) {X=W[S];if(X>=N&&X<O) {O=X}}P=T+1;if(O-N>g((o-Y)/P)) {y('overflow')}Y+=(O-N)*P;N=O;for(S=0;S<Q;++S) {X=W[S];if(X<N&&++Y>o) {y('overflow')}if(X==N) {for(K=Y,R=p;;R+=p) {aa=R<=U?i:(R>=U+H?H:R-U);if(K<aa) {break}V=K-aa;Z=p-aa;M.push(j(A(aa+V%Z,0)));K=g(V/Z)}M.push(j(A(K,0)));U=w(Y,P,T==L);Y=0;++T}}++Y;++N}return M.join('')}function r(K) {return f(K,function(L) {return E.test(L)?a(L.slice(4).toLowerCase()):L})}function x(K) {return f(K,function(L) {return t.test(L)?'xn--'+d(L):L})}I={version:'1.2.0',ucs2:{decode:D,encode:F},decode:a,encode:d,toASCII:x,toUnicode:r};if(J) {if(q&&q.exports==J) {q.exports=I}else{for(n in I) {I.hasOwnProperty(n)&&(J[n]=I[n])}}}else{if(e) {define('punycode',I)}else{u.punycode=I}}}(this));

var actionInCatalog = '';

var storage = {
  counterToChangeCountProduct: 0,
  changeNameButtonOrder: false,
};

$('body').on('click', '[name=toOrder]', function(e) {
  if(storage.counterToChangeCountProduct > 0) {
    e.stopPropagation();
    e.preventDefault();
  }
});

function convertPunicode(val) {
  val = val.replace('http://', '');
  ascii = punycode.toASCII(val),
    uni = punycode.toUnicode(val);
  if (ascii == val)
    res = uni;
  else if (uni == val)
    res = ascii;
  else
    res = val;
  return res;
}

function getSetting(setting) {
  return $.ajax({
    type: "POST",
    url: mgBaseDir+"/ajaxrequest",
    data:{
      actionerClass: 'Ajaxuser', // класс Pactioner в Pactioner.php - в папке плагина
      action: 'getSetting',
      setting: setting,
    },
    cache: false,
    dataType: 'json',
  });
}

function checkSessionSettings() {
  $.when(
    getSetting('sessionToDB'),
    getSetting('sessionAutoUpdate')
  ).then(
    function (sessionToDBResp, sessionAutoUpdateResp, sessionLifeTimeResp) {
      sessionToDB = sessionToDBResp[0].data.sessionToDB;
      sessionAutoUpdate = sessionAutoUpdateResp[0].data.sessionAutoUpdate;
      
      $.ajax({
        type: "POST",
        url: mgBaseDir+"/ajaxrequest",
        data:{
          actionerClass: 'Ajaxuser', // класс Pactioner в Pactioner.php - в папке плагина
          action: 'getSessionLifeTime',
        },
        cache: false,
        dataType: 'json',
        success: function(response) {
          sessionLifeTime = response.data.sessionLifeTime;
          
          if (sessionAutoUpdate != 'false') {
            setInterval(function() {
              $.ajax({
                type: "POST",
                url: mgBaseDir+"/ajaxrequest",
                data:{
                  actionerClass: 'Ajaxuser', // класс Pactioner в Pactioner.php - в папке плагина
                  action: 'updateSession',
                },
                cache: false,
                dataType: 'json',
                success: function(response) {}
              });
            }, (sessionLifeTime/2*1000));
          }
        }
      });
    }
  );
}

var mgBaseDir = '';
var protocol = '';
var phoneMask='';
var sessionToDB = '';
var sessionAutoUpdate = '';
var sessionLifeTime = '';
var timeWithoutUser = 0;
var agreementClasses = '';
var langP = '';

document.cookie.split(/; */).forEach(function(cookieraw){
  if(cookieraw.indexOf('mg_to_script') === 0) {
    var cookie = cookieraw.split('=');
    var name = cookie[0].substr(13);//print it
    var value = decodeURIComponent(decodeURI(cookie[1])); //print it
    window[name] = value.replace(/&nbsp;/g, ' ');
  }
});


$(document).ready(function() {

  // $('script').each(function() {
  //   if ($(this).attr('src')) {
  //     $(this).attr('src').replace(/&amp;/g, '&');
  //     $(this).attr('src').replace(/&nbsp;/g, ' ');
  //     $(this).attr('src').replace(/(\w+)(?:=([^&]*))?/g, function(a, key, value) {
  //       if (key === 'protocol') {
  //         protocol = value;
  //       }
  //       if (key === 'mgBaseDir') {
  //         if (protocol == 'http') {
  //           mgBaseDir = 'http://' + convertPunicode(value);
  //         } else {
  //           mgBaseDir = convertPunicode(value);
  //         }
  //       }
  //       if (key === 'currency') {
  //         currency = value;
  //       }
  //       if (key === 'phoneMask') {
  //         phoneMask = value;         
  //       }
  //       if (key === 'agreementClasses') {
  //         agreementClasses = value;         
  //       }
  //       if (key === 'lang') {
  //         lang = value;         
  //       }
  //     });
  //   }
  // });

  
  if (!mgBaseDir) {
    mgBaseDir = $('.mgBaseDir').text();
  }
  
  $.ajax({
    type: "POST",
    url: mgBaseDir+"/ajaxrequest",
    data:{
      actionerClass: 'Ajaxuser', // класс Pactioner в Pactioner.php - в папке плагина
      action: 'isUserAuth',
    },
    cache: false,
    dataType: 'json',
    success: function(response) {
      if (!response.data.auth.id || response.data.auth.role == 2) {
        checkSessionSettings();
      }
    }
  });

  //эмуляция радиокнопок в форме характеристик продукта
  if ($('.mg__prop_radio').length && $('.mg__prop_p_radio').length && $('.mg__prop_label_radio').length) {
    $('body').on('change', '.property-form .mg__prop_radio', function() {
      $(this).parents('.mg__prop_p_radio').find('.mg__prop_radio').prop('checked', false);
      $(this).prop('checked', true);
      $(this).parents('.mg__prop_p_radio').find('.mg__prop_label_radio').removeClass('active');
      if ($(this).parents('.mg__prop_p_radio').length) {
        $(this).parent().addClass('active');
      }
    });
  }
  else{
    $('body').on('change', '.property-form input[type=radio]', function() {
      $(this).parents('p').find('input[type=radio]').prop('checked', false);
      $(this).prop('checked', true);
      $(this).parents('p').find('label').removeClass('active');
      if ($(this).parents('p').length) {
        $(this).parent().addClass('active');
      }
    });
  }
  
  //эмуляция радиокнопок в форме характеристик продукта
  if ($('.mg__prop_check').length) {
    $('body').on('change', '.property-form .mg__prop_check', function() {
      $(this).parent().toggleClass('active');
    });
  }
  else{
    $('body').on('change', '.property-form input[type=checkbox]', function() {
      $(this).parent().toggleClass('active');
    });
  }
  
  //пересчет цены товара аяксом
  if ($('.mg__prop_radio').length && $('.mg__prop_check').length && $('.mg__prop_select').length && $('.mg__oldprice_li').length && $('.mg__count_link').length) {
    $('body').on('change', '.property-form .mg__prop_radio, .property-form .mg__prop_check, .property-form .mg__prop_select, .product-wrapper .block-variants select', function() {
      var request = $('.buy-block form').formSerialize();
      var priceBlock = '.product-status-list .price';
      var productList = $('.product-status');

      if ($(this).parents('.product-wrapper').length) {// для вызова из каталога
        priceBlock = $(this).parents('.product-wrapper').find('.product-price');
        request = $(this).parents('.product-wrapper').find('.property-form').formSerialize();
        productList = $(this).parents('.product-wrapper');
      }

      if ($(this).parents('.mg-compare-product').length) {// для вызова из сравнений    
        priceBlock = $(this).parents('.mg-compare-product').find('.price');
        request = $(this).parents('.mg-compare-product').find('.property-form').formSerialize();
        request += '&remInfo=false';
        productList = $(this).parents('.mg-compare-product');
      }

      // Пересчет цены            
      $.ajax({
        type: "POST",
        url: mgBaseDir + "/product/",
        data: "calcPrice=1&" + request,
        dataType: "json",
        cache: false,
        success: function(response) {
          productList.find('.rem-info').hide();

          if(response.data.wholesalesTable != undefined) {
            $('.wholesales-data').html(response.data.wholesalesTable);
          }
          
          productList.find('.buy-container.product .hidder-element').hide();
          if ($('.buy-block .count').length > 0) {
              $('.buy-container .hidder-element').hide();
          }  
          if ('success' == response.status) {
            if ($(priceBlock).find('.product-default-price').length) {
              $(priceBlock).find('.product-default-price').html(response.data.price);
            } else {
              $(priceBlock).html(response.data.price);
            } 
            $(priceBlock).find('.product-default-price').html(response.data.price);
            productList.find('.code').text(response.data.code);
            var message = '';
            if (response.data.title) {
              message = locale.countMsg1 + response.data.title.replace("'", '"') + locale.countMsg2 + response.data.code + locale.countMsg3;
            }
            productList.find('.rem-info .mg__count_link').attr('href', mgBaseDir + '/feedback?message=' + message);
            productList.find('.code-msg').text(response.data.code);
            var val = response.data.count;
            if (val != 0) {

              $('.depletedLanding').hide();
              $('.addToOrderLanding').show();

              productList.find('.rem-info').hide();
              productList.find('.buy-container .hidder-element').show();
              if ($('.buy-block .count').length >0) {
                $('.buy-container .hidder-element').show();
              }  
              productList.find('.buy-container.product').show();            
              if (productList.find('.buy-container .hidder-element').closest('.addToCart').length == 0) {
                if ('false' == actionInCatalog) {
                  productList.find('.buy-container .hidder-element .product-info').show();
                  productList.find('.buy-container .hidder-element .addToCart').hide();
                }
                else{
                  productList.find('.buy-container .hidder-element .product-info').hide();
                  productList.find('.buy-container .hidder-element .addToCart').show();
                }
                productList.find('.buy-container .hidder-element').show();
              }
            } else {
              $('.depletedLanding').show();
              $('.addToOrderLanding').hide();
              
              productList.find('.rem-info').show();
              if ($('.buy-block .count').length >0) {
                $('.buy-container .hidder-element').hide();
              }  
              productList.find('.buy-container.product').hide();            
              if (productList.find('.buy-container .hidder-element').closest('.addToCart').length) {
                productList.find('.buy-container .hidder-element .product-info').show();
                productList.find('.buy-container .hidder-element .addToCart').hide();
                // productList.find('.buy-container .hidder-element:first').hide();
              }
            }
            if (response.data.count_layout) {
              if (productList.find('.count').length>0) {
                productList.find('.count').parent().html(response.data.count_layout);
              } else {
                productList.find('.in-stock').parent().html(response.data.count_layout);
              }
             
            } else {
              if ((val == '\u221E' || val == '' || parseFloat(val) < 0)) {
                val = '<span itemprop="availability" class="count"><span class="sign">&#10004;</span>'+locale.countInStock+'</span>';
                productList.find('.rem-info').hide();
              } else {
                val = locale.remaining+': <span itemprop="availability" class="label-black count">'+ val+'</span> '+locale.unit;
              }
              productList.find('.count').parent().html(val);
            }
           
            var val = response.data.old_price;
            if (val != "0 " + currency && val != ' ' + currency) {
              productList.find('.old-price').parents('.mg__oldprice_li').show();
              productList.find('.old-price').parents('.old').show();
            } else {
              productList.find('.old-price').parents('.mg__oldprice_li').hide();
            }

            if (val != "0 " + currency && val != ' ' + currency) {
              productList.find('.old-price').text(response.data.old_price);
            }

            productList.find('.amount_input').data('max-count', response.data.count);

            productList.find('.weight').text(response.data.weight);

            if (parseFloat(productList.find('.amount_input').val()) > parseFloat(response.data.count)) {
              val = response.data.count;
              if ((val == '\u221E' || val == '' || parseFloat(val) < 0)) {
                val = productList.find('.amount_input').val();
              }
              if (val == 0) {
                val = 1
              };
              productList.find('.amount_input').val(val);
            }
          }
          if(response.data.storage != undefined) {
            maxStorageCount = 0;
            for(var i in response.data.storage) {
              $('.count-on-storage[data-id='+i+']').html(response.data.storage[i]);
              if(response.data.storage[i] > maxStorageCount) maxStorageCount = response.data.storage[i];
            }
            $('.actionBuy .amount_input').data('max-count', maxStorageCount);
          }
        }
      });

      return false;
    });
  } else {
    $('body').on('change', '.property-form input, .property-form select , .product-wrapper .block-variants select, .cart_form .amount_input', function() {
      var request = $('.buy-block form').formSerialize();
      var priceBlock = '.product-status-list .price';
      var productList = $('.product-status');

      if ($(this).parents('.product-wrapper').length) {// для вызова из каталога
        priceBlock = $(this).parents('.product-wrapper').find('.product-price');
        request = $(this).parents('.product-wrapper').find('.property-form').formSerialize();
        productList = $(this).parents('.product-wrapper');
      }

      if ($(this).parents('.mg-compare-product').length) {// для вызова из сравнений    
        priceBlock = $(this).parents('.mg-compare-product').find('.price');
        request = $(this).parents('.mg-compare-product').find('.property-form').formSerialize();
        request += '&remInfo=false'
        productList = $(this).parents('.mg-compare-product');
      }
      
      // Пересчет цены            
      $.ajax({
        type: "POST",
        url: mgBaseDir + "/product/",
        data: "calcPrice=1&" + request,
        dataType: "json",
        cache: false,
        success: function(response) {

          if(response.data.wholesalesTable != undefined) {
            $('.wholesales-data').html(response.data.wholesalesTable);
          }

          actionInCatalog = response.data.actionInCatalog;

          productList.find('.rem-info').hide();
          
          productList.find('.buy-container.product .hidder-element').hide();
          if ($('.buy-block .count').length > 0) {
              $('.buy-container .hidder-element').hide();
          }  
          if ('success' == response.status) {
            if ($(priceBlock).find('.product-default-price').length) {
              $(priceBlock).find('.product-default-price').html(response.data.price);
            } else {
              $(priceBlock).html(response.data.price);
            } 
            $(priceBlock).find('.product-default-price').html(response.data.price);
            productList.find('.code').text(response.data.code);
            var message = '';
            if (response.data.title) {
              message = locale.countMsg1 + response.data.title.replace("'", '"') + locale.countMsg2 + response.data.code + locale.countMsg3;
            }
            productList.find('.rem-info a').attr('href', mgBaseDir + '/feedback?message=' + message);
            productList.find('.code-msg').text(response.data.code);
            var val = response.data.count;
            if (val != 0) {

              $('.depletedLanding').hide();
              $('.addToOrderLanding').show();

              productList.find('.rem-info').hide();
              productList.find('.buy-container .hidder-element').show();
              if ($('.buy-block .count').length >0) {
                $('.buy-container .hidder-element').show();
              }  
              productList.find('.buy-container.product').show();            
              if (!productList.find('.buy-container .hidder-element a:visible').hasClass('addToCart')) {
                if ('false' == actionInCatalog) {
                  productList.find('.buy-container .hidder-element .product-info').show();
                  productList.find('.buy-container .hidder-element .addToCart').hide();
                }
                else{
                  productList.find('.buy-container .hidder-element .product-info').hide();
                  productList.find('.buy-container .hidder-element .addToCart').show();
                }
                
                productList.find('.buy-container .hidder-element').show();
              }
            } else {
              $('.depletedLanding').show();
              $('.addToOrderLanding').hide();

              productList.find('.rem-info').show();
              if ($('.buy-block .count').length >0) {
                $('.buy-container .hidder-element').hide();
              }  
              productList.find('.buy-container.product').hide();            
              if (productList.find('.buy-container .hidder-element a:visible').hasClass('addToCart')) {
                productList.find('.buy-container .hidder-element .product-info').show();
                productList.find('.buy-container .hidder-element .addToCart').hide();
                // productList.find('.buy-container .hidder-element:first').hide();
              }
            }
            if (response.data.count_layout) {
              if (productList.find('.count').length>0) {
                productList.find('.count').parent().html(response.data.count_layout);
              } else {
                productList.find('.in-stock').parent().html(response.data.count_layout);
              }
             
            } else {
              if ((val == '\u221E' || val == '' || parseFloat(val) < 0)) {
                val = '<span itemprop="availability" class="count"><span class="sign">&#10004;</span>'+locale.countInStock+'</span>';
                productList.find('.rem-info').hide();
              } else {
                val = locale.remaining+'Остаток: <span itemprop="availability" class="label-black count">'+ val+'</span> '+locale.pcs;
              }
              productList.find('.count').parent().html(val);
            }
           
            var val = response.data.old_price;
            if (val != "0 " + currency && val != ' ' + currency) {
              productList.find('.old-price').parents('li').show();
              productList.find('.old-price').parents('.old').show();
            } else {
              productList.find('.old-price').parents('li').hide();
            }

            if (val != "0 " + currency && val != ' ' + currency) {
              productList.find('.old-price').text(response.data.old_price);
            }

            productList.find('.amount_input').data('max-count', response.data.count);

            productList.find('.weight').text(response.data.weight);

            if (parseFloat(productList.find('.amount_input').val()) > parseFloat(response.data.count)) {
              val = response.data.count;
              if ((val == '\u221E' || val == '' || parseFloat(val) < 0)) {
                val = productList.find('.amount_input').val();
              }
              if (val == 0) {
                val = 1
              }
              ;
              productList.find('.amount_input').val(val);
            }
          }
          if(response.data.storage != undefined) {
            maxStorageCount = 0;
            for(var i in response.data.storage) {
              $('.count-on-storage[data-id='+i+']').html(response.data.storage[i]);
              if(response.data.storage[i] > maxStorageCount) maxStorageCount = response.data.storage[i];
            }
            $('.actionBuy .amount_input').data('max-count', maxStorageCount);
          }
        }
      });

      return false;
    });
  }

  // ссылка на главную картинку продукта
  var linkDefaultPreview = "";
  var variantId = "";

  //подстановка картинки варианта вместо картинки товара  
  if ($('.mg__var_radio').length && $('.mg__var_tbody').length && $('.variant-tr').length && $('.mg__var_label').length && $('.mg__prod_img_link').length && $('.product-details-image').length && $('.mg-product-image').length && $('.slides-item').length && $('.mg-peview-foto').length) {
    $('body').on('change', '.block-variants .mg__var_radio', function(e) {
      $(this).parents('.mg__var_tbody').find('.variant-tr .mg__var_label').removeClass('active');
      $(this).parents('.variant-tr').find('.mg__var_label').addClass('active');
      // обработчик подстановки картинки варианта для страницы с карточкой товара
      if ($('.mg-product-slides').length) {
        // текущая ссылка на главную картинку продукта  
        var linkInPreview = $('.mg-product-slides .main-product-slide .product-details-image .mg__prod_img_link').eq(0).attr('href');
        if (linkDefaultPreview == "") {
          // запоминаем стоящую поумолчанию ссылку на картинку товара
          linkDefaultPreview = linkInPreview;
        }
        // получаем новую ссылку на продукт из картинки варианта
        var src = $(this).parents('.variant-tr').find('.mg__var_img').attr('src');
        // если она оличается от той что уже установлена в качестве главной
        if (src != linkInPreview) {
          // проверяем есть ли в варианте ссылка на картинку, еси нет то показываем картинку продукта по умолчанию  
          if (!src) {
            src = linkDefaultPreview;
          }
          // меняем ссылку на картинку в модалке, для увеличенного просмотра  
          $('.mg-product-slides .main-product-slide .product-details-image .mg__prod_img_link').eq(0).attr('href', src.replace('thumbs/30_', ''));
          // меняем главную картинку товара в просмотрщике
          $('.mg-product-slides .main-product-slide .product-details-image').eq(0).find('.mg-product-image').attr('src', src.replace('thumbs/30_', 'thumbs/70_')).attr('data-magnify-src',src.replace('thumbs/30_', ''));
          // меняем первую картинку товара в ленте просмотрщика
          $('.slides-inner .slides-item[data-slide-index=0]').find('.mg-peview-foto').attr('src', src.replace('thumbs/30_', 'thumbs/70_'));
          // кликаем по первому элементу, чтобы показать картинку в просмотрщике.
          $('.mg-product-slides .slides-item[data-slide-index="0"]').click();
          e.stopPropagation();
          e.stopImmediatePropagation();
        }
      } else {
        var obj = $(this).parents('.product-wrapper');
        var count = $(this).data('count');
        if (!obj.length) {
          obj = $(this).parents('.mg-compare-product');
        }
        if (obj.length) {// для вызова из каталога

          //Обнуление дефолтной картинки, если перешли к вариантам другого товара 
          if(!variantId) {
            variantId = $(this).attr('id');
          }else{
            var newVariantId = $(this).attr('id');
            if(newVariantId != variantId) {
              linkDefaultPreview = "";
              variantId = newVariantId;
            }
          }
          
          // текущая ссылка на главную картинку продукта  
          var linkInPreview = obj.find('.mg-product-image[data-transfer="true"]').eq(0).attr('src');
          
          if (linkDefaultPreview == "") {
            // запоминаем стоящую по умолчанию ссылку на картинку товара
            linkDefaultPreview = linkInPreview;
          }
          // получаем новую ссылку на продукт из картинки варианта
          var src = $(this).parents('.variant-tr').find('.mg__var_img').attr('src');
          // если она отличается от той, что уже установлена в качестве главной
          if (src != linkInPreview) {
            // проверяем есть ли в варианте ссылка на картинку, еси нет то показываем картинку продукта по умолчанию  
            if (!src) {
              src = linkDefaultPreview;
            }
            // меняем ссылку на картинку в модалке, для увеличенного просмотра  
            // $('.mg-product-slides .main-product-slide li a').eq(0).attr('href',src.replace('thumbs/30_', ''));
            // меняем главную картинку товара в просмотрщике    
            obj.find('.mg-product-image[data-transfer="true"]').eq(0).attr('src', src.replace('thumbs/30_', 'thumbs/70_'));
            // меняем первую картинку товара в ленте просмотрщика
            //$('.slides-inner a[data-slide-index=0]').find('img').attr('src',src.replace('thumbs/70_', ''));      
            // кликаем по первому элементу, чтобы показать картинку в просмотрщике.
            //$('.mg-product-slides a[data-slide-index="0"]').click();
          }
        }   
        var form = $(this).parents('form');
      
        if(form.hasClass('actionView')) {
          return false;
        }

        if ($(obj).find('.buy-container .hidder-element').closest('.addToCart').length) {
          var buttonbuy = true;
        }
        else{
          var buttonbuy = false;
        }
        
        // var buttonbuy = $(obj).find('.buy-container .hidder-element a:visible').hasClass('addToCart');
        
        if (count != '0' && !buttonbuy) {
          if ('false' == actionInCatalog) {
            $(obj).find('.buy-container .hidder-element .product-info').show();
            $(obj).find('.buy-container .hidder-element .addToCart').hide();
          }
          else{
            $(obj).find('.buy-container .hidder-element .product-info').hide();
            $(obj).find('.buy-container .hidder-element .addToCart').show();
          }
        } else if (count == '0' && buttonbuy == true) {
          $(obj).find('.buy-container .hidder-element .product-info').show();
          $(obj).find('.buy-container .hidder-element .addToCart').hide();
        }
      }
    });
  }
  else{
    $('body').on('change', '.block-variants input[type=radio]', function(e) {
      $(this).parents('tbody').find('tr label').removeClass('active');
      $(this).parents('tr').find('label').addClass('active');
      // обработчик подстановки картинки варианта для страницы с карточкой товара
      if ($('.mg-product-slides').length) {
        // текущая ссылка на главную картинку продукта  
        var linkInPreview = $('.mg-product-slides .main-product-slide li a').eq(0).attr('href');
        if (linkDefaultPreview == "") {
          // запоминаем стоящую поумолчанию ссылку на картинку товара
          linkDefaultPreview = linkInPreview;
        }
        // получаем новую ссылку на продукт из картинки варианта
        var src = $(this).parents('tr').find('img').attr('src');
        // если она оличается от той что уже установлена в качестве главной
        if (src != linkInPreview) {
          // проверяем есть ли в варианте ссылка на картинку, еси нет то показываем картинку продукта по умолчанию  
          if (!src) {
            src = linkDefaultPreview;
          }
          // меняем ссылку на картинку в модалке, для увеличенного просмотра  
          $('.mg-product-slides .main-product-slide li a').eq(0).attr('href', src.replace('thumbs/30_', ''));
          // меняем главную картинку товара в просмотрщике
          $('.mg-product-slides .main-product-slide li').eq(0).find('.mg-product-image').attr('src', src.replace('thumbs/30_', 'thumbs/70_')).attr('data-magnify-src',src.replace('thumbs/30_', ''));
          // меняем первую картинку товара в ленте просмотрщика
          $('.slides-inner a[data-slide-index=0]').find('img').attr('src', src.replace('thumbs/30_', 'thumbs/70_'));
          // кликаем по первому элементу, чтобы показать картинку в просмотрщике.
          $('.mg-product-slides a[data-slide-index="0"]').click();
          e.stopPropagation();
          e.stopImmediatePropagation();
        }
      } else {
        var obj = $(this).parents('.product-wrapper');
        var count = $(this).data('count');
        if (!obj.length) {
          obj = $(this).parents('.mg-compare-product');
        }
        if (obj.length) {// для вызова из каталога

          //Обнуление дефолтной картинки, если перешли к вариантам другого товара 
          if(!variantId) {
            variantId = $(this).attr('id');
          }else{
            var newVariantId = $(this).attr('id');
            if(newVariantId != variantId) {
              linkDefaultPreview = "";
              variantId = newVariantId;
            }
          }
          
          // текущая ссылка на главную картинку продукта  
          var linkInPreview = obj.find('img[data-transfer="true"]').eq(0).attr('src');
          
          if (linkDefaultPreview == "") {
            // запоминаем стоящую по умолчанию ссылку на картинку товара
            linkDefaultPreview = linkInPreview;
          }
          // получаем новую ссылку на продукт из картинки варианта
          var src = $(this).parents('tr').find('img').attr('src');
          // если она отличается от той, что уже установлена в качестве главной
          if (src != linkInPreview) {
            // проверяем есть ли в варианте ссылка на картинку, еси нет то показываем картинку продукта по умолчанию  
            if (!src) {
              src = linkDefaultPreview;
            }
            // меняем ссылку на картинку в модалке, для увеличенного просмотра  
            // $('.mg-product-slides .main-product-slide li a').eq(0).attr('href',src.replace('thumbs/30_', ''));
            // меняем главную картинку товара в просмотрщике    
            obj.find('img[data-transfer="true"]').eq(0).attr('src', src.replace('thumbs/30_', 'thumbs/70_'));
            // меняем первую картинку товара в ленте просмотрщика
            //$('.slides-inner a[data-slide-index=0]').find('img').attr('src',src.replace('thumbs/70_', ''));      
            // кликаем по первому элементу, чтобы показать картинку в просмотрщике.
            //$('.mg-product-slides a[data-slide-index="0"]').click();
          }
        }   
        var form = $(this).parents('form');
      
        if(form.hasClass('actionView')) {
          return false;
        }
        
        var buttonbuy = $(obj).find('.buy-container .hidder-element a:visible').hasClass('addToCart');
        
        if (count != '0' && !buttonbuy) {
          if ('false' == actionInCatalog) {
            $(obj).find('.buy-container .hidder-element .product-info').show();
            $(obj).find('.buy-container .hidder-element .addToCart').hide();
          }
          else{
            $(obj).find('.buy-container .hidder-element .product-info').hide();
            $(obj).find('.buy-container .hidder-element .addToCart').show();
          }
        } else if (count == '0' && buttonbuy == true) {
          $(obj).find('.buy-container .hidder-element .product-info').show();
          $(obj).find('.buy-container .hidder-element .addToCart').hide();
        }
      }
    });
  }

  //Количество товаров
  $('body').on('click','.amount_change .up', function() {
    //bp-за вариантов товара делаем  бесконечное возможное количество
    // 

    var obj = $(this).parents('.cart_form').find('.amount_input');
    var val = obj.data('max-count');
    if ((val == '\u221E' || val == '' || parseFloat(val) < 0)) {
      obj.data('max-count', 99999);
    }
    var i = obj.val();
    i++;
    if (i > obj.data('max-count')) {
      i = obj.data('max-count');
    }
    obj.val(i).trigger('change');
    return false;
  });

  //Изменение валюты
  $('body').on('change','[name=userCustomCurrency]', function() {
    $.ajax({
      type: "GET",
      url: mgBaseDir+"/ajaxrequest",
      data: {
        userCustomCurrency: $('[name=userCustomCurrency]').val()
      },
      success: function(response) {
        window.location.reload(true);
      }
    });
  });

  //Изменение языка
  $('body').on('change','[name=multiLang-selector]', function() {
    window.location.href = $('[name=multiLang-selector]').val();
  });

  $('body').on('click','.amount_change .down', function() { 
    var obj = $(this).parents('.cart_form').find('.amount_input');
    var val = obj.val();
    // if((val=='\u221E'||val==''||parseFloat(val)<0)) {val = 0;} 
    var i = val;
    i--;
    if (i <= 0) {
      i = 1;
    }
    obj.val(i).trigger('change');
    return false;
  });


  // Исключение ввода в поле выбора количества недопустимых значений.
  $('body').on('keyup', '.amount_input', function() {
    if ($(this).hasClass('zeroToo')) {
      if (isNaN($(this).val()) || $(this).val() < 0) {
        $(this).val('1');
      }
      $(this).val(Math.round($(this).val()));
    } else {
      if (isNaN($(this).val()) || $(this).val() <= 0) {
        $(this).val('1');
      }
      $(this).val($(this).val().replace(/\./g, ''));
    }
    if (parseFloat($(this).val()) > parseFloat($(this).data('max-count')) && parseFloat($(this).data('max-count')) > 0) {
      $(this).val($(this).data('max-count'));
    }
  });

  // Исключение ввода в поле выбора количества недопустимых значений.
  $('body').on('.deleteFromCart', function() {
    if (isNaN($(this).val()) || $(this).val() <= 0) {
      $(this).val('1');
    }
  });
  
  $('.product-wrapper .variants-table').each(function() {
    var form = $(this).parents('form');
    
    if(form.hasClass('actionView') || ('false' == actionInCatalog)) {
      return;
    }
    if ($('.mg__var_td').length && $('.mg__prod_id').length) {
      if ($(this).find('.mg__var_td input:checked').data('count') != 0 && $(form).find('.buy-container .addToCart').length==0) {
        var namebutton = $('.addToCart:first').text();
        $(form).find('.buy-container .hidder-element .product-info').hide();
        var id = $(form).find('.buy-container .hidder-element .mg__prod_id').val();
        var buttonbuy = '<a href="http://'+mgBaseDir+'/catalog?inCartProductId='+id+'" class="addToCart product-buy" rel="nofollow" data-item-id="'+id+'">'+namebutton+'</a>';
        $(form).find('.buy-container .hidder-element ').append(buttonbuy);
      }
    }
    else{
      if ($(this).find('td input:checked').data('count') != 0 && $(form).find('.buy-container a.addToCart').length==0) {
        var namebutton = $('.addToCart:first').text();
        $(form).find('.buy-container .hidder-element .product-info').hide();
        var id = $(form).find('.buy-container .hidder-element input').val();
        var buttonbuy = '<a href="http://'+mgBaseDir+'/catalog?inCartProductId='+id+'" class="addToCart product-buy" rel="nofollow" data-item-id="'+id+'">'+namebutton+'</a>';
        $(form).find('.buy-container .hidder-element ').append(buttonbuy);
      }
    }
  });
  $('body').on('click', '.product-cart .cart_form .amount_change .up, .product-cart .cart_form .amount_change .down', function() {
     // Пересчет цены и количества    
    var request =  $(this).parents('.cart-wrapper').find('form').formSerialize();
    updateCartCount(request);
    $(this).parents('.cart-wrapper').find('form .amount_change .up').prop('disabled', true).addClass('disabled');
    $(this).parents('.cart-wrapper').find('form .amount_change .down').prop('disabled', true).addClass('disabled');
    return false;
  })
  // ввод количества покупаемого товара в корзине, пересчет корзины
  $('body').on('blur', '.product-cart .amount_input', function() {
    var count = $(this).val();
    if (count == 0) {
      if ($('.mg__cart_tr').length) {
        $(this).parents('.mg__cart_tr').find('.price-cell .deleteItemFromCart').trigger('click');
      }
      else{
        $(this).parents('tr').find('.price-cell .deleteItemFromCart').trigger('click');
      }
    } else {
      var request =  $(this).parents('.cart-wrapper').find('form').formSerialize();
      updateCartCount(request);
    }
    $(this).parents('.cart-wrapper').find('form .amount_change .up').prop('disabled', true).addClass('disabled');
    $(this).parents('.cart-wrapper').find('form .amount_change .down').prop('disabled', true).addClass('disabled');
    return false;
  });
    
  $('.spoiler-content').hide();  
  $('.spoiler-title').click(function() {
    $(this).next().slideToggle(); 
  }); 

  if (agreementClasses.length > 1) {
    
    var agreementArray = agreementClasses.split(",");

    $(agreementClasses).click(function (e) {

      for (var i = 0; i < agreementArray.length; i++) {
        if ($(this).hasClass(agreementArray[i].slice(1))) {
          var className = agreementArray[i].slice(1);
          break;  
        }
      }

      if ($('.agreement-data-checkbox-'+className).is(':checked')) {
        $('.agreement-data-checkbox-'+className).parent().find('span').removeClass('agreement-data-denied');
      }
      else{
        e.stopImmediatePropagation()
        $('.agreement-data-checkbox-'+className).parent().find('span').addClass('agreement-data-denied');
        return false;
      }
    });
  
    $('body').on('click', agreementClasses, function(e) {

      for (var i = 0; i < agreementArray.length; i++) {
        if ($(this).hasClass(agreementArray[i].slice(1))) {
          var className = agreementArray[i].slice(1);
          break;  
        }
      }

      if ($('.agreement-data-checkbox-'+className).is(':checked')) {
        $('.agreement-data-checkbox-'+className).parent().find('span').removeClass('agreement-data-denied');
      }
      else{    
        e.stopImmediatePropagation()
        $('.agreement-data-checkbox-'+className).parent().find('span').addClass('agreement-data-denied');
        return false;
      }
    });
  }
  

  $('body').on('click', '.show-more-agreement-data', function () {

    if ($('.more-agreement-data-container').length < 1) {

      $.ajax({
        type: "GET",
        url: mgBaseDir+"/ajaxrequest",
        data: {
          layoutAgreement: 'agreement'
        },
        dataType: "HTML",
        success: function(response) {
          $('body').append(response);
        }
      });
    }
    else{
      $('.more-agreement-data-overlay').show();
      $('.more-agreement-data-container').show();
    }
  });
  $('body').on('click', '.close-more-agreement-data', function () {
    $('.more-agreement-data-overlay').hide();
    $('.more-agreement-data-container').hide();
  });
  $('body').on('click', '.more-agreement-data-overlay', function () {
    $('.more-agreement-data-overlay').hide();
    $('.more-agreement-data-container').hide();
  }); 
  
});

function transferEffect(productId, buttonClick, wrapperClass) {

  var $css = {
    'height': '100%',
    "opacity": 0.5,
    "position": "relative",
    "z-index": 100
  };

  var $transfer = {
    to: $(".small-cart-icon"),
    className: "transfer_class"
  }

  //если кнопка на которую нажали находится внутри нужного контейнера. 
  if (buttonClick.parents(wrapperClass).find('img[data-transfer=true][data-product-id=' + productId + ']').length) {

    // даем способность летать для картинок из слайдера новинок и прочих.
    var tempObj = buttonClick.parents(wrapperClass).find('img[data-transfer=true][data-product-id=' + productId + ']');
    tempObj.effect("transfer", $transfer, 600);
    $('.transfer_class').html(tempObj.clone().css($css));

  } else {
    //Если кнопка находится не в контейнере, проверяем находится ли она на странице карточки товара.
    if ($('.product-details-image').length) {
      // даем способность летать для картинок из галереи в карточке товара.
      $('.product-details-image').each(function() {
        if ($(this).css('display') != 'none') {
          $(this).find('.mg-product-image').effect("transfer", $transfer, 600);
          $('.transfer_class').html($(this).find('img').clone().css($css));
        }
      });

    } else {
      // даем способность летать для всех картинок.
      var tempObj = $('img[data-transfer=true][data-product-id=' + productId + ']');
      tempObj.effect("transfer", $transfer, 600);
    }
  }

  if (tempObj) {
    $('.transfer_class').html(tempObj.clone().css($css));
  }

}

function getInternetExplorerVersion() {
	var rv = -1;
	if (navigator.appName == 'Microsoft Internet Explorer')	{
		var ua = navigator.userAgent;
		var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null)
			rv = parseFloat( RegExp.$1 );
	}
	else if (navigator.appName == 'Netscape')	{
		var ua = navigator.userAgent;
		var re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null)
			rv = parseFloat( RegExp.$1 );
	}
	return rv;
}

function updateCartCount(request) {
  storage.counterToChangeCountProduct++;
  storage.changeNameButtonOrder = true;
  setTimeout(function() {
    if(storage.changeNameButtonOrder) {
      $('[name=toOrder]').val(locale.waitCalc);
      storage.changeNameButtonOrder = false;
    }
  }, 500);
if ($('.mg__cart_input_prop').length && $('.amount_input').length && $('.mg__sum_strong').length && $('.total-payment').length) {
  $.ajax({
  type: "POST",
    url: mgBaseDir + "/cart",
    data: "refresh=1&count_change=1&" + request,
    dataType: "json",
    cache: false,
    success: function(response) {
      if (response.deliv && response.curr) {
        var i = 0;
        response.deliv.forEach(function(element, index, arr) {
          $('.delivery-details-list li:eq('+i+') .deliveryPrice').html('&nbsp;'+element);
          if ($('.delivery-details-list input[type=radio]:eq('+i+')').is(':checked')) {
            if (element == 0) {
              $('.summ-info .delivery-summ').html('');
            }
            else{
              $('.summ-info .delivery-summ').html(locale.delivery+'<span class="order-delivery-summ">'+element+' '+response.curr+'</span>');
            }
          }
          i++;
        });
      }
      storage.counterToChangeCountProduct--;
      if(storage.counterToChangeCountProduct == 0) {
        $('[name=toOrder]').val(locale.checkout);
        storage.changeNameButtonOrder = false;
      }
      if (response.data) {
        var dataSmalCart = '';
        response.data.dataCart.forEach(function(element, index, arr) {
          if (element.variantId == null) {var varBlock = '';} else{var varBlock = '[data-variant=' + (element.variantId)+ ']';}
          var tr = $('.cart-wrapper .cart-table .deleteItemFromCart[data-delete-item-id='+element.id+'][data-property='+element.property+']'+varBlock).parents('tr');
          var prod = $('.cart-wrapper .cart-table .mg__cart_input_prop[name="property_'+element.id+'[]"][value='+element.property+']');
          
            dataSmalCart += '<tr>\
            <td class="small-cart-img">\
              <a href="' + mgBaseDir + '/' + (element.category_url ? element.category_url : 'catalog') + '/'
              + element.product_url + '"><img src="' + element.image_url_new + '" alt="'
              + element.title + '" alt="" /></a>\
            </td>\
              <td class="small-cart-name">\
                <ul class="small-cart-list">\
                  <li><a href="' + mgBaseDir + '/' + (element.category_url ? element.category_url : 'catalog') + '/'
                  + element.product_url + '">' + element.title + '</a><span class="property">'
                  + element.property_html + '</span></li>\
                  <li class="qty">x' + element.countInCart + ' <span>'
                  + element.priceInCart + '</span></li>\
                </ul>\
              </td>\
              <td class="small-cart-remove"><a href="#" class="deleteItemFromCart" title="Удалить" data-delete-item-id="' + element.id
            + '" data-property="' + element.property
            + '" data-variant="' + element.variantId
            + '">&#215;</a></td>\
            </tr>';        
          if(tr.find('.cart_form .amount_input[name="item_'+element.id+'[]"]').val() > element.countInCart) {
            tr.find('.cart_form .maxCount').detach();
            tr.find('.cart_form').append('<span class="maxCount" style="display:block;text-align:center;">'+locale.MAX+': '+element.countInCart+'</span>');
          } else {
            tr.find('.cart_form .maxCount').detach();
          }  
          tr.find('.cart_form .up, .cart_form .down').prop('disabled', false).removeClass('disabled');
          tr.find('.cart_form .amount_input[name="item_'+element.id+'[]"]').val(element.countInCart);
          tr.find('.price-cell').html(element.priceInCart);
         
      });
      if ($('.small-cart-table tbody').length) {
        $('.small-cart-table tbody').html(dataSmalCart);
      }
      else{
        $('.small-cart-table').html(dataSmalCart);
      }
      $('.total .total-sum .total-payment').text(response.data.cart_price_wc);
      $('.pricesht').text(response.data.cart_price);
      $('.countsht').text(response.data.cart_count);
      $('.cart-wrapper .total-sum .mg__sum_strong').text(response.data.cart_price_wc);
    }
    }
  })
}
else{
  $.ajax({
  type: "POST",
    url: mgBaseDir + "/cart",
    data: "refresh=1&count_change=1&" + request,
    dataType: "json",
    cache: false,
    success: function(response) {
      if (response.deliv && response.curr) {
        var i = 0;
        response.deliv.forEach(function(element, index, arr) {
          $('.delivery-details-list li:eq('+i+') .deliveryPrice').html('&nbsp;'+element);
          if ($('.delivery-details-list input[type=radio]:eq('+i+')').is(':checked')) {
            if (element == 0) {
              $('.summ-info .delivery-summ').html('');
            }
            else{
              $('.summ-info .delivery-summ').html(locale.delivery+'<span class="order-delivery-summ">'+element+' '+response.curr+'</span>');
            }
          }
          i++;
        });
      }
      storage.counterToChangeCountProduct--;
      if(storage.counterToChangeCountProduct == 0) {
        $('[name=toOrder]').val(locale.checkout);
        storage.changeNameButtonOrder = false;
      }
      if (response.data) {
        var dataSmalCart = '';
        response.data.dataCart.forEach(function(element, index, arr) {
          if (element.variantId == null) {var varBlock = '';} else{var varBlock = '[data-variant=' + (element.variantId)+ ']';}
          var tr = $('.cart-wrapper .cart-table td .deleteItemFromCart[data-delete-item-id='+element.id+'][data-property='+element.property+']'+varBlock).parents('tr');
          var prod = $('.cart-wrapper .cart-table input[name="property_'+element.id+'[]"][value='+element.property+']');
          
            dataSmalCart += '<tr>\
            <td class="small-cart-img">\
              <a href="' + mgBaseDir + '/' + (element.category_url ? element.category_url : 'catalog') + '/'
              + element.product_url + '"><img src="' + element.image_url_new + '" alt="'
              + element.title + '" alt="" /></a>\
            </td>\
              <td class="small-cart-name">\
                <ul class="small-cart-list">\
                  <li><a href="' + mgBaseDir + '/' + (element.category_url ? element.category_url : 'catalog') + '/'
                  + element.product_url + '">' + element.title + '</a><span class="property">'
                  + element.property_html + '</span></li>\
                  <li class="qty">x' + element.countInCart + ' <span>'
                  + element.priceInCart + '</span></li>\
                </ul>\
              </td>\
              <td class="small-cart-remove"><a href="#" class="deleteItemFromCart" title="Удалить" data-delete-item-id="' + element.id
            + '" data-property="' + element.property
            + '" data-variant="' + element.variantId
            + '">&#215;</a></td>\
            </tr>';      
          if(tr.find('.cart_form input[name="item_'+element.id+'[]"]').val() > element.countInCart) {
            tr.find('.cart_form .maxCount').detach();
            tr.find('.cart_form').append('<span class="maxCount" style="display:block;text-align:center;">'+locale.MAX+': '+element.countInCart+'</span>');
          } else {
            tr.find('.cart_form .maxCount').detach();
          }  
          tr.find('.cart_form .up, .cart_form .down').prop('disabled', false).removeClass('disabled');
          tr.find('.cart_form input[name="item_'+element.id+'[]"]').val(element.countInCart);
          tr.find('.price-cell').html(element.priceInCart);
         
      });
      if ($('.small-cart-table tbody').length) {
        $('.small-cart-table tbody').html(dataSmalCart);
      }
      else{
        $('.small-cart-table').html(dataSmalCart);
      }
      $('.total .total-sum span').text(response.data.cart_price_wc);
      $('.pricesht').text(response.data.cart_price);
      $('.countsht').text(response.data.cart_count);
      $('.cart-wrapper .total-sum strong').text(response.data.cart_price_wc);
    }
    }
  })
}

}

$(document).ready(function() { 
  // $('body').on('change', 'input[name=delivery]', function () {
  //   $.ajax({
  //   type: "POST",
  //     url: mgBaseDir + "/cart",
  //     data: {
  //       'deliveryCheckToStorage': $(this).val(),
  //     },
  //     dataType: "json",
  //     cache: false,
  //     success: function(response) {
  //       if(response == 1) {
  //         $('.order-storage').show();
  //       } else {
  //         $('.order-storage').hide();
  //       }
  //     }
  //   });
  // });

  

  // для сброса фильтров
  $('body').on('click', '.refreshFilter', function() {
    location.href = $(this).data('url');
  });
  // if ($('.mg__prop_radio').length && $('.mg__prop_check').length && $('.mg__prop_select').length) {
  //   $('.property-form .mg__prop_radio, .property-form .mg__prop_check, .property-form .mg__prop_select , .product-wrapper .block-variants select').change();
  // }
  // else{
  //   $('.property-form input:not([name=variant]), .property-form select , .product-wrapper .block-variants select').change();
  // }

  $('.variants-table').each(function() {
    var tmp = $(this).find('tr:eq(0)').data('color');
    if (tmp != undefined && tmp != '') {
      $(this).parents('form').find('.color[data-id='+tmp+']').addClass('active');
    } 
    var tmp = $(this).find('tr:eq(0)').data('size');
    if (tmp != undefined && tmp != '') {
      $(this).parents('form').find('.size[data-id='+tmp+']').addClass('active');
    }
  });

  $('body').on('change', '.variants-table tr input[type=radio]', function() {
    setTimeout(function() {
      $('.slides-item[data-slide-index=0]').click();
    }, 0);
  });
  
  $('body').on('click', '.variants-table tr input[type=radio]', function() {
    sizeMapObject = $(this).parents('form');
    sizeMapObject.find('.color').removeClass('active');
    sizeMapObject.find('.size').removeClass('active');

    var tmp = $(this).parents('tr').data('color');
    if (tmp != undefined && tmp != '') {
      sizeMapObject.find('.color[data-id='+$(this).parents('tr').data('color')+']').addClass('active');
    }
    tmp = $(this).parents('tr').data('size');
    if (tmp != undefined && tmp != '') {
      sizeMapObject.find('.size[data-id='+$(this).parents('tr').data('size')+']').addClass('active');
    }
  });  

  

  if(sizeMapMod == 'size') {
    $('body').on('click', '.color', function() {
      sizeMapObject = $(this).parents('form');
      sizeMapObject.find('.color').removeClass('active');
      $(this).addClass('active');
      choseVariant();
    });

    $('body').on('click', '.size', function() {
      sizeMapObject = $(this).parents('form');
      sizeMapShowSize($(this).data('id'));
      sizeMapObject.find('.size').removeClass('active');
      $(this).addClass('active');
      choseVariant();
    });
  } else {
    $('body').on('click', '.size', function() {
      sizeMapObject = $(this).parents('form');
      sizeMapObject.find('.size').removeClass('active');
      $(this).addClass('active');
      choseVariant();
    });

    $('body').on('click', '.color', function() {
      sizeMapObject = $(this).parents('form');
      sizeMapShow($(this).data('id'));
      sizeMapObject.find('.color').removeClass('active');
      $(this).addClass('active');
      choseVariant();
    });
  }

  

  function sizeMapShowSize(id, click) {
    click = typeof click !== 'undefined' ? click : true;
    if(sizeMapObject == undefined) return false;
    sizeMapObject.find('.color').hide();
    var toCheck = '';
    sizeMapObject.find('.variants-table .variant-tr').each(function() {   
      if($(this).data('size') == id/* && $(this).data('size').length*/) {
        if(sizeMapObject.find(this).data('color') != '') {
          sizeMapObject.find('.color[data-id='+sizeMapObject.find(this).data('color')+']').show();
          if($(this).data('count') == 0) {
            sizeMapObject.find('.color[data-id='+sizeMapObject.find(this).data('color')+']').addClass('inactive');
          } else {
            sizeMapObject.find('.color[data-id='+sizeMapObject.find(this).data('color')+']').removeClass('inactive');
          }
          if(toCheck == '') {
            toCheck = sizeMapObject.find('.color[data-id='+sizeMapObject.find(this).data('color')+']');
          }
        }
      }
    });
    if(click) {
      if(toCheck != '') {
        toCheck.click();
      }
    }
    
  }

  function sizeMapShow(id, click) {
    click = typeof click !== 'undefined' ? click : true;
    if(sizeMapObject == undefined) return false;
    sizeMapObject.find('.size').hide();
    var toCheck = '';
    sizeMapObject.find('.variants-table .variant-tr').each(function() {   
      if($(this).data('color') == id/* && $(this).data('size').length*/) {
        if(sizeMapObject.find(this).data('size') != '') {
          sizeMapObject.find('.size[data-id='+sizeMapObject.find(this).data('size')+']').show();
          if($(this).data('count') == 0) {
            sizeMapObject.find('.size[data-id='+sizeMapObject.find(this).data('size')+']').addClass('inactive');
          } else {
            sizeMapObject.find('.size[data-id='+sizeMapObject.find(this).data('size')+']').removeClass('inactive');
          }
          if(toCheck == '') {
            toCheck = sizeMapObject.find('.size[data-id='+sizeMapObject.find(this).data('size')+']');
          }
        }
      }
    });
    if(click) {
      if(toCheck != '') {
        toCheck.click();
      }
    }
    
  }

  function choseVariant() {
    if(sizeMapObject == undefined) return false;
    if(sizeMapObject.find('.color').length != 0) {
      color = '[data-color='+sizeMapObject.find('.color.active').data('id')+']';
    } else {
      color = '';
    }
    if(sizeMapObject.find('.size').length != 0) {
      size = '[data-size='+sizeMapObject.find('.size.active').data('id')+']';
    } else {
      size = '';
    }
    sizeMapObject.find('.variants-table .variant-tr'+color+size+' input[type=radio]').click();
  }

  var sizeMapObject = undefined;

  $('.variants-table').each(function() {
    // $(this).find('[type=radio]:eq(0)').click().trigger('change');
  });

  $('.color-block .color.active').click();

  function getCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
  }

  // для избранного

  $('body').on('click', '.mg-add-to-favorites', function() {
    obj = $(this);
    $.ajax({
      type: "POST",
      url: mgBaseDir + "/favorites/",
      data: {'addFav':'1','id':$(this).data('item-id')},
      dataType: "json",
      cache: false,
      success: function(response) {
        obj.hide();
        obj.parent().find('.mg-remove-to-favorites').show();
        $('.favourite .favourite__count').html('('+response+')');
      }
    });
  });

  $('body').on('click', '.mg-remove-to-favorites', function() {
    obj = $(this);
    $.ajax({
      type: "POST",
      url: mgBaseDir + "/favorites/",
      data: {'delFav':'1','id':$(this).data('item-id')},
      dataType: "json",
      cache: false,
      success: function(response) {
        obj.hide();
        obj.parent().find('.mg-add-to-favorites').show();
        $('.favourite .favourite__count').html('('+response+')');
      }
    });
  });

  $('.mg-add-to-favorites').on('click', function () {
      $('.j-favourite').removeClass('j-favourite--open');
      setTimeout(function() {
        $('.j-favourite').addClass('j-favourite--open');
      },0);
  });
  
  $('[name="delivery"][checked]').parents('label').addClass('active');

  $('.c-variant__column input[name=variant][checked=checked]').each(function() {
      $(this).parents('.c-form').addClass('active');
  });

  // для выбора варианта по якорю 
  if(location.hash != "") {
    $('[data-code='+location.hash.replace('#', '')+']:eq(0)').click();
  } else {
    if($('.variants-table tr input[type=radio]:eq(0)').data('code') != undefined) 
      location.hash = $('.variants-table tr input[type=radio]:eq(0)').data('code');
  }

  $('body').on('click', '.variants-table tr input[type=radio]', function() {
    data = $(this).data('code');
    if(data != undefined) location.hash = data;
  });
  
});