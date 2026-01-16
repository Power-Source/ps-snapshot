<?php
$destinations = array();
$all_destinations = PSOURCESnapshot::instance()->get_setting( 'destinationClasses' );

foreach ( PSOURCESnapshot::instance()->config_data['destinations'] as $key => $item ) {

	if (isset( $all_destinations[$item['type']] )){

		$item["type_name_display"] = $all_destinations[$item['type']]->name_display;

	} else {

		$item["type_name_display"] = "local";
		$item["type"] = "local";

	}

	$destinations[$key] = $item;

} ?>

<div class="wpmud-box wps-widget-destinations">

	<div class="wpmud-box-title has-button">

		<h3 class="has-count"><?php _e( 'Speicherorte', SNAPSHOT_I18N_DOMAIN ); ?><span class="wps-count"><?php echo count( PSOURCESnapshot::instance()->config_data['destinations'] ) ?></span></h3>

		<a href="<?php echo PSOURCESnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations'); ?>" class="button button-blue button-small"><?php _e( 'Neu hinzufügen', SNAPSHOT_I18N_DOMAIN ); ?></a>

	</div>

	<div class="wpmud-box-content">

		<div class="row">

			<div class="col-xs-12">

				<p><?php _e( 'Speicherorte sind Orte, an denen Deine Snapshots hochgeladen und gespeichert werden. Speichere Dateien auf Dropbox, Google Drive, Amazon S3, FTP/SFTP oder Deinem lokalen Computer.', SNAPSHOT_I18N_DOMAIN ); ?></p>

				<table class="has-footer" cellpadding="0" cellspacing="0">

					<thead>

						<tr>

							<th class="wpsd-name"><?php _e( 'Aktive Speicherorte', SNAPSHOT_I18N_DOMAIN ); ?></th>

							<th class="wpsd-type"><?php _e( 'Typ', SNAPSHOT_I18N_DOMAIN ); ?></th>

						</tr>

					</thead>

					<tbody>

					<?php 
					$count = 0;
					foreach($destinations as $key => $destination) :
						if( $count >= 3 )	break;
						$count++;
					?>
						<tr>

							<td class="wpsd-name">

									<span class="wps-typecon <?php echo $destination['type']; ?>"></span>

									<p>
									<a href="<?php echo add_query_arg( array( 'snapshot-action' => 'edit' , 'type' => urlencode( $destination['type'] ) , 'item' => urlencode( $key ) ), PSOURCESnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ); ?>"><?php echo $destination['name']; ?></a>

									</p>

								</td>

								<td class="wpsd-type"><?php echo $destination['type_name_display']; ?></td>

							</tr>

						<?php endforeach; ?>

					</tbody>

					<tfoot>

						<tr>

							<td colspan="2">

								<a href="<?php echo PSOURCESnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations'); ?>" class="button button-outline button-gray"><?php echo __( 'Alle anzeigen', SNAPSHOT_I18N_DOMAIN ) ?></a>

							</td>

						</tr>

					</tfoot>

				</table>

			</div>

		</div>

	</div>

</div>