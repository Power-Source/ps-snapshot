<?php
/**
 * Network Backup Widget for Dashboard
 */
if ( ! is_multisite() ) {
return;
}

$model = new Snapshot_Model_Full_Backup();
$all_backups = $model->get_backups();
$backups_count = count( $all_backups );
$backups = array();

// Sort by timestamp descending
if ( is_array( $all_backups ) && count( $all_backups ) ) {
$backups = $all_backups;
usort( $backups, function( $a, $b ) {
$ts_a = isset( $a['timestamp'] ) ? intval( $a['timestamp'] ) : 0;
$ts_b = isset( $b['timestamp'] ) ? intval( $b['timestamp'] ) : 0;
return $ts_b - $ts_a;
});
$backups = array_slice( $backups, 0, 3 );
}
?>

<section class="wpmud-box wps-widget-network-backup-on">

<div class="wpmud-box-title">

<h3>
<?php esc_html_e( 'Netzwerk-Backup', SNAPSHOT_I18N_DOMAIN ); ?>

<?php if ( ! empty( $backups ) ) : ?>
<span class="wps-count"><?php echo esc_html( $backups_count ); ?></span>
<?php endif; ?>
</h3>

<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'snapshot_network_backup' ), network_admin_url( 'admin.php' ) ) ); ?>" class="button button-small button-blue"><?php esc_html_e( 'Erstellen', SNAPSHOT_I18N_DOMAIN ); ?></a>

</div>

<div class="wpmud-box-content">

<div class="row">

<div class="col-xs-12">

<p><?php esc_html_e( 'Sichern Sie Ihr gesamtes WordPress-Netzwerk (alle Seiten, Dateien und Datenbanken) mit einem Klick.', SNAPSHOT_I18N_DOMAIN ); ?></p>

<table class="has-footer" cellpadding="0" cellspacing="0">

<thead>

<tr>

<th class="wss-name"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></th>

<th class="wss-date"><?php esc_html_e( 'Datum', SNAPSHOT_I18N_DOMAIN ); ?></th>

</tr>

</thead>

<tbody>

<?php if ( ! empty( $backups ) ) : ?>

<?php foreach ( $backups as $backup ) : ?>

<tr>

<td class="wss-name">

<p>

<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'snapshot_snapshots', 'item' => $backup['timestamp'], 'full-backup' => 'true' ), network_admin_url( 'admin.php' ) ) ); ?>">
<?php 
if ( isset( $backup['name'] ) ) { 
	echo '<strong>' . esc_html( $backup['name'] ) . '</strong>';
} else if ( isset( $backup['timestamp'] ) ) { 
	echo '<strong>' . esc_html( 'Backup vom ' . date_i18n( 'j. M Y H:i', $backup['timestamp'] ) ) . '</strong>';
} else {
	echo '<strong>-</strong>';
}
?>
</a>

<?php if ( isset( $backup['size'] ) ) { ?>
<small><?php echo esc_html( size_format( $backup['size'] ) ); ?></small>
<?php } ?>

</p>

</td>

<td class="wss-date">

<?php if ( isset( $backup['timestamp'] ) ) {
echo esc_html( date_i18n( 'M j, Y', $backup['timestamp'] ) );
} ?>

</td>

</tr>

<?php endforeach; ?>

<?php else : ?>

<tr>

<td colspan="2" style="padding: 20px; text-align: center; color: #999;">

<?php esc_html_e( 'Noch keine Netzwerk-Backups vorhanden.', SNAPSHOT_I18N_DOMAIN ); ?>

</td>

</tr>

<?php endif; ?>

</tbody>

<tfoot>

<tr>

<td colspan="2">

<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'snapshot_network_backup' ), network_admin_url( 'admin.php' ) ) ); ?>" class="button button-outline button-gray"><?php esc_html_e( 'Alle anzeigen', SNAPSHOT_I18N_DOMAIN ); ?></a>

</td>

</tr>

</tfoot>

</table>

</div>

</div>

</div>

</section>
