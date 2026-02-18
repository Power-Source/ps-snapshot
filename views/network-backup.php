<?php
if ( ! is_multisite() || ! is_network_admin() || ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Diese Seite ist nur im Netzwerk-Admin verfügbar.', SNAPSHOT_I18N_DOMAIN ) );
}

$backups = ( isset( $backups ) && is_array( $backups ) ) ? $backups : array();
$restore_path_default = apply_filters( 'snapshot_home_path', get_home_path() );
$restore_nonce = wp_create_nonce( 'snapshot-full-backup-restore' );
?>

<section id="header">
	<h1><?php esc_html_e( 'Netzwerk-Backup', SNAPSHOT_I18N_DOMAIN ); ?></h1>
	<div style="margin-top:10px;">
		<button id="snapshot-debug-toggle" class="button button-secondary" style="font-size:11px; padding:2px 8px;"><?php esc_html_e( 'Debug aktivieren', SNAPSHOT_I18N_DOMAIN ); ?></button>
	</div>
</section>

<div style="display:none; background:#f0f0f0; padding:10px; margin:10px 0; border:1px solid #ccc; font-family:monospace; font-size:11px; max-height:200px; overflow-y:auto;" id="snapshot-debug-panel">
	<strong>Debug-Log:</strong>
	<div id="snapshot-debug-log" style="margin-top:5px;"></div>
	<button onclick="document.getElementById('snapshot-debug-panel').style.display='none';" style="margin-top:5px;">Schließen</button>
</div>

