<div id="container" class="hidden snapshot-three wps-page-builder">

	<section class="wpmud-box">

		<div class="wpmud-box-title has-button">

			<div class="wps-title-progress">

				<h3><?php _e('Snapshot erstellen', SNAPSHOT_I18N_DOMAIN); ?></h3>

				<button  id="wps-show-full-log" data-wps-show-title="<?php _e('Vollständiges Protokoll anzeigen', SNAPSHOT_I18N_DOMAIN); ?>" data-wps-hide-title="<?php _e('Vollständiges Protokoll ausblenden', SNAPSHOT_I18N_DOMAIN); ?>" class="button button-outline button-gray"><?php _e('Vollständiges Protokoll anzeigen', SNAPSHOT_I18N_DOMAIN); ?></button>

			</div>

			<div class="wps-title-result hidden">

				<h3><?php _e('Snapshot Ergebnis', SNAPSHOT_I18N_DOMAIN); ?></h3>

			</div>

		</div>

		<div class="wpmud-box-content">

			<div class="row">

				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

						<div id="wps-build-error" class="hidden">

							<div class="wps-auth-message error">

								<p></p>

							</div>

							<p>

								<a href="#" id="wps-build-error-back" class="button button-outline button-gray"><?php _e('Zurück', SNAPSHOT_I18N_DOMAIN); ?></a>

								<a href="#" id="wps-build-error-again" class="button button-gray"><?php _e('Erneut versuchen', SNAPSHOT_I18N_DOMAIN); ?></a>

							</p>

						</div>

						<div id="wps-build-progress">

							<p><?php _e('Dein Snapshot wird erstellt. <strong> Du musst diese Seite geöffnet lassen, damit die Sicherung abgeschlossen werden kann. </strong> Sobald Deine Webseite gesichert wurde, wird sie an Ihr Ziel hochgeladen. Wenn Deine Webseite klein ist, dauert dies nur wenige Minuten, kann aber bei größeren Webseiten einige Stunden dauern.', SNAPSHOT_I18N_DOMAIN); ?></p>

							<div class="wpmud-box-gray">

								<div class="wps-loading-status wps-total-status wps-spinner">

									<p class="wps-loading-number">0%</p>

									<div class="wps-loading-bar">

										<div class="wps-loader">

											<span style="width: 0%"></span>

										</div>

									</div>

								</div>

							</div>

							<p><a id="wps-cancel" class="button button-outline button-gray"><?php _e('Abbrechen', SNAPSHOT_I18N_DOMAIN); ?></a></p>

						</div>

						<div id="wps-build-success" class="hidden">

							<div class="wps-auth-message success">

								<p><?php _e('Dein Snapshot wurde erfolgreich erstellt und gespeichert! <a href="">Snapshot anzeigen</a>.', SNAPSHOT_I18N_DOMAIN); ?></p>

							</div>

							<div class="wpmud-box-gray">

								<div class="wps-loading-status">

									<p class="wps-loading-number">100%</p>

									<div class="wps-loading-bar">

										<div class="wps-loader done">

											<span style="width: 100%"></span>

										</div>

									</div>

								</div>

							</div>

							<p>
								<a href="<?php echo PSOURCESnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-snapshots'); ?>&amp;snapshot-action=view&amp;item=<?php echo $item['timestamp']; ?>" class="button button-gray"><?php _e('View Snapshot', SNAPSHOT_I18N_DOMAIN); ?></a>
							</p>

						</div>

						<div id="wps-log" class="hidden">

							<h4><?php _e('Snapshot Protokoll', SNAPSHOT_I18N_DOMAIN); ?></h4>

							<div id="wps-log-resume" class="wpmud-box-gray">

								<div class="log-memory">

									<p><strong><?php _e('Speicherlimit', SNAPSHOT_I18N_DOMAIN); ?>:</strong><span class="number"><?php echo ini_get( 'memory_limit' ); ?></span></p>

								</div>

								<div class="log-usage">

									<p><strong><?php _e('Speichernutzung', SNAPSHOT_I18N_DOMAIN); ?>:</strong><span class="number"><?php echo Snapshot_Helper_Utility::size_format( memory_get_usage( true ) ); ?></span></p>

								</div>

								<div class="log-peak">

									<p><strong><?php _e('Spitzenwert', SNAPSHOT_I18N_DOMAIN); ?>:</strong><span class="number"><?php echo Snapshot_Helper_Utility::size_format( memory_get_peak_usage( true ) ); ?></span></p>

								</div>

							</div>

							<table cellpadding="0" cellspacing="0">

								<thead>

									<tr>

										<th class="wps-log-process"><?php _e('Prozess', SNAPSHOT_I18N_DOMAIN); ?></th>

										<th class="wps-log-progress"><?php _e('Fortschritt', SNAPSHOT_I18N_DOMAIN); ?></th>

									</tr>

								</thead>

								<tbody>

									<tr id="wps-log-process-init">

										<td class="wps-log-process"><?php _e('Snapshot Initialisierung', SNAPSHOT_I18N_DOMAIN); ?></td>

										<td class="wps-log-progress">

											<div class="wps-log-progress-elements">

												<a class="snapshot-button-abort button button-outline button-gray"><?php _e('Abbrechen', SNAPSHOT_I18N_DOMAIN); ?></a>

												<span class="wps-spinner hidden"></span>

												<div class="wps-loading-status">

													<p class="wps-loading-number">0%</p>

													<div class="wps-loading-bar">

														<div class="wps-loader">

															<span style="width: 0%"></span>

														</div>

													</div>

												</div>

											</div>

										</td>

									</tr>

									<?php // A template TR that will be clonned and managed by javascript ?>
									<tr id="wps-log-process-template" style="display: none;">

										<td class="wps-log-process name"></td>

										<td class="wps-log-progress">

											<div class="wps-log-progress-elements">

												<a class="snapshot-button-abort hidden button button-outline button-gray"><?php _e('Abbrechen', SNAPSHOT_I18N_DOMAIN); ?></a>

												<span class="wps-spinner hidden"></span>

												<div class="wps-loading-status">

													<p class="wps-loading-number">0%</p>

													<div class="wps-loading-bar">

														<div class="wps-loader">

															<span style="width: 0%"></span>

														</div>

													</div>

												</div>

											</div>

										</td>

									</tr>

									<tr id="wps-log-process-finish">

										<td class="wps-log-process"><?php _e('Snapshot Fertigstellung (Erstellen des Zip-Archivs der Tabellen)', SNAPSHOT_I18N_DOMAIN); ?></td>

										<td class="wps-log-progress">

											<div class="wps-log-progress-elements">

												<a class="snapshot-button-abort hidden button button-outline button-gray"><?php _e('Abbrechen', SNAPSHOT_I18N_DOMAIN); ?></a>

												<span class="wps-spinner hidden"></span>

												<div class="wps-loading-status">

													<p class="wps-loading-number">0%</p>

													<div class="wps-loading-bar">

														<div class="wps-loader done">

															<span style="width: 0%"></span>

														</div>

													</div>

												</div>

											</div>

										</td>

									</tr>

								</tbody>

							</table>

						</div><?php // #wps-log ?>

					</div><?php // .col-xs-12 ?>

				</div><?php // .row ?>

			</div><?php // .col ?>

		<?php // </div> .wpmud-box-content ?>

	</section>

</div>