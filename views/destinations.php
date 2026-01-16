<?php
$model = new Snapshot_Model_Full_Backup;
$backups =  $model->get_backups();

$has_backups = !empty( $backups );

?>

<section id="header">
	<h1><?php esc_html_e( 'Speicherorte', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-destinations">
	<?php

	// removed managed backups promo widget

	$destination_types = array( 'dropbox', 'google', 'amazon', 'sftp', 'local' );

	foreach ( $destination_types as $destination_type ) {
		$this->render( 'boxes/destinations/widget-' . $destination_type, false, array(), false, false );
	}

	?>
</div>