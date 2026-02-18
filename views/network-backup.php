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
			<div id="snapshot-network-backup-status" class="notice notice-info" style="display:none;padding:10px;"></div>
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

		fetchEstimate(function(totalSteps){
			$.post(ajaxurl, { action: 'snapshot-full_backup-start' })
				.done(function(resp){
					if (!resp || !resp.id) {
						setStatus('Backup konnte nicht gestartet werden.', 'notice-error');
						$('#snapshot-network-backup-start').prop('disabled', false);
						return;
					}
					doProcessBackup(resp.id, 1, totalSteps);
				})
				.fail(function(xhr){
					ajaxError(xhr);
					$('#snapshot-network-backup-start').prop('disabled', false);
				});
		});
	}

	function doProcessBackup(id, step, totalSteps) {
		var percent = 0;
		if (totalSteps && totalSteps > 0) {
			percent = Math.min(100, Math.round((step / totalSteps) * 100));
			setStatus('Backup läuft … ' + percent + '%', 'notice-info');
		} else {
			setStatus('Backup läuft … Schritt ' + step, 'notice-info');
		}

		$.post(ajaxurl, { action: 'snapshot-full_backup-process', idx: id })
			.done(function(resp){
				if (!resp) {
					setStatus('Ungültige Antwort beim Backup-Prozess.', 'notice-error');
					$('#snapshot-network-backup-start').prop('disabled', false);
					return;
				}

				if (resp.done) {
					doFinishBackup(id);
					return;
				}

				setTimeout(function(){ doProcessBackup(id, step + 1, totalSteps); }, 400);
			})
			.fail(function(xhr){
				ajaxError(xhr);
				$('#snapshot-network-backup-start').prop('disabled', false);
			});
	}

	function doFinishBackup(id) {
		setStatus('Backup wird abgeschlossen … 100%', 'notice-info');

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
		runRestoreStep(archive, restorePath, 1);
	}

	function runRestoreStep(archive, restorePath, step) {
		setStatus('Wiederherstellung läuft … Schritt ' + step, 'notice-info');
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
				setTimeout(function(){ runRestoreStep(archive, restorePath, step + 1); }, 500);
				return;
			}

			if (resp.task === 'clearing' && resp.status) {
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

	$(function(){
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
	});
})(jQuery);
</script>
