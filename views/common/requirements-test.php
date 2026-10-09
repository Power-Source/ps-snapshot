<div class="wpmud-box-tab requirements-check-box<?php if ( !$all_good || $warning ) { echo ' open'; } ?>">
	<div class="wpmud-box-tab-title can-toggle">
		<h3><?php _e( 'Anforderungsprüfung', 'snapshot' ); ?>
		<span class="wps-tag wps-tag--<?php if ( !$all_good ) { echo 'red'; } else if ( $warning ) { echo 'yellow'; } else { echo 'green'; } ?>">
		<?php
			if ( !$all_good ) {
			_e( 'FEHLGESCHLAGEN', 'snapshot' );
			} else if ( $warning ) {
			_e( 'WARNUNG', 'snapshot' );
			} else {
			_e( 'BESTANDEN', 'snapshot' );
			} ?>
		</span></h3>
		<i class="wps-icon i-arrow-right"></i>
	</div>
	<div class="wpmud-box-tab-content">
		<div class="wps-requirements-list">
			<div class="wpmud-box-gray">
				<table class="wps-table" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<th>
								<?php _e( 'PHP Version', 'snapshot' ); ?>
								<?php if( !$checks['PhpVersion']['test'] ) : ?>
								<span class="wps-tag wps-tag--red"><?php _e( 'FEHLGESCHLAGEN', 'snapshot' ); ?></span>
								<?php else : ?>
								<span class="wps-tag wps-tag--green"><?php _e( 'BESTANDEN', 'snapshot' ); ?></span>
								<?php endif; ?>
							</th>
							<?php if( !$checks['PhpVersion']['test'] ) : ?>
							<td>
								<?php printf( __( 'Deine PHP-Version ist veraltet.
									Deine aktuelle Version ist %s und wir benötigen 7.4 oder neuer.
									Du musst deine PHP-Version aktualisieren, um fortzufahren.
									Wenn du einen Managed Host verwendest, kontaktiere diesen direkt, um ein Update zu veranlassen.', 'snapshot' ) ,$checks['PhpVersion']['value'] ); ?>
							</td>
							<?php endif; ?>
						</tr>
						<tr>
							<th <?php if( $checks['MaxExecTime']['test'] ) : ?> colspan="2" <?php endif; ?> >
								<?php _e( 'Maximale Ausführungszeit', 'snapshot' ); ?>
								<?php if( !$checks['MaxExecTime']['test'] ) : ?>
								<span class="wps-tag wps-tag--yellow"><?php _e( 'WARNUNG', 'snapshot' ); ?></span>
								<?php else : ?>
								<span class="wps-tag wps-tag--green"><?php _e( 'BESTANDEN', 'snapshot' ); ?></span>
								<?php endif; ?>
							</th>
							<?php if( !$checks['MaxExecTime']['test'] ) : ?>
							<td>
								<?php printf( __( '<b><code>max_execution_time</code> ist auf %s gesetzt, was zu niedrig ist</b>.
									Es wird eine Mindestausführungszeit von 150 Sekunden empfohlen, um dem Migrationsprozess die
									bestmögliche Chance auf Erfolg zu geben. Wenn du einen Managed Host verwendest, kontaktiere diesen direkt, um ein Update zu veranlassen.', 'snapshot' ) ,$checks['MaxExecTime']['value'] ); ?>
							</td>
							<?php endif; ?>
						</tr>
						<tr>
							<th <?php if( $checks['Mysqli']['test'] ) : ?> colspan="2" <?php endif; ?> >
								<?php _e( 'MySQLi', 'snapshot' ); ?>
								<?php if( !$checks['Mysqli']['test'] ) : ?>
								<span class="wps-tag wps-tag--red"><?php _e( 'FEHLGESCHLAGEN', 'snapshot' ); ?></span>
								<?php else : ?>
								<span class="wps-tag wps-tag--green"><?php _e( 'BESTANDEN', 'snapshot' ); ?></span>
								<?php endif; ?>
							</th>
							<?php if( !$checks['Mysqli']['test'] ) : ?>
							<td>
								<?php _e( '<b>PHP MySQLi Modul nicht gefunden</b>.
									Snapshot benötigt das MySQLi Modul, das auf dem Zielserver installiert und aktiviert sein muss.
									Wenn du einen Managed Host verwendest, kontaktiere diesen direkt, um das Modul installieren und aktivieren zu lassen.', 'snapshot' );
									?>
							</td>
							<?php endif; ?>
						</tr>
						<tr>
							<th <?php if( $checks['Zip']['test'] ) : ?> colspan="2" <?php endif; ?> >
								<?php _e( 'GZip', 'snapshot' ); ?>
								<?php if( !$checks['Zip']['test'] ) : ?>
								<span class="wps-tag wps-tag--red"><?php _e( 'FEHLGESCHLAGEN', 'snapshot' ); ?></span>
								<?php else : ?>
								<span class="wps-tag wps-tag--green"><?php _e( 'BESTANDEN', 'snapshot' ); ?></span>
								<?php endif; ?>
							</th>
							<?php if( !$checks['Zip']['test'] ) : ?>
							<td>
								<?php _e( '<b>PHP Zip Modul nicht gefunden</b>.
									Um die Zip-Datei zu entpacken, benötigt Snapshot das Zip-Modul, das installiert und aktiviert sein muss.
									Wenn du einen Managed Host verwendest, kontaktiere diesen direkt, um ein Update zu veranlassen.', 'snapshot' );
									?>
							</td>
							<?php endif; ?>
						</tr>
					</tbody>
				</table>
			</div>
			<p><a href="" class="button button-outline button-gray"><?php _e('Neu prüfen', 'snapshot'); ?></a></p>
		</div>
	</div>
</div>