<?php

global $wpdb;

if ( isset( $_GET['snapshot-data-item'] ) ) {
	$data_item = $item['data'][ $_GET['snapshot-data-item'] ];
}

$backupFolder = PSOURCESnapshot::instance()->snapshot_get_item_destination_path( $item, $data_item );
if ( empty( $backupFolder ) ) {
	$backupFolder = PSOURCESnapshot::instance()->get_setting( 'backupBaseFolderFull' );
}

if ( ! empty( $data_item['filename'] ) ) {
	$manifest_filename = @Snapshot_Helper_Utility::extract_archive_manifest( trailingslashit( $backupFolder ) . $data_item['filename'] );
	if ( $manifest_filename ) {
		$manifest_data = Snapshot_Helper_Utility::consume_archive_manifest( $manifest_filename );
		if ( $manifest_data ) {
			$item['MANIFEST'] = $manifest_data;
		}
	}
}

$requirements_test = Snapshot_Helper_Utility::check_system_requirements();
$checks = $requirements_test['checks'];
$all_good = $requirements_test['all_good'];
$warning = $requirements_test['warning'];
?>

<div id="snapshot-ajax-warning" class="updated fade" style="display: none;"></div>
<div id="snapshot-ajax-error" class="error snapshot-error" style="display: none;"></div>

