<?php 

	$fields = unserialize(stripcslashes(MG::getSetting('optionalFields'))); 

	foreach ($fields as $item) {
		if($item['active'] == 1) {
			if($item['required'] == 1) {
				$required = 'required';
			} else {
				$required = '';
			}
			switch ($item['type']) {
				case 'input':
					echo '<li class="c-order__list--item c-order__input"><input name='.MG::translitIt(trim($item['name'])).' placeholder="'.$item['name'].'" type="text" '.$required.'></li>';			
					break;
				case 'textarea':
					echo '<li class="c-order__list--width c-order__textarea"><textarea name='.MG::translitIt(trim($item['name'])).' placeholder="'.$item['name'].'"'.$required.'></textarea></li>';			
					break;
				case 'radiobutton':
					echo '<li class="c-order__list--item c-order__radiobutton">';
					foreach ($item['variants'] as $variant) {
						echo '<label><input name='.MG::translitIt(trim($item['name'])).' value="'.htmlspecialchars($variant).'" type="radio" '.$required.'>'.htmlspecialchars($variant).'</label>';
					}
					echo '</li>';				
					break;
				case 'checkbox':
						echo '<li class="c-order__list--item c-order__checkbox"><label><input name='.MG::translitIt($item['name']).' value="'.$item['name'].'" type="checkbox" '.$required.'>'.$item['name'].'</label></li>';
					break;
				case 'select':
					echo '<li class="c-order__list--item c-order__select"><select name='.MG::translitIt(trim($item['name'])).'>';
					foreach ($item['variants'] as $variant) {
						echo '<option placeholder="'.$item['name'].'" '.$required.' value="'.htmlspecialchars($variant).'">'.htmlspecialchars($variant).'</option>';			
					}
					echo '</select></li>';
					break;
				
				default:
					echo '<li class="c-order__list--item c-order__default"><input name='.MG::translitIt(trim($item['name'])).' placeholder="'.$item['name'].'" type="text" '.$required.'></li>';		
					break;
			}
		} 
	} 

?>