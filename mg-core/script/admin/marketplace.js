var marketplaceModule = (function() {
	
	return { 
		init: function() {

			$('.mg-admin-html').on('click', '.section-marketplace .applyFilter, .section-marketplace .search-block .fa-search', function() {
				marketplaceModule.applyFilter();
			});
			$('.mg-admin-html').on('change', '.section-marketplace [name=mpFilter], .section-marketplace [name=mpFilterType]', function() {
				marketplaceModule.applyFilter();
			});
			$('.mg-admin-html').on('keypress', '.section-marketplace input[name=mpFilterName]', function(e) {
				if(e.keyCode==13) {
					marketplaceModule.applyFilter();
				}
			});

			$('.mg-admin-html').on('click', '.section-marketplace .showMore', function() {
				$('.section-marketplace [name=mpFilter]').val($(this).data('tagid')).trigger('change');
				window.scrollTo(0, 0);
			});

			$('.mg-admin-html').on('click', '.section-marketplace .mp-table thead .order', function(e) {
				var n = $(this).data('n');
				var table = $(this).closest('.mp-table');
				var dir = marketplaceModule.sortTable(n, table[0]);
				table.find('thead .order').removeClass('asc desc');
				$(this).addClass(dir);
			});

			$('.mg-admin-html').on('click', '.section-marketplace .resetMpCache', function() {
				admin.ajaxRequest({
					mguniqueurl:"action/resetMpCache"
				},
				function(response) {
					if (response.status == 'success') {
						admin.indication(response.status, 'Каталог обновлен');
					}
					else{
						admin.indication(response.status, 'При обновлении каталога произошла ошибка');
					}
					marketplaceModule.applyFilter();
				});
			});

			$('.mg-admin-html').on('click', '.section-marketplace .startTrial, .section-marketplace .installPlugin, .section-marketplace .addFreePlugin', function() {
				var trial = 'no';
				if ($(this).hasClass('startTrial')) {trial = 'yes';}
				var type = $(this).data('mptype');
				admin.ajaxRequest({
					mguniqueurl:"action/mpInstallPlugin",
					code: $(this).closest('.item-container').data('mpcode'),
					trial: trial
				},
				function(response) {
					if (response.status == 'success') {
						if (trial == 'yes') {
							admin.indication(response.status, 'Установлена пробная версия, включить можно в разделе "Плагины"');
						}
						else{
							if (type == 'p') {
								admin.indication(response.status, 'Плагин установлен, вы можете включить его в разделе "Плагины"');
							}
							if (type == 't') {
								admin.indication(response.status, 'Шаблон установлен, вы можете включить его в разделе "Настройки"->"Шаблоны"');
							}
						}
						admin.closeModal('.section-marketplace #mp-descr-modal');
						if ($('.section-marketplace select[name="mpFilter"]').val() == 'main') {
							admin.show("marketplace.php", cookie("type"), "mpFilter=main");
						}
						else{
							marketplaceModule.applyFilter();
						}
					}
					else{
						admin.closeModal('.section-marketplace #mp-descr-modal');
						admin.indication(response.status, 'При установке произошла ошибка');
						window.scrollTo(0, 0);
						setTimeout(function () {
							window.location.reload(true);
						}, 1500);
					}
				});
			});

			$('.mg-admin-html').on('click', '.section-marketplace .showMpDescr', function() {
				var tr = $(this).closest('tr');
				var title = tr.find('.title').html();
				var price = tr.find('td.price').html();
				var button = tr.find('td.actions .buttons').html();
				var trial = '';
				if (tr.find('.startTrial').length) {
					trial = tr.find('.startTrial').clone();
				}
				
				admin.ajaxRequest({
					mguniqueurl:"action/mpGetDescr",
					code: $(this).closest('.item-container').data('mpcode')
				},
				function(response) {
					if (response.status == 'error') {return false;}
					$('.section-marketplace #mp-descr-modal .reveal-header h2 span').html(title);
					$('.section-marketplace #mp-descr-modal .reveal-body .item-container').data('mpcode', tr.data('mpcode'));
					$('.section-marketplace #mp-descr-modal .reveal-body .item-container img').attr('src', response.data.img).attr('alt', title);
					$('.section-marketplace #mp-descr-modal .reveal-body .item-container .price').html('').html(price);
					$('.section-marketplace #mp-descr-modal .reveal-body .item-container .buttons').html('').html(button);
					$('.section-marketplace #mp-descr-modal .reveal-body .item-container .trial').html('').html(trial);
					$('.section-marketplace #mp-descr-modal .reveal-body .descrContainer').html(response.data.description);
					admin.openModal('.section-marketplace #mp-descr-modal');
				});
			});
		},

		applyFilter: function() {
			var mpFilter = $('.section-marketplace [name=mpFilter]').val();
			var mpFilterName = '';
			var mpFilterType = '';
			if ($('.section-marketplace [name=mpFilterName]').val()) {
				mpFilterName = '&mpFilterName='+$('.section-marketplace [name=mpFilterName]').val();
			}
			if ($('.section-marketplace [name=mpFilterType]').val()) {
				mpFilterType = '&mpFilterType='+$('.section-marketplace [name=mpFilterType]').val();
			}
			admin.show("marketplace.php", cookie("type"), "mpFilter="+mpFilter+mpFilterName+mpFilterType);
		},

		sortTable: function(n, table) {
			var rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
			switching = true;
			dir = "asc";
			while (switching) {
				switching = false;
				rows = table.getElementsByTagName("TR");
				for (i = 1; i < (rows.length - 1); i++) {
					shouldSwitch = false;
					x = rows[i].getElementsByTagName("TD")[n];
					y = rows[i + 1].getElementsByTagName("TD")[n];
					if (dir == "asc") {
						if ($.isNumeric(x.dataset.sortval) && $.isNumeric(y.dataset.sortval)) {
							if (parseFloat(x.dataset.sortval) > parseFloat(y.dataset.sortval)) {
								shouldSwitch = true;
								break;
							}
						}
						else{
							if (x.dataset.sortval.toLowerCase() > y.dataset.sortval.toLowerCase()) {
								shouldSwitch = true;
								break;
							}
						}
					} 
					else if (dir == "desc") {
						if ($.isNumeric(x.dataset.sortval) && $.isNumeric(y.dataset.sortval)) {
							if (parseFloat(x.dataset.sortval) < parseFloat(y.dataset.sortval)) {
								shouldSwitch = true;
								break;
							}
						}
						else{
							if (x.dataset.sortval.toLowerCase() < y.dataset.sortval.toLowerCase()) {
								shouldSwitch = true;
								break;
							}
						}
					}
				}
				if (shouldSwitch) {
					rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
					switching = true;
					switchcount ++;      
				} 
				else {
					if (switchcount == 0 && dir == "asc") {
						dir = "desc";
						switching = true;
					}
				}
			}
			return dir;
		}

	};
})();

$(document).ready(function() {
	marketplaceModule.init();
});