<?php


/**
 * Class Snapshot_Process_Import_Archives
 */
class Snapshot_Process_Import_Archives {

	/**
	 * @var int
	 */
	public $error_count;

	/**
	 * @param array $error_status
	 */
	private function print_error_status( $error_status ) {

		if ( ! isset( $error_status['errorStatus'] ) ) {
			return;
		}

		if ( $error_status['errorStatus'] ) {

			if ( ! empty( $error_status['errorText'] ) ) {
				echo '<div class="wps-auth-message error"><p>', sprintf( __( 'Fehler: %s', SNAPSHOT_I18N_DOMAIN ), $error_status['errorText'] ), '</p></div>';
				$this->error_count ++;
			}

		} else {
			if ( ! empty( $error_status['responseText'] ) ) {
				echo '<div class="wps-auth-message success"><p>', sprintf( __( 'Erfolg: %s', SNAPSHOT_I18N_DOMAIN ), $error_status['responseText'] ), '</p></div>';
			}

		}
	}

	/**
	 * @param string $dir
	 *
	 * @return bool
	 */
	private function process_local_archives( $dir = '' ) {

		$base_dir = trailingslashit( PSOURCESnapshot::instance()->get_setting( 'backupBaseFolderFull' ) );

		if ( empty( $dir ) ) {
			$dir = $base_dir;
		} else {

			// If the path is relative, append it to the base backup folder
			$base_dir = '/' === substr( $dir, 0, 1 ) ? '' : $base_dir;
			$dir = $base_dir . $dir;
		}

		$dir = trailingslashit( $dir );

		if ( ! is_dir( $dir ) ) {
			return false;
		}

		echo '<div class="wps-notice"><p>', sprintf( __( 'Importiere Archive von: %s', SNAPSHOT_I18N_DOMAIN ), $dir ), '</p></div>';

		$dh = opendir( $dir );

		if ( ! $dh ) {
			return false;
		}

		$restore_folder = trailingslashit( PSOURCESnapshot::instance()->get_setting( 'backupRestoreFolderFull' ) ) . '_imports';

		echo '<ol>';

		while ( false !== ( $file = readdir( $dh ) ) ) {

			if ( $file == '.' || $file == '..' || $file == 'index.php' || $file[0] == '.' ) {
				continue;
			}

			if ( 'zip' !== pathinfo( $file, PATHINFO_EXTENSION ) ) {
				continue;
			}

			$restore_file = $dir . $file;

			if ( is_dir( $restore_file ) ) {
				continue;
			}

			// Check if the archive is full backup - we don't import those
			if ( Snapshot_Helper_Backup::is_full_backup( $file ) ) {
				continue;
			}

			printf( '<li><strong>%s: %s</strong> (%s)<ul><li>',
				__( 'Verarbeite Archiv', SNAPSHOT_I18N_DOMAIN ),
				basename( $restore_file ),
				Snapshot_Helper_Utility::size_format( filesize( $restore_file ) )
			);

			flush();

			$error_status = Snapshot_Helper_Utility::archives_import_proc( $restore_file, $restore_folder );
			$this->print_error_status( $error_status );

			echo '</li></ul></li>';
		}

		echo '</ol>';
		closedir( $dh );
		return true;
	}

	/**
	 * @param string $remote_file
	 */
	private function process_remote_archive( $remote_file ) {

		@set_time_limit( 15 * 60 ); // 15 minutes - technically, server to server should be quick for large files.

		printf( '<p>%s: %ds</p>', __( 'PHP max_execution_time', SNAPSHOT_I18N_DOMAIN ), ini_get( 'max_execution_time' ) );
		printf( '<p>%s: %s</p>', __( 'Versuche, die Remote-Datei herunterzuladen', SNAPSHOT_I18N_DOMAIN ), esc_html( $remote_file ) );

		flush();

		$restore_file = trailingslashit( PSOURCESnapshot::instance()->get_setting( 'backupBaseFolderFull' ) ) . basename( $remote_file );

		Snapshot_Helper_Utility::remote_url_to_local_file( $remote_file, $restore_file );

		if ( ! file_exists( $restore_file ) ) {

			echo "<div class='wps-notice'><p>" . __( 'Lokale Importdatei nicht gefunden. Dies könnte bedeuten, dass die eingegebene URL nicht gültig war oder die Datei nicht öffentlich zugänglich war.', SNAPSHOT_I18N_DOMAIN ) . "</p></div>";
			return;
		}

		$restore_folder = trailingslashit( PSOURCESnapshot::instance()->get_setting( 'backupRestoreFolderFull' ) ) . "_imports";

		echo '<ol>';

		printf( '<li><strong>%s: %s</strong> (%s)<ul><li>',
			__( 'Verarbeite Archiv', SNAPSHOT_I18N_DOMAIN ),
			basename( $restore_file ),
			Snapshot_Helper_Utility::size_format( filesize( $restore_file ) )
		);

		flush();

		$error_status = Snapshot_Helper_Utility::archives_import_proc( $restore_file, $restore_folder );
		$this->print_error_status( $error_status );

		echo '</li></ul></li>';
		echo '</ol>';
	}

