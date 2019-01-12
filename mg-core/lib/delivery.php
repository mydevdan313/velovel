<?php
/**
 * Класс Delivery - получает параметры способа доставки по его id, вычисляет стоимость доставки по условию бесплатной доставки.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */

class Delivery {

  /**
   * Получает параметры способа доставки по его id.
   * <code>
   *  $deliv = new Delivery();
   *  $res = $deliv->getDeliveryById(1);
   *  viewData($res);
   * </code>
   * @param int $id доставки
   * @return array
   */
  public function getDeliveryById($id){
    $result = array();
    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'delivery`
      WHERE id = '.DB::quote($id));

    if(!empty($res)){
      if($deliv = DB::fetchAssoc($res)){
        $this->tmp = $result = $deliv;

        if(!empty($_POST['orderItems'])){
          $itemsCart['items'] = $_POST['orderItems'];
        } else {
          $cart = new Models_Cart();
          $itemsCart = $cart->getItemsCart();
        }    

        $sumWeight = 0;

        for($i=0; $i<count($itemsCart['items']); $i++){
          $sumWeight += $itemsCart['items'][$i]['weight']*$itemsCart['items'][$i]['countInCart'];
        }

        if (!MG::isAdmin() && $result['weight']) {
          $weights = json_decode($result['weight'],1);
          foreach ($weights as $key => $value) {
            if ($sumWeight >= $value['w']) {
              $result['cost'] = $value['p'];
            }
          }
        }
      }
    }

    $args = func_get_args();
    return MG::createHook( __CLASS__ ."_". __FUNCTION__, $result, $args );
  }

  /**
   * Бесплатная доставка если проходит по условию в найстройках.
   * <code>
   *  $deliv = new Delivery();
   *  $res = $deliv->getCostDelivery(1);
   *  viewData($res);
   * </code>
   * @param int $id id доставки
   * @return numeric
   */
  public function getCostDelivery($id) {
    $delivery = $this->getDeliveryById($id);
    $cart = new Models_Cart;
    $cartSumm = $cart->getTotalSumm();   
    if($delivery['free']!=0 && $delivery['free'] <= $cartSumm){
      return 0;
    }
    return $delivery['cost'];
  }
  /**
   * Получение частей адреса для доставки.
   * <code>
   *  $deliv = new Delivery();
   *  $res = $deliv->getDeliveryAddressParts(1);
   *  viewData($res);
   * </code>
   * @param int $id id доставки
   * @return numeric
   */
  public function getDeliveryAddressParts($id) {
    if ($this->tmp['id'] == $id) {
      $delivery = $this->tmp;
    }
    else{
      $delivery = $this->getDeliveryById($id);
    }
    return $delivery['address_parts'];
  }
}