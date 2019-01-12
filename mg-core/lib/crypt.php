<?php

/**
 * Класс CRYPT - предназначен для обратимого шифрования данных
 *
 * @package moguta.cms
 * @subpackage Libraries
 */
class CRYPT {
  static public $_salt = "hY*6nk7!!#2qj";
  private function __construct() {
  }
/**
 * Функция шифрования
 * @param  String $str   Строка с исходным текстом
 * @return String          Строка с результатом Шифрования
 */
static function mgCrypt($str) {
  $data = $str.self::$_salt;
  $result =  base64_encode($data);
  $args = func_get_args();
  return $result;
}
/**
 * Функция дешифрования
 * @param  String $str   Строка с закодированным текстом
 * @return String          Строка с результатом Дешифрования
 */
static function mgDecrypt($str) {
  $data =  base64_decode($str);
  $result = str_replace(self::$_salt, '', $data);
  $args = func_get_args();
  return $result;
}
/**
 * функция  Сохранение русских букв(кирилицы) при использовании json_encode()
 * @param string $data - исходные данные в UTF-8 
 * @return string JSON строка 
 */
static function json_encode_cyr($data) {
  $arr_replace_utf = array('\u0410', '\u0430','\u0411','\u0431','\u0412','\u0432',
    '\u0413','\u0433','\u0414','\u0434','\u0415','\u0435','\u0401','\u0451','\u0416',
    '\u0436','\u0417','\u0437','\u0418','\u0438','\u0419','\u0439','\u041a','\u043a',
    '\u041b','\u043b','\u041c','\u043c','\u041d','\u043d','\u041e','\u043e','\u041f',
    '\u043f','\u0420','\u0440','\u0421','\u0441','\u0422','\u0442','\u0423','\u0443',
    '\u0424','\u0444','\u0425','\u0445','\u0426','\u0446','\u0427','\u0447','\u0428',
    '\u0448','\u0429','\u0449','\u042a','\u044a','\u042b','\u044b','\u042c','\u044c',
    '\u042d','\u044d','\u042e','\u044e','\u042f','\u044f');
  $arr_replace_cyr = array('А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е',
    'Ё', 'ё', 'Ж','ж','З','з','И','и','Й','й','К','к','Л','л','М','м','Н','н','О','о',
    'П','п','Р','р','С','с','Т','т','У','у','Ф','ф','Х','х','Ц','ц','Ч','ч','Ш','ш',
    'Щ','щ','Ъ','ъ','Ы','ы','Ь','ь','Э','э','Ю','ю','Я','я');
  $result = json_encode($data);
  $result = str_replace($arr_replace_utf,$arr_replace_cyr,$result);
  return $result;
}
   
}