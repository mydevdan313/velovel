<div class="mg-filter">
<?php 
foreach ($data['props'] as $prop) {
	$viewCount = MG::getSetting('filterCountProp');
	$counter = 0;
	if(empty($prop)) continue;
	echo '<div class="mg-filter-item" style="'.$prop['style'].'">';
	echo '<span class="mg-filter-title">'.$prop['name'];
	if(!empty($prop['description'])) {
		echo '<div class="mg-tooltip">?<div class="mg-tooltip-content" style="display:none;">'.$prop['description'].'</div></div>';
	}
	echo '</span>';
	echo '<ul>';
	if (!empty($prop['data'])) {
		if ($prop['type'] == 'select') {
			echo '<li><select name="prop['.$prop['idProp'].'][] " class="mg-filter-prop-select">';
        	echo '<option value="">'.lang('filterNotSelected').'</option>';
        	foreach ($prop['data'] as $propData) {
        		echo ' <option  value="'.$propData['value_id'].'|'.$propData['value_type'].'" '.$propData['selected'].'>'.$propData['value_name'].'</option>';
        	}
        	echo '</select></li>';
		} else {
			foreach ($prop['data'] as $propData) {
				// viewData($propData);
				if($propData['value_name'] == '') continue;
				$counter++;
				switch ($propData['type']) {
					case 'active':
						echo ' <li style="'.($counter > $viewCount?'display:none;':'').'"><label '.$propData['active'].'>'.$color.'<input  type="checkbox" name="prop['.$prop['idProp'].'][]" value="'.$propData['value_id'].'|'.$propData['value_type'].'" '.$propData['checked'].'  class="mg-filter-prop-checkbox"/>'.$propData['value_name'].'<span class="cbox"></span>&nbsp;<span class="unit"> '.$propData['value_unit'].'</span></label>'.'</li>';
						break;
					case 'normal':
						if($propData['img'] != '') {
							echo ' <li title="'.$propData['value_name'].'" '.(!empty($propData['color']) ? ' class="color-filter"' : '').' style="'.($counter > $viewCount?'display:none;':'').'"><label>'.$color.'<input type="checkbox" name="prop['.$prop['idProp'].'][]" value="'.$propData['value_id'].'|'.$propData['value_type'].'" '.$propData['checked'].'  class="mg-filter-prop-checkbox"/><span class="value-name">'.$propData['value_name'].'</span><span class="cbox" style="background: url('.SITE.'/'.$propData['img'].');background-size:cover;"></span>&nbsp;<span class="unit"> '.$propData['value_unit'].'</span></label></li>';
						} else {
							echo ' <li title="'.$propData['value_name'].'" '.(!empty($propData['color']) ? ' class="color-filter"' : '').' style="'.($counter > $viewCount?'display:none;':'').'"><label>'.$color.'<input type="checkbox" name="prop['.$prop['idProp'].'][]" value="'.$propData['value_id'].'|'.$propData['value_type'].'" '.$propData['checked'].'  class="mg-filter-prop-checkbox"/><span class="value-name">'.$propData['value_name'].'</span><span class="cbox" style="background-color: '.$propData['color'].'"></span>&nbsp;<span class="unit"> '.$propData['value_unit'].'</span></label></li>';
						}
						break;
					case 'slider|easy':
					case 'slider|hard':
						echo '<div class="wrapper-field range-field"><div class="price-slider-wrapper"><li>
			                <input type="hidden" name="prop['.$prop['idProp'].'][0]" value="'.$propData['type'].'" />
			                <ul class="price-slider-list">
			                     <li><span class="label-field">от</span><input type="text" id="Prop'.$prop['idProp'].'-min" class="price-input start-price numericProtection  price-input" data-fact-min="'.$propData['min'].'" name="prop['.$prop['idProp'].'][]" value="'.$propData['fMin'].'"></li>
			                     <li><span class="label-field">до</span><input type="text" id="Prop'.$prop['idProp'].'-max" class="price-input end-price numericProtection  price-input" data-fact-max="'.$propData['max'].'" name="prop['.$prop['idProp'].'][]" value="'.$propData['fMax'].'"><span>'.$propData['value_unit'].'</span></li>
			                </ul>
			                <div class="clear"></div>
			                <div name="prop['.$prop['idProp'].'][] " class="mg-filter-prop-slider" data-id="'.$prop['idProp'].'" data-min="'.$propData['min'].'" data-max="'.$propData['max'].'" data-factmin="'.$propData['fMin'].'" data-factmax="'.$propData['fMax'].'"></div>
			              </li></div></div>';
						break;
					
					default:
						if($propData['img'] != '') {
							echo ' <li title="'.$propData['value_name'].'" '.(!empty($propData['color']) ? ' class="color-filter disabled"' : '').' style="'.($counter > $viewCount?'display:none;':'').'"><label class="disabled-prop">'.$color.'<input disabled type="checkbox" name="prop['.$prop['idProp'].'][]" value="'.$propData['value_id'].'|'.$propData['value_type'].'" '.$propData['checked'].'  class="mg-filter-prop-checkbox"/><span class="value-name">'.$propData['value_name'].'</span><span class="cbox" style="background: url('.SITE.'/'.$propData['img'].');background-size:cover;"></span>&nbsp;<span class="unit"> '.$propData['value_unit'].'</span></label></li>';
						} else {
							echo ' <li title="'.$propData['value_name'].'" '.(!empty($propData['color']) ? ' class="color-filter disabled"' : '').' style="'.($counter > $viewCount?'display:none;':'').'"><label class="disabled-prop">'.$color.'<input disabled type="checkbox" name="prop['.$prop['idProp'].'][]" value="'.$propData['value_id'].'|'.$propData['value_type'].'" '.$propData['checked'].'  class="mg-filter-prop-checkbox"/><span class="value-name">'.$propData['value_name'].'</span><span class="cbox" style="background-color: '.$propData['color'].'"></span>&nbsp;<span class="unit"> '.$propData['value_unit'].'</span></label></li>';
						}
						
						break;
				}
			}
			if($counter > $viewCount) {
				echo '<a href="javascript:void(0);" class="mg-viewfilter">'.lang('viewFilterAll').'</a>';
			}
		}
	}
	echo ' </ul></div>';
}
echo $data['allFilter'];
?>
</div>