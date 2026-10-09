<?php

/**
 * @var PSOURCESnapshot_New_Ui_Tester $this
 * @var SnapshotDestinationGoogleDrive $item_object
 * @var array $item
 */

if ( ! isset( $_GET['item'] ) || empty( $item['name'] ) ) {
	$form_step = 1;
} else if ( empty( $item['clientid'] ) || empty( $item['clientsecret'] ) ) {
	$form_step = 2;
} else if ( empty( $item['access_token'] ) ) {
	$form_step = 3;
} else {
	$form_step = 4;
}

$item = array_merge( array(
	'name' => '',
	'directory' => '',
	'clientid' => '',
	'clientsecret' => '',
), $item );

?>

<input type="hidden" name="snapshot-destination[form-step]" id="snapshot-destination-form-step" value="<?php echo esc_attr( $form_step ); ?>"/>

<div class="form-content">

	<div id="wps-destination-type" class="form-row">
		<div class="form-col-left">
			<label><?php _e( 'Typ', 'snapshot' ); ?></label>
		</div>

		<div class="form-col">
			<i class="wps-typecon google"></i>
			<label><?php _e( 'Google Drive', 'snapshot' ); ?></label>
		</div>
	</div>

	<div id="wps-destination-name" class="form-row">
		<div class="form-col-left">
			<label for="snapshot-destination-name"><?php _e( 'Name', 'snapshot' ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col upload-progress">
			<input type="text" class="inline<?php $this->input_error_class( 'name' ); ?>" name="snapshot-destination[name]" id="snapshot-destination-name" value="<?php echo esc_attr( $item['name'] ); ?>">
			<?php $this->input_error_message( 'name' ); ?>
		</div>
	</div>

	<div id="wps-destination-dir" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-directory"><?php _e( "Verzeichnis-ID", 'snapshot' ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<input type="text" class="inline<?php $this->input_error_class( 'directory' ); ?>" name="snapshot-destination[directory]" id="snapshot-destination-directory"
			       value="<?php echo esc_attr( $item['directory'] ); ?>">

			<?php $this->input_error_message( 'directory' ); ?>

			<p>
				<small>
					<?php

					esc_html_e( "Dies ist kein traditioneller Verzeichnispfad wie /app/snapshot/, sondern eine eindeutige Verzeichnis-ID, die Google Drive für sein Dateisystem verwendet. ", 'snapshot' );
					printf(
							__( 'Um Ihre Verzeichnis-ID abzurufen, folgen Sie <a %s>diesen Anweisungen</a>.', 'snapshot' ),
							'class="show-instructions" data-instructions="#directory-instructions"'
					);

					?>
				</small>
			</p>

			<ol class="instructions" id="directory-instructions">
				<li><?php _e( 'Gehe zu deinem <a href="https://drive.google.com/#my-drive" target="_blank">Drive-Konto</a>. Navigiere zu einem bestehenden Verzeichnis oder erstelle ein neues, in das du die Snapshot-Archive hochladen möchtest. Stelle sicher, dass du das Zielverzeichnis ansiehst.', 'snapshot' ); ?></li>
				<li><?php _e( 'Die URL für das Verzeichnis sieht ungefähr so aus: <em>https://drive.google.com/#folders/0B6GD66ctHXXCOWZKNDRIRGJJXS3</em>. Die Verzeichnis-ID ist der letzte Teil nach <em>/#folders/</em>: <strong>0B6GD66ctHXXCOWZKNDRIRGJJXS3.</strong>', 'snapshot' ); ?></li>
				<li><?php printf( esc_html__( 'Du kannst mehrere Verzeichnis-IDs angeben, getrennt durch ein Komma "%s"', 'snapshot' ),',' ); ?></li>
			</ol>

		</div>

	</div>

	<?php if ( $form_step > 1 ) : ?>

		<div id="wps-destination-clientid" class="form-row">

			<div class="form-col-left">
				<label for="snapshot-destination-clientid"><?php _e( 'Client ID', 'snapshot' ); ?> <span class="required">*</span></label>
			</div>

			<div class="form-col upload-progress">

				<input type="text" class="inline<?php $this->input_error_class( 'clientid' ); ?>" name="snapshot-destination[clientid]" id="snapshot-destination-clientid" value="<?php if ( isset( $item['clientid'] ) ) { echo sanitize_text_field( $item['clientid'] ); } ?>"/>

				<?php $this->input_error_message( 'clientid' ); ?>

				<p><small><?php

					printf(
						__( 'Folge <a %s>diesen Anweisungen</a>, um deine Client-ID und dein Geheimnis abzurufen.', 'snapshot' ),
						'class="show-instructions" data-instructions="#clientid-instructions"'
					);

					?></small></p>

				<ol class="instructions" id="clientid-instructions">
					<li><?php echo sprintf( __( 'Gehe zu %s', 'snapshot' ), '<a href="https://console.developers.google.com/cloud-resource-manager" target="_blank">' . __( 'Google API Console', 'snapshot' ) . '</a>' ) ?></li>
					<li><?php _e( 'Wähle ein bestehendes Projekt aus oder erstelle ein neues. Wenn du ein neues Projekt erstellst, musst du einen Namen eingeben, aber die ID ist nicht wichtig und kann ignoriert werden.', 'snapshot' ); ?></li>
					<li><?php _e( 'Sobald die Projekterstellung abgeschlossen ist, gehe zum <strong>API Manager</strong>. Hier musst du die <strong>Drive API</strong> aktivieren.', 'snapshot' ) ?></li>
					<li><?php _e( 'Gehe als nächstes zum Abschnitt <strong>API Manager > Anmeldedaten</strong>. Klicke auf <strong>Anmeldedaten erstellen > OAuth 2.0-Client-ID</strong>. Wähle im Popup-Fenster den <strong>Anwendungstyp</strong> als <strong>Webanwendung</strong> aus. Kopiere im Feld <strong>Autorisierte Weiterleitungs-URI</strong> den Wert aus dem unten stehenden Feld <strong>Weiterleitungs-URI</strong>. Klicke dann auf die Schaltfläche <strong>Client-ID erstellen</strong>.', 'snapshot' ) ?></li>
					<li><?php _e( 'Nachdem das Popup-Fenster geschlossen wurde, kopiere die Client-ID und das Client-Geheimnis von der Google-Seite und füge sie in die Formularfelder ein.', 'snapshot' ) ?></li>
				</ol>

			</div>

		</div>

		<div id="wps-destination-secretid" class="form-row">

			<div class="form-col-left">
				<label for="snapshot-destination-clientsecret"><?php _e( 'Client Secret', 'snapshot' ); ?> <span class="required">*</span></label>
			</div>

			<div class="form-col upload-progress">

				<input type="password" class="inline<?php $this->input_error_class( 'clientsecret' ); ?>" name="snapshot-destination[clientsecret]" id="snapshot-destination-clientsecret"
				       value="<?php echo esc_attr( $item['clientsecret'] ); ?>">

				<?php $this->input_error_message( 'clientsecret' ); ?>
			</div>

		</div>

		<div id="wps-destination-redirect" class="form-row">

			<div class="form-col-left">
				<label for="snapshot-destination-redirecturi"><?php _e( 'Redirect URL', 'snapshot' ); ?></label>
			</div>

			<div class="form-col">

				<?php

				$item['redirecturi'] = self_admin_url( 'admin.php' );
				$query_vars = array( 'page', 'snapshot-action', 'type', 'item' );

				foreach ( $query_vars as $query_var ) {
					if ( isset( $_GET[ $query_var ] ) ) {
						$query_value = sanitize_text_field( wp_unslash( $_GET[ $query_var ] ) );
						$item['redirecturi'] = add_query_arg( $query_var, $query_value, $item['redirecturi'] );

					}
				}

				?>

				<div class="wps-input--copy">
					<input type="text" name="snapshot-destination[redirecturi]" id="snapshot-destination-redirecturi" class="disabled"
					       value="<?php echo esc_url( $item['redirecturi'] ) ?>">

					<button class="button button-gray copy-to-clipboard" data-clipboard-target="#snapshot-destination-redirecturi">
						<?php esc_html_e('URL kopieren', 'snapshot'); ?>
					</button>
				</div>

				<p><small><?php _e( 'Wenn du deine neuen Anmeldedaten erstellst, füge diese als Weiterleitungs-URL hinzu.', 'snapshot' ); ?></small></p>
			</div>

		</div>

	<?php endif; ?>

	<?php if ( $form_step > 2 ) : ?>

		<div id="wps-destination-auth" class="form-row">

			<div class="form-col-left">
				<label><?php _e( 'Authentifiziert', 'snapshot' ); ?></label>
			</div>

			<div class="form-col">

				<?php

				$auth_error = false;
				$item_object->init();
				$item_object->load_class_destination( $item );

				if ( $form_step > 3 && ! empty( $item_object->destination_info['access_token'] ) ) {

					echo '<div class="wps-auth-message success"><p>';
					esc_html_e( 'Dieses Ziel ist authentifiziert und einsatzbereit.', 'snapshot' );
					echo '</p></div>';

				} else if ( ! empty( $_GET['code'] ) ) {

					$item_object->login();

					if ( is_object( $item_object->client ) ) {

						try {
							$item_object->client->authenticate( $_GET['code'] );

						} catch (Google_0814_Auth_Exception $e) {
							$auth_error = true;
							echo '<div class="wps-auth-message error">';
							echo '<p>', esc_html__( 'Bei der Authentifizierung mit Google ist ein Fehler aufgetreten: ', 'snapshot' ), '<br>', $e->getMessage(), '</p>';
							echo '<p>', esc_html__( 'Bitte überprüfe deine Client-ID und dein Secret, bevor du dieses Formular erneut absendest, um es erneut zu versuchen', 'snapshot' ), '</p>';
							echo '</div>';
						}

						$item_object->destination_info['access_token'] = $item_object->client->getAccessToken();

						if ( ! empty( $item_object->destination_info['access_token'] ) ) {
							echo '<div class="wps-auth-message warning"><p>';
							esc_html_e( 'Erfolg. Das Google Access Token wurde empfangen.', 'snapshot' );
							echo ' <strong>', esc_html__( 'Du musst dieses Formular ein letztes Mal speichern, um das Token zu behalten.', 'snapshot' ), '</strong> ';
							esc_html_e( 'Das gespeicherte Token wird in Zukunft verwendet, wenn eine Verbindung zu Google hergestellt wird.', 'snapshot' );
							echo '</p></div>';
						}
					}

				} else {

					echo '<div class="wps-auth-message warning"><p>';
					esc_html_e( 'Um das Hinzufügen dieses Ziels abzuschließen, musst du es mit Google Drive authentifizieren.', 'snapshot' );
					echo '</p></div>';
				}

				if ( ! $auth_error ) {
					if ( $auth_url = $item_object->getAuthorizationUrl() ) { ?>

						<p class="wps-auth-button">
							<a id="snapshot-destination-authorize-connection" class="button button-blue" href="<?php echo esc_url( $auth_url ); ?>">
								<?php echo empty( $item_object->destination_info['access_token'] ) ?
									esc_html__( 'Autorisieren', 'snapshot' ) :
									esc_html__( 'Erneut autorisieren', 'snapshot' ); ?>
							</a>
						</p>

					<?php } else {
						echo '<div class="wps-auth-message error"><p>', esc_html__( 'Es konnte keine Autorisierungs-URL von Google abgerufen werden', 'snapshot' ), '</p></div>';
					}
				}

				if ( ! empty( $item_object->destination_info['access_token'] ) ) {

					printf(
						'<input type="hidden" name="snapshot-destination[access_token]" id="snapshot-destination-access_token" value="%s">',
						esc_attr( $item_object->destination_info['access_token'] )
					);
				}

				?>

			</div>

		</div>

	<?php endif; ?>

	<input type="hidden" name="snapshot-destination[type]" id="snapshot-destination-type" value="<?php echo $item['type'] ?>"/>

</div>