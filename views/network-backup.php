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
</section>

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
				<div style="background:#e5e5e5;border-radius:4px;height:10px;overflow:hidden;">
					<div id="snapshot-network-progress-bar" style="background:#2ea2cc;height:10px;width:0%;"></div>
				</div>
				<div style="margin-top:8px;font-size:12px;">
					<span id="snapshot-network-progress-text">0%</span>
					<span style="margin-left:12px;" id="snapshot-network-time-elapsed">Laufzeit: 00:00</span>
					<span style="margin-left:12px;" id="snapshot-network-time-eta">Restzeit: --:--</span>
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
		var percent = 0;
		if (totalSteps && totalSteps > 0) {
			percent = Math.min(100, Math.round((step / totalSteps) * 100));
		}
		$('#snapshot-network-progress-bar').css('width', percent + '%');
		$('#snapshot-network-progress-text').text(percent + '%');

		if (startedAt) {
			var elapsed = (Date.now() - startedAt) / 1000;
			$('#snapshot-network-time-elapsed').text('Laufzeit: ' + formatTime(elapsed));
			if (totalSteps && totalSteps > 0 && step > 0) {
				var perStep = elapsed / step;
				var remaining = perStep * Math.max(0, totalSteps - step);
				$('#snapshot-network-time-eta').text('Restzeit: ' + formatTime(remaining));
			} else {
				$('#snapshot-network-time-eta').text('Restzeit: --:--');
			}
		}
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

	function doStartBackup() {
		setStatus('Backup wird gestartet …', 'notice-info');
		$('#snapshot-network-backup-start').prop('disabled', true);
		var startedAt = Date.now();

		fetchEstimate(function(totalSteps){
			$.post(ajaxurl, { action: 'snapshot-full_backup-start' })
				.done(function(resp){
					if (!resp || !resp.id) {
						setStatus('Backup konnte nicht gestartet werden.', 'notice-error');
						$('#snapshot-network-backup-start').prop('disabled', false);
						return;
					}
					updateProgress(1, totalSteps, startedAt);
					doProcessBackup(resp.id, 1, totalSteps, startedAt);
				})
				.fail(function(xhr){
					ajaxError(xhr);
					$('#snapshot-network-backup-start').prop('disabled', false);
				});
		});
	}

	function doProcessBackup(id, step, totalSteps, startedAt) {
		if (totalSteps && totalSteps > 0) {
			setStatus('Backup läuft … ' + Math.min(100, Math.round((step / totalSteps) * 100)) + '%', 'notice-info');
		} else {
			setStatus('Backup läuft … Schritt ' + step, 'notice-info');
		}
		updateProgress(step, totalSteps, startedAt);

		$.post(ajaxurl, { action: 'snapshot-full_backup-process', idx: id })
			.done(function(resp){
				if (!resp) {
					setStatus('Ungültige Antwort beim Backup-Prozess.', 'notice-error');
					$('#snapshot-network-backup-start').prop('disabled', false);
					return;
				}

				if (resp.done) {
					doFinishBackup(id, startedAt);
					return;
				}

				setTimeout(function(){ doProcessBackup(id, step + 1, totalSteps, startedAt); }, 400);
			})
			.fail(function(xhr){
				ajaxError(xhr);
				$('#snapshot-network-backup-start').prop('disabled', false);
			});
	}

	function doFinishBackup(id, startedAt) {
		setStatus('Backup wird abgeschlossen … 100%', 'notice-info');
		updateProgress(1, 1, startedAt || Date.now());

		$.post(ajaxurl, { action: 'snapshot-full_backup-finish', idx: id })
			.done(function(resp){
				if (resp && resp.status) {
					setStatus('Netzwerk-Backup erfolgreich erstellt.', 'notice-success');
					window.location.reload();
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
		$.post(ajaxurl, { action: 'snapshot-network_backup-check_status' })
			.done(function(resp){
				if (resp && resp.running && resp.id) {
					// A backup is running - try to resume it
					var startTime = resp.start_time ? resp.start_time * 1000 : Date.now();
					fetchEstimate(function(totalSteps){
						resumeProcessBackup(resp.id, resp.current || 1, totalSteps || resp.total || 0, startTime);
					});
				}
			})
			.fail(function(){
				// Silently fail - no backup running
			});
	}

	function resumeProcessBackup(id, step, totalSteps, startedAt) {
		var startedTime = startedAt || Date.now();
		$('#snapshot-network-backup-start').prop('disabled', true);
		setStatus('Backup läuft (fortgesetzt) … ' + Math.min(100, Math.round((step / totalSteps) * 100)) + '%', 'notice-info');
		updateProgress(step, totalSteps, startedTime);

		// Continue processing
		doProcessBackup(id, step + 1, totalSteps, startedTime);
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
		// Load schedule on page load
		loadSchedule();
		// Check if a backup is currently running
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