<div id="container" class="snapshot-three wps-page-settings">
	<section class="wpmud-box">
		<div class="wpmud-box-title">
			<h3><?php esc_html_e( 'Komplettes Netzwerk sichern', SNAPSHOT_I18N_DOMAIN ); ?></h3>
		</div>
		<div class="wpmud-box-content">
			<p><?php esc_html_e( 'Diese Aktion erstellt ein vollständiges Backup des gesamten Netzwerks (Dateien + Datenbanktabellen).', SNAPSHOT_I18N_DOMAIN ); ?></p>
			<p>
				<button id="snapshot-network-backup-start" class="button button-blue"><?php esc_html_e( 'Netzwerk-Backup jetzt starten', SNAPSHOT_I18N_DOMAIN ); ?></button>
			</p>
			<div id="snapshot-network-progress" style="display:none;max-width:760px;">
				<style>
					@keyframes backup-loading {
						0% { background-position: 0% 50%; }
						50% { background-position: 100% 50%; }
						100% { background-position: 0% 50%; }
					}
					.backup-progress-bar {
						background: linear-gradient(90deg, #2ea2cc, #1e90ff, #2ea2cc);
						background-size: 200% 100%;
						animation: backup-loading 2s ease-in-out infinite;
					}
				</style>
				<div style="background:#e5e5e5;border-radius:4px;height:10px;overflow:hidden;">
					<div class="backup-progress-bar" style="height:10px;width:100%;"></div>
				</div>
				<div style="margin-top:8px;font-size:12px;">
					<strong id="snapshot-network-status-text">Backup läuft …</strong>
					<span style="margin-left:12px;" id="snapshot-network-time-elapsed">Laufzeit: 00:00</span>
				</div>
			</div>
			<div id="snapshot-network-backup-status" class="notice notice-info" style="display:none;padding:10px;"></div>
		</div>
	</section>

	<section class="wpmud-box" style="margin-top:20px;">
		<div class="wpmud-box-title">
			<h3><?php esc_html_e( 'Wöchentliche Zeitplanung', SNAPSHOT_I18N_DOMAIN ); ?></h3>
		</div>
		<div class="wpmud-box-content">
			<p><?php esc_html_e( 'Konfigurieren Sie an welchen Wochentagen und um welche Uhrzeit automatische Backups durchgeführt werden sollen. Das Backup läuft im Hintergrund ohne dass der Browser-Tab offen sein muss.', SNAPSHOT_I18N_DOMAIN ); ?></p>
			
			<div>
				<fieldset>
					<legend style="font-weight:bold;margin-bottom:10px;"><?php esc_html_e( 'Backup-Tage (Wählen Sie mindestens einen Tag aus)', SNAPSHOT_I18N_DOMAIN ); ?></legend>
					<div style="margin-left:10px;">
						<label><input type="checkbox" name="snapshot-schedule-day" value="1" class="snapshot-schedule-day"> Montag</label><br>
						<label><input type="checkbox" name="snapshot-schedule-day" value="2" class="snapshot-schedule-day"> Dienstag</label><br>
						<label><input type="checkbox" name="snapshot-schedule-day" value="3" class="snapshot-schedule-day"> Mittwoch</label><br>
						<label><input type="checkbox" name="snapshot-schedule-day" value="4" class="snapshot-schedule-day"> Donnerstag</label><br>
						<label><input type="checkbox" name="snapshot-schedule-day" value="5" class="snapshot-schedule-day"> Freitag</label><br>
						<label><input type="checkbox" name="snapshot-schedule-day" value="6" class="snapshot-schedule-day"> Samstag</label><br>
						<label><input type="checkbox" name="snapshot-schedule-day" value="0" class="snapshot-schedule-day"> Sonntag</label>
					</div>
				</fieldset>
			</div>

			<div style="margin-top:20px;">
				<label for="snapshot-schedule-time"><strong><?php esc_html_e( 'Uhrzeit für Backup-Start', SNAPSHOT_I18N_DOMAIN ); ?></strong></label><br>
				<input type="time" id="snapshot-schedule-time" value="02:00" style="margin-top:5px;">
				<p style="font-size:11px;color:#888;">
					<?php esc_html_e( 'Wählen Sie eine Uhrzeit mit niedrigerem Serveraufkommen, z.B. nachts.', SNAPSHOT_I18N_DOMAIN ); ?>
				</p>
			</div>

			<p style="margin-top:20px;">
				<button id="snapshot-schedule-save" class="button button-primary"><?php esc_html_e( 'Zeitplan speichern', SNAPSHOT_I18N_DOMAIN ); ?></button>
				<span id="snapshot-schedule-status" style="margin-left:10px;display:none;"></span>
			</p>

			<div id="snapshot-schedule-info" style="margin-top:15px;padding:10px;background:#f9f9f9;border-left:4px solid #2ea2cc;display:none;">
				<strong><?php esc_html_e( 'Zeitplan aktiv:', SNAPSHOT_I18N_DOMAIN ); ?></strong><br>
				<span id="snapshot-schedule-info-text"></span>
				<p style="margin-top:10px;font-size:11px;color:#888;">
					<?php esc_html_e( 'Nächstes Backup: ', SNAPSHOT_I18N_DOMAIN ); ?><span id="snapshot-next-backup-time"></span>
				</p>
			</div>
		</div>
	</section>

	<section class="wpmud-box" style="margin-top:20px;">
		<div class="wpmud-box-title">
			<h3><?php esc_html_e( 'Vorhandene Netzwerk-Backups', SNAPSHOT_I18N_DOMAIN ); ?></h3>
		</div>
		<div class="wpmud-box-content">
			<p>
				<label for="snapshot-network-restore-path"><strong><?php esc_html_e( 'Restore-Zielpfad', SNAPSHOT_I18N_DOMAIN ); ?></strong></label><br>
				<input type="text" id="snapshot-network-restore-path" value="<?php echo esc_attr( $restore_path_default ); ?>" style="width:100%;max-width:760px;">
			</p>
			<p>
				<label>
					<input type="checkbox" id="snapshot-network-delete-archive" value="1">
					<?php esc_html_e( 'Backup-Archiv nach erfolgreichem Restore loeschen', SNAPSHOT_I18N_DOMAIN ); ?>
				</label>
			</p>
			<div id="snapshot-network-restore-progress" style="display:none;max-width:760px;">
				<div style="background:#e5e5e5;border-radius:4px;height:10px;overflow:hidden;">
					<div id="snapshot-network-restore-progress-bar" style="background:#46b450;height:10px;width:0%;"></div>
				</div>
				<div style="margin-top:8px;font-size:12px;">
					<span id="snapshot-network-restore-progress-text">0%</span>
					<span style="margin-left:12px;" id="snapshot-network-restore-time-elapsed">Laufzeit: 00:00</span>
					<span style="margin-left:12px;" id="snapshot-network-restore-time-eta">Restzeit: --:--</span>
				</div>
			</div>

			<?php if ( empty( $backups ) ) : ?>
				<p><?php esc_html_e( 'Es sind noch keine Netzwerk-Backups vorhanden.', SNAPSHOT_I18N_DOMAIN ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Zeitpunkt', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<th><?php esc_html_e( 'Datei', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<th><?php esc_html_e( 'Größe', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<th><?php esc_html_e( 'Aktion', SNAPSHOT_I18N_DOMAIN ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $backups as $backup_item ) :
						$timestamp = isset( $backup_item['timestamp'] ) ? intval( $backup_item['timestamp'] ) : 0;
						$name = isset( $backup_item['name'] ) ? $backup_item['name'] : '';
						$size = isset( $backup_item['size'] ) ? size_format( intval( $backup_item['size'] ) ) : '-';
						?>
						<tr>
							<td><?php echo $timestamp ? esc_html( date_i18n( 'd.m.Y H:i:s', $timestamp ) ) : '-'; ?></td>
							<td><?php echo esc_html( $name ); ?></td>
							<td><?php echo esc_html( $size ); ?></td>
							<td>
								<button class="button snapshot-network-restore" data-archive="<?php echo esc_attr( $timestamp ); ?>"><?php esc_html_e( 'Wiederherstellen', SNAPSHOT_I18N_DOMAIN ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</section>
</div>

<script type="text/javascript">
(function($){
	// Debug mode - wird nur aktiviert wenn WP_DEBUG true ist
	var debug_mode = <?php echo ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'true' : 'false'; ?>;

	// Debug logging function
	function debugLog(msg, data) {
		if (!debug_mode) return; // Nichts tun wenn Debug nicht aktiv
		
		console.log(msg, data);
		var logEl = $('#snapshot-debug-log');
		var timestamp = new Date().toLocaleTimeString();
		var html = '<div style="margin:5px 0; padding:5px; background:white; border-left:3px solid #2ea2cc;">' +
			'<strong>[' + timestamp + ']</strong> ' + msg;
		if (data) {
			html += '<br><small style="color:#666;">' + JSON.stringify(data).substring(0, 200) + '</small>';
		}
		html += '</div>';
		logEl.append(html);
		$('#snapshot-debug-panel').show();
		logEl.scrollTop(logEl[0].scrollHeight);
	}

	var currentBackupStep = 0;
	var currentBackupTotal = 0;
	var currentBackupId = null;
	var currentBackupStartTime = 0;

	function setStatus(message, type) {
		var $status = $('#snapshot-network-backup-status');
		$status.removeClass('notice-info notice-success notice-error').addClass(type || 'notice-info');
		$status.html(message).show();
	}

	function formatTime(seconds) {
		seconds = Math.max(0, Math.round(seconds));
		var h = Math.floor(seconds / 3600);
		var m = Math.floor((seconds % 3600) / 60);
		var s = seconds % 60;
		var mm = (m < 10 ? '0' : '') + m;
		var ss = (s < 10 ? '0' : '') + s;
		if (h > 0) {
			var hh = (h < 10 ? '0' : '') + h;
			return hh + ':' + mm + ':' + ss;
		}
		return mm + ':' + ss;
	}

	function updateProgress(step, totalSteps, startedAt) {
		$('#snapshot-network-progress').show();
		
		if (!startedAt || startedAt <= 0) {
			startedAt = Date.now();
		}
		
		var elapsed = (Date.now() - startedAt) / 1000; // in seconds
		var elapsedText = 'Laufzeit: ' + formatTime(elapsed);
		$('#snapshot-network-time-elapsed').text(elapsedText);
	}


	function updateRestoreProgress(step, startedAt, isDone) {
		$('#snapshot-network-restore-progress').show();
		var percent = isDone ? 100 : Math.min(95, Math.round(step * 2));
		$('#snapshot-network-restore-progress-bar').css('width', percent + '%');
		$('#snapshot-network-restore-progress-text').text(percent + '%');

		if (startedAt) {
			var elapsed = (Date.now() - startedAt) / 1000;
			$('#snapshot-network-restore-time-elapsed').text('Laufzeit: ' + formatTime(elapsed));
			var remaining = percent >= 100 ? 0 : (elapsed * (100 - percent) / Math.max(1, percent));
			$('#snapshot-network-restore-time-eta').text('Restzeit: ' + formatTime(remaining));
		}
	}

	function ajaxError(xhr) {
		var text = (xhr && xhr.responseText) ? xhr.responseText : 'Unbekannter Fehler';
		setStatus('Fehler: ' + text, 'notice-error');
	}

	function doStartBackup() {
		debugLog('Starting new backup...');
		setStatus('Backup wird gestartet …', 'notice-info');
		$('#snapshot-network-backup-start').prop('disabled', true);
		var startedAt = Date.now();

		$.post(ajaxurl, { action: 'snapshot-full_backup-start' })
			.done(function(resp){
				debugLog('Backup start response:', resp);
				if (!resp || !resp.id) {
					debugLog('ERROR: No backup ID in response');
					setStatus('Backup konnte nicht gestartet werden.', 'notice-error');
					$('#snapshot-network-backup-start').prop('disabled', false);
					return;
				}
				debugLog('Backup started with ID:', resp.id);
				// Estimate total steps and start processing
				fetchEstimate(function(totalSteps){
					debugLog('Backup estimate:', totalSteps);
					doProcessBackup(resp.id, 1, totalSteps, startedAt);
				});
			})
			.fail(function(xhr){
				debugLog('Backup start failed:', xhr);
				ajaxError(xhr);
				$('#snapshot-network-backup-start').prop('disabled', false);
			});
	}

	function fetchEstimate(callback) {
		$.post(ajaxurl, { action: 'snapshot-full_backup-estimate' })
			.done(function(resp){
				var total = resp && resp.total ? parseInt(resp.total, 10) : 0;
				if (isNaN(total) || total < 1) {
					total = 0;
				}
				callback(total);
			})
			.fail(function(){
				callback(0);
			});
	}

	function doProcessBackup(id, step, totalSteps, startedAt) {
		// Store current state for resume capability
		currentBackupId = id;
		currentBackupStartTime = startedAt;
		
		setStatus('Backup läuft …', 'notice-info');
		updateProgress(step, totalSteps, startedAt);

		$.post(ajaxurl, { action: 'snapshot-full_backup-process', idx: id })
			.done(function(resp){
				debugLog('Backup process response:', resp);
				if (!resp) {
					debugLog('ERROR: Invalid response from backup process');
					setStatus('Ungültige Antwort beim Backup-Prozess.', 'notice-error');
					$('#snapshot-network-backup-start').prop('disabled', false);
					return;
				}

				if (resp.done) {
					debugLog('Backup done, finishing...');
					doFinishBackup(id, startedAt);
					return;
				}

				setTimeout(function(){ doProcessBackup(id, step + 1, totalSteps, startedAt); }, 400);
			})
			.fail(function(xhr){
				debugLog('Backup process failed:', xhr);
				ajaxError(xhr);
				$('#snapshot-network-backup-start').prop('disabled', false);
			});
	}

	function doFinishBackup(id, startedAt) {
		setStatus('Backup wird abgeschlossen …', 'notice-info');
		updateProgress(1, 1, startedAt || Date.now());

		$.post(ajaxurl, { action: 'snapshot-full_backup-finish', idx: id })
			.done(function(resp){
				if (resp && resp.status) {
					setStatus('Netzwerk-Backup erfolgreich erstellt.', 'notice-success');
					setTimeout(function(){
						window.location.reload();
					}, 2000);
					return;
				}

				setStatus('Backup abgeschlossen, aber Status ist unklar.', 'notice-error');
				$('#snapshot-network-backup-start').prop('disabled', false);
			})
			.fail(function(xhr){
				ajaxError(xhr);
				$('#snapshot-network-backup-start').prop('disabled', false);
			});
	}

	function pollBackupStatus(id, startedAt) {
		// Poll every 2 seconds for backup status
		var pollInterval = setInterval(function(){
			$.post(ajaxurl, { action: 'snapshot-network_backup-check_status' })
				.done(function(resp){
					if (!resp || !resp.running) {
						clearInterval(pollInterval);
						setStatus('Netzwerk-Backup erfolgreich erstellt.', 'notice-success');
						setTimeout(function(){
							window.location.reload();
						}, 2000);
						return;
					}

					// Update progress
					var percent = Math.min(100, Math.round((resp.current / resp.total) * 100)) || 0;
					$('#snapshot-network-progress').show();
					$('#snapshot-network-progress-bar').css('width', percent + '%');
					$('#snapshot-network-progress-text').text(percent + '%');

					if (startedAt) {
						var elapsed = (Date.now() - startedAt) / 1000;
						$('#snapshot-network-time-elapsed').text('Laufzeit: ' + formatTime(elapsed));
						if (resp.total && resp.total > 0 && resp.current > 0) {
							var perStep = elapsed / resp.current;
							var remaining = perStep * Math.max(0, resp.total - resp.current);
							$('#snapshot-network-time-eta').text('Restzeit: ' + formatTime(remaining));
						}
					}

					setStatus('Backup läuft … ' + percent + '%', 'notice-info');
				})
				.fail(function(){
					// On error, continue polling
				});
		}, 2000); // Poll every 2 seconds
	}

	function doRestore(archive) {
		var restorePath = $('#snapshot-network-restore-path').val();
		if (!restorePath) {
			setStatus('Bitte Restore-Zielpfad angeben.', 'notice-error');
			return;
		}

		setStatus('Wiederherstellung gestartet …', 'notice-info');
		var startedAt = Date.now();
		updateRestoreProgress(1, startedAt, false);
		runRestoreStep(archive, restorePath, 1, startedAt);
	}

	function runRestoreStep(archive, restorePath, step, startedAt) {
		setStatus('Wiederherstellung läuft … Schritt ' + step, 'notice-info');
		updateRestoreProgress(step, startedAt, false);
		var deleteArchive = $('#snapshot-network-delete-archive').is(':checked') ? '1' : '0';

		$.post(ajaxurl, {
			action: 'snapshot-full_backup-restore',
			archive: archive,
			restore: restorePath,
			delete_archive: deleteArchive,
			security: '<?php echo esc_js( $restore_nonce ); ?>'
		})
		.done(function(resp){
			if (!resp || !resp.task) {
				setStatus('Ungültige Antwort bei der Wiederherstellung.', 'notice-error');
				return;
			}

			if (resp.task === 'restoring' && resp.status) {
				setTimeout(function(){ runRestoreStep(archive, restorePath, step + 1, startedAt); }, 500);
				return;
			}

			if (resp.task === 'clearing' && resp.status) {
				updateRestoreProgress(step, startedAt, true);
				setStatus('Wiederherstellung abgeschlossen.', 'notice-success');
				window.location.reload();
				return;
			}

			setStatus('Wiederherstellung fehlgeschlagen.', 'notice-error');
		})
		.fail(function(xhr){
			ajaxError(xhr);
		});
	}

	function loadSchedule() {
		$.post(ajaxurl, { action: 'snapshot-network_backup-load_schedule' })
			.done(function(resp){
				if (resp && resp.schedule) {
					var schedule = resp.schedule;
					if (schedule.days && schedule.days.length > 0) {
						schedule.days.forEach(function(day){
							$('[value="' + day + '"].snapshot-schedule-day').prop('checked', true);
						});
					}
					if (schedule.time) {
						$('#snapshot-schedule-time').val(schedule.time);
					}
					displayScheduleInfo(schedule);
				}
			});
	}

	function checkAndResumeBackup() {
		debugLog('Checking for running backup on page load...');
		$.post(ajaxurl, { action: 'snapshot-network_backup-check_status' })
			.done(function(resp){
				debugLog('Network Backup Status Check response:', resp);
				if (resp && resp.running && resp.id) {
					debugLog('Found running backup, resuming AJAX loop...:', resp.id);
					// A backup is running - resume the AJAX processing loop
					$('#snapshot-network-backup-start').prop('disabled', true);
					var startTime = resp.start_time ? resp.start_time * 1000 : Date.now();
					var totalSteps = parseInt(resp.total, 10) || 0;
					var currentStep = parseInt(resp.current, 10) || 1;
					
					setStatus('Backup läuft …', 'notice-info');
					$('#snapshot-network-progress').show();
					updateProgress(currentStep, totalSteps, startTime);
					
					// Resume the AJAX loop from the right step
					doProcessBackup(resp.id, currentStep + 1, totalSteps, startTime);
				} else {
					debugLog('No running backup found', resp);
				}
			})
			.fail(function(xhr){
				debugLog('Status check failed:', xhr);
				// Silently fail - no backup running
			});
	}

	function saveSchedule() {
		var days = [];
		$('.snapshot-schedule-day:checked').each(function(){
			days.push(parseInt($(this).val(), 10));
		});

		if (days.length === 0) {
			$('#snapshot-schedule-status').removeClass('hidden').html('Fehler: Bitte wählen Sie mindestens einen Tag aus.').css('color', 'red').show();
			return;
		}

		var time = $('#snapshot-schedule-time').val();
		if (!time) {
			$('#snapshot-schedule-status').removeClass('hidden').html('Fehler: Bitte geben Sie eine Uhrzeit an.').css('color', 'red').show();
			return;
		}

		var data = {
			action: 'snapshot-network_backup-save_schedule',
			days: days,
			time: time,
			security: '<?php echo wp_create_nonce( 'snapshot-network-backup-schedule' ); ?>'
		};

		$.post(ajaxurl, data)
			.done(function(resp){
				if (resp && resp.status) {
					$('#snapshot-schedule-status').removeClass('hidden').html('✓ Zeitplan gespeichert').css('color', 'green').show();
					setTimeout(function(){
						$('#snapshot-schedule-status').fadeOut();
					}, 3000);
					displayScheduleInfo(resp.schedule);
				} else {
					$('#snapshot-schedule-status').removeClass('hidden').html('Fehler beim Speichern.').css('color', 'red').show();
				}
			})
			.fail(function(xhr){
				var msg = xhr && xhr.responseText ? xhr.responseText : 'Fehler beim Speichern.';
				$('#snapshot-schedule-status').removeClass('hidden').html('Fehler: ' + msg).css('color', 'red').show();
			});
	}

	function displayScheduleInfo(schedule) {
		if (!schedule || !schedule.days || schedule.days.length === 0) {
			$('#snapshot-schedule-info').hide();
			return;
		}

		var dayNames = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
		var daysText = schedule.days.map(function(d){ return dayNames[d]; }).join(', ');
		$('#snapshot-schedule-info-text').html('Täglich ' + daysText + ' um ' + schedule.time + ' Uhr');
		
		if (schedule.next_backup) {
			$('#snapshot-next-backup-time').text(schedule.next_backup);
		}
		
		$('#snapshot-schedule-info').show();
	}

	$(function(){
		debugLog('Network Backup page loaded, initializing...');
		// Load schedule on page load
		loadSchedule();
		// Check if a backup is currently running
		debugLog('About to check for running backup...');
		checkAndResumeBackup();

		$('#snapshot-network-backup-start').on('click', function(e){
			e.preventDefault();
			doStartBackup();
		});

		$(document).on('click', '.snapshot-network-restore', function(e){
			e.preventDefault();
			var archive = $(this).data('archive');
			if (!archive) {
				setStatus('Ungültiges Backup.', 'notice-error');
				return;
			}
			var deleteArchive = $('#snapshot-network-delete-archive').is(':checked');
			var confirmText = deleteArchive
				? 'Dieses Backup wirklich wiederherstellen? Das Archiv wird danach geloescht.'
				: 'Dieses Backup wirklich wiederherstellen? Das Archiv bleibt erhalten.';

			if (!window.confirm(confirmText)) {
				return;
			}

			doRestore(archive);
		});

		// Schedule management
		$('#snapshot-schedule-save').on('click', function(e){
			e.preventDefault();
			saveSchedule();
		});
	});
})(jQuery);
</script>
