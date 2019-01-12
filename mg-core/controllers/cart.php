<?php

/**
 * Контроллер: Cart
 *
 * Класс Controllers_Cart обрабатывает действия пользователей на странице корзины интернет-магазина.
 * - Пересчитывает суммарную стоимость товаров в корзине;
 * - Очищает корзину;
 * - Формирует ленту сопутствующих товаров;
 * - Подготавливает массив данных $data для вывода в шаблоне. 
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Cart extends BaseController {

  /**
   * Определяет поведение при изменении и удаление данных в корзине,
   * а так же выводит список позиций к заказу.
   *
   * @return void
   */
  public function __construct() {

    // Обновление данных.
    if (!empty($_REQUEST['updateCart'])) {
      $this->updateCart();
      exit;
    }

    // Удаление данных.
    if (!empty($_REQUEST['delFromCart'])) {
      $this->delFromCart();
      exit;
    }
    
    // Пересчет скидки купона.
    if (!empty($_POST['coupon'])) {
      $this->applyCoupon();
    }

    // Обновление данных.
    if (!empty($_REQUEST['deliveryCheckToStorage'])) {
      $res = DB::query('SELECT name FROM '.PREFIX.'delivery WHERE id = '.DB::quoteInt($_REQUEST['deliveryCheckToStorage']));
      $row = DB::fetchAssoc($res);
      if(substr_count($row['name'], 'Без доставки') == 1) {
        $result = 1;
      } else {
        $result = 0;
      }
      echo json_encode($result);
      exit;
    }

    $model = new Models_Cart;
    
    // Если пользователь изменил данные в корзине.
    if (!empty($_REQUEST['refresh'])) {
      $update = array();
      $refreshData = $_REQUEST;

      // Пробегаем по массиву, находим пометки на удаление и на изменение количества.
      foreach ($refreshData as $key => $val) {
        $id = '';
        if ('item_' == substr($key, 0, 5)) {
          $id = substr($key, 5);
          // Находим propertyReal для текущего ID продукта.
          $propertyReal = array();
          $variantId = array();
          if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
              if ($item['id'] == $id) {
                $propertyReal[] = $item['propertyReal'];
                $variantId[] = $item['variantId'];
              }
            }
          }

          if (!empty($val)) {
            $product = new Models_Product();

            foreach ($val as $k => $count) {
              $propertySetId = $refreshData['property_' . $id][$k];

              if ($count > 0) {

                $tempProduct = $product->getProduct($id);
                $countMax = $tempProduct['count'];

                if ($variantId[$k]) {
                  $tempProdVar = $product->getVariants($id);
                  $countMax = $tempProdVar[$variantId[$k]]['count'];
                }

                if ($count > $countMax && $countMax > 0) {
                  $count = $countMax;
                }

                $update[] = array(
                  'id' => $id,
                  'count' => ($count >= 0) ? $count : 0,
                  'property' => $_SESSION['propertySetArray'][$propertySetId],
                  'propertyReal' => $propertyReal[$k],
                  'propertySetId' => $propertySetId,
                  'variantId' => $variantId[$k]
                );
              } else {
                if (!empty($_SESSION['propertySetArray'][$propertySetId])) {
                  unset($_SESSION['propertySetArray'][$propertySetId]);
                }
              }
            }
          }
        } elseif ('del_' == substr($key, 0, 4)) {
          $id = substr($ItemId, 4);
          $count = 0;
        }
      }
      // Передаем в модель данные для обновления корзины.
      $model->refreshCart($update);
      if (!empty($_REQUEST['count_change'])) {
        $data = SmalCart::getCartData();
        $data['cart'] = $_SESSION['cart'];

        $totalOrderPrice = 0;
        foreach ($data['cart'] as $value) {
          $totalOrderPrice += ($value['count']*$value['priceWithDiscount']);
        }

        $deliverys = Models_Order::getDeliveryMethod();
        $deliv = array();
        foreach ($deliverys as $value) {
          if($value['activity'] == 0){continue;}
          if ($value['free'] > 0 && $totalOrderPrice > $value['free']) {
            $deliv[] = 0;
          }
          else{
            $deliv[] = MG::numberFormat((float)$value['cost']);
          }
        }

        $short = MG::getSetting('currencyShort');
        $curr = MG::getSetting('currencyShopIso');
        $curr = $short[$curr];

        $response = array(
          'status' => 'success',
          'data' => $data,
          'deliv' => $deliv,
          'curr' => $curr,
          );
        echo json_encode($response);
        exit;
      }

      // Пересчитываем маленькую корзину.
      header('Location: ' . SITE . '/cart');
      exit;
    }
    
    if (!empty($_REQUEST['clear'])) {
      $model->clearCart();
      // Пересчитываем маленькую корзину.
      SmalCart::setCartData();
      header('Location: ' . SITE . '/cart');
      exit;
    }

    $settings = MG::get('settings');
    $cartData = $model->getItemsCart();
    
    // Подготавливает список связанных товаров.
    foreach ($cartData['items'] as $item) {
      $related .= ',' . $item['related'];
      $relatedCat .= ',' . $item['related_cat'];
    }

    if (!empty($related)) {
      $codes = explode(',', $related);
      $codes = array_unique($codes);
      $related = implode(',', $codes);
            
    }
     if (!empty($relatedCat)) {
      $cat_id = explode(',', $relatedCat);
      $cat_id = array_unique($cat_id);
      $relatedCat = implode(',', $cat_id); 
    }
    if (!empty($related)||$relatedCat) {
      $product = new Models_Product();
      $related = $product->createRelatedForm(array('product'=>$related, 'category'=>$relatedCat),
                  'Добавить в заказ похожие товары', 'layout_relatedcart');
    }
    

    $this->data = array(
      'isEmpty' => $model->isEmptyCart(),
      'productPositions' => $cartData['items'],
      'totalSumm' => $cartData['totalSumm'],
      'related' => $related,
      'meta_title' => 'Корзина',
      'meta_keywords' => !empty($model->currentCategory['meta_keywords']) ? $model->currentCategory['meta_keywords'] : "корзина,покупки,заказ,купленные товары",
      'meta_desc' => !empty($model->currentCategory['meta_desc']) ? $model->currentCategory['meta_desc'] : "Положите понравившиеся товары в корзину и вы сможете оформить заказ.",
      'currency' => $settings['currency']
    );
  }

  /**
   * Обновляет корзину в соответствии с внесенными изменениями.
   * <code>
   * $model = new Controllers_Cart;
   * $model->updateCart();
   * </code>
   */
  public function updateCart() {

    $cart = new Models_Cart;
    // Если был выбран вариант, то запоминаем его ID.
    $variantId = null;
    if (!empty($_POST["variant"])) {
      $variantId = $_POST["variant"];
      unset($_POST["variant"]);
    }

    // Если происходит обновление по заданному id набора характеристик.
    if (isset($_POST['propertySetId'])) {
      foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['propertySetId'] == $_POST['propertySetId'] && $item['id'] == $_POST['inCartProductId']) {
          $_SESSION['cart'][$key]['count'] = (is_numeric($_REQUEST['amount_input'])) ? 
                  intval($_REQUEST['amount_input']) : 1;
        }
      }
      $response = array(
        'status' => 'success',
        'data' => SmalCart::getCartData()
      );
      
      echo json_encode($response);
      exit;
    }

    // Если характеристик не передано , то возможно 
    // была нажата кнопка купить из мини карточки, в этом случае самостоятельно вычисляем набор
    // параметров, которые были бы указаны при открытии карточки товара.    
    if (empty($_POST)&&isset($_REQUEST['inCartProductId']) || (isset($_POST['updateCart']) && isset($_POST['inCartProductId']) && (count($_POST) == 3 || count($_POST) == 2) )) {

      $modelProduct = new Models_Product;
      $product = $modelProduct->getProduct(intval($_REQUEST['inCartProductId']));
      $blockVariants = $modelProduct->getBlockVariants($product['id']);

      if (!$variantId) {
        $variants = $modelProduct->getVariants($product['id']);
        $variantsKey = array_keys($variants);
        $variantId = $variantsKey[0];
      }
      $blockedProp = $modelProduct->noPrintProperty();

      $propertyFormData = $modelProduct->createPropertyForm($param = array(
        'id' => $product['id'],
        'maxCount' => $product['count'],
        'productUserFields' => $product['thisUserFields'],
        'action' => "/catalog",
        'method' => "POST",
        'ajax' => true,
        'blockedProp' => $blockedProp,
        'noneAmount' => false,
        'titleBtn' => MG::getSetting('buttonBuyName'),
        'blockVariants' => $blockVariants,
        'currency_iso' => $product['id'],
      ));
      $_POST = $propertyFormData['defaultSet'];
      $_POST['inCartProductId'] = $product['id'];
    } elseif (empty($_POST)) {
      header('Location: ' . SITE . '/cart');
      exit;
    }

    $property = $cart->createProperty($_POST);
    $result = $cart->addToCart($_REQUEST['inCartProductId'], intval($_REQUEST['amount_input']), $property, $variantId);
    if ($result) {
      $response = array(
        'status' => 'success',
        'data' => SmalCart::getCartData()
      );
      echo json_encode($response);
      exit;
    } 
    
  }

  /**
   * Удаляет товар из корзины.
   * <code>
   * $model = new Controllers_Cart;
   * $model->delFromCart();
   * </code>
   */
  public function delFromCart() {
    $cart = new Models_Cart;
    $property = $_SESSION['propertySetArray'][$_POST['property']];
    $cart->delFromCart($_POST['itemId'], $property, $_POST['variantId']);

    $data = SmalCart::getCartData();
    $data['cart'] = $_SESSION['cart'];

    $totalOrderPrice = 0;
    foreach ($data['cart'] as $value) {
      $totalOrderPrice += ($value['count']*$value['priceWithDiscount']);
    }

    $deliverys = Models_Order::getDeliveryMethod();
    $deliv = array();
    foreach ($deliverys as $value) {
      if($value['activity'] == 0){continue;}
      if ($value['free'] > 0 && $totalOrderPrice > $value['free']) {
        $deliv[] = 0;
      }
      else{
        $deliv[] = MG::numberFormat((float)$value['cost']);
      }
    }

    $short = MG::getSetting('currencyShort');
    $curr = MG::getSetting('currencyShopIso');
    $curr = $short[$curr];

    $response = array(
      'status' => 'success',
      'data' => $data,
      'deliv' => $deliv,
      'curr' => $curr,
      );
    
    echo json_encode($response);
    exit;
  }

  /**
   * Применение купона.
   * <code>
   * $model = new Controllers_Cart;
   * $model->applyCoupon();
   * </code>
   */
  public function applyCoupon() {
    $_SESSION['couponCode'] = $_POST['couponCode'];
  }

}