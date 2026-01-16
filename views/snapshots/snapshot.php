<?php

/**
 * @global array $item
 * @global string $action
 */

$action = 'add' == $action ? 'add' : 'update';
$update = $action == 'update';

$time_key = time();

while ( isset( PSOURCESnapshot::instance()->config_data['items'][ $time_key ] ) ) {
	$time_key = time();
}

if ( ! $update ) {
	$item['timestamp'] = $time_key;
}

$requirements_test = Snapshot_Helper_Utility::check_system_requirements();
$checks = $requirements_test['checks'];
$all_good = $requirements_test['all_good'];
$warning = $requirements_test['warning'];

?>

<section id="header">
	<h1><?php esc_html_e( 'Snapshots', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<?php $this->render( 'snapshots/partials/create-snapshot-progress', false, array( 'item' => $item, 'time_key' => $time_key ), false, false ); ?>

<form id="snapshot-add-update" method="post" action="<?php echo PSOURCESnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ); ?>">
	<input type="hidden" id="snapshot-action" name="snapshot-action" value="<?php echo $update ? 'update' : 'add' ?>">
	<input type="hidden" id="snapshot-item" name="snapshot-item" value="<?php echo $item['timestamp']; ?>">
	<input type="hidden" id="snapshot-data-item" name="snapshot-data-item" value="<?php echo $time_key; ?>">

	<?php wp_nonce_field( 'snapshot-' . $action, 'snapshot-noonce-field' ); ?>
	<div id="container" class="snapshot-three wps-page-wizard">

		<section class="wpmud-box new-snapshot-main-box">

			<?php if ( $update ) : ?>

				<div class="wpmud-box-title">
					<h3><?php _e( 'Snapshot bearbeiten', SNAPSHOT_I18N_DOMAIN ); ?>: <?php echo $item['name'] ?></h3>
				</div>

			<?php else : ?>

				<div class="wpmud-box-title has-button">
					<h3><?php _e( 'Snapshot-Assistent', SNAPSHOT_I18N_DOMAIN ); ?></h3>

					<a href="<?php echo esc_url( PSOURCESnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>"
					   class="button button-small button-gray button-outline"><?php _e( 'Zurück', SNAPSHOT_I18N_DOMAIN ); ?></a>

				</div>

			<?php endif; ?>

			<div class="wpmud-box-content">

				<?php $this->render( "common/requirements-test", false, $requirements_test, false, false ); ?>

				<div class="wpmud-box-tab configuration-box<?php echo $all_good ? ' open' : ''; ?>">

					<div class="wpmud-box-tab-title can-toggle">
						<h3><?php _e( 'Konfiguration', SNAPSHOT_I18N_DOMAIN ); ?></h3>
						<?php if ( $all_good ): ?>
							<i class="wps-icon i-arrow-right"></i>
						<?php endif; ?>
					</div>

					<?php if ( $all_good ): ?>

						<div class="wpmud-box-tab-content">

							<div id="wps-check-notice" class="row">

								<div class="col-xs-12">

									<div class="wps-auth-message <?php echo $all_good ? ( $warning ? 'warning' : 'success' ) : 'error'; ?>">
										<?php if ( ! $all_good ) { ?>
											<p><?php _e( 'Du musst die Serveranforderungen erfüllen, bevor du fortfahren kannst.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } else if ( $warning ) { ?>
											<p><?php _e( 'Du hast 1 oder mehr Warnungen zu den Anforderungen. Du kannst fortfahren, aber Snapshot kann aufgrund der Warnungen auf Probleme stoßen.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } else { ?>
											<p><?php _e( 'Du erfüllst die Serveranforderungen. Du kannst jetzt fortfahren.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } ?>
									</div>

								</div>

							</div>

							<?php if ( ! $update && is_multisite() ) { ?>

								<div id="wps-new-subsite" class="row">

									<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

										<label class="label-box"><?php _e( 'Blog zum Sichern', SNAPSHOT_I18N_DOMAIN ); ?></label>

									</div>

									<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
										<?php

										$submitted = isset( $item['blog-id'] );

										if ( $submitted ) {
											$blog_info = get_blog_details( $item['blog-id'] );
										}

										?>

										<div class="wpmud-box-mask">
											<div class="wps-subsite-map">

												<?php

												if ( $submitted ) {
													if ( isset( $blog_info ) ) {
														printf( '%s (%s)', esc_html( $blog_info->blogname ), esc_html( $blog_info->domain ) );
													} else {
														_e( 'Unbekannter Blog', SNAPSHOT_I18N_DOMAIN );
													}
												} else { ?>


													<input type="hidden" name="snapshot-blog-id" id="snapshot-blog-id"
													       value="<?php echo esc_attr( $GLOBALS['current_blog']->blog_id ); ?>" />

													<div id="snapshot-blog-search-success" style="display: block;">
														<span id="snapshot-blog-name">
															<?php echo esc_html( trailingslashit( site_url() ) ); ?>
														</span>
														<button id="snapshot-blog-id-change" class="button button-small button-gray button-outline">
															<?php _e( 'Ändern', SNAPSHOT_I18N_DOMAIN ); ?>
														</button>
													</div>
													<div id="snapshot-blog-search" style="display: none;">
														<span id="snapshot-blog-search-error" style="color: #FF0000; display: none;">
															<?php _e( 'Fehler bei der Blog-Suche. Versuche es erneut', SNAPSHOT_I18N_DOMAIN ); ?>
															<br>
														</span>
														<?php

														if ( ! is_subdomain_install() ) {
															echo esc_html( trailingslashit( site_url() ) );
														} ?>

														<input name="snapshot-blog-id-search" id="snapshot-blog-id-search" value="" style="width: 20%;">

														<?php

														if ( is_subdomain_install() ) {
															$blog_path = trailingslashit( network_site_url( $GLOBALS['current_blog']->path ) );
															$blog_path = preg_replace( '/(http|https):\/\/|/', '', $blog_path );

															printf( '.%s', esc_html( $blog_path ) );
														} ?>

														<span class="wps-spinner" style="display: none;"></span>

														<p class="description">
															<small>
																<?php if ( is_subdomain_install() ) {
																	_e( 'Gib das Blog-Subdomain-Präfix ein (z.B. site1), die Blog-ID (z.B. 22) oder die zugeordnete Domain, oder lasse das Feld für die Hauptseite leer.', SNAPSHOT_I18N_DOMAIN );
																} else {
																	_e( 'Gib den Pfad, die Blog-ID (z.B. 22) ein oder lasse das Feld für die Hauptseite leer.', SNAPSHOT_I18N_DOMAIN );
																}
																_e( ' Sobald das Formular abgeschickt wurde, kann dies nicht mehr geändert werden.', SNAPSHOT_I18N_DOMAIN );
																?>
															</small>
														</p>

														<div class="wps-subsite-btns">
															<button id="snapshot-blog-id-lookup" class="button button-small button-blue">
																<?php _e( 'Suchen', SNAPSHOT_I18N_DOMAIN ); ?>
															</button>
															<button id="snapshot-blog-id-cancel" class="button button-small button-gray">
																<?php _e( 'Abbrechen', SNAPSHOT_I18N_DOMAIN ); ?>
															</button>
														</div>

													</div>

												<?php } ?>

											</div><!-- #wps-subsite-map -->
										</div><!-- #wpmud-box-mask -->
									</div>

								</div>

								<?php

							} elseif ( ! $update ) {
								printf( '<input type="hidden" id="snapshot-blog-id" name="snapshot-blog-id" value="%d">', $GLOBALS['wpdb']->blogid );
							}

							?>

							<div id="wps-new-destination" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Speicherort', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php
											printf(
												__( 'Wähle, wohin dieser Snapshot gesendet werden soll. Neue Speicherorte können über den Tab <a href="%s">Speicherorte</a> hinzugefügt werden.', SNAPSHOT_I18N_DOMAIN ),
												esc_url( PSOURCESnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-destinations' ) )
											); ?></label>

										<?php
										$all_destinations = PSOURCESnapshot::instance()->config_data['destinations'];

										if ( ! isset( $item['destination'] ) ) {
											$item['destination'] = "local";
										}
										$selected_destination = $item['destination'];
										$destinationClasses = PSOURCESnapshot::instance()->get_setting( 'destinationClasses' );

										// This global is set within the next calling function. Helps determine which set of descriptions to show.
										global $snapshot_destination_selected_type;

										Snapshot_Helper_UI::destination_select_radio_boxes( $all_destinations, $selected_destination, $destinationClasses );
										?>

									</div>

								</div>

							</div>

							<div id="wps-custom-directory" class="row">
								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
									<label class="label-box" for="snapshot-destination-directory">
										<?php esc_html_e( 'Directory (optional)', SNAPSHOT_I18N_DOMAIN ); ?>
									</label>
								</div>
								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
									<input
										type="text"
										id="snapshot-destination-directory"
										name="snapshot-destination-directory"
										value="<?php
										echo ! empty( $item['destination-directory'] )
											? esc_attr( $item['destination-directory'] )
											: '';
										?>"
									/>
									<p>
										<?php esc_html_e( 'Das optionale Verzeichnis kann verwendet werden, um den ausgewählten Speicherort-Verzeichniswert zu überschreiben oder zu ergänzen.', SNAPSHOT_I18N_DOMAIN ); ?>
										<?php esc_html_e( 'Wenn "lokaler Server" ausgewählt ist und das Verzeichnis nicht mit einem Schrägstrich "/" beginnt, ist das Verzeichnis relativ zum Stammverzeichnis der Website.', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
									<p>
										<?php esc_html_e( 'Dieses Feld unterstützt Tokens, mit denen du dynamische Werte erstellen kannst.', SNAPSHOT_I18N_DOMAIN ); ?>
										<?php esc_html_e( 'Du kannst eine beliebige Kombination der folgenden Tokens verwenden.', SNAPSHOT_I18N_DOMAIN ); ?>
										<?php esc_html_e( 'Verwende den Schrägstrich "/" um Verzeichniselemente zu trennen.', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
									<p>
										<code>[DEST_PATH]</code> -
										<?php esc_html_e( 'Dies stellt das Verzeichnis/Bucket dar, das vom ausgewählten Sicherungsziel verwendet wird oder, wenn lokal, den Speicherort des Einstellungsordners. Dies kann verwendet werden, um den in diesem Snapshot eingegebenen Wert zu ergänzen. Wenn [DEST_PATH] nicht verwendet wird, überschreibt der Verzeichniswert hier den vollständigen Wert des ausgewählten Ziels.', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
									<p>
										<code>[SITE_DOMAIN]</code> -
										<?php esc_html_e( 'Dies stellt die vollständige Domain der ausgewählten Website für diesen Snapshot dar.', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
									<p>
										<code>[SNAPSHOT_ID]</code> -
										<?php esc_html_e( 'Dies ist die eindeutige ID, die diesem Snapshot zugewiesen wurde.', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
								</div>
							</div>

							<div id="wps-new-files" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Dateien', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php _e( 'Wähle aus, welche Dateien du einbeziehen möchtest.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<?php

										if ( ! isset( $item['blog-id'] ) ) {
											$item['blog-id'] = $GLOBALS['wpdb']->blogid;
										}

										if ( ! isset( $item['files-option'] ) ) {
											$item['files-option'] = 'all'; // Default to all files
										}

										if ( ! isset( $item['files-sections'] ) ) {
											$item['files-sections'] = array();
										} ?>

										<div class="wps-input--group">

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-files-option" id="snapshot-files-option-none" value="none"
													       name="snapshot-files-option"<?php checked( $item['files-option'], 'none' ); ?>>

													<label for="snapshot-files-option-none"></label>

												</div>

												<label for="snapshot-files-option-none"><?php _e( "Keine Dateien einbeziehen", SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<?php $blog_upload_path = Snapshot_Helper_Utility::get_blog_upload_path( $item['blog-id'] );

											if ( ! empty( $blog_upload_path ) ) { ?>

												<div class="wps-input--item">

													<div class="wps-input--radio">

														<input type="radio" class="snapshot-files-option" id="snapshot-files-option-all" value="all" name="snapshot-files-option"<?php checked( $item['files-option'], 'all' ); ?>>

														<label for="snapshot-files-option-all"></label>

													</div>

													<label for="snapshot-files-option-all">
														<?php _e( 'Gemeinsame Dateien einbeziehen', SNAPSHOT_I18N_DOMAIN ); ?>:
														<span class="snapshot-backup-files-sections-main-only"<?php if ( ! is_main_site( $item['blog-id'] ) ) {
															echo ' style="display:none" ';
														} ?>>
														<?php _e( 'Themes, Plugins,', SNAPSHOT_I18N_DOMAIN ); ?>
													</span>
														<?php _e( 'Medien', SNAPSHOT_I18N_DOMAIN ); ?>
														(<span class="snapshot-media-upload-path"><?php echo $blog_upload_path; ?></span>)
													</label>

												</div>

											<?php } ?>

											<div class="wps-input--item">

												<div class="wps-input--radio">
													<input type="radio" class="snapshot-files-option" id="snapshot-files-option-selected" value="selected" name="snapshot-files-option"<?php checked( $item['files-option'], 'selected' ); ?>>
													<label for="snapshot-files-option-selected"></label>
												</div>

												<label for="snapshot-files-option-selected"><?php _e( 'Nur ausgewählte Dateien einbeziehen', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<div id="snapshot-selected-files-container"<?php if ( 'none' === $item['files-option'] || 'all' === $item['files-option'] ) {
												echo ' class="hidden"';
											} ?>>

												<ul id="snapshot-select-files-option" class="wpmud-box-gray">

													<li class="wps-input--item">

														<div class="wps-input--checkbox">

															<input type="checkbox" class="snapshot-backup-sub-options" <?php if ( array_search( 'themes', $item['files-sections'] ) !== false ) {
																echo ' checked="checked" ';
															} ?> id="snapshot-files-option-themes" value="themes" name="snapshot-files-sections[themes]">

															<label for="snapshot-files-option-themes"></label>

														</div>

														<label for="snapshot-files-option-themes"><?php _e( 'Alle Themes', SNAPSHOT_I18N_DOMAIN ); ?></label>

													</li>

													<li class="wps-input--item">

														<div class="wps-input--checkbox">

															<input type="checkbox" class="snapshot-backup-sub-options" <?php if ( array_search( 'plugins', $item['files-sections'] ) !== false ) {
																echo ' checked="checked" ';
															} ?> id="snapshot-files-option-plugins" value="plugins" name="snapshot-files-sections[plugins]">

															<label for="snapshot-files-option-plugins"></label>

														</div>

														<label for="snapshot-files-option-plugins"><?php _e( 'Alle Plugins', SNAPSHOT_I18N_DOMAIN ); ?></label>

													</li>

													<?php if ( is_multisite() ) { ?>

														<li class="wps-input--item">

															<div class="wps-input--checkbox">

																<input type="checkbox" class="snapshot-backup-sub-options" <?php if ( array_search( 'plugins', $item['files-sections'] ) !== false ) {
																	echo ' checked="checked" ';
																} ?> id="snapshot-files-option-mu-plugins" value="mu-plugins" name="snapshot-files-sections[mu-plugins]">

																<label for="snapshot-files-option-mu-plugins"></label>

															</div>

															<label for="snapshot-files-option-mu-plugins"><?php _e( 'MU-Plugins: Alle aktiven und inaktiven Plugins werden einbezogen', SNAPSHOT_I18N_DOMAIN ); ?></label>

														</li>

													<?php } ?>

													<li class="wps-input--item">

														<div class="wps-input--checkbox">

															<input type="checkbox" class="snapshot-backup-sub-options" <?php if ( array_search( 'media', $item['files-sections'] ) !== false ) {
																echo ' checked="checked" ';
															} ?> id="snapshot-files-option-media" value="media" name="snapshot-files-sections[media]">

															<label for="snapshot-files-option-media"></label>

														</div>

														<label for="snapshot-files-option-media"><?php _e( 'Mediendateien:', SNAPSHOT_I18N_DOMAIN ); ?>
															<span class="snapshot-media-upload-path"><?php echo Snapshot_Helper_Utility::get_blog_upload_path( $item['blog-id'] ); ?></span></label>

													</li>

													<li class="wps-input--item">

														<div class="wps-input--checkbox">

															<input type="checkbox" class="snapshot-backup-sub-options" <?php if ( array_search( 'config', $item['files-sections'] ) !== false ) {
																echo ' checked="checked" ';
															} ?> id="snapshot-files-option-config" value="config" name="snapshot-files-sections[config]">

															<label for="snapshot-files-option-config"></label>

														</div>

														<label for="snapshot-files-option-config"><?php _e( 'wp-config.php', SNAPSHOT_I18N_DOMAIN ); ?></label>

													</li>

													<li class="wps-input--item">

														<div class="wps-input--checkbox">

															<input type="checkbox" class="snapshot-backup-sub-options" <?php if ( array_search( 'htaccess', $item['files-sections'] ) !== false ) {
																echo ' checked="checked" ';
															} ?> id="snapshot-files-option-htaccess" value="htaccess" name="snapshot-files-sections[htaccess]">

															<label for="snapshot-files-option-htaccess"></label>

														</div>

														<label for="snapshot-files-option-htaccess"><?php _e( '.htaccess', SNAPSHOT_I18N_DOMAIN ); ?></label>

													</li>

												</ul>

											</div>

											<?php if ( ! isset( $item['destination-sync'] ) ) {
												$item['destination-sync'] = "archive";
											} ?>

											<?php if ( Snapshot_Helper_Utility::is_pro() ) { ?>

												<div id="snapshot-selected-files-sync-container">

													<label class="label-title"><?php _e( 'Nur Dropbox - Wähle für diesen Snapshot die Option Archivieren oder Spiegeln .', SNAPSHOT_I18N_DOMAIN ); ?></label>

													<ul class="wpmud-box-gray wps-input--group">

														<?php $_is_mirror_disabled = ' disabled="disabled" ';

														if ( isset( $item['destination'] ) ) {

															$destination_key = $item['destination'];

															if ( isset( PSOURCESnapshot::instance()->config_data['destinations'][ $destination_key ] ) ) {

																$destination = PSOURCESnapshot::instance()->config_data['destinations'][ $destination_key ];

																if ( ( isset( $destination['type'] ) ) && ( $destination['type'] == "dropbox" ) ) {

																	$_is_mirror_disabled = '';

																}

															}

														} ?>

														<li class="wps-input--item">

															<div class="wps-input--radio">

																<input type="radio" name="snapshot-destination-sync" id="snapshot-destination-sync-archive" value="archive" class="snapshot-destination-sync" <?php if ( $item['destination-sync'] == "archive" ) {
																	echo ' checked="checked" ';
																} ?> />

																<label for="snapshot-destination-sync-archive"></label>

															</div>

															<label for="snapshot-destination-sync-archive"><?php _e( '<strong>Archiv</strong> – (Standard) Wenn Du Archiv auswählst, wird ein ZIP-Archiv erstellt. Dies ist die Standardmethode zum Sichern Deiner Webseite. Es wird ein einzelnes ZIP-Archiv für Dateien und Datenbanktabellen erstellt.', SNAPSHOT_I18N_DOMAIN ); ?></label>

														</li>

														<li class="wps-input--item">

															<div class="wps-input--radio">

																<input type="radio" <?php echo $_is_mirror_disabled; ?> name="snapshot-destination-sync" id="snapshot-destination-sync-mirror" value="mirror" class="snapshot-destination-sync" <?php if ( $item['destination-sync'] == "mirror" ) {
																	echo ' checked="checked" ';
																} ?>/>

																<label for="snapshot-destination-sync-mirror"></label>

															</div>

															<label for="snapshot-destination-sync-mirror"><?php _e( '<strong>Spiegeln/Synchronisieren</strong> – <strong>NUR Dropbox</strong> Wähle Spiegeln, wenn Du die Dateistruktur Deiner Website in Dropbox replizieren möchtest. Falls Du Datenbanktabellen einbeziehst, werden diese als ZIP-Archiv hinzugefügt. <strong>Für „Spiegeln/Synchronisieren" gibt es derzeit keine Wiederherstellungsoption.</strong>', SNAPSHOT_I18N_DOMAIN ); ?></label>

														</li>

													</ul>

												</div>

											<?php } ?>

											<label class="label-title"><?php _e( 'Füge benutzerdefinierte URLs hinzu, die in diesem Snapshot nicht enthalten sein sollen.', SNAPSHOT_I18N_DOMAIN ); ?></label>

											<textarea name="snapshot-files-ignore" id="snapshot-files-ignore" cols="20" rows="5"><?php if ( ( isset( $item['files-ignore'] ) ) && ( count( $item['files-ignore'] ) ) ) {
													echo implode( "\n", $item['files-ignore'] );
												} ?></textarea>

											<p>
												<small>
													<?php
													_e( 'URLs können auf Dateien verweisen und müssen jeweils in einer eigenen Zeile angegeben werden. Die Ausschlussfunktion verwendet Mustervergleich. Die Eingabe von twentyten schließt also den Ordner twentyten sowie alle Filter aus, deren Dateiname twentyten enthält.', SNAPSHOT_I18N_DOMAIN ); ?>
												</small>
											</p>
											<p>
												<small>
													<?php _e( 'Beispiel: Um das Twenty Ten-Theme auszuschließen, kannst Du twentyten, theme/twentyten oder public/wp-content/theme/twentyten verwenden. <strong>Der lokale Ordner ist standardmäßig von Snapshot-Backups ausgeschlossen.</strong>', SNAPSHOT_I18N_DOMAIN ); ?>
												</small>
											</p>

										</div>

									</div>

								</div>

							</div>

							<div id="wps-new-database" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Datenbank', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<?php if ( ! isset( $item['blog-id'] ) ) {
											$item['blog-id'] = $wpdb->blogid;
										}

										$table_sets = Snapshot_Helper_Utility::get_database_tables( $item['blog-id'] );

										if ( isset( PSOURCESnapshot::instance()->config_data['config']['tables_last'][ $item['blog-id'] ] ) ) {

											$blog_tables_last = PSOURCESnapshot::instance()->config_data['config']['tables_last'][ $item['blog-id'] ];

										} else {

											$blog_tables_last = array();

										}

										if ( ! isset( $item['tables-option'] ) ) {

											$item['tables-option'] = "all";

										} ?>

										<label class="label-title"><?php _e( 'Wähle aus, welche Datenbanktabellen Du einbeziehen möchtest.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<div class="wps-input--group">

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-none" value="none" <?php if ( $item['tables-option'] == "none" ) {
														echo ' checked="checked" ';
													} ?> name="snapshot-tables-option">

													<label for="snapshot-tables-option-none"></label>

												</div>

												<label for="snapshot-tables-option-none"><?php _e( 'Keine Datenbanktabellen einbeziehen', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-all" value="all" <?php if ( $item['tables-option'] == "all" ) {
														echo ' checked="checked" ';
													} ?> name="snapshot-tables-option">

													<label for="snapshot-tables-option-all"></label>

												</div>

												<label for="snapshot-tables-option-all"><?php _e( 'Alle Datenbanktabellen einbeziehen', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-selected" value="selected" <?php if ( $item['tables-option'] == "selected" ) {
														echo ' checked="checked" ';
													} ?> name="snapshot-tables-option">

													<label for="snapshot-tables-option-selected"></label>

												</div>

												<label for="snapshot-tables-option-selected"><?php _e( 'Nur ausgewählte Datenbanktabellen einbeziehen', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

										<div id="snapshot-selected-tables-container" class="wpmud-box-gray" style=" <?php if ( ( $item['tables-option'] == "none" ) || ( $item['tables-option'] == "all" ) ) {
											echo ' display:none; ';
										} ?>">

											<?php
											$tables_sets_idx = array(
												'global' => __( "WordPress Globale Tabellen", SNAPSHOT_I18N_DOMAIN ),
												'wp'     => __( "WordPress Kern Tabellen", SNAPSHOT_I18N_DOMAIN ),
												'non'    => __( "Nicht-WordPress Tabellen", SNAPSHOT_I18N_DOMAIN ),
												'other'  => __( "Andere Tabellen", SNAPSHOT_I18N_DOMAIN ),
												'error'  => __( "Fehlerhafte Tabellen - Diese Tabellen werden aus den angegebenen Gründen übersprungen.", SNAPSHOT_I18N_DOMAIN )
											);

											foreach ( $tables_sets_idx as $table_set_key => $table_set_title ) {

												if ( ( isset( $table_sets[ $table_set_key ] ) ) && ( count( $table_sets[ $table_set_key ] ) ) ) {

													$display_set = 'block';

												} else {

													$display_set = 'none';

												} ?>

												<div id="snapshot-tables-<?php echo $table_set_key ?>-set" style="display: <?php echo $display_set; ?>">

													<h3><?php echo $table_set_title; ?><?php if ( $table_set_key != 'error' ) { ?>
															<a class="snapshot-table-select-all" href="#" id="snapshot-table-<?php echo $table_set_key ?>-select-all"><?php _e( 'Select all', SNAPSHOT_I18N_DOMAIN ); ?></a><?php } ?>
													</h3>

													<?php if ( $table_set_key == "global" ) { ?>

														<p class="description"><?php _e( 'Diese globalen Benutzertabellen enthalten blogspezifische Benutzerinformationen, die als Teil des Snapshot-Archivs einbezogen werden können. Nur Benutzer, deren primäres Blog mit diesem ausgewählten Blog übereinstimmt, werden einbezogen. <strong>Superadmin-Benutzer werden im Sub-Site-Archiv nicht einbezogen.</strong>', SNAPSHOT_I18N_DOMAIN ); ?></p>

													<?php } ?>

													<ul class="snapshot-table-list" id="snapshot-table-list-<?php echo $table_set_key; ?>">

														<?php if ( ( isset( $table_sets[ $table_set_key ] ) ) && ( count( $table_sets[ $table_set_key ] ) ) ) {

															$tables = $table_sets[ $table_set_key ];

															foreach ( $tables as $table_key => $table_name ) {

																$is_checked = '';

																if ( $table_set_key == 'error' ) { ?>

																	<li style="clear:both"><?php echo $table_name['name']; ?>
																		&ndash; <?php echo $table_name['reason']; ?></li>

																<?php } else {

																	if ( isset( $_REQUEST['backup-tables'] ) ) {

																		if ( isset( $_REQUEST['backup-tables'][ $table_set_key ][ $table_key ] ) ) {
																			$is_checked = ' checked="checked" ';
																		}

																	} else {

																		if ( isset( $_GET['page'] ) && $_GET['page'] == "snapshots_new_panel" ) {
																			if ( isset( $blog_tables_last[ $table_set_key ] ) && array_search( $table_key, $blog_tables_last[ $table_set_key ] ) !== false ) {
																				$is_checked = ' checked="checked" ';
																			}
																		}

																		if ( isset( $_GET['page'] ) && ( $_GET['page'] === "snapshot_snapshots" || $_GET['page'] === 'snapshot_snapshots' ) ) {

																			if ( isset( $item['tables-sections'] ) ) {

																				if ( isset( $item['tables-sections'][ $table_set_key ][ $table_key ] ) ) {
																					$is_checked = ' checked="checked" ';
																				}

																			} else if ( isset( $item['tables'] ) ) {

																				if ( array_search( $table_key, $item['tables'] ) !== false ) {
																					$is_checked = ' checked="checked" ';
																				}
																			}
																		}

																	} ?>

																	<li class="wps-input--item">

																		<div class="wps-input--checkbox">

																			<input type="checkbox" <?php echo $is_checked; ?> class="snapshot-table-item" id="snapshot-tables-<?php echo $table_key; ?>" value="<?php echo $table_key; ?>" name="snapshot-tables[<?php echo $table_set_key; ?>][<?php echo $table_key; ?>]">

																			<label for="snapshot-tables-<?php echo $table_key; ?>"></label>

																		</div>

																		<label for="snapshot-tables-<?php echo $table_key; ?>"><?php echo $table_name; ?></label>

																	</li>

																<?php }

															}

														} else { ?>

															<li><?php _e( 'Keine Tabellen', SNAPSHOT_I18N_DOMAIN ) ?></li>

														<?php } ?>
													</ul>

												</div>

											<?php } ?>

										</div>

									</div>

								</div>

							</div>

							<div id="wps-new-frequency" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Frequenz', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php _e( 'Möchtest Du diesen Snapshot regelmäßig oder einmalig planen?', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<div class="wps-input--group">

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input id="frequency-once" type="radio" name="frequency" value="once"<?php
													checked( ! $update || isset( $item['interval'] ) && $item['interval'] === 'immediate' ); ?>>

													<label for="frequency-once"></label>

												</div>

												<label for="frequency-once"><?php _e( 'Einmalig', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input id="frequency-daily" type="radio" name="frequency" value="schedule"<?php
													checked( $update && isset( $item['interval'] ) && $item['interval'] !== 'immediate' ); ?>>

													<label for="frequency-daily"></label>

												</div>

												<label for="frequency-daily"><?php _e( 'Täglich, wöchentlich oder monatlich ausführen', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

										<div id="snapshot-schedule-options-container" class="wpmud-box-gray">

											<h3><?php _e( 'Zeitplan', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<input type="hidden" id="snapshot-immediate" name="snapshot-interval" checked="checked" value="immediate" />

											<div class="schedule-inline-form">

												<select name="snapshot-interval" id="snapshot-interval">

													<?php if ( isset( $item['interval'] ) ) {
														$item_interval = $item['interval'];
													} else {
														$item_interval = 'snapshot-weekly';
													}

													$scheds = (array) wp_get_schedules();
													foreach ( $scheds as $sched_key => $sched_item ) {
														if ( ! in_array( $sched_key, array( 'snapshot-daily', 'snapshot-weekly', 'snapshot-monthly' ) ) ) {
															continue;
														}
														if ( substr( $sched_key, 0, strlen( 'snapshot-' ) ) == "snapshot-" ) {
															?>
															<option value="<?php echo $sched_key; ?>" <?php
															if ( $item_interval == $sched_key ) {
																echo ' selected="selected" ';
															} ?>><?php
															echo $sched_item['display']; ?></option><?php
														}
													}
													?>

												</select>

												<?php if ( ( ! defined( 'DISABLE_WP_CRON' ) ) || ( DISABLE_WP_CRON == false ) ) : ?>

												<?php
												$default_time = new DateTime( 'monday 4am' );
												$timestamp = $default_time->format( 'U' ) + ( get_option( 'gmt_offset' ) * 3600 );
												$localtime = localtime( $timestamp, true );

												// Ensure defaults for interval offsets so selectors are always visible on new snapshots
												if ( ! isset( $item['interval-offset'] ) || ! is_array( $item['interval-offset'] ) ) {
													$item['interval-offset'] = array();
												}
												if ( ! isset( $item['interval-offset']['snapshot-daily']['tm_hour'] ) ) {
													$item['interval-offset']['snapshot-daily']['tm_hour'] = $localtime['tm_hour'];
												}
												if ( ! isset( $item['interval-offset']['snapshot-weekly']['tm_wday'] ) ) {
													$item['interval-offset']['snapshot-weekly']['tm_wday'] = $localtime['tm_wday'];
												}
												if ( ! isset( $item['interval-offset']['snapshot-weekly']['tm_hour'] ) ) {
													$item['interval-offset']['snapshot-weekly']['tm_hour'] = $localtime['tm_hour'];
												}
												if ( ! isset( $item['interval-offset']['snapshot-monthly']['tm_mday'] ) ) {
													$item['interval-offset']['snapshot-monthly']['tm_mday'] = 1;
												}
												if ( ! isset( $item['interval-offset']['snapshot-monthly']['tm_hour'] ) ) {
													$item['interval-offset']['snapshot-monthly']['tm_hour'] = $localtime['tm_hour'];
												}
												?>

													<div id="interval-offset">
														<!-- Daily -->
														<div class="interval-offset-daily" <?php
														if ( ( $item_interval == "snapshot-daily" ) || ( $item_interval == "snapshot-twicedaily" ) ) {
															echo ' style="display: inline-flex;" ';
														} else {
															echo ' style="display: none;" ';
														} ?> >
															<span class="inbetween"><?php _e( 'um', SNAPSHOT_I18N_DOMAIN ); ?></span>
															<select id="snapshot-interval-offset-daily-hour"
																	name="snapshot-interval-offset[snapshot-daily][tm_hour]">
																<?php
																if ( ! isset( $item['interval-offset']['snapshot-daily']['tm_hour'] ) ) {
																	$item['interval-offset']['snapshot-daily']['tm_hour'] = $localtime['tm_hour'];
																}

																Snapshot_Helper_UI::form_show_hour_selector_options( $item['interval-offset']['snapshot-daily']['tm_hour'] );
																?>
															</select>&nbsp;&nbsp;
														</div>

														<!-- Weekly -->
														<div class="interval-offset-weekly" <?php
														if ( ( $item_interval == "snapshot-weekly" ) || ( $item_interval == "snapshot-twiceweekly" ) ) {
															echo ' style="display: inline-flex;" ';
														} else {
															echo ' style="display: none;" ';
														} ?> >
															<span class="inbetween"><?php _e( 'am', SNAPSHOT_I18N_DOMAIN ); ?></span>
															<select id="snapshot-interval-offset-weekly-wday"
																	name="snapshot-interval-offset[snapshot-weekly][tm_wday]">
																<?php
																if ( ! isset( $item['interval-offset']['snapshot-weekly']['tm_wday'] ) ) {
																	$item['interval-offset']['snapshot-weekly']['tm_wday'] = $localtime['tm_wday'];
																}

																Snapshot_Helper_UI::form_show_wday_selector_options( $item['interval-offset']['snapshot-weekly']['tm_wday'] );
																?>
															</select>&nbsp;&nbsp;

															<span class="inbetween"><?php _e( 'um', SNAPSHOT_I18N_DOMAIN ); ?></span>
															<select id="snapshot-interval-offset-weekly-hour"
																	name="snapshot-interval-offset[snapshot-weekly][tm_hour]">
																<?php
																if ( ! isset( $item['interval-offset']['snapshot-weekly']['tm_hour'] ) ) {
																	$item['interval-offset']['snapshot-weekly']['tm_hour'] = $localtime['tm_hour'];
																}

																Snapshot_Helper_UI::form_show_hour_selector_options( $item['interval-offset']['snapshot-weekly']['tm_hour'] );

																?>
															</select>&nbsp;&nbsp;
														</div>

														<!-- Monthly -->
														<div class="interval-offset-monthly" <?php
														if ( ( $item_interval == "snapshot-monthly" ) || ( $item_interval == "snapshot-twicemonthly" ) ) {
															echo ' style="display: inline-flex;" ';
														} else {
															echo ' style="display: none;" ';
														} ?> >

															<span class="inbetween"><?php _e( 'am', SNAPSHOT_I18N_DOMAIN ); ?></span>
															<select id="snapshot-interval-offset-monthly-mday"
																	name="snapshot-interval-offset[snapshot-monthly][tm_mday]">
																<?php
																if ( ! isset( $item['interval-offset']['snapshot-monthly']['tm_mday'] ) ) {
																	$item['interval-offset']['snapshot-monthly']['tm_mday'] = 1;
																}

																Snapshot_Helper_UI::form_show_mday_selector_options( $item['interval-offset']['snapshot-monthly']['tm_mday'] );
																?>
															</select>&nbsp;&nbsp;

															<span class="inbetween"><?php _e( 'um', SNAPSHOT_I18N_DOMAIN ); ?></span>
															<select id="snapshot-interval-offset-monthly-hour"
																	name="snapshot-interval-offset[snapshot-monthly][tm_hour]">
																<?php
																if ( ! isset( $item['interval-offset']['snapshot-monthly']['tm_hour'] ) ) {
																	$item['interval-offset']['snapshot-monthly']['tm_hour'] = $localtime['tm_hour'];
																}

																Snapshot_Helper_UI::form_show_hour_selector_options( $item['interval-offset']['snapshot-monthly']['tm_hour'] );
																?>
															</select>&nbsp;&nbsp;
														</div>
													</div>
												<?php endif; ?>
											</div>

											<h3><?php _e( 'Speicherlimit', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<div class="storage-inline-form">

												<span class="inbetween">Behalte</span>
												<?php
												if ( ! isset( $item['archive-count'] ) ) {
													$item['archive-count'] = 3; // Default to limited number of recurring archives
												}

												?>
												<input type="number" name="snapshot-archive-count" id="snapshot-archive-count"
												       value="<?php echo esc_attr( $item['archive-count'] ); ?>" />

												<span class="inbetween"><?php _e( 'Backups bevor ältere Archive entfernt werden.', SNAPSHOT_I18N_DOMAIN ); ?></span>

											</div>

											<p>
												<small><?php _e( 'PS Snapshot erstellt Backups gemäß Deines Zeitplans und sendet sie an den von Dir gewählten Speicherort. Zusätzlich zur externen Speicherung bewahren wir eine lokale Kopie auf, um für den Fall der Fälle gerüstet zu sein. Hier kannst Du festlegen, wie viele lokale Archive aufbewahrt werden sollen, bevor das älteste gelöscht wird.', SNAPSHOT_I18N_DOMAIN ); ?></small>
											</p>

											<h3><?php _e( 'Optional', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<div class="wps-input--item">

												<div class="wps-input--checkbox">

													<input type="checkbox" id="checkbox-run-backup-now" class="" value="1"<?php checked( ! $update ); ?>>

													<label for="checkbox-run-backup-now"></label>

												</div>

												<label for="checkbox-run-backup-now"><?php _e( 'Backup jetzt auch ausführen', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

									</div>

								</div>

							</div>

							<div id="wps-new-name" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php _e( 'Gib deinem Snapshot einen aussagekräftigen Namen!', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<?php
										if ( isset( $_REQUEST['snapshot-name'] ) ) {
											$snapshot_name = sanitize_text_field( $_REQUEST['snapshot-name'] );
										} else if ( isset( $item['name'] ) ) {
											$snapshot_name = sanitize_text_field( $item['name'] );
										} else {
											$snapshot_name = __( "Snapshot", SNAPSHOT_I18N_DOMAIN );
										}
										?>
										<input type="text" name="snapshot-name" id="snapshot-name" value="<?php echo esc_attr( $snapshot_name ); ?>">

										<p>
											<small><?php _e( 'Snapshot fügt automatisch das Datum und eine ID zu deiner Archiv-ZIP-Datei hinzu.', SNAPSHOT_I18N_DOMAIN ); ?></small>
										</p>

									</div>

								</div>

							</div>

							<div class="row">
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									<div id="snapshot-ajax-warning" class="wps-auth-message warning" style="display: none;"></div>
									<div id="snapshot-ajax-error" class="wps-auth-message error" style="display: none;"></div>
								</div>
							</div>

							<div class="row">

								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

									<div class="form-button-container">

										<a class="button button-gray" href="<?php echo PSOURCESnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ); ?>"><?php _e( 'Abbrechen', SNAPSHOT_I18N_DOMAIN ); ?></a>

										<button id="snapshot-add-update-submit" data-title-save-only="<?php _e( 'Speichern', SNAPSHOT_I18N_DOMAIN ); ?>" data-title-save-and-run="<?php _e( 'Speichern & Backup ausführen', SNAPSHOT_I18N_DOMAIN ); ?>" type="submit" class="button button-blue"><?php _e( 'Speichern & Backup ausführen', SNAPSHOT_I18N_DOMAIN ); ?></button>


									</div>

								</div>

							</div>

						</div>

					<?php endif; ?>

				</div>

			</div>

		</section>

	</div>
</form>

<?php if ( isset( $force_backup ) && $force_backup ) : ?>
	<script type="text/javascript">
        jQuery(function ($) {
            $('#checkbox-run-backup-now').attr('checked', 'checked');
            $('#snapshot-add-update-submit').click();
        });
	</script>
<?php endif; ?>