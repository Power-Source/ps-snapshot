<?php
// Full Backup Detail View - ähnlich wie snapshots/item.php aber für Netzwerk-Backups
$timestamp = isset( $item['timestamp'] ) ? intval( $item['timestamp'] ) : 0;
$name = isset( $item['name'] ) ? $item['name'] : 'Netzwerk-Backup';
$filename = isset( $item['filename'] ) ? $item['filename'] : '';
$size = isset( $item['size'] ) ? intval( $item['size'] ) : 0;
$download_nonce = wp_create_nonce( 'snapshot-full-backup-download' );
$download_error = isset( $_GET['snapshot-full-backup-error'] )
	? sanitize_text_field( wp_unslash( $_GET['snapshot-full-backup-error'] ) )
	: '';

// Get the actual file path
$model = new Snapshot_Model_Full_Backup();
$backup_path = $model->local()->get_backup( $timestamp );

// Get actual file size from disk (not cached)
if ( $backup_path && file_exists( $backup_path ) ) {
	$actual_size = @filesize( $backup_path );
	if ( $actual_size !== false ) {
		$size = $actual_size; // Use actual size from disk
	}
}

// Get actual file size from disk (not cached)
if ( $backup_path && file_exists( $backup_path ) ) {
	$actual_size = @filesize( $backup_path );
	if ( $actual_size === false ) {
		$actual_size = $size; // Fallback to stored size if we can't read the file
	} else {
		$size = $actual_size; // Use actual size
	}
} else {
	// File doesn't exist, use the stored size
}
?>

<section id="header">
	<h1><?php esc_html_e( 'Netzwerk-Backup', 'snapshot' ); ?></h1>
</section>

<?php if ( 'invalid_nonce' === $download_error ) : ?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'Download fehlgeschlagen: Ungueltiger Sicherheits-Token. Bitte Seite neu laden und erneut versuchen.', 'snapshot' ); ?></p>
	</div>
<?php endif; ?>

