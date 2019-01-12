/**
 * Модуль для бэкапов
 */
var Backup = (function () {
	return {
		block: false,
		createBlock: false,
		totalTables: 0,
		totalFiles: 0,
		zipName: '',

		init: function() {

			$('body').on('click', '#tab-system-settings .calcDumpSize', function() {
				if (Backup.block) {return false;}
				$('#tab-system-settings .backup .dumpSizePH').hide();
				$('#tab-system-settings .backup .dumpSizeCalculating').show();
				admin.ajaxRequest({
					mguniqueurl: "action/getDumpSize",
				},
				function (response) {
					$('#tab-system-settings .backup .dumpSizeResult').show().find('.number').html(response.data+' MB');
					$('#tab-system-settings .backup .dumpSizeCalculating').hide();
					$('#tab-system-settings .calcDumpSize').removeClass('calcDumpSize');
				});
			});

			$('body').on('click', '#tab-system-settings .stopNewBackup', function() {
				Backup.createBlock = true;
			});

			$('body').on('click', '#tab-system-settings .createNewBackup', function() {
				if (Backup.block) {return false;}
				if (!confirm(lang.BACKUP_CREATE_CONFIRM)) {return false;}
				Backup.createBlock = false;
				Backup.blockAll(true);
				Backup.progressbar(0);
				$('#tab-system-settings .backup .backupLog').html('').show();
				$('#tab-system-settings .backup .progress').show();
				$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_CREATE_START);
				admin.ajaxRequest({
					mguniqueurl: "action/startBackup",
				},
				function (response) {
					if (response.data.errors != null) {
						$('#tab-system-settings .backup .backupLog').append(response.data.errors);
						Backup.blockAll(false);
					}
					else{
						$('#tab-system-settings .stopNewBackup').show();
						$('#tab-system-settings .backup .header_create').show();
						Backup.progressbar(0);
						Backup.totalTables = response.data.length;
						Backup.createTables(response.data);
					}
				});
			});

			$('body').on('click', '#tab-system-settings .backupTable .drop', function() {
				if (Backup.block) {return false;}
				if (!confirm(lang.BACKUP_DROP)) {return false;}
				admin.ajaxRequest({
					mguniqueurl: "action/dropBackup",
					zip: $(this).attr('zip'),
				},
				function (response) {
					$('#tab-system-settings .backupTable tbody').html(response.data);
				});
			});

			$('body').on('click', '#tab-system-settings .backupTable .unpack', function() {
				if (Backup.block) {return false;}
				var edition = $(this).parents('tr').find('td:eq(2)').text();
				var version = $(this).parents('tr').find('td:eq(3)').text();
				var time = $(this).parents('tr').find('td:eq(4)').text();
				if (!confirm(lang.BACKUP_RESTORE_CONFIRM_1+edition+' '+version+lang.BACKUP_RESTORE_CONFIRM_2+time+lang.BACKUP_RESTORE_CONFIRM_3)) {return false;}
				Backup.blockAll(true);
				Backup.progressbar(0);
				$('#tab-system-settings .backup .warnings').hide();
				$('#tab-system-settings .backup .backupLog').html('').show();
				$('#tab-system-settings .backup .progress').show();
				Backup.zipName = $(this).attr('zip');
				$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_RESTORE_START);
				admin.ajaxRequest({
					mguniqueurl: "action/getBackupZipArrays",
					zip: $(this).attr('zip'),
				},
				function (response) {
					if (response.data.errors != null) {
						Backup.blockAll(false);
						$('#tab-system-settings .backup .backupLog').append(response.data.errors);
						$('#tab-system-settings .backup .warnings').show();
					}
					else{
						$('#tab-system-settings .backup .header_restore').show();
						Backup.totalFiles = response.data.miscfiles;
						Backup.restoreFromZipCore();
					}
				});
			});

			$('body').on('click', '#tab-system-settings .backupTable .download', function() {
				location.href = mgBaseDir+'/backups/'+$(this).attr('zip');
			});

			$('body').on('click', '#tab-system-settings .backup .uploadNewBackup', function() {
				if (Backup.block) {return false;}
				$('#tab-system-settings .backup .backupInput').trigger('click');
			});

			$('body').on('click', '#tab-system-settings .backup .restoreRecentBackup', function() {
				$("#tab-system-settings .backupTable .unpack:first").click();
			});

			$('body').on('change', '#tab-system-settings .backup .backupInput', function() {
				if (Backup.block) {return false;}
				$(".backupInputForm").ajaxForm({
					type:"POST",
					url: "ajax",
					data: {
						mguniqueurl:"action/addNewBackup"
					},
					cache: false,
					dataType: 'json',
					success: function(response){
						if(response.status == 'error'){
							admin.indication(response.status, response.msg);
						}
						else{
							var zip = response.data.zip;
							admin.ajaxRequest({
								mguniqueurl: "action/BackupCheckZip",
								zip: zip
							},
							function (response) {
								admin.indication(response.status, response.msg);  
								Backup.drawTable();
							});
						}
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) { 
						var maxSize = $('#tab-system-settings .maxUploadSize').text();
						admin.indication('error', lang.BACKUP_UPLOAD_ERROR+maxSize+" MB)");
					}
				}).submit();
			});
		},
		createStop: function() {
			$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_CANCEL);  
			Backup.blockAll(false);
			$('#tab-system-settings .stopNewBackup').hide();
			if (Backup.zipName != '') {
				admin.ajaxRequest({
					mguniqueurl: "action/dropBackup",
					zip: Backup.zipName,
				},
				function (response) {
					$('#tab-system-settings .backupTable tbody').html(response.data);
				});
			}
		},
		blockAll: function(state) {
			if (state) {
				$('#tab-system-settings .backup .header_table').hide();
				Backup.block = true;
				$('.button').prop('disabled', true);
				$('#tab-system-settings .updateAccordion').hide();
				$('#tab-system-settings .backupTable').hide();
			}
			else{
				$('#tab-system-settings .backup .header_table').show();
				$('#tab-system-settings .backup .header_create').hide();
				$('#tab-system-settings .backup .header_restore').hide();
				$('#tab-system-settings .backup .stopNewBackup').hide();
				Backup.block = false;
				$('.button').prop('disabled', false);
				$('#tab-system-settings .updateAccordion').show();
				$('#tab-system-settings .backupTable').show();
			}
			$('#tab-system-settings .stopNewBackup').prop('disabled', false);
		},
		progressbar: function(percent) {
			$('#tab-system-settings .backup .echoPercent').html(percent+'%');
			$('#tab-system-settings .backup .percentWidth').css('width', percent+'%');
			$("#tab-system-settings .backup .backupLog").animate({scrollTop:$("#tab-system-settings .backup .backupLog")[0].scrollHeight - $("#tab-system-settings .backup .backupLog").height()},1,function(){});
		},
		restoreFromZipCore: function() {
			$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_RESTORE_FILES_START);
			admin.ajaxRequest({
				mguniqueurl: "action/restoreBackupFromZip",
				zip: Backup.zipName,
				mode: 'core',
			},
			function (response) {
				$('#tab-system-settings .backup .backupLog').append(response.data.errors);
				if (response.data.remainingFiles > 0) {
					Backup.restoreFromZipCore();
				}
				else{
					Backup.restoreFromZipMisc();
				}
			});
		},
		restoreFromZipMisc: function() {
			$.ajax({
				url: "ajax",
				type: "POST",
				data: {
					mguniqueurl: "action/restoreBackupFromZip",
					zip: Backup.zipName,
					mode: 'misc'
					},
				dataType: 'json',
				success: function(response){
					$('#tab-system-settings .backup .backupLog').append(response.data.errors);
					if (response.data.remainingFiles > 0) {
						var percent = (Backup.totalFiles - response.data.remainingFiles) / (Backup.totalFiles / 100);
						percent = Math.round(percent * 100) / 100;
						Backup.progressbar(percent);
						Backup.restoreFromZipMisc();
					}
					else{
						Backup.progressbar(1);
						$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_RESTORE_FILES_FINISH);
						Backup.restoreDB(0);
						$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_RESTORE_BASE_START);
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) { 
					Backup.timer = setTimeout(function () {Backup.restoreFromZipMisc(); },2000);
				}
			});
		},
		restoreDB: function(lineNum) {
			admin.ajaxRequest({
				mguniqueurl: "action/backupRestoreDB",
				lineNum: lineNum,
			},
			function (response) {
				if (response.data.remaining > 0) {
					var percent = (Backup.total - response.data.remaining) / (Backup.total / 100);
					percent = Math.round(percent * 100) / 100;
					Backup.progressbar(percent);
					Backup.restoreDB(response.data.currentLine);
				}
				else{
					Backup.progressbar(100);
					Backup.blockAll(false);
					$('#tab-system-settings .backup .warnings').show();
					$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_RESTORE_BASE_FINISH);
					$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_RESTORE_FINISH);
				}
			});
		},
		createTables: function(tables) {
			if (Backup.createBlock) {Backup.createStop();return false;}
			admin.ajaxRequest({
				mguniqueurl: "action/backupCreateTables",
				tables: tables,
			},
			function (response) {
				$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_CREATE_BASE_START);
				Backup.dumpTables(response.data, 0);
			});
		},
		dumpTables: function(tables, startingLine) {
			if (Backup.createBlock) {Backup.createStop();return false;}
			admin.ajaxRequest({
				mguniqueurl: "action/backupTables",
				tables: tables,
				startingLine: startingLine,
			},
			function (response) {
				if (response.data.remaining > 0) {
					var percent = (Backup.totalTables - response.data.remaining) / (Backup.totalTables / 100);
					percent = Math.round(percent * 100) / 100;
					Backup.progressbar(percent);

					Backup.dumpTables(response.data.tables, response.data.startingLine);
				}
				else{
					$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_CREATE_BASE_FINISH);
					Backup.progressbar(1);
					Backup.getFileList();
				}
			});
		},
		getFileList: function() {
			if (Backup.createBlock) {Backup.createStop();return false;}
			$.ajax({
				url: "ajax",
				type: "POST",
				data: {
					mguniqueurl: "action/backupGetFileList"
					},
				dataType: 'json',
				success: function(response){
					$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_CREATE_FILES_START);
					Backup.totalFiles = response.data.totalFiles;
					Backup.zipName = response.data.zipName;
					Backup.zipFiles();
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) { 
					$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_CREATE_FILELIST_ERROR);
					Backup.blockAll(false);
				}
			});
		},
		zipFiles: function() {
			if (Backup.createBlock) {Backup.createStop();return false;}
			var zipName = Backup.zipName;
			$.ajax({
				url: "ajax",
				type: "POST",
				data: {
					mguniqueurl: "action/backupZipFiles",
					zipName: zipName
					},
				dataType: 'json',
				success: function(response){
					$('#tab-system-settings .backup .backupLog').append(response.data.errors);
					if (response.data.remainingFiles > 0) {
						var percent = (Backup.totalFiles - response.data.remainingFiles) / (Backup.totalFiles / 100);
						percent = Math.round(percent * 100) / 100;
						Backup.progressbar(percent);

						Backup.zipFiles();
					}
					else{
						$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_CREATE_FILES_FINISH);
						Backup.progressbar(99);

						admin.ajaxRequest({
							mguniqueurl: "action/BackupCheckZip",
							zip: zipName
						},
						function (response) {
							if(response.status == 'error'){
								$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_CREATE_FINISH_ERROR);
							}
							else{
								$('#tab-system-settings .backup .backupLog').append(lang.BACKUP_CREATE_FINISH);
							}
							Backup.progressbar(100);  
							Backup.drawTable();
							Backup.blockAll(false);
						});
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) { 
					Backup.timer = setTimeout(function () {Backup.zipFiles(); },2000);
				}
			});
		},
		drawTable: function() {
			admin.ajaxRequest({
				mguniqueurl: "action/backupDrawTable",
			},
			function (response) {
				$('#tab-system-settings .backupTable tbody').html(response.data);
			});
		}
	}
})();

$(document).ready(function() {
	Backup.init();
});