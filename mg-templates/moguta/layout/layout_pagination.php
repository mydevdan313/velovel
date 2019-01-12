<div class="mg-pager">
	<div class="allPages"><?php echo lang('totalPages'); ?> <span><?php echo $data['totalPages'] ?></span></div>
	<ul class="clearfix">
		<?php 

			if ($data['first']['needed'] == true) {
				echo "<li".$data['first']['liClass']."><a class='".$data['first']['class']."' ".$data['first']['href']." >".$data['first']['ancor']."</a></li>";
			}

			if ($data['prev']['needed'] == true) {
				echo "<li".$data['prev']['liClass']."><a class='".$data['prev']['class']."' ".$data['prev']['href']." >".$data['prev']['ancor']."</a></li>";
			}

			if (strlen($data['leftpoint'] > 5)) {
				echo "<span class='point'>...</span>";
			}

			if ($data['firstPages']['needed'] == true) {
				echo "<li".$data['firstPages']['liClass']."><a class='".$data['firstPages']['class']."' ".$data['firstPages']['href']." >".$data['firstPages']['ancor']."</a></li>";
			}

			foreach ($data['pager'] as $pager) {
				echo "<li".$pager['liClass']."><a class='".$pager['class']."' ".$pager['href']." >".$pager['ancor']."</a></li>";
			}

			if ($data['lastPages']['needed'] == true) {
				echo "<li".$data['lastPages']['liClass']."><a class='".$data['lastPages']['class']."' ".$data['lastPages']['href']." >".$data['lastPages']['ancor']."</a></li>";
			}

			if (strlen($data['rightpoint'] > 5)) {
				echo "<span class='point'>...</span>";
			}

			if ($data['next']['needed'] == true) {
				echo "<li".$data['next']['liClass']."><a class='".$data['next']['class']."' ".$data['next']['href']." >".$data['next']['ancor']."</a></li>";
			}

			if ($data['last']['needed'] == true) {
				echo "<li".$data['last']['liClass']."><a class='".$data['last']['class']."' ".$data['last']['href']." >".$data['last']['ancor']."</a></li>";
			}

		?>
	</ul>
</div>