<section id="header">
	<h1><?php esc_html_e( 'Snapshots', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<?php $this->render( "snapshots/partials/restore-snapshot-progress", false, array( 'item' => $item ), false, false ); ?>

<form id="snapshot-edit-restore" action="<?php echo PSOURCESnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ); ?>" method="post">
	<input type="hidden" name="snapshot-action" value="restore-request"/>
	<input type="hidden" name="item" value="<?php echo $item['timestamp']; ?>"/>
	<?php wp_nonce_field( 'snapshot-restore', 'snapshot-noonce-field' ); ?>

	<div id="container" class="snapshot-three wps-page-wizard wps-page-wizard_restore">

		<section class="wpmud-box new-snapshot-main-box">

			<div class="wpmud-box-title has-button">
				<h3><?php _e( 'Wiederherstellungsassistent', SNAPSHOT_I18N_DOMAIN ); ?></h3>
				<a class="button button-outline button-gray"
				   href="<?php echo esc_url( PSOURCESnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>">
					<?php _e( 'Zurück', SNAPSHOT_I18N_DOMAIN ); ?>
				</a>
			</div>

			<div class="wpmud-box-content">

				<?php $this->render( "common/requirements-test", false, $requirements_test, false, false ); ?>

				<div class="wpmud-box-tab configuration-box<?php if ( $all_good ) {
					echo ' open';
				} ?>">

					<div class="wpmud-box-tab-title can-toggle">

						<h3>
							<?php _e( 'Konfiguration', SNAPSHOT_I18N_DOMAIN ); ?>
							<?php if ( ! $all_good ) { ?>
								<span class="wps-restore-backup-notice">
						<?php _e( 'Du musst die Servervoraussetzungen erfüllen, bevor du fortfahren kannst.', SNAPSHOT_I18N_DOMAIN ); ?>
					</span>
							<?php } ?>
							<?php if ( $all_good && $warning ) { ?>
								<span class="wps-restore-backup-notice">
						<?php _e( 'Du hast eine oder mehrere Warnungen zu den Anforderungen. Du kannst fortfahren, jedoch kann es aufgrund der Warnungen zu Problemen mit Snapshot kommen.', SNAPSHOT_I18N_DOMAIN ); ?>
					</span>
							<?php } ?>
						</h3>
						<?php if ( $all_good ): ?>
							<i class="wps-icon i-arrow-right"></i>
						<?php endif; ?>
					</div>

					<?php if ( $all_good ): ?>

						<div class="wpmud-box-tab-content">

							<div id="wps-restore-subsite" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
									<label class="label-box"><?php _e( 'Blog Optionen', SNAPSHOT_I18N_DOMAIN ); ?></label>
								</div>

								<?php

								global $blog_id;

								$siteurl = '';
								$domain = '';
								if ( isset( $item['blog-id'] ) ) {
									if ( is_multisite() ) {
										$blog_details = get_blog_details( $item['blog-id'] );
									} else {
										$blog_details = new stdClass();
										$blog_details->blog_id = $blog_id;
										$blog_details->siteurl = get_option( 'siteurl' );
										if ( $blog_details->siteurl ) {
											$blog_details->domain = parse_url( $blog_details->siteurl, PHP_URL_HOST );
											$blog_details->path = parse_url( $blog_details->siteurl, PHP_URL_PATH );
											if ( empty( $blog_details->path ) ) {
												$blog_details->path = '/';
											}
										}
									}
								}

								?>

								<input type="hidden" name="snapshot-blog-id" id="snapshot-blog-id"
								       value="<?php echo esc_attr( isset( $item['blog-id'] ) ? $item['blog-id'] : '' ); ?>">

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<?php if ( is_multisite() ) { ?>
											<div class="wps-notice">
												<p><?php _e( 'Du kannst das Backup in einem anderen Blog innerhalb deiner Multisite-Umgebung wiederherstellen.<br><strong>Hinweis: Der Ziel-Blog MUSS bereits existieren.</strong>', SNAPSHOT_I18N_DOMAIN ); ?></p>
											</div>

											<div class="wps-auth-message warning">
												<p><?php _e( 'Diese Migrationslogik gilt noch als Beta.', SNAPSHOT_I18N_DOMAIN ); ?></p>
											</div>

											<?php if ( ! isset( $item['MANIFEST']['WP_SITEURL'] ) || $blog_details->siteurl !== $item['MANIFEST']['WP_SITEURL'] ) { ?>
												<div class="wps-auth-message error">

													<p><?php _e( 'Wiederherstellungs-Hinweis: URL stimmt nicht überein! Das Snapshot-Archiv scheint nicht vom aktuellen WordPress-System erstellt worden zu sein. Es wird jeder Versuch unternommen, die Quell-URL durch die URL des Ziels zu ersetzen.', SNAPSHOT_I18N_DOMAIN ); ?></p>
												</div>

											<?php }
										} ?>

										<div class="wps-restore-row">

											<div class="wps-restore-col">

												<label class="label-title"><?php _e( 'Information aus dem Archiv', SNAPSHOT_I18N_DOMAIN ); ?></label>
												<?php

												global $wpdb;

												$sections = array(
													__( 'Blog ID:', SNAPSHOT_I18N_DOMAIN ) => 'WP_BLOG_ID',
													__( 'Web URL:', SNAPSHOT_I18N_DOMAIN ) => 'WP_SITEURL',
													__( 'Datenbank Name:', SNAPSHOT_I18N_DOMAIN ) => 'WP_DB_NAME',
													__( 'Datenbank Basis Präfix:', SNAPSHOT_I18N_DOMAIN ) => 'WP_DB_BASE_PREFIX',
													__( 'Datenbank Präfix:', SNAPSHOT_I18N_DOMAIN ) => 'WP_DB_PREFIX',
													__( 'Upload Pfad:', SNAPSHOT_I18N_DOMAIN ) => 'WP_UPLOAD_PATH',
												);

												if ( ! is_multisite() ) {
													unset( $sections[ __( 'Blog ID:', SNAPSHOT_I18N_DOMAIN ) ] );
												}

												?>

												<table cellspacing="0" cellpadding="0">
													<tbody>
													<?php foreach ( $sections as $label => $key ) { ?>

														<tr>
															<th><?php echo esc_html( $label ); ?></th>
															<td class="snapshot-org-<?php
															echo str_replace( 'database', 'db', sanitize_title_with_dashes( $label ) );
															?>"><?php

																echo esc_html( ! $item['blog-id'] && isset( $item[ $key ] ) ?
																	$item['IMPORT'][ $key ] : $item['MANIFEST'][ $key ] );

																?></td>
														</tr>

													<?php } ?>
													</tbody>
												</table>

											</div>

											<div class="wps-restore-col">

												<label class="label-title"><?php _e( 'Wird wiederhergestellt in', SNAPSHOT_I18N_DOMAIN ); ?></label>

												<table cellspacing="0" cellpadding="0">

													<tbody>

													<?php if ( is_multisite() ) { ?>
														<tr>
															<th><?php _e( 'Blog ID:', SNAPSHOT_I18N_DOMAIN ); ?></th>
															<td id="snapshot-new-blog-id"><?php
																echo esc_html( $item['blog-id'] && ! isset( $item['IMPORT'] ) ?
																	$item['MANIFEST']['WP_BLOG_ID'] : '' );
																?></td>
														</tr>

													<?php } ?>

													<tr>
														<th><?php _e( 'Web URL:', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<td>
													<span id="snapshot-blog-search-success">
														<span id="snapshot-blog-name"><?php

															if ( is_multisite() ) {
																$item_siteurl = $item['blog-id'] && ! isset( $item['IMPORT'] ) ?
																	$blog_details->siteurl : '';
															} else {
																$item_siteurl = get_option( 'siteurl' );
															}

															echo esc_html( $item_siteurl );

															?></span>

														<?php if ( is_multisite() ) { ?>
															<button id="snapshot-blog-id-change" style="margin-left: 10px;"
															        class="button button-small button-gray button-outline"><?php
																_e( 'Ändern', SNAPSHOT_I18N_DOMAIN );
																?></button>
														<?php } ?>

													</span>

															<?php if ( is_multisite() ) { ?>
																<span id="snapshot-blog-search" style="display: none;">
														<span id="snapshot-blog-search-error" style="color: #FF0000; display:none;">
															<?php _e( 'Fehler bei der Blog-Suche. Bitte erneut versuchen', SNAPSHOT_I18N_DOMAIN ); ?>
															<br>
														</span>

														<span class="wps-spinner" style="display: none;"></span>

																	<?php

																	if ( is_subdomain_install() ) {
																		$site_domain = untrailingslashit( preg_replace( '/^(http|https):\/\//', '', network_site_url() ) );
																		$current_sub_domain = str_replace( '.' . network_site_url(), '', parse_url( $item_siteurl, PHP_URL_HOST ) );
																		$site_part = str_replace( '.' . $site_domain, '', $current_sub_domain );

																	} else {
																		$current_scheme = parse_url( network_site_url(), PHP_URL_SCHEME );
																		$current_scheme .= $current_scheme ? '://' : '';

																		$current_domain = apply_filters( 'snapshot_current_domain', DOMAIN_CURRENT_SITE );
																		$current_path = apply_filters( 'snapshot_current_path', PATH_CURRENT_SITE );
																		echo esc_html( $current_scheme . $current_domain . $current_path );

																		$site_part = str_replace( untrailingslashit( network_site_url() ), '', untrailingslashit( $item_siteurl ) );
																		$site_part = ltrim( $site_part, '/\\' );

																	} ?>

																	<input type="text" style="width: 50%; display: inline-block;" name="snapshot-blog-id-search" id="snapshot-blog-id-search"
																	       value="<?php echo esc_attr( $site_part ); ?>">

																	<?php if ( is_subdomain_install() ) {
																		printf( '.%s', esc_html( $site_domain ) );
																	} ?>

																	<p class="description"><small style="white-space: normal;"><?php

																			if ( is_subdomain_install() ) {
																				_e( 'Gib das Blog-Subdomain-Präfix (z. B. site 1) oder die Blog-ID (z. B. 22) oder eine zugeordnete Domain ein, oder lasse das Feld für die primäre Website leer.', SNAPSHOT_I18N_DOMAIN );
																			} else {
																				_e( 'Gib den Blog-Pfad (z. B. site1) oder die Blog-ID (z. B. 22) ein, oder lasse das Feld für die primäre Website leer.', SNAPSHOT_I18N_DOMAIN );
																			}
																			?></small></p>

														<p>
															<button id="snapshot-blog-id-lookup" class="button button-small button-blue">
																<?php _e( 'Suchen', SNAPSHOT_I18N_DOMAIN ); ?>
															</button>
															<button id="snapshot-blog-id-cancel" class="button button-small button-gray">
																<?php _e( 'Abbrechen', SNAPSHOT_I18N_DOMAIN ); ?>
															</button>
														</p>
													</span>
															<?php } ?>
														</td>
													</tr>

													<tr>
														<th><?php _e( 'Datenbank Name:', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<td id="snapshot-new-db-name"><?php
															echo is_multisite() && ! $item['blog-id'] && isset( $item['IMPORT'] ) ?
																'' : DB_NAME;
															?></td>
													</tr>

													<tr>
														<th><?php _e( 'Datenbank Basis-Präfix:', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<td id="snapshot-new-db-base-prefix"><?php
															echo is_multisite() && ! $item['blog-id'] && isset( $item['IMPORT'] ) ?
																'' : $wpdb->base_prefix;
															?></td>
													</tr>

													<tr>
														<th><?php _e( 'Datenbank Präfix:', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<td id="snapshot-new-db-prefix"><?php

															if ( is_multisite() ) {
																echo ! $item['blog-id'] && isset( $item['IMPORT'] ) ?
																	'' : $wpdb->get_blog_prefix( $item['MANIFEST']['WP_BLOG_ID'] );
															} else {
																echo $wpdb->prefix;
															}

															?></td>
													</tr>

													<tr>
														<th><?php _e( 'Upload-Pfad:', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<td id="snapshot-new-upload-path"><?php

															if ( is_multisite() ) {
																if ( ! $item['blog-id'] && isset( $item['IMPORT'] ) ) {
																	echo '';
																} else {
																	echo Snapshot_Helper_Utility::get_blog_upload_path( $item['blog-id'] );
																}
															} else {
																echo Snapshot_Helper_Utility::get_blog_upload_path( $blog_id );
															}

															?></td>
													</tr>

													</tbody>

												</table>

											</div>

										</div>

									</div>

								</div>

							</div>

							<div id="wps-restore-archive" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Archive', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php _e( 'Wähle das Archiv, das du wiederherstellen möchtest.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<?php
										if ( ( isset( $item['data'] ) ) && ( count( $item['data'] ) ) ) :
											$data_items = $item['data'];
											krsort( $data_items );
											$data_items = array_slice( $data_items, 0, 6, true );

											foreach ( $data_items as $data_key => $data_item ) : ?>

												<div class="wps-input--item">

													<div class="wps-input--radio">

														<input type="radio" name="snapshot-restore-file" class="snapshot-restore-file"
														       id="snapshot-restore-<?php echo $data_item['timestamp']; ?>"
														       value="<?php echo $data_item['timestamp']; ?>" <?php
														if ( ( isset( $_GET['snapshot-data-item'] ) ) && ( intval( $_GET['snapshot-data-item'] ) == $data_item['timestamp'] ) ) {
															echo ' checked="checked" ';
														} ?>/>

														<label for="snapshot-restore-<?php echo $data_item['timestamp']; ?>"></label>

													</div>

													<label for="snapshot-restore-<?php echo $data_item['timestamp']; ?>"><?php echo Snapshot_Helper_Utility::show_date_time( $data_item['timestamp'], 'd.m.Y@ g:i a' ); ?></label>

												</div>
											<?php endforeach; ?>
										<?php endif; ?>

									</div>

								</div>

							</div><?php // Archive ?>

							<?php $data_item = $item['data'][ $data_item_key ]; ?>

							<?php if ( ( isset( $data_item['files-sections'] ) ) && ( ! empty( $data_item['files-sections'] ) ) ) : ?>

								<div id="wps-restore-files" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Dateien', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">
										<?php
										if ( isset( $data_item['files-sections'] ) ) {
											if ( ( array_search( 'config', $item['data'][ $data_item_key ]['files-sections'] ) !== false )
											     || ( array_search( 'htaccess', $item['data'][ $data_item_key ]['files-sections'] ) !== false )
											) {
												?><p
														class="snapshot-error"><?php _e( "Wiederherstellungs-Hinweis: Das Archiv, das du wiederherstellen möchtest, enthält die Dateien .htaccess und/oder wp-config.php. Normalerweise möchtest du diese Dateien nicht wiederherstellen, es sei denn, deine Seite ist defekt. Um eine dieser Dateien wiederherzustellen, musst du sie im Abschnitt 'Ausgewählte Dateien einbeziehen' unten auswählen.", SNAPSHOT_I18N_DOMAIN ); ?></p>
												<?php
											}
										}
										?>

										<label class="label-title"><?php _e( 'Wähle aus, welche Dateien du einbeziehen möchtest.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<div class="wps-input--item">

											<div class="wps-input--radio">

												<input type="radio" class="snapshot-files-option" id="snapshot-files-option-none" value="none" name="snapshot-files-option"/>

												<label for="snapshot-files-option-none"></label>

											</div>

											<label for="snapshot-files-option-none"><?php _e( 'Keine Dateien einbeziehen', SNAPSHOT_I18N_DOMAIN ); ?></label>

										</div>

										<div class="wps-input--item">

											<div class="wps-input--radio">

												<input type="radio" class="snapshot-files-option" id="snapshot-files-option-all" value="all" checked="checked" name="snapshot-files-option">
												<label for="snapshot-files-option-all"></label>

											</div>

											<label for="snapshot-files-option-all"><?php _e( 'Alle Dateien wiederherstellen', SNAPSHOT_I18N_DOMAIN ); ?></label>
											<?php
											if ( ( array_search( 'config', $item['data'][ $data_item_key ]['files-sections'] ) !== false )
											     || ( array_search( 'htaccess', $item['data'][ $data_item_key ]['files-sections'] ) !== false )
											) {
												?> <span>
												<strong><?php _e( '(ohne .htaccess & wp-config.php Dateien)', SNAPSHOT_I18N_DOMAIN ); ?></strong>
												</span><?php
											}
											?>

										</div>

										<div class="wps-input--item">

											<div class="wps-input--radio">

												<input type="radio" class="snapshot-files-option" id="snapshot-files-option-selected" value="selected" name="snapshot-files-option">

												<label for="snapshot-files-option-selected"></label>

											</div>

											<label for="snapshot-files-option-selected"><?php _e( 'Nur ausgewählte Dateien einbeziehen', SNAPSHOT_I18N_DOMAIN ); ?></label>

										</div>

										<div id="snapshot-selected-files-container"
										     style="margin-left: 30px; padding-top: 10px; display: none;">

											<?php if ( is_multisite() ) { ?>
												<p class="snapshot-error"><?php _e( "Wiederherstellungs-Hinweis: Die Dateien wp-config.php und .htaccess können nur für die Hauptseite wiederhergestellt werden. Selbst dann ist es nicht ratsam, diese Dateien für eine funktionierende Multisite-Installation wiederherzustellen.", SNAPSHOT_I18N_DOMAIN ); ?></p>
											<?php } ?>

											<ul id="snapshot-select-files-option" class="wpmud-box-gray">
												<?php if ( array_search( 'themes', $item['data'][ $data_item_key ]['files-sections'] ) !== false ) { ?>
													<li id="snapshot-files-option-themes-li" class="wps-input--item">
														<div class="wps-input--checkbox">
															<input type="checkbox" class="snapshot-backup-sub-options" checked="checked" id="snapshot-files-option-themes" value="themes" name="snapshot-files-sections[themes]">
															<label for="snapshot-files-option-themes"></label>
														</div>
														<label for="snapshot-files-option-themes"><?php _e( 'Themes', SNAPSHOT_I18N_DOMAIN ); ?></label>
													</li>
												<?php } ?>
												<?php if ( array_search( 'plugins', $item['data'][ $data_item_key ]['files-sections'] ) !== false ) { ?>
													<li id="snapshot-files-option-plugins-li" class="wps-input--item">
														<div class="wps-input--checkbox">
															<input type="checkbox" class="snapshot-backup-sub-options" checked="checked" id="snapshot-files-option-plugins" value="plugins" name="snapshot-files-sections[plugins]">
															<label for="snapshot-files-option-plugins"></label>
														</div>
														<label for="snapshot-files-option-plugins"><?php _e( 'Plugins', SNAPSHOT_I18N_DOMAIN ); ?></label>
													</li>
												<?php } ?>
												<?php if ( array_search( 'media', $item['data'][ $data_item_key ]['files-sections'] ) !== false ) { ?>
													<li id="snapshot-files-option-media-li" class="wps-input--item">
														<div class="wps-input--checkbox">
															<input type="checkbox" class="snapshot-backup-sub-options" checked="checked" id="snapshot-files-option-media" value="media" name="snapshot-files-sections[media]">
															<label for="snapshot-files-option-media"></label>
														</div>
														<label for="snapshot-files-option-media"><?php _e( 'Mediendateien', SNAPSHOT_I18N_DOMAIN ); ?></label>
													</li>
												<?php } ?>
												<?php if ( array_search( 'config', $item['data'][ $data_item_key ]['files-sections'] ) !== false ) { ?>
													<li id="snapshot-files-option-config-li">
														<div class="wps-input--checkbox">
															<input type="checkbox" class="snapshot-backup-sub-options" id="snapshot-files-option-config" value="config" name="snapshot-files-sections[config]">
															<label for="snapshot-files-option-config"></label>
														</div>
														<label for="snapshot-files-option-config"><?php _e( 'wp-config.php', SNAPSHOT_I18N_DOMAIN ); ?></label>
													</li>
												<?php } ?>
												<?php if ( array_search( 'htaccess', $item['data'][ $data_item_key ]['files-sections'] ) !== false ) { ?>
													<li id="snapshot-files-option-htaccess-li">
														<div class="wps-input--checkbox">
															<input type="checkbox" class="snapshot-backup-sub-options" id="snapshot-files-option-htaccess" value="htaccess" name="snapshot-files-sections[htaccess]">
															<label for="snapshot-files-option-htaccess"></label>
														</div>
														<label for="snapshot-files-option-htaccess"><?php _e( '.htaccess', SNAPSHOT_I18N_DOMAIN ); ?></label>
													</li>
												<?php } ?>
											</ul>
										</div>

									</div>

								</div>

								</div><?php // Files ?>

							<?php endif; ?>

							<?php

							if ( is_multisite() ) {
								if ( ( isset( $item['data'][ $data_item_key ]['tables-sections'] ) ) && ( count( $item['data'][ $data_item_key ]['tables-sections'] ) ) ) {
									foreach ( $item['data'][ $data_item_key ]['tables-sections'] as $tables_section => $tables_sections_data ) {
										foreach ( $tables_sections_data as $table_name_idx => $table_name ) {
											$table_name_part = str_replace( $item['MANIFEST']['WP_DB_PREFIX'], '', $table_name );
											//echo "table_name_part=[". $table_name_part ."] [". $table_name ."]<br />";

											if ( array_search( $table_name_part, $wpdb->global_tables ) !== false ) {
												if ( ! isset( $item['data'][ $data_item_key ]['tables-sections']['global'] ) ) {
													$item['data'][ $data_item_key ]['tables-sections']['global'] = array();
												}
												$item['data'][ $data_item_key ]['tables-sections']['global'][ $table_name ] = $table_name;

												unset( $item['data'][ $data_item_key ]['tables-sections'][ $tables_section ][ $table_name ] );

											}
										}
									}
								}
							}

							if ( ( isset( $data_item['tables-sections'] ) ) && ( ! empty( $data_item['tables-sections'] ) ) ) : ?>

								<div id="wps-restore-database" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Datenbank', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<?php if ( is_multisite() && ( isset( $item['data'][ $data_item_key ]['tables-sections']['global'] ) ) && ( count( $item['data'][ $data_item_key ]['tables-sections']['global'] ) ) ) : ?>

											<p class="snapshot-error"><?php _e( "Wiederherstellungs-Hinweis: Das Archiv, das Du wiederherstellen möchtest, enthält die globalen Datenbanktabellen users und/oder usermeta. Normalerweise möchtest Du diese Tabellen nicht wiederherstellen, es sei denn, Deine Website ist beschädigt. Um eine dieser Datenbanktabellen wiederherzustellen, musst Du sie im Abschnitt 'Ausgewählte Datenbanktabellen wiederherstellen' unten auswählen. Die in diesen Tabellen enthaltenen Daten werden mit den aktuellen globalen Tabellen zusammengeführt", SNAPSHOT_I18N_DOMAIN ); ?></p>

										<?php endif; ?>

										<?php if ( ( ! is_multisite() ) && ( $item['MANIFEST']['WP_DB_PREFIX'] != $wpdb->prefix ) ) : ?>

											<p class="snapshot-error"><?php printf( __( "Wiederherstellungs-Hinweis: Das Archiv enthält Tabellennamen mit einem anderen Datenbankpräfix ( %s ) als diese Website ( %s ). Die wiederhergestellten Tabellen werden automatisch auf das Website-Präfix umbenannt", SNAPSHOT_I18N_DOMAIN ), $item['MANIFEST']['WP_DB_PREFIX'], $wpdb->prefix ); ?></p>

										<?php endif; ?>

										<label class="label-title"><?php _e( 'Wähle aus, welche Datenbanktabellen Du einbeziehen möchtest.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<div class="wps-input--group">

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-all" checked="checked" value="all" name="snapshot-tables-option">
													<label for="snapshot-tables-option-all"></label>

												</div>

												<label for="snapshot-tables-option-all">
													<?php
													( is_multisite() ) ? _e( 'Stelle <strong>alle</strong> in diesem Archiv enthaltenen Blog-Datenbanktabellen wieder her <strong>(mit Ausnahme der globalen Tabellen users & usermeta)</strong>', SNAPSHOT_I18N_DOMAIN ) : _e( 'Stelle <strong>alle</strong> in diesem Archiv enthaltenen Blog-Datenbanktabellen wieder her', SNAPSHOT_I18N_DOMAIN );
													?>
												</label>

											</div>

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-none" value="none" name="snapshot-tables-option">
													<label for="snapshot-tables-option-none"></label>

												</div>

												<label for="snapshot-tables-option-none"><?php _e( 'Keine Datenbanktabellen einbeziehen', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" class="snapshot-tables-option" id="snapshot-tables-option-selected" value="selected" name="snapshot-tables-option">
													<label for="snapshot-tables-option-selected"></label>

												</div>

												<label for="snapshot-tables-option-selected"><?php _e( 'Nur ausgewählte Tabellen einbeziehen', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

										<div id="snapshot-selected-tables-container" class="wpmud-box-gray" style="display: none;">

											<?php $tables_sets_idx = array(
												'global' => __( "WordPress Globale Tabellen", SNAPSHOT_I18N_DOMAIN ),
												'wp' => __( "WordPress Blog Tabellen", SNAPSHOT_I18N_DOMAIN ),
												'non' => __( "Nicht-WordPress Tabellen", SNAPSHOT_I18N_DOMAIN ),
												'other' => __( "Andere Tabellen", SNAPSHOT_I18N_DOMAIN ),
											);

											//echo "item<pre>"; print_r($item); echo "</pre>";

											foreach ( $tables_sets_idx as $table_set_key => $table_set_title ) {

												if ( isset( $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ] ) ) {
													$display_set = 'block';
												} else {
													$display_set = 'none';
												} ?>

											<div id="snapshot-tables-<?php echo $table_set_key ?>-set" class="snapshot-tables-set" style="display: <?php echo $display_set; ?>">

												<h3 class="snapshot-tables-title"><?php echo $table_set_title; ?><?php if ( ( isset( $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ] ) ) && ( count( $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ] ) ) ) { ?>
														<a class="button-link snapshot-table-select-all" href="#" id="snapshot-table-<?php echo $table_set_key ?>-select-all"><?php _e( 'Alle auswählen', SNAPSHOT_I18N_DOMAIN ); ?></a>
													<?php } ?></h3>

												<?php if ( ( is_multisite() ) && ( $table_set_key == "global" ) ) { ?>

													<p class="snapshot-error"><?php _e( 'Beim Wiederherstellen von Benutzer- und Usermeta-Datensätzen in einer Multisite-Umgebung gibt es einige Einschränkungen. Bitte lesen Sie die folgenden Hinweise sorgfältig durch', SNAPSHOT_I18N_DOMAIN ); ?></p>

													<ol class="snapshot-error">
														<li><?php _e( "Wenn Du auf den primären Blog wiederherstellst, werden ALLE Benutzereinträge ersetzt!", SNAPSHOT_I18N_DOMAIN ); ?></li>
														<li><?php _e( "Wenn Du auf einen nicht primären Blog wiederherstellst, werden die Benutzer-ID- und Benutzername-Felder mit vorhandenen Benutzern abgeglichen.", SNAPSHOT_I18N_DOMAIN ); ?>
															<ul>
																<li><?php _e( "- Wird keine Übereinstimmung gefunden, wird ein neuer Benutzer angelegt. Dies bedeutet, dass ihm eine neue Benutzer-ID zugewiesen wird.", SNAPSHOT_I18N_DOMAIN ); ?></li>
																<li><?php _e( "- Wird eine Übereinstimmung gefunden, aber die Benutzer-ID ist unterschiedlich. Die gefundene Benutzer-ID wird verwendet.", SNAPSHOT_I18N_DOMAIN ); ?></li>
															</ul>
														</li>
														<li><?php _e( "Wenn die wiederhergestellte Benutzer-ID geändert wird, aktualisiert Snapshot die Benutzer-Meta-, Beiträge- und Kommentar-Datensätze mit der neuen Benutzer-ID. Ein neuer Benutzer-Meta-Datensatz wird mit dem Schlüssel '_old_user_id' und dem Wert der vorherigen Benutzer-ID hinzugefügt. Snapshot kann keine Updates für andere Tabellen wie BuddyPress vornehmen, bei denen die Benutzer-ID-Felder nicht bekannt sind. Diese müssen manuell aktualisiert werden.", SNAPSHOT_I18N_DOMAIN ); ?></li>
													</ol>

												<?php } ?>

												<?php if ( ( isset( $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ] ) ) && ( count( $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ] ) ) ) {

													$tables = $item['data'][ $data_item_key ]['tables-sections'][ $table_set_key ]; ?>

													<ul class="snapshot-table-list" id="snapshot-table-list-<?php echo $table_set_key; ?>">

														<?php foreach ( $tables as $table_key => $table_name ) {

															if ( $table_set_key != "global" ) {
																$checked = ' checked="checked" ';
															} else {
																if ( is_multisite() ) {
																	$checked = '';
																} else {
																	$checked = ' checked="checked" ';
																}
															} ?>

															<li class="wps-input--item">

																<div class="wps-input--checkbox">
																	<input type="checkbox" <?php echo $checked; ?> class="snapshot-table-item" id="snapshot-tables-<?php echo $table_key; ?>" value="<?php echo $table_key; ?>" name="snapshot-tables[<?php echo $table_set_key; ?>][<?php echo $table_key; ?>]">
																	<label for="snapshot-tables-<?php echo $table_key; ?>"></label>
																</div>

																<label for="snapshot-tables-<?php echo $table_key; ?>"><?php echo $table_name; ?></label>

															</li>

														<?php } ?>

													</ul>

												<?php } else { ?>

													<p><?php _e( 'Keine Tabellen', SNAPSHOT_I18N_DOMAIN ); ?></p>

												<?php } ?>

												</div><?php // .snapshot-tables-set ?>

											<?php } ?>

										</div><?php // #snapshot-selected-tables-container ?>

									</div>

								</div>

								</div><?php // Database ?>

							<?php endif; ?>

							<div id="wps-restore-plugins" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Plugins', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<div class="wps-input--item">

											<div class="wps-input--checkbox">

												<input type="checkbox" id="snapshot-restore-option-plugins"
												       name="restore-option-plugins" value="yes"/>

												<label for="snapshot-restore-option-plugins"></label>

											</div>

											<label for="snapshot-restore-option-plugins"><?php _e( 'Plugins deaktivieren', SNAPSHOT_I18N_DOMAIN ); ?></label>

											<p>
												<small><?php _e( 'Dies deaktiviert alle Plugins. Du kannst sie nach Abschluss der Wiederherstellung manuell wieder aktivieren.', SNAPSHOT_I18N_DOMAIN ); ?></small>
											</p>

										</div>

									</div>

								</div>

							</div><?php // Plugins ?>

							<div id="wps-restore-themes" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Themes', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title"><?php _e( 'Wähle das Theme aus, das aktiviert werden soll, wenn diese Seite wiederhergestellt wird.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										<?php
										if ( isset( $item['blog-id'] ) ) {
											$current_theme = Snapshot_Helper_Utility::get_current_theme( $item['blog-id'] );
										} else {
											$current_theme = Snapshot_Helper_Utility::get_current_theme();
										}

										if ( isset( $item['blog-id'] ) ) {
											$themes = Snapshot_Helper_Utility::get_blog_active_themes( $item['blog-id'] );
										} else {
											$themes = Snapshot_Helper_Utility::get_blog_active_themes();
										}

										?>

										<?php if ( $themes ) : foreach ( $themes as $theme_key => $theme_name ) : ?>
											<div class="wps-input--item">

												<div class="wps-input--radio">

													<input type="radio" id="snapshot-restore-option-theme-<?php echo $theme_key; ?>" <?php echo ( $theme_key == $current_theme ) ? 'checked="checked"' : '' ?> name="restore-option-theme" value="<?php echo $theme_key; ?>"/>

													<label for="snapshot-restore-option-theme-<?php echo $theme_key; ?>"></label>

												</div>

												<label for="snapshot-restore-option-theme-<?php echo $theme_key; ?>">
													<?php echo ( $theme_key == $current_theme ) ? '<strong>' : '' ?>
													<?php echo $theme_name ?>
													<?php echo ( $theme_key == $current_theme ) ? '</strong>' : '' ?>
												</label>

											</div>
										<?php endforeach; endif; ?>

									</div>

								</div>

							</div><?php // Themes ?>

							<div class="row">

								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

									<div class="form-button-container">

										<a class="button button-gray" href=""><?php _e( 'Abbrechen', SNAPSHOT_I18N_DOMAIN ); ?></a>
										<input class="button button-blue" id="snapshot-form-restore-submit" class="button-primary" type="submit" value="<?php _e( 'Jetzt wiederherstellen', SNAPSHOT_I18N_DOMAIN ); ?>">

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