	/**
	 *
	 */
	public function process() {
		$this->error_count = 0;

		/* If no URL or directory is specified, check the local directory */
		if ( empty( $_POST['snapshot-import-archive-remote-url'] ) ) {
			$this->process_local_archives();
			return;
		}

		if ( substr( $_POST['snapshot-import-archive-remote-url'], 0, 4 ) != 'http' ) {
			$dir = sanitize_text_field( $_POST['snapshot-import-archive-remote-url'] );

			if ( ! $this->process_local_archives( $dir ) ) {
				echo '<div class="wps-notice"><p>', sprintf( __( 'Die lokale Importdatei %s wurde nicht gefunden. Dies kann bedeuten, dass der eingegebene Pfad ungültig oder nicht erreichbar ist.', SNAPSHOT_I18N_DOMAIN ), $dir ), '</p></div>';
			}

		} else {

			if ( ! function_exists( 'curl_version' ) ) {

				echo '<div class="wps-auth-message error"><p>', __( 'Fehler: Dein Server hat lib_curl nicht installiert. Daher kann der Importprozess keine Remote-Datei abrufen.', SNAPSHOT_I18N_DOMAIN ), '</p></div>';
				return;
			}

			$this->process_remote_archive( esc_url_raw( $_POST['snapshot-import-archive-remote-url'] ) );
		}
	}
}

