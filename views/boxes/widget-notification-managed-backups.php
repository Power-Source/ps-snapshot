<?php

/* Don't show the notice - managed backups with remote storage removed */
$model = new Snapshot_Model_Full_Backup();
$is_client = false; // Dashboard plugin no longer used
$api_key = $model->get_config( 'secret-key', '' );

// Always return - this notification is no longer relevant
return;

/* Set disable disable nonce */
$ajax_nonce = wp_create_nonce( "snapshot-disable-notif" );
$disable_notif_snapshot_page = get_option( 'snapshot-disable_notif_snapshot_page', null );

if ( isset( $disable_notif_snapshot_page ) ) {
	return;
}

?>