<?php

$backups = PSOURCESnapshot::instance()->config_data['items'];

$backup_status = array(
	'title' => __( 'No Backups', SNAPSHOT_I18N_DOMAIN ),
	'content' => __( "You haven't backed up your site yet. Create your first backup now<br>– it'll only take a minute.", SNAPSHOT_I18N_DOMAIN ),
	'date' => __( 'Never', SNAPSHOT_I18N_DOMAIN ),
	'size' => __( '-', SNAPSHOT_I18N_DOMAIN ),
);

$model = new Snapshot_Model_Full_Backup();

// Dashboard plugin no longer used
$is_dashboard_active = false;
$is_dashboard_installed = false;
$has_dashboard_key = false;
$is_client = false;

$apiKey = $model->get_config( 'secret-key', '' );
$has_snapshot_key = false;

if ( ! empty( $latest_backup ) && $latest_backup ) {

	$one_week_ago = strtotime( '-1 week' );
	if ( $latest_backup['timestamp'] > $one_week_ago ) {
		$backup_status['title'] = __( 'All Backed up', SNAPSHOT_I18N_DOMAIN );
		$backup_status['content'] = __( 'Your last backup was created less than a week ago. Excellent work!', SNAPSHOT_I18N_DOMAIN );
	} else {
		$backup_status['title'] = __( 'Getting Older', SNAPSHOT_I18N_DOMAIN );
		$backup_status['content'] = __( 'Your last backup was over a week ago. Make sure you\'re backing up regulary!', SNAPSHOT_I18N_DOMAIN );
	}
	$backup_status['date'] = sprintf( _x( '%s ago', '%s = human-readable time difference', SNAPSHOT_I18N_DOMAIN ), human_time_diff( $latest_backup['timestamp'] ) );
	$backup_status['size'] = size_format( $latest_backup['file_size'] );
}

$snapshot = PSOURCESnapshot::instance()->config_data['items'];
$latest_snapshot = Snapshot_Helper_Utility::latest_backup( $snapshot );

?>

<section class="wps-backups-status<?php if ( ! $is_client ) : echo ' wps-backups-status-free'; endif; ?> wpmud-box">

	<div class="wpmud-box-content">
		<div class="wps-backups-summary">

			<div class="wps-backups-summary-align">

				<h3><?php printf( __( 'Hallo, %s!', SNAPSHOT_I18N_DOMAIN ), wp_get_current_user()->display_name ); ?></h3>

				<p><?php _e( 'Willkommen im Dashboard. Hier kannst Du alle Deine Snapshots verwalten.', SNAPSHOT_I18N_DOMAIN ); ?></p>

			</div>
		</div>

		<div class="wps-backups-details">
			<table cellpadding="0" cellspacing="0">
				<tbody>
				<tr>
					<th><?php _e( 'Letztes Backup', SNAPSHOT_I18N_DOMAIN ); ?></th>

					<?php if ( isset( $latest_snapshot['timestamp'] ) ) : ?>
						<td class="fancy-date-time">
							<?php echo Snapshot_Helper_Utility::show_date_time( $latest_snapshot['timestamp'], 'd.m.Y' ) ?>
							<span><?php
								printf(
									esc_html__( 'um %s', SNAPSHOT_I18N_DOMAIN ),
									Snapshot_Helper_Utility::show_date_time( $latest_snapshot['timestamp'], 'H:i' )
								); ?></span>
						</td>
					<?php else: ?>
						<td><?php esc_html_e( 'Noch keine', SNAPSHOT_I18N_DOMAIN ); ?></span></td>
					<?php endif; ?>
				</tr>

				<tr>
					<th><?php _e( 'Verfügbare Speicherorte', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<td>
						<span class="wps-count"><?php echo count( PSOURCESnapshot::instance()->config_data['destinations'] ); ?></span>
					</td>
				</tr>



				</tbody>
			</table>

		</div>
	</div>

</section>