<?php

if ( class_exists( 'PSOURCESnapshot_New_Ui_Tester' ) ) {
	return;
}

class PSOURCESnapshot_New_Ui_Tester {

	public function dashboard() {
		$this->render( 'dashboard' );
	}

	public function snapshots() {

		if ( isset( $_REQUEST['snapshot-action'] ) && 'new' === $_REQUEST['snapshot-action'] ) {
			$this->render( 'snapshots/snapshot', false, array( 'action' => 'add', 'item' => array() ) );
			return;
		}

		// Check if this is a full backup request
		if ( isset( $_REQUEST['item'] ) && isset( $_REQUEST['full-backup'] ) && 'true' === $_REQUEST['full-backup'] ) {
			$timestamp = intval( sanitize_text_field( $_REQUEST['item'] ) );
			$this->render_full_backup_item( $timestamp );
			return;
		}

		if ( isset( $_REQUEST['item'], PSOURCESnapshot::instance()->config_data['items'][ sanitize_text_field( $_REQUEST['item'] ) ] ) ) {

			$item = PSOURCESnapshot::instance()->config_data['items'][ sanitize_text_field( $_REQUEST['item'] ) ];

			$snapshot_action = 'default';

			if ( isset( $_REQUEST['snapshot-action'] ) ) {
				$snapshot_action = sanitize_text_field( $_REQUEST['snapshot-action'] );
			}

			$force_backup = false;

			switch ( $snapshot_action ) {
				case 'backup':
					$force_backup = true;
				case 'edit':
					$this->render( 'snapshots/snapshot', false, array( 'action' => 'update', 'item' => $item, 'force_backup' => $force_backup ) );
					break;

				case 'restore':
					if ( ( isset( $_GET['snapshot-data-item'] ) ) && ( isset( $item['data'][ intval( $_GET['snapshot-data-item'] ) ] ) ) ) {
						$data_item_key = intval( $_GET['snapshot-data-item'] );
						$this->render( 'snapshots/restore', false, array( 'item' => $item, 'data_item_key' => $data_item_key ) );
					} else {
						$this->render( 'snapshots/item', false, array( 'item' => $item ) );
					}
					break;
				default:
					$this->render( 'snapshots/item', false, array( 'item' => $item ) );
			}


		} else {
			$snapshots = PSOURCESnapshot::instance()->config_data['items'];
			$count_all_snapshots = count( $snapshots );
			$all_destinations = PSOURCESnapshot::instance()->config_data['destinations'];
			$filter = ( isset( $_GET['destination'] ) ) ? sanitize_text_field( $_GET['destination'] ) : '';
			if ( $filter !== '' && isset( $all_destinations[ $filter ] ) ) {
				$filtred_snapshot = array();
				foreach ( $snapshots as $key => $snapshot ) {
					if ( isset( $snapshot['destination'] ) && $snapshot['destination'] === $filter ) {
						$filtred_snapshot[ $key ] = $snapshot;
					}
				}
				$snapshots = $filtred_snapshot;
			}
			$results_count = count( $snapshots );
			$per_page = 20;
			//Max number of pages
			$max_pages = ceil( $results_count / $per_page );
			$paged = ( ! isset( $_GET['paged'] ) ) ? 1 : intval( $_GET['paged'] );
			$offset = $per_page * ( $paged - 1 );

			$data = array(
				'snapshots' => $snapshots,
				'results_count' => $results_count,
				'count_all_snapshots' => $count_all_snapshots,
				'per_page' => $per_page,
				'max_pages' => $max_pages,
				'paged' => $paged,
				'offset' => $offset,
				'filter' => $filter
			);
			$this->render( "snapshots", false, $data );
		}
	}

	/*public function create_snapshot(){
		$this->render( "snapshots/partials/create-snapshot-progress", "Create Snapshot" );
	}*/

	public function destinations() {
		$snapshot_action = 'default';

		if ( isset( $_REQUEST['snapshot-action'] ) ) {
			$snapshot_action = sanitize_text_field( $_REQUEST['snapshot-action'] );
		}

		switch ( $snapshot_action ) {

			case 'add':
			case 'edit':
			case 'update':
				$this->render( 'destinations/add/index' );
				break;

			default:
				$this->render( 'destinations' );
		}
	}

	public function import() {
		$this->render( 'import' );
	}

	public function settings() {
		$this->render( 'settings' );
	}

	public function network_backup() {
		$model = new Snapshot_Model_Full_Backup();
		$backups = $model->get_backups();

		if ( is_array( $backups ) && count( $backups ) ) {
			$timestamps = array();
			foreach ( $backups as $idx => $backup_item ) {
				$timestamps[ $idx ] = isset( $backup_item['timestamp'] ) ? intval( $backup_item['timestamp'] ) : 0;
			}
			array_multisort( $timestamps, SORT_DESC, $backups );
		}

		$this->render( 'network-backup', false, array(
			'model' => $model,
			'backups' => $backups,
		) );
	}

	/**
	 * Render a full backup item in snapshot-style view
	 *
	 * @param int $timestamp The backup timestamp
	 */
	private function render_full_backup_item( $timestamp ) {
		$model = new Snapshot_Model_Full_Backup();
		$backups = $model->get_backups();
		
		$backup_item = null;
		foreach ( $backups as $backup ) {
			if ( isset( $backup['timestamp'] ) && intval( $backup['timestamp'] ) === $timestamp ) {
				$backup_item = $backup;
				break;
			}
		}
		
		if ( ! $backup_item ) {
			// Backup not found, redirect to network backup page
			wp_redirect( PSOURCESnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-network-backup' ) );
			exit;
		}
		
		$this->render( 'snapshots/full-backup-item', false, array( 'item' => $backup_item ) );
	}

	/**
	 * @param string $file
	 * @param bool   $deprecated
	 * @param array  $params
	 * @param bool   $return
	 * @param bool   $footer
	 *
	 * @return string
	 */
	public function render( $file, $deprecated = false, $params = array(), $return = false, $footer = true ) {

		$template_filename = "views/$file.php";
		$template_file = trailingslashit( dirname( __FILE__ ) ) . $template_filename;

		if ( ! file_exists( $template_file ) ) {
			wp_die( 'Template not found: ' . esc_html( $template_file ) );
		}

		if ( $return ) {
			ob_start();
		}

		extract( $params, EXTR_SKIP );

		include $template_file;

		if ( $footer ) {
			$this->render( 'common/footer', false, array(), false, false );
		}

		if ( $return ) {
			return ob_get_clean();
		}

		foreach ( $params as $param ) {
			unset( $param );
		}

		return null;
	}

	/**
	 * Print the form errors for a particular field, if there are any
	 *
	 * @param $field
	 */
	public function input_error_message( $field ) {

		if ( ! isset( PSOURCESnapshot::instance()->form_errors[ $field ] ) ) {
			return;
		}

		$field_errors = (array) PSOURCESnapshot::instance()->form_errors[ $field ];

		echo '<div class="error-text">';

		foreach ( $field_errors as $error ) {
			echo '<p>', esc_html( $error ), '</p>';
		}

		echo '</div>';
	}

	public function input_error_class( $field, $echo = true ) {

		if ( ! isset( PSOURCESnapshot::instance()->form_errors[ $field ] ) ) {
			return '';
		}

		$class = ' validation-error';

		if ( $echo ) {
			echo $class;
		}

		return $class;
	}
}