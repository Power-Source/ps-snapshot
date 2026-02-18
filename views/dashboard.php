
<section id="header">
    <h1><?php esc_html_e( 'Dashboard', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-dashboard">

	<div class="row">

		<div class="col-xs-12">

			<?php

			$model  = new Snapshot_Model_Full_Backup;
			$apiKey = $model->get_config( 'secret-key', '' );

			// Dashboard plugin no longer used
			$is_client = false;
			$has_snapshot_key = false;

			$data = array(
				"hasApikey" => ! empty( $apiKey ),
				"apiKey" => $apiKey,
				"apiKeyUrl" => '', // No remote storage
				"is_client" => $is_client,
				"has_snapshot_key" => $has_snapshot_key
			);

			$this->render( "boxes/dashboard/widget-status", false, $data, false, false );

			?>

		</div>

	</div>

	<div class="row">

		<div class="col-xs-12 col-md-6">
			<?php

			$this->render( 'boxes/dashboard/widget-snapshots', false, array(), false, false );

			?>

		</div>

		<div class="col-xs-12 col-md-6">

			<?php

			$this->render( "boxes/dashboard/widget-destinations", false, array(), false, false );

			?>

		</div>

		<?php if ( is_multisite() && is_network_admin() ) : ?>
		<div class="col-xs-12 col-md-6">
			<?php
			$this->render( 'boxes/dashboard/widget-network-backup', false, array(), false, false );
			?>
		</div>
		<?php endif; ?>

	</div>

	<!-- Emergency Recovery Notice -->
	<div class="row" style="margin-top:20px;">
		<div class="col-xs-12">
			<div class="wpmud-box" style="background:#fff3cd; border-left:4px solid #ff9800;">
				<div class="wpmud-box-title" style="padding:10px;">
					<h3 style="margin:0; color:#ff6f00;"><?php esc_html_e( '⚠️ Notfall-Wiederherstellung', SNAPSHOT_I18N_DOMAIN ); ?></h3>
				</div>
				<div class="wpmud-box-content" style="padding:15px;">
					<p style="margin:0 0 12px 0;">
						<?php esc_html_e( 'Falls Deine WordPress-Seite nicht mehr erreichbar ist und Du nicht auf das Dashboard zugreifen kannst, kannst Du die Seite mit unserem Recovery-Skript wiederherstellen.', SNAPSHOT_I18N_DOMAIN ); ?>
					</p>
					<ol style="margin:12px 0; padding-left:20px;">
						<li style="margin-bottom:8px;">
							<?php esc_html_e( 'Lade das Recovery-Skript herunter und speichere es an einem sicheren Ort (z.B. auf Deinem Computer).', SNAPSHOT_I18N_DOMAIN ); ?>
						</li>
						<li style="margin-bottom:8px;">
							<?php esc_html_e( 'Falls die Seite ausfällt, lade das Skript via FTP in das Root-Verzeichnis Deiner Installation hoch.', SNAPSHOT_I18N_DOMAIN ); ?>
						</li>
						<li style="margin-bottom:8px;">
							<?php esc_html_e( 'Öffne das Skript im Browser (z.B. https://example.com/snapshot-recovery.php).', SNAPSHOT_I18N_DOMAIN ); ?>
						</li>
						<li style="margin-bottom:0;">
							<?php esc_html_e( 'Folge den Anweisungen im Skript, um ein Backup auszuwählen und wiederherzustellen.', SNAPSHOT_I18N_DOMAIN ); ?>
						</li>
					</ol>
					<p style="margin:12px 0 0 0;">
						<strong><?php esc_html_e( '💾 Recovery-Skript herunterladen:', SNAPSHOT_I18N_DOMAIN ); ?></strong><br>
						<a href="<?php echo esc_url( plugins_url( 'snapshot-recovery.php', dirname( dirname( __FILE__ ) ) ) ); ?>" class="button button-secondary" style="margin-top:8px;" download>
							<?php esc_html_e( '📥 snapshot-recovery.php herunterladen', SNAPSHOT_I18N_DOMAIN ); ?>
						</a>
					</p>
					<p style="margin:12px 0 0 0; padding:10px; background:#fff; border-left:3px solid #ff9800; font-size:12px;">
						<strong><?php esc_html_e( 'Wichtig:', SNAPSHOT_I18N_DOMAIN ); ?></strong> 
						<?php esc_html_e( 'Speichere dieses Skript jetzt! Es ist Deine Rettung im Notfall, wenn die Seite nicht mehr erreichbar ist.', SNAPSHOT_I18N_DOMAIN ); ?>
					</p>
				</div>
			</div>
		</div>
	</div>

</div>

<?php

$this->render( 'boxes/modals/popup-welcome', false, $data, false, false );
$this->render( 'boxes/modals/popup-snapshot', false, $data, false, false );