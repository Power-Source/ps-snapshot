<?php

$plugin = PSOURCESnapshot::instance();

/* Don't display this notice if it has already been seen */
if ( isset( $plugin->config_data['seen_welcome'] ) && $plugin->config_data['seen_welcome'] ) {
	return;
}

$plugin->config_data['seen_welcome'] = true;
$plugin->save_config();

?>
<div id="wps-welcome-message" class="snapshot-three wps-popup-modal show">

	<div class="wps-popup-mask"></div>

	<div class="wps-popup-content">
		<div class="wpmud-box">
			<div class="wpmud-box-title has-button can-close">
				<h3><?php _e('Willkommen bei Snapshot', SNAPSHOT_I18N_DOMAIN); ?></h3>
				<a href="#" class="button button-outline button-gray wps-popup-close wps-dismiss-welcome">
					<?php _e('Überspringen', SNAPSHOT_I18N_DOMAIN); ?>
				</a>
			</div>

			<div class="wpmud-box-content">
				<div class="row">

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

						<?php if ( $is_client && ! $has_snapshot_key) : ?>

							<p><?php _e('Willkommen bei PS Snapshot, dem heißesten Backup-Plugin für ClassicPress! Lass uns damit beginnen, auszuwählen, welche Art von Backup Du erstellen möchtest - es gibt zwei Arten...', SNAPSHOT_I18N_DOMAIN); ?></p>

						<?php else : ?>

							<p><?php _e('Willkommen bei Snapshot, dem heißesten Backup-Plugin für ClassicPress! Mit diesem Plugin kannst Du Teile Deiner Webseite sichern und zu Drittanbietern wie Dropbox, Google Drive und mehr migrieren.', SNAPSHOT_I18N_DOMAIN); ?></p>

						<?php endif; ?>

						<?php if ( $is_client && ! $has_snapshot_key) : ?>

							<!-- Managed Backups removed in local-only version -->

							<div class="wps-welcome-message-pro">
								<h3><?php _e('Snapshots', SNAPSHOT_I18N_DOMAIN); ?></h3>
								<p><small><?php _e('Mit Snapshots kannst Du Teile Deiner Webseite sichern und migrieren. Du kannst auswählen, welche Dateien, Plugins/Themes und Datenbanktabellen gesichert werden sollen, und diese dann bei Drittanbietern speichern. Um zu beginnen, füge Dein erstes Ziel hinzu.', SNAPSHOT_I18N_DOMAIN); ?></small></p>
							</div>

						<?php endif; ?>

							<p><?php _e("<strong>Lass uns damit beginnen, einen neuen Speicherort hinzuzufügen</strong>; wo möchtest Du Deinen ersten Snapshot speichern?", SNAPSHOT_I18N_DOMAIN); ?></p>

						<table cellpadding="0" cellspacing="0">
							<tbody>
								<tr><?php // Dropbox ?>
									<td class="start-icon"><i class="wps-typecon dropbox"></i></td>
									<td class="start-name"><?php _e('Dropbox', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<a class="button button-blue button-small wps-dismiss-welcome"
										   href="<?php echo esc_url( add_query_arg( array( 'snapshot-action' => 'add' , 'type' => 'dropbox' ), PSOURCESnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ) ); ?>">
											<?php _e('Speicherort hinzufügen', SNAPSHOT_I18N_DOMAIN); ?>
										</a>
									</td>
								</tr>

								<tr><?php // Google Drive ?>
									<td class="start-icon"><i class="wps-typecon google"></i></td>
									<td class="start-name"><?php _e('Google Drive', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<a class="button button-blue button-small wps-dismiss-welcome"
										   href="<?php echo esc_url( add_query_arg( array( 'snapshot-action' => 'add' , 'type' => 'google-drive' ), PSOURCESnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ) ); ?>">
											<?php _e('Speicherort hinzufügen', SNAPSHOT_I18N_DOMAIN); ?>
											</a>
									</td>
								</tr>

								<tr><?php // sFTP ?>
									<td class="start-icon"><i class="wps-typecon sftp"></i></td>
									<td class="start-name"><?php _e('FTP / sFTP', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<a class="button button-blue button-small wps-dismiss-welcome"
										   href="<?php echo esc_url( add_query_arg( array( 'snapshot-action' => 'add' , 'type' => 'ftp' ), PSOURCESnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ) ); ?>">
											<?php _e('Speicherort hinzufügen', SNAPSHOT_I18N_DOMAIN); ?>
										</a>
									</td>
								</tr>

								<tr><?php // Local ?>
									<td class="start-icon"><i class="wps-typecon local"></i></td>
									<td class="start-name"><?php _e('Lokal', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<a class="button button-gray button-small button-outline wps-dismiss-welcome"
										   href="<?php echo esc_url( PSOURCESnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-new-snapshot') ); ?>" >
											<?php _e('Use Destination', SNAPSHOT_I18N_DOMAIN); ?></a>
									</td>
								</tr>

							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>