<?php if(USER::access('wholesales') > 0 && count($data['data']) > 0) { ?>
<div class="whole-info">
	<table>
		<thead>
			<th>
				<?php 
					switch ($data['type']) {
						case 'sum':
							echo lang('wholesalesTypeSum');
							break;
						case 'cartSum':
							echo lang('wholesalesTypeCartSum');
							break;
						
						default:
							echo lang('wholesalesTypeCount');
							break;
					}
				?>
			</th>
			<th>Цена</th>
		</thead>
		<?php foreach ($data['data'] as $item) { ?>
			<tr>
				<td><?php echo $item['count']; ?></td>
				<td><?php echo $item['price']; ?></td>
			</tr>
		<?php } ?>
	</table>
</div>
<?php } ?>