<div id="container" class="snapshot-three wps-page-snapshots">

	<section class="wpmud-box snapshot-info-box">

		<div class="wpmud-box-title has-button">

			<h3 class="has-button">
				<?php _e( 'Backup Info', 'snapshot' ); ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'snapshot_network_backup' ), network_admin_url( 'admin.php' ) ) ); ?>" class="button button-outline button-small button-gray">
					<?php _e( 'Zurück', 'snapshot' ); ?>
				</a>
			</h3>

			<div class="wps-menu">

				<div class="wps-menu-dots">

					<div class="wps-menu-dot"></div>

					<div class="wps-menu-dot"></div>

					<div class="wps-menu-dot"></div>

				</div>

				<div class="wps-menu-holder">

					<ul class="wps-menu-list">

						<li class="wps-menu-list-title"><?php _e( 'Optionen', 'snapshot' ); ?></li>
						<?php if ( $backup_path && file_exists( $backup_path ) ) : ?>
						<li>
							<a href="<?php echo esc_url( add_query_arg( array(
								'snapshot-full-backup-action' => 'download-archive',
								'snapshot-item' => $timestamp,
								'snapshot-full-backup-nonce' => $download_nonce,
							), admin_url( 'admin.php' ) ) ); ?>"><?php _e( 'Herunterladen', 'snapshot' ); ?></a>
						</li>
						<?php endif; ?>
						<li>
							<a href="#" class="snapshot-full-backup-restore" data-item="<?php echo esc_attr( $timestamp ); ?>"><?php _e( 'Wiederherstellen', 'snapshot' ); ?></a>
						</li>
						<li>
							<a href="#" class="snapshot-full-backup-delete" data-item="<?php echo esc_attr( $timestamp ); ?>"><?php _e( 'Löschen', 'snapshot' ); ?></a>
						</li>

					</ul>

				</div>

			</div>

		</div>

		<div class="wpmud-box-content">

			<div class="row">

				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

					<table class="has-footer" cellpadding="0" cellspacing="0">

						<tbody>

						<tr>
							<th><?php _e( 'Name', 'snapshot' ); ?></th>
							<td>
								<p><?php echo esc_html( $name ); ?></p>
							</td>
						</tr>

						<?php if ( $filename ) : ?>
						<tr>
							<th><?php _e( 'Dateiname', 'snapshot' ); ?></th>
							<td>
								<p>
									<?php if ( $backup_path && file_exists( $backup_path ) ) : ?>
										<a href="<?php echo esc_url( add_query_arg( array(
											'snapshot-full-backup-action' => 'download-archive',
											'snapshot-item' => $timestamp,
											'snapshot-full-backup-nonce' => $download_nonce,
										), admin_url( 'admin.php' ) ) ); ?>" title="<?php esc_attr_e( 'Lade das Backup-Archiv herunter', 'snapshot' ); ?>">
											<?php echo esc_html( $filename ); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html( $filename ); ?>
									<?php endif; ?>
								</p>
							</td>
						</tr>
						<?php endif; ?>

						<tr>
							<th><?php _e( 'Erstellt am', 'snapshot' ); ?></th>
							<td>
								<p>
									<?php
									if ( $timestamp ) {
										$date_time_format = get_option( 'date_format' ) . _x( ' @ ', 'date and time separator', 'snapshot' ) . get_option( 'time_format' );
										echo esc_html( date_i18n( $date_time_format, $timestamp ) );
									} else {
										echo "-";
									}
									?>
								</p>
							</td>
						</tr>

						<tr>
							<th><?php _e( 'Speicherort', 'snapshot' ); ?></th>
							<td>
								<p class="has-typecon">
									<span class="wps-typecon local"></span> <?php _e( 'Local Snapshot (Netzwerk-Backup)', 'snapshot' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th><?php _e( 'Dateigröße', 'snapshot' ); ?></th>
							<td>
								<p><?php
									if ( $size > 0 ) {
										echo esc_html( size_format( $size ) );
									} else {
										echo "-";
									}
								?></p>
							</td>
						</tr>

						<tr>
							<th><?php _e( 'Typ', 'snapshot' ); ?></th>
							<td>
								<p><?php _e( 'Komplettes Netzwerk-Backup (alle Seiten, Dateien und Datenbanken)', 'snapshot' ); ?></p>
							</td>
						</tr>

						</tbody>

						<tfoot>

						<tr>
							<td>

								<a href="#" class="button button-outline button-gray snapshot-full-backup-delete" data-item="<?php echo esc_attr( $timestamp ); ?>"><?php _e( 'Löschen', 'snapshot' ); ?></a>

							</td>
							<td>

								<a href="#" class="button button-green snapshot-full-backup-restore" data-item="<?php echo esc_attr( $timestamp ); ?>"><?php _e( 'Wiederherstellen', 'snapshot' ); ?></a>

								<a class="button button-outline button-gray thickbox" href="<?php echo esc_url( add_query_arg( array(
									'action' => 'snapshot-full_backup-get_log',
									'idx' => $timestamp,
									'TB_iframe' => 'true',
								), admin_url( 'admin-ajax.php' ) ) ); ?>" title="<?php esc_attr_e( 'Backup-Log', 'snapshot' ); ?>"><?php _e( 'Log ansehen', 'snapshot' ); ?></a>

								<?php if ( $backup_path && file_exists( $backup_path ) ) : ?>
									<a class="button button-blue" href="<?php echo esc_url( add_query_arg( array(
										'snapshot-full-backup-action' => 'download-archive',
										'snapshot-item' => $timestamp,
										'snapshot-full-backup-nonce' => $download_nonce,
									), admin_url( 'admin.php' ) ) ); ?>">
										<?php _e( 'Herunterladen', 'snapshot' ); ?>
									</a>
								<?php endif; ?>

							</td>
						</tr>

						</tfoot>

					</table>

				</div>

			</div>

		</div>

	</section>

</div>

<script type="text/javascript">
(function($) {
	$(document).ready(function() {
		$('.snapshot-full-backup-delete').on('click', function(e) {
			e.preventDefault();
			
			if (!confirm('<?php echo esc_js( __( 'Möchten Sie dieses Backup wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.', 'snapshot' ) ); ?>')) {
				return;
			}
			
			var timestamp = $(this).data('item');
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'snapshot_full_backup_delete',
					timestamp: timestamp,
					_wpnonce: '<?php echo wp_create_nonce( 'snapshot-full-backup-delete' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						window.location.href = '<?php echo esc_url( add_query_arg( array( 'page' => 'snapshot_network_backup' ), network_admin_url( 'admin.php' ) ) ); ?>';
					} else {
						alert(response.data || '<?php echo esc_js( __( 'Fehler beim Löschen des Backups.', 'snapshot' ) ); ?>');
					}
				},
				error: function() {
					alert('<?php echo esc_js( __( 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.', 'snapshot' ) ); ?>');
				}
			});
		});

		$('.snapshot-full-backup-restore').on('click', function(e) {
			e.preventDefault();
			
			if (!confirm('<?php echo esc_js( __( 'Möchten Sie dieses Backup wirklich wiederherstellen? Dies wird alle aktuellen Dateien und Datenbanken überschreiben!', 'snapshot' ) ); ?>')) {
				return;
			}
			
			var timestamp = $(this).data('item');
			// Weiterleiten zur Netzwerk-Backup Seite mit restore Parameter (UNTERSTRICH im Slug!)
			window.location.href = '<?php echo esc_url( network_admin_url( 'admin.php?page=snapshot_network_backup' ) ); ?>&snapshot-restore=' + timestamp;
		});
	});
})(jQuery);
</script>
