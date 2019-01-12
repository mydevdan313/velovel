<?php
  MG::disableTemplate();
  $letters = 'ABCDEFGKIJKLMNOPQRSTUVWXYZ'; // àëôàâèò

  $caplen = 6; //äëèíà òåêñòà
  $width = 170; $height = 36; //øèðèíà è âûñîòà êàðòèíêè
  $font = 'mg-pages/comic.ttf';//øðèôò òåêñòà
  $fontsize = 14;// ðàçìåð òåêñòà

  //header('Content-type: image/png'); //òèï âîçâðàùàåìîãî ñîäåðæèìîãî (êàðòèíêà â ôîðìàòå PNG) 

  $im = imagecreatetruecolor($width, $height); //ñîçäà¸ò íîâîå èçîáðàæåíèå
  imagesavealpha($im, true); //óñòàíàâëèâàåò ïðîçðà÷íîñòü èçîáðàæåíèÿ
  $bg = imagecolorallocatealpha($im, 0, 0, 0, 127); //èäåíòèôèêàòîð öâåòà äëÿ èçîáðàæåíèÿ
  imagefill($im, 0, 0, $bg); //âûïîëíÿåò çàëèâêó öâåòîì
  
  //putenv( 'GDFONTPATH=' . realpath('.') ); //ïðîâåðÿåò ïóòü äî ôàéëà ñî øðèôòàìè

  if ($_SESSION['capcha'] && (($_SESSION['capcha_created']+10) > time())) {
    $captcha = $_SESSION['capcha'];
    for ($i = 0; $i < $caplen; $i++)
    { 
      $x = ($width - 20) / $caplen * $i + 10;//ðàñòîÿíèå ìåæäó ñèìâîëàìè
      $x = rand($x, $x+4);//ñëó÷àéíîå ñìåùåíèå
      $y = $height - ( ($height - $fontsize) / 2 ); // êîîðäèíàòà Y
      $curcolor = imagecolorallocate( $im, rand(0, 100), rand(0, 100), rand(0, 100) );//öâåò äëÿ òåêóùåé áóêâû
      $angle = rand(-25, 25);//ñëó÷àéíûé óãîë íàêëîíà 
      imagettftext($im, $fontsize, $angle, $x, $y, $curcolor, $font, $captcha[$i]); //âûâîä òåêñòà
    }
  }
  else{
    $captcha = '';//îáíóëÿåì òåêñò
    for ($i = 0; $i < $caplen; $i++)
    {
      $captcha .= $letters[ rand(0, strlen($letters)-1) ]; // äîïèñûâàåì ñëó÷àéíûé ñèìâîë èç àëôàâèëà 
      $x = ($width - 20) / $caplen * $i + 10;//ðàñòîÿíèå ìåæäó ñèìâîëàìè
      $x = rand($x, $x+4);//ñëó÷àéíîå ñìåùåíèå
      $y = $height - ( ($height - $fontsize) / 2 ); // êîîðäèíàòà Y
      $curcolor = imagecolorallocate( $im, rand(0, 100), rand(0, 100), rand(0, 100) );//öâåò äëÿ òåêóùåé áóêâû
      $angle = rand(-25, 25);//ñëó÷àéíûé óãîë íàêëîíà 
      imagettftext($im, $fontsize, $angle, $x, $y, $curcolor, $font, $captcha[$i]); //âûâîä òåêñòà
    }
    $_SESSION['capcha_created'] = time();
  }

  // îòêðûâàåì ñåññèþ äëÿ ñîõðàíåíèÿ ñãåíåðèðîâàííîãî òåêñòà
  session_start();
  $_SESSION['capcha'] = $captcha;

  imagepng($im); //âûâîäèì èçîáðàæåíèå
  imagedestroy($im);//îò÷èùàåì ïàìÿòü