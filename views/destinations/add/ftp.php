<?php /** @var PSOURCESnapshot_New_Ui_Tester $this */ ?>

<div class="form-content">

	<div id="wps-destination-type" class="form-row">
		<div class="form-col-left">
			<label><?php _e('Typ', SNAPSHOT_I18N_DOMAIN); ?></label>
		</div>

		<div class="form-col">
			<i class="wps-typecon sftp"></i>
			<label><?php _e('FTP', SNAPSHOT_I18N_DOMAIN); ?></label>
		</div>

	</div>

	<div id="wps-destination-name" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-name"><?php _e( "Name", SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col upload-progress">
			<input name="snapshot-destination[name]" id="snapshot-destination-name" type="text" class="inline<?php $this->input_error_class( 'name' ); ?>"
			       value="<?php if ( isset( $item['name'] ) ) { echo esc_attr( stripslashes( $item['name'] ) ); } ?>" />
			<?php $this->input_error_message( 'name' ); ?>
		</div>

	</div>

	<div id="wps-destination-contype" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-protocol"><?php _e( "Verbindungstyp", SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<select class="<?php $this->input_error_class( 'protocol' ); ?>" name="snapshot-destination[protocol]" id="snapshot-destination-protocol">

				<?php foreach ( $item_object->protocols as $_key => $_name ) : ?>

					<option value="<?php echo esc_attr( $_key ); ?>"<?php selected( isset( $item['protocol'] ) && $item['protocol'] == $_key ); ?>>
						<?php echo esc_html( $_name ); ?> (<?php echo $_key ?>)
					</option>

				<?php endforeach; ?>

			</select>

			<?php $this->input_error_message( 'protocol' ); ?>

			<p><small><?php echo sprintf( __( 'Die FTP-Option verwendet die Standard-PHP-Bibliotheksfunktionen. Die Wahl von FTPS verwendet die <a target="_blank" href="%s">PHP Secure Communications Library</a>. Diese Option funktioniert möglicherweise nicht, abhängig davon, wie deine PHP-Binärdateien kompiliert sind. FTPS mit TSL/SSL versucht eine sichere Verbindung herzustellen, funktioniert jedoch nur, wenn PHP und OpenSSL auf deinem Host und dem Zielhost ordnungsgemäß konfiguriert sind. Diese Option funktioniert auch nicht unter Windows mit den Standard-PHP-Binärdateien. Sieh dir die PHP-Dokumentation für ftp_ssl_connection an.', SNAPSHOT_I18N_DOMAIN ), esc_url( '#' ) ); ?></small></p>

		</div>

	</div>

	<div id="wps-destination-host" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-address"><?php _e('Host', SNAPSHOT_I18N_DOMAIN); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<input type="text" name="snapshot-destination[address]" id="snapshot-destination-address" class="<?php $this->input_error_class( 'address' ); ?>"
			       value="<?php if ( isset( $item['address'] ) ) { echo esc_attr( $item['address'] ); } ?>" />

			<span class="inbetween"><?php _e( 'Port', SNAPSHOT_I18N_DOMAIN ); ?></span>

			<input type="text" name="snapshot-destination[port]" id="snapshot-destination-port" class="<?php $this->input_error_class( 'port' ); ?>"
			       value="<?php if ( isset( $item['port'] ) ) { echo esc_attr( $item['port'] ); } ?>" />

			<?php $this->input_error_message( 'address' ); $this->input_error_message( 'port' ); ?>
		</div>

	</div>

	<div id="wps-destination-host" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-username"><?php _e('Benutzer', SNAPSHOT_I18N_DOMAIN); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">
			<input type="text" name="snapshot-destination[username]" id="snapshot-destination-username" class="<?php $this->input_error_class( 'username' ); ?>"
			       value="<?php if ( isset( $item['username'] ) ) { echo esc_attr( $item['username'] ); } ?>">
			<?php $this->input_error_message( 'username' ); ?>
		</div>

	</div>

	<div id="wps-destination-password" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-password"><?php _e('Passwort', SNAPSHOT_I18N_DOMAIN); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">
			<input type="password" name="snapshot-destination[password]" id="snapshot-destination-password" class="<?php $this->input_error_class( 'password' ); ?>"
			       value="<?php if ( isset( $item['password'] ) ) { echo esc_attr( $item['password'] ); } ?>" />
			<?php $this->input_error_message( 'password' ); ?>
		</div>

	</div>

	<div id="wps-destination-dir" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-directory"><?php _e('Verzeichnis', SNAPSHOT_I18N_DOMAIN); ?></label>
		</div>

		<div class="form-col">
			<input type="text" name="snapshot-destination[directory]" id="snapshot-destination-directory" class="<?php $this->input_error_class( 'directory' ); ?>"
			       value="<?php if ( isset( $item['directory'] ) ) { echo esc_attr( $item['directory'] ); } ?>" />

			<?php $this->input_error_message( 'directory' ); ?>

			<p><small><?php _e( "Dieses Verzeichnis wird verwendet, um deine Snapshot-Archive zu speichern und muss bereits auf dem Server existieren. Wenn der Remote-Pfad leer gelassen wird, wird das FTP-Home-Verzeichnis als Ziel für deine Snapshot-Dateien verwendet.", SNAPSHOT_I18N_DOMAIN ); ?></small></p>
		</div>

	</div>

	<?php
	if ( ! isset( $item['passive'] ) ) {
		$item['passive'] = "no";
	} ?>

	<div id="wps-destination-mode" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-passive"><?php _e('Passiven Modus verwenden', SNAPSHOT_I18N_DOMAIN); ?></label>
		</div>

		<div class="form-col">

			<input name="snapshot-destination[passive]" type="hidden" value="no" <?php checked( $item['passive'], "no" ); ?> />

			<div class="wps-input--checkbox">

				<input name="snapshot-destination[passive]" id="snapshot-destination-passive" type="checkbox" value="yes" <?php checked( $item['passive'], "yes" ); ?> />

				<label for="snapshot-destination-passive"></label>

			</div>

			<p><small><?php _e( "Im passiven Modus werden Datenverbindungen vom Client initiiert, anstatt vom Server. Dies kann erforderlich sein, wenn sich der Client hinter einer Firewall befindet. Der passive Modus ist standardmäßig deaktiviert.", SNAPSHOT_I18N_DOMAIN ); ?></small></p>

		</div>

	</div>

	<div id="wps-destination-server" class="form-row">

		<div class="form-col-left">
			<label><?php _e('Server Timeout', SNAPSHOT_I18N_DOMAIN); ?></label>
		</div>

		<div class="form-col">
			<input type="text" name="snapshot-destination[timeout]" id="snapshot-destination-timeout" value="<?php echo ( isset( $item['timeout'] ) ) ? $item['timeout'] : 90 ;?>" style="min-width: 10%;" />

			<p><small><?php _e( "Die Standard-Timeout-Einstellung für PHP-FTP-Verbindungen beträgt 90 Sekunden. Manchmal muss dieses Timeout für langsamere Verbindungen zu stark ausgelasteten Servern länger sein.", SNAPSHOT_I18N_DOMAIN ); ?></small></p>

			<button id="snapshot-destination-test-connection" class="button button-gray"><?php _e( "Verbindung testen", SNAPSHOT_I18N_DOMAIN ); ?></button>
			<div id="snapshot-ajax-destination-test-result" style="display:none"></div>
		</div>

	</div>

	<input type="hidden" name="snapshot-destination[type]" id="snapshot-destination-type" value="<?php echo $item['type'] ?>"/>

</div>