?>
<section id="header">
	<h1><?php esc_html_e( 'Import', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-import">
	<section class="wpmud-box">

		<div class="wpmud-box-title">
			<h3><?php esc_html_e( 'Lokaler Import', SNAPSHOT_I18N_DOMAIN ); ?></h3>
		</div>

		<div class="wpmud-box-content">
			<form action="?page=snapshot_import" method="post">
				<input type="hidden" value="archives-import" name="snapshot-action">
				<?php wp_nonce_field( 'snapshot-import', 'snapshot-noonce-field' ); ?>

				<div id="wps-import-message" class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<p><?php _e( 'Fehlt ein Snapshot? Du kannst dieses Import-Tool verwenden, um fehlende Snapshots zu finden. Snapshot überprüft automatisch deine Integrationen, aber du kannst auch ein benutzerdefiniertes Verzeichnis unten hinzufügen.', SNAPSHOT_I18N_DOMAIN ); ?></p>
						<div class="wps-notice">
							<h4><?php _e( 'Importoptionen', SNAPSHOT_I18N_DOMAIN); ?></h4>
							<h5><?php _e( 'Remote-Archive', SNAPSHOT_I18N_DOMAIN ); ?></h5>
							<p><?php _e( 'Der Importvorgang kann ein Archiv von einem entfernten Systemserver via FTP, Amazon S3 oder Dropbox importieren. Das entfernte Archiv muss öffentlich zugänglich sein, da dieser Importvorgang noch keine Authentifizierung unterstützt. Hinweise zu den einzelnen Diensten findest Du weiter unten.', SNAPSHOT_I18N_DOMAIN ); ?></p>
							<ul>
								<li><?php _e( '<strong>Remote-FTP:</strong> Beim Herunterladen von einem Remote-FTP-Server musst Du sicherstellen, dass die Datei an einen Ort verschoben wird, an dem sie über eine einfache http://- oder https://-URL erreichbar ist.', SNAPSHOT_I18N_DOMAIN ); ?></li>
								<li><?php _e( '<strong>Dropbox:</strong> Wenn Du versuchst, ein Dropbox-Snapshot-Archiv herunterzuladen, das im Ordner <strong>App/PSOURCE Snapshot</strong> gespeichert ist, musst Du die Datei zuerst in einen öffentlichen Ordner innerhalb Deines Dropbox-Kontos kopieren, bevor Du den öffentlichen Link abrufst.', SNAPSHOT_I18N_DOMAIN ); ?></li>
								<li><?php _e( '<strong>Amazon S3:</strong> Beim Herunterladen einer Datei von S3 musst Du sicherstellen, dass die Datei öffentlich ist.', SNAPSHOT_I18N_DOMAIN ); ?></li>
							</ul>
							<h5><?php _e( 'Lokale Archive', SNAPSHOT_I18N_DOMAIN ); ?></h5>
							<p><?php _e( 'Für Archive, die sich bereits auf Deinem Server befinden, aber nicht in der Liste ALLER Snapshots angezeigt werden, kannst Du dieses Formular einfach absenden, ohne unten einen Wert einzugeben. Dies durchsucht das Snapshot-Archivverzeichnis <strong>/media/storage/www/wp/snapshotold/wp-content/uploads/snapshots</strong> nach fehlenden Archiven und fügt sie der Liste hinzu.', SNAPSHOT_I18N_DOMAIN ); ?></p>
							<p><?php _e( 'Wenn das fehlende Archiv auf dem Server vorhanden ist, aber in einem anderen Pfad gespeichert wurde. Vielleicht hast Du das Archiv so eingerichtet, dass es in einem alternativen Verzeichnis gespeichert wird. Dann kannst Du den vollständigen Serverpfad zum <strong>Verzeichnis</strong> eingeben, in dem sich das Archiv befindet.', SNAPSHOT_I18N_DOMAIN ); ?></p>
						</div>
					</div>
				</div>

				<div id="wps-import-integrations" class="row">
					<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
						<label class="label-box"><?php _e( 'Integrationen', SNAPSHOT_I18N_DOMAIN ); ?></label>
					</div>

					<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
						<div class="wpmud-box-mask">
							<p class="wps-integration-item"><span class="wps-typecon dropbox"></span>Dropbox</p>
							<p class="wps-integration-item"><span class="wps-typecon amazon"></span>Amazon S3</p>
						</div>
					</div>

				</div>

				<div id="wps-import-directory" class="row">
					<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
						<label class="label-box"><?php _e( 'Verzeichnis-URL', SNAPSHOT_I18N_DOMAIN ); ?></label>
					</div>

					<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
						<div class="wpmud-box-mask">
							<input id="snapshot-import-archive-remote-url" type="text"
								   name="snapshot-import-archive-remote-url" class="inline" value=""
								   placeholder="<?php _e( 'Verzeichnis eingeben', SNAPSHOT_I18N_DOMAIN ); ?>"/>

							<p>
								<small><?php printf( __( 'Dein aktuelles Snapshot-Verzeichnis ist %s. Wir werden dieses Verzeichnis auch automatisch überprüfen.', SNAPSHOT_I18N_DOMAIN ), trailingslashit( PSOURCESnapshot::instance()->get_setting( 'backupBaseFolderFull' ) ) ); ?></small>
							</p>

						</div>
					</div>
				</div>

				<div class="row">

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<div class="form-button-container">
							<input id="snapshot-add-button" class="button button-blue float-r" type="submit" value="<?php _e( 'Importieren', SNAPSHOT_I18N_DOMAIN ); ?>">
						</div>

					</div>

				</div>

			</form>

			<div class="row">

				<div class="col-xs-12">

					<?php

					if ( isset( $_REQUEST['snapshot-action'] ) && esc_attr( $_REQUEST['snapshot-action'] ) === "archives-import" ) {
						if ( wp_verify_nonce( $_POST['snapshot-noonce-field'], 'snapshot-import' ) ) {

							$import_class = new Snapshot_Process_Import_Archives();
							$import_class->process();

							if ( $import_class->error_count > 0 ) {
								echo '<div class="wps-auth-message error"><p>', esc_html__( 'Oh nein! Mindestens eines Deiner Snapshot-Archive konnte nicht erfolgreich importiert werden. Bitte überprüfe Deine Snapshot-Protokolle für weitere Details oder versuche die Wiederherstellung in wenigen Augenblicken erneut.', SNAPSHOT_I18N_DOMAIN ), '</p></div>';
							} else {
								echo "<div class='wps-auth-message success'><p>" . esc_html__( 'Während des Importvorgangs wurden keine Fehler festgestellt.', SNAPSHOT_I18N_DOMAIN ) . "</p></div>";
							}

						}
					}

					?>

				</div><?php // .col-xs-12 ?>

			</div><?php // .row ?>

		</div>

	</section>
</div>