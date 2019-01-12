<?php
	class Pactioner extends Actioner {  

		function adaptization() {
			$this->data = adaptizator::adaptization($_POST['data']);
		  	return true;
		}

	}
?>