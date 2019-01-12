<div style="margin: 10px 15px;">
	<div class="row">
		<div class="large-6 medium-9 columns">
			<p>Процесс обновления может занять длительное время (чем больше данных, тем дольше обновление)</p>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="large-5 medium-9 columns">
			<div class="progress" role="progressbar" tabindex="0" aria-valuenow="20" aria-valuemin="0" aria-valuetext="25 percent" aria-valuemax="100" style="height:3rem;position:relative;">
			  	<span class="progress-meter percentWidth" style="width: 0">
			  	</span>
			  	<p class="progress-meter-text echoPercent" style="font-size:2rem;position:absolute;top:15px;left:50%;">0%</p>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="large-12 columns">
			<button class="button success startAdap">
				<span class="text">Начать обновление данных</span>
			</button>
		</div>
		<spanc class="loaderAdap" style="display:none;margin:10px;">Обработка данных <img src="<?php echo SITE ?>/mg-admin/design/images/loader-small.gif"><br><br></span>
	</div>
</div>

<script type="text/javascript">

var adaptizator = {

	init: function() {

		$('body').on('click', '.startAdap', function() {
			adaptizator.adaptization();
			$('.loaderAdap').show();
			$(this).prop('disabled', true);
		});

	},

	adaptization: function(data = {}) {
		admin.ajaxRequest({
			mguniqueurl: "action/adaptization",
			pluginHandler: 'adaptizator',
			data: data
		},
		function(response) {
			data = response.data;
			// console.log(data);
			allRow = +data['countProperty'] + +data['countPropertyProduct'];

			var rowWorked = 0;
			if(data['stage'] == 'product') {
				rowWorked += +data['countProperty'];
			}
			rowWorked += +data['row'];

			if(allRow <= rowWorked) {
				percent = 100;
			} else {
				percent = rowWorked / (allRow / 100);
				percent = Math.round(percent * 100) / 100;
			}

			$('.echoPercent').html(percent+'%');
			$('.percentWidth').css('width', percent+'%');

			if((data['stage'] == 'product')&&(data['row'] < data['countPropertyProduct'])) {
				adaptizator.adaptization(data);
				return true;
			}

			$('.loaderAdap').hide();
			$('.startAdap').prop('disabled', false);
		});
	},
}

adaptizator.init();
	
</script>