<?php 
	foreach ($data as $prop) {
		switch ($prop['type']) {
			case 'assortment': ?>

					<p>
					<span class="property-title"><?php echo $prop['name']; ?></span><span class="property-delimiter">:</span> 
					<span class="label-black"><?php echo $prop['additional']; ?></span>
					</p>

			<?php 	break;
			case 'assortment-select': ?>

					<p class="select-type"><span class="property-title"><?php echo $prop['name']; ?><span class="property-delimiter">:</span> </span>
					<select name="<?php echo $prop['name']; ?>" class="last-items-dropdown mg__prop_select">

			<?php foreach ($prop['additional'] as $option) {
					echo '<option value="'.$option['value'].'" '.$option['selected'].'>'.$option['itemName'].$option['price'].'</option>';
				} ?>
					</select></p>

			<?php break;
			case 'assortment-radio': ?>
				
					<p class="mg__prop_p_radio"><span class="property-title"><?php echo $prop['name']; ?></span><span class="property-delimiter">:</span><br/>

				<?php foreach ($prop['additional'] as $option) { ?>
					<label class="mg__prop_label_radio <?php echo ($option['checked'] ? 'active': ''); ?>">
					<input class="mg__prop_radio" type="radio" name="<?php echo $option['name']; ?>" value="<?php echo $option['value']; ?>" <?php echo $option['checked']; ?>>
					<span class="label-black"><?php echo $option['itemName'].$option['price']; ?></span></label><br>
				<?php } ?>
					</p>

			<?php break;
			case 'assortment-checkBox': ?>
				
					<p><span class="property-title"><?php echo $prop['name']; ?></span><span class="property-delimiter">:</span><br/>

				<?php foreach ($prop['additional'] as $option) { ?>
					<label><input class="mg__prop_check" type="checkbox" name="<?php echo $option['name']; ?>" value="<?php echo $option['value']; ?>">
                    <span class="label-black"><?php echo $option['itemName'].$option['price']; ?></span></label><br>
				<?php } ?>
					</p>

			<?php break;
			case 'string': ?>

					<p><span class="property-title"><?php echo $prop['name']; ?></span><span class="property-delimiter">:</span> 
					<span class="label-black"><?php echo $prop['text']; ?></span>
					<span class="unit"> <?php echo $prop['unit']; ?></span></p>

			<?php break;	
			default: 

				echo $prop['name'].': <span class="label-black">'.$prop['text'].'</span>';

				break;
		}
	}
?>