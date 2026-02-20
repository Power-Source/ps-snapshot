<?php

/**
 * Authenticated AJAX action controller
 */
class Snapshot_Controller_Full_Ajax extends Snapshot_Controller_Full {

	const OPTIONS_FLAG = 'snapshot_ajax_backup_run';

	/**
	 * Internal instance reference
	 *
	 * @var object Snapshot_Controller_Full_Ajax instance
	 */
	private static $_instance;

	/**
	 * Singleton instance getter
	 *
	 * @return object Snapshot_Controller_Full_Ajax instance
	 */
	public static function get () {
		if (empty(self::$_instance)) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * Dispatch AJAX actions handling.
	 */
	public function run () {
		add_action( 'wp_ajax_snapshot-full_backup-check_requirements', array( $this, 'json_check_requirements' ) );

		add_action( 'wp_ajax_snapshot-full_backup-download', array( $this, 'json_download_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-delete', array( $this, 'json_delete_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-get_log', array( $this, 'json_get_log' ) );

		add_action( 'wp_ajax_snapshot-full_backup-reload', array( $this, 'json_reload_backups' ) );
		add_action( 'wp_ajax_snapshot-full_backup-reset_api', array( $this, 'json_reset_api' ) );

		add_action( 'wp_ajax_snapshot-full_backup-start', array( $this, 'json_start_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-estimate', array( $this, 'json_estimate_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-process', array( $this, 'json_process_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-finish', array( $this, 'json_finish_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-abort', array( $this, 'json_abort_backup' ) );

		add_action( 'wp_ajax_snapshot-full_backup-restore', array( $this, 'json_start_restore' ) );

		add_action( 'wp_ajax_snapshot-full_backup-exchange_key', array( $this, 'json_remote_key_exchange' ) );
		add_action( 'wp_ajax_snapshot-full_backup-deactivate', array( $this, 'json_deactivate' ) );

		// Network backup schedule actions
		add_action( 'wp_ajax_snapshot-network_backup-load_schedule', array( $this, 'json_load_network_backup_schedule' ) );
		add_action( 'wp_ajax_snapshot-network_backup-save_schedule', array( $this, 'json_save_network_backup_schedule' ) );
		add_action( 'wp_ajax_snapshot-network_backup-check_status', array( $this, 'json_check_backup_status' ) );

		add_site_option( self::OPTIONS_FLAG, '' );
	}

	public function json_deactivate () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die; // Only some users can reload
		return wp_send_json_success(Snapshot_Controller_Full_Admin::get()->deactivate());
	}

	/**
	 * Runs on deactivation
	 */
	public function deactivate () {
		delete_site_option(self::OPTIONS_FLAG);
	}

	/**
	 * Outputs log file content
	 */
	public function json_get_log () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die; // Only some users can reload

		$response = __('Your log file is empty', SNAPSHOT_I18N_DOMAIN);
		$content = Snapshot_Helper_Log::get()->get_log();
		if (!empty($content)) {
			$response = '<textarea readonly style="width:100%; height:100%">' . esc_textarea($content) . '</textarea>';
		}

		die($response);
	}

	/**
	 * Sets up backup key exchange
	 */
	public function json_remote_key_exchange () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die;
		
		// Remote key exchange removed - no longer using remote storage
		return wp_send_json_error(__('Remote storage not available', SNAPSHOT_I18N_DOMAIN));
	}

	/**
	 * Forces backup list reloads
	 */
	public function json_reload_backups () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die;
		
		// Just return success - local backups don't need cache reset
		wp_send_json(array(
			'status' => true,
		));
	}

	/**
	 * Forces API info refresh
	 *
	 * @since 1.0.0
	 */
	public function json_reset_api () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die; // Only some users can do this

		$hub = Snapshot_Controller_Full_Hub::get();
		$status = $hub->clear_api_cache();

		$status = is_wp_error($status)
			? $status->get_error_message()
			: 0
		;

		wp_send_json(array(
			'status' => $status,
		));
	}

	/**
	 * Prepare backup for download
	 */
	public function json_download_backup () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die; // Only some users can restore
		if (!$this->_is_backup_processing_ready()) die;

		$data = stripslashes_deep($_POST);
				$delete_archive = !empty($data['delete_archive']) && $data['delete_archive'] === '1';
		$timestamp = !empty($data['idx']) && is_numeric($data['idx'])
			? $data['idx']
			: false
		;
		if (!$timestamp) wp_send_json(array(
			'task' => 'clearing',
			'status' => false,
		));

		$archive_path = $this->_model->local()->get_backup($timestamp);
		if (empty($archive_path) || !file_exists($archive_path)) {
			// No local backup available
			wp_send_json(array(
				'task' => 'fetching',
				'error' => true,
				'status' => false,
			));
		} else {
			// If we don't have the full archive path yet, we're still fetching the file
			if (!file_exists($archive_path)) {
				wp_send_json(array(
					'task' => 'fetching',
					'error' => !!$this->_model->has_errors(),
					'status' => false,
				));
			} else {
				wp_send_json(array(
					'task' => 'clearing',
					'status' => true,
					'nonce' => wp_create_nonce('snapshot-full_backups-download'),
				));
			}
		}

		// We shouldn't be getting here but oh well
		die;
	}

	/**
	 * Delete remote backup and force cache cleanup.
	 */
	public function json_delete_backup () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die; // Only some users can restore
		if (!$this->_is_backup_processing_ready()) die;

		$data = stripslashes_deep($_POST);
		$timestamp = !empty($data['idx']) && is_numeric($data['idx'])
			? $data['idx']
			: false
		;
		if (!$timestamp) wp_send_json(array(
			'task' => 'clearing',
			'status' => false,
		));

		$status = $this->_model->delete_backup($timestamp);

		wp_send_json(array(
			'task' => 'clearing',
			'status' => $status,
		));
	}

	/**
	 * Check requirements
	 */
	public function json_check_requirements () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die;

		$minimum_exec_time = 150;

		// Check WP version
		wp_version_check();
		$wp_state_response = get_site_transient('update_core');
		$wp_state = !empty($wp_state_response->updates[0]->response)
			? ('latest' === $wp_state_response->updates[0]->response)
			: false
		;

		if (!$wp_state) Snapshot_Helper_Log::note("There has been an issue with determining WordPress state");

		// Fileset
		$set = Snapshot_Model_Fileset::get_source('full');
		$location = $set->get_root();

		if (empty($location) || !file_exists($location)) Snapshot_Helper_Log::note("There has been an issue with determining location");

		// Tables
		$tables = Snapshot_Model_Queue_Tableset::get_all_tables();
		if (empty($tables)) Snapshot_Helper_Log::note("There has been an issue with determining your database setup");

		$open_basedir = ini_get('open_basedir');
		if ($open_basedir) Snapshot_Helper_Log::note("It seems that open_basedir is in effect");

		$exec_time = ini_get('max_execution_time');
		$runtime = (int)$exec_time >= $minimum_exec_time;
		if (!$runtime) Snapshot_Helper_Log::note("Run time might not be enough: {$exec_time}");

		$mysqli = (bool)function_exists('mysqli_connect');
		if (!$mysqli) Snapshot_Helper_Log::note("We do not seem to have mysqli available");

		// Check backup method
		$backup_helper = new Snapshot_Helper_Backup();
		$system_backup_available = $backup_helper->supports_system_backup();
		$will_use_system = $backup_helper->will_do_system_backup();
		
		if ($will_use_system) {
			Snapshot_Helper_Log::info("System backup (CLI) will be used - optimal for large sites");
		} else if ($system_backup_available) {
			Snapshot_Helper_Log::note("System backup available but not enabled");
		}

		// Detailed system backup requirements check
		$system_requirements = array();
		$system_requirements['escapeshellarg'] = Snapshot_Helper_System::is_available('escapeshellarg');
		$system_requirements['escapeshellcmd'] = Snapshot_Helper_System::is_available('escapeshellcmd');
		$system_requirements['exec'] = Snapshot_Helper_System::is_available('exec');
		$system_requirements['zip'] = Snapshot_Helper_System::has_command('zip');
		$system_requirements['mysqldump'] = Snapshot_Helper_System::has_command('mysqldump');
		$system_requirements['ln'] = Snapshot_Helper_System::has_command('ln');
		$system_requirements['rm'] = Snapshot_Helper_System::has_command('rm');

		$missing_requirements = array();
		foreach ($system_requirements as $req => $available) {
			if (!$available) {
				$missing_requirements[] = $req;
			}
		}

		wp_send_json(array(
			'webserver' => array(
				'system' => array(
					'value' => $_SERVER['SERVER_SOFTWARE'],
					'result' => true,
				),
			),
			'php' => array(
				'basedir' => array(
					'value' => $open_basedir ? __('Enabled', SNAPSHOT_I18N_DOMAIN) : __('Disabled', SNAPSHOT_I18N_DOMAIN),
					'result' => !$open_basedir,
				),
				'maxtime' => array(
					'value' => $exec_time,
					'result' => $runtime,
				),
				'mysqli' => array(
					'value' => (int)$mysqli,
					'result' => (bool)$mysqli,
				),
			),
			'wordpress' => array(
				'version' => array(
					'value' => get_bloginfo('version'),
					'result' => (bool)$wp_state,
				),
			),
			'backup' => array(
				'method' => array(
					'value' => $will_use_system 
						? __('System (CLI)', SNAPSHOT_I18N_DOMAIN)
						: __('PHP (ZipArchive)', SNAPSHOT_I18N_DOMAIN),
					'result' => true,
					'info' => $will_use_system 
						? __('Optimiert für große Websites', SNAPSHOT_I18N_DOMAIN)
						: ($system_backup_available 
							? __('System-Backup verfügbar aber nicht aktiviert', SNAPSHOT_I18N_DOMAIN)
							: __('System-Backup nicht verfügbar', SNAPSHOT_I18N_DOMAIN)
						),
					'system_available' => $system_backup_available,
					'will_use_system' => $will_use_system,
					'missing_requirements' => $missing_requirements,
					'all_requirements' => $system_requirements,
				),
			),
			'fileset' => array(
				'location' => array(
					'value' => basename($location),
					'result' => file_exists($location),
				),
			),
			'tableset' => array(
				'quantity' => array(
					'value' => count($tables),
					'result' => (bool)count($tables),
				),
			),
		));
	}

	/**
	 * Process restore requests
	 */
	public function json_start_restore () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) {
			Snapshot_Helper_Log::error(__('Keine Berechtigung für Wiederherstellung', SNAPSHOT_I18N_DOMAIN));
			wp_send_json_error(__('Sie haben keine Berechtigung, Backups wiederherzustellen.', SNAPSHOT_I18N_DOMAIN));
		}
		
		if (!$this->_is_backup_processing_ready()) {
			Snapshot_Helper_Log::error(__('Backup-Verarbeitung nicht bereit', SNAPSHOT_I18N_DOMAIN));
			wp_send_json_error(__('Die Backup-Verarbeitung ist derzeit nicht verfügbar.', SNAPSHOT_I18N_DOMAIN));
		}
		
		// Extend execution time for restore operations
		@set_time_limit(300); // 5 minutes per restore step
		
		check_ajax_referer('snapshot-full-backup-restore', 'security');

		$data = stripslashes_deep($_POST);
		$archive = !empty($data['archive']) && is_numeric($data['archive'])
			? $data['archive']
			: false
		;
		$restore_path = !empty($data['restore']) && file_exists($data['restore'])
			? $data['restore']
			: false
		;

		$credentials = !empty($data['credentials'])
			? stripslashes_deep($data['credentials'])
			: true
		;
		
		$delete_archive = !empty($data['delete_archive']) ? (bool)$data['delete_archive'] : false;

		// Signal intent - starting action
		Snapshot_Helper_Log::start();

		if (!WP_Filesystem($credentials)) {
			Snapshot_Helper_Log::error(__('WP_Filesystem Initialisierung fehlgeschlagen', SNAPSHOT_I18N_DOMAIN));
			wp_send_json_error(__('Dateisystem-Initialisierung fehlgeschlagen. Bitte prüfen Sie die Dateiberechtigungen.', SNAPSHOT_I18N_DOMAIN));
		}

		if (empty($archive)) {
			Snapshot_Helper_Log::error(__('Kein Archiv-Zeitstempel angegeben', SNAPSHOT_I18N_DOMAIN));
			wp_send_json_error(__('Kein gültiges Backup ausgewählt.', SNAPSHOT_I18N_DOMAIN));
		}

		if (empty($restore_path)) {
			$restore_path = apply_filters('snapshot_home_path', get_home_path());
		}

		if (empty($restore_path) || !file_exists($restore_path)) {
			Snapshot_Helper_Log::error(sprintf(__('Wiederherstellungspfad nicht gefunden: %s', SNAPSHOT_I18N_DOMAIN), $restore_path));
			wp_send_json_error(__('Der Ziel-Ordner für die Wiederherstellung existiert nicht.', SNAPSHOT_I18N_DOMAIN));
		}

		$archive_path = $this->_model->get_backup($archive);

		// If we don't have the full archive path yet, we're still fetching the file
		if (!file_exists($archive_path)) {
			$error_msg = $this->_model->has_errors() 
				? __('Fehler beim Abrufen des Backups.', SNAPSHOT_I18N_DOMAIN)
				: __('Backup-Archiv wird noch heruntergeladen.', SNAPSHOT_I18N_DOMAIN);
			Snapshot_Helper_Log::error(sprintf(__('Archiv nicht gefunden: %s', SNAPSHOT_I18N_DOMAIN), $archive_path));
			wp_send_json_error($error_msg);
		}

		$restore = Snapshot_Helper_Restore::from($archive_path);
		$restore->to($restore_path);

		$task = $restore->is_done()
			? 'clearing'
			: 'restoring'
		;

		$status = 'clearing' === $task
			? $restore->clear()
			: $restore->process_files()
		;

		if ('clearing' === $task && $delete_archive) {
			@unlink($archive_path);
		}

		if (!$status && 'restoring' === $task) {
			Snapshot_Helper_Log::error(__('Wiederherstellung fehlgeschlagen', SNAPSHOT_I18N_DOMAIN));
			wp_send_json_error(__('Die Wiederherstellung ist fehlgeschlagen. Bitte überprüfen Sie die Logs.', SNAPSHOT_I18N_DOMAIN));
		}

		wp_send_json_success(array(
			'task' => $task,
			'status' => $status,
			'message' => 'restoring' === $task 
				? __('Wiederherstellung läuft...', SNAPSHOT_I18N_DOMAIN)
				: __('Wiederherstellung abgeschlossen.', SNAPSHOT_I18N_DOMAIN)
		));
	}

	/**
	 * Sends back backup size estimate
	 */
	public function json_estimate_backup () {
		if (!$this->_is_backup_processing_ready()) die;

		$idx = $this->_get_backup_type();
		$backup = Snapshot_Helper_Backup::load($idx);

		$total = $backup
			? $backup->get_total_steps_estimate()
			: 0
		;

		wp_send_json(array(
			'total' => $total,
		));
	}

	/**
	 * Backup start JSON handler
	 *
	 * First in the cascade of requests actually performing the backup
	 */
	public function json_start_backup () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die;

		// Extend execution time for backup initialization
		@set_time_limit(300); // 5 minutes

		if (!$this->_model->is_active()) {
			$this->_model->set_config('active', true);
		}

		if (!$this->_is_backup_processing_ready()) die;

		// Signal intent - starting action
		Snapshot_Helper_Log::start();

		$idx = $this->_get_backup_type();
		$this->_start_backup($idx);

		update_site_option(self::OPTIONS_FLAG, true);
		// Save current backup ID and start time for status tracking
		update_site_option('snapshot_network_backup_current_id', $idx);
		update_site_option('snapshot_network_backup_start_time', time());

		// Save selected destination
		$data = stripslashes_deep($_POST);
		$destination = !empty($data['destination']) ? sanitize_text_field($data['destination']) : 'local';
		update_site_option('snapshot_network_backup_destination', $destination);

		// Calculate and save total backup size
		$backup = Snapshot_Helper_Backup::load($idx);
		$total_size = $backup ? $backup->get_total_size_bytes() : 0;
		update_site_option('snapshot_network_backup_total_size', $total_size);
		update_site_option('snapshot_network_backup_processed_size', 0);
		// Initialize process counter for incremental size tracking
		update_site_option('snapshot_backup_process_counter_' . $idx, 0);

		// Determine backup method for frontend display
		$backup_instance = Snapshot_Helper_Backup::load($idx);
		$will_use_system = $backup_instance ? $backup_instance->will_do_system_backup() : false;
		$supports_system = $backup_instance ? $backup_instance->supports_system_backup() : false;
		
		$backup_method = $will_use_system 
			? __('System (CLI)', SNAPSHOT_I18N_DOMAIN)
			: __('PHP (ZipArchive)', SNAPSHOT_I18N_DOMAIN);
		$backup_method_detail = $will_use_system
			? __('Nutzt Shell-Befehle für optimale Performance', SNAPSHOT_I18N_DOMAIN)
			: ($supports_system 
				? __('System-Backup verfügbar aber nicht aktiviert', SNAPSHOT_I18N_DOMAIN)
				: __('Standard PHP-Methode', SNAPSHOT_I18N_DOMAIN)
			);

		wp_send_json(array(
			'id' => $idx,
			'total_size' => $total_size,
			'total_size_formatted' => size_format($total_size),
			'backup_method' => $backup_method,
			'backup_method_detail' => $backup_method_detail,
			'will_use_system' => $will_use_system,
		));
	}

	/**
	 * Backup processing JSON handler
	 *
	 * This will get called repeatedly, as long as the backup isn't ready
	 */
	public function json_process_backup () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die;

		// Extend execution time for large backup operations
		@set_time_limit(300); // 5 minutes per AJAX call

		$data = stripslashes_deep($_POST);
		$idx = !empty($data['idx']) ? $data['idx'] : $this->_get_backup_type();

		// Check if backup was aborted (current_id option will be deleted)
		$current_id = get_site_option('snapshot_network_backup_current_id');
		if (!$current_id || $current_id !== $idx) {
			// Backup was aborted, return error
			wp_send_json(array(
				'done' => true,
				'error' => 'Backup was aborted'
			));
		}

		$status = false;
		try {
			$status = $this->_process_backup($idx);
		} catch (Snapshot_Exception $e) {
			$key = $e->get_error_key();
			$msg = Snapshot_Model_Full_Error::get_human_description($key);

			Snapshot_Helper_Log::error("Error processing manual backup: {$key}");
			Snapshot_Helper_Log::note($msg);

			delete_site_option(self::OPTIONS_FLAG);

			/**
			 * Automatic backup processing encountered too many errors
			 *
			 * @since 1.0.0
			 *
			 * @param string Action type indicator (process or finish)
			 * @param string $key Error message key
			 * @param string $msg Human-friendly message description
			 */
			do_action($this->get_filter('ajax-error-stop'), 'process', $key, $msg); // Notify anyone interested

			die(esc_js($msg));
		}

		// Update processed size incrementally with caching (only scan every 10 calls)
		$backup = Snapshot_Helper_Backup::load($idx);
		if ($backup) {
			$backup->update_backup_size_incremental();
		}

		wp_send_json(array(
			'done' => $status,
		));
	}

	/**
	 * Backup end JSON handler
	 *
	 * The last in the cascade of requests actually performing the backup
	 */
	public function json_finish_backup () {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) die;

		// Extend execution time for backup finalization
		@set_time_limit(300); // 5 minutes

		$data = stripslashes_deep($_POST);
		$idx = !empty($data['idx']) ? $data['idx'] : $this->_get_backup_type();

		try {
			$status = $this->_finish_backup($idx);
		} catch (Snapshot_Exception $e) {
			$key = $e->get_error_key();
			$msg = Snapshot_Model_Full_Error::get_human_description($key);

			Snapshot_Helper_Log::error("Error finalizing manual backup: {$key}");
			Snapshot_Helper_Log::note($msg);

			delete_site_option(self::OPTIONS_FLAG);
			delete_site_option('snapshot_network_backup_current_id');
			delete_site_option('snapshot_network_backup_start_time');

			/**
			 * Automatic backup processing encountered too many errors
			 *
			 * @since 1.0.0
			 *
			 * @param string Action type indicator (process or finish)
			 * @param string $key Error message key
			 * @param string $msg Human-friendly message description
			 */
			do_action($this->get_filter('ajax-error-stop'), 'finish', $key, $msg); // Notify anyone interested

			die(esc_js($msg));
		}

		delete_site_option(self::OPTIONS_FLAG);
		delete_site_option('snapshot_network_backup_current_id');
		delete_site_option('snapshot_network_backup_start_time');
		delete_site_option('snapshot_backup_process_counter_' . $idx);

		if (!$status && !$this->_model->has_api_info()) {
			$response = array(
				'status' => true,
				'msg' => __('Could not communicate with remote service', SNAPSHOT_I18N_DOMAIN),
			);
		} else {
			$response = array(
				'status' => $status,
			);
		}

		wp_send_json($response);
	}

	/**
	 * Abort the current backup and remove all temporary data
	 *
	 * @return void
	 */
	public function json_abort_backup() {
		if (!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())) {
			wp_send_json_error(array('message' => 'Insufficient permissions'));
		}

		$data = stripslashes_deep($_POST);
		$idx = !empty($data['idx']) ? $data['idx'] : $this->_get_backup_type();

		// Load the backup and stop it
		$backup = Snapshot_Helper_Backup::load($idx);
		if ($backup) {
			$backup->stop_and_remove();
			Snapshot_Helper_Log::info("Backup {$idx} was aborted by user");
		}

		// Clean up site options
		delete_site_option(self::OPTIONS_FLAG);
		delete_site_option('snapshot_network_backup_current_id');
		delete_site_option('snapshot_network_backup_start_time');
		delete_site_option('snapshot_network_backup_total_size');
		delete_site_option('snapshot_network_backup_processed_size');
		delete_site_option('snapshot_backup_process_counter_' . $idx);

		wp_send_json(array(
			'status' => true,
			'message' => 'Backup abgebrochen'
		));
	}

	/**
	 * Check if a backup is currently running and get its status
	 *
	 * @return void
	 */
	public function json_check_backup_status() {
		// Basic capability check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( array(
				'running' => false,
				'id' => null,
				'current' => 0,
				'total' => 0,
				'start_time' => 0,
				'debug' => 'No manage_options capability',
			) );
			return;
		}

		$backup_id = get_site_option( 'snapshot_network_backup_current_id' );
		if ( ! $backup_id ) {
			wp_send_json( array(
				'running' => false,
				'id' => null,
				'current' => 0,
				'total' => 0,
				'start_time' => 0,
				'debug' => 'No backup_id in options',
			) );
			return;
		}

		// Check if backup with this ID still exists and is running
		$backup = Snapshot_Helper_Backup::load( $backup_id );
		if ( ! $backup ) {
			delete_site_option( 'snapshot_network_backup_current_id' );
			delete_site_option( 'snapshot_network_backup_start_time' );
			wp_send_json( array(
				'running' => false,
				'id' => null,
				'current' => 0,
				'total' => 0,
				'start_time' => 0,
				'debug' => 'Backup cannot be loaded',
			) );
			return;
		}

		$is_done = $backup->is_done();
		if ( $is_done ) {
			delete_site_option( 'snapshot_network_backup_current_id' );
			delete_site_option( 'snapshot_network_backup_start_time' );
			wp_send_json( array(
				'running' => false,
				'id' => null,
				'current' => 0,
				'total' => 0,
				'start_time' => 0,
				'debug' => 'Backup is finished',
			) );
			return;
		}

		// Get progress info
		$total_steps = (int) $backup->get_total_steps_estimate();
		$current_steps = (int) $backup->get_processed_steps();
		$start_time = (int) get_site_option( 'snapshot_network_backup_start_time', 0 );
		
		// Get size information
		$total_size = (int) get_site_option( 'snapshot_network_backup_total_size', 0 );
		$processed_size = (int) get_site_option( 'snapshot_network_backup_processed_size', 0 );

		wp_send_json( array(
			'running' => true,
			'id' => $backup_id,
			'current' => $current_steps,
			'total' => $total_steps,
			'start_time' => $start_time,
			'total_size' => $total_size,
			'total_size_formatted' => size_format( $total_size ),
			'processed_size' => $processed_size,
			'processed_size_formatted' => size_format( $processed_size ),
			'debug' => 'Backup running normally',
		) );
	}

	/**
	 * Load network backup schedule settings
	 *
	 * @return void
	 */
	public function json_load_network_backup_schedule() {
		if ( ! is_multisite() || ! is_network_admin() || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$schedule = get_site_option( 'snapshot_network_backup_schedule', array() );
		$days = isset( $schedule['days'] ) ? $schedule['days'] : array();
		$time = isset( $schedule['time'] ) ? $schedule['time'] : '02:00';
		$destination = isset( $schedule['destination'] ) ? $schedule['destination'] : 'local';
		$next_backup = isset( $schedule['next_backup'] ) ? $schedule['next_backup'] : '';

		wp_send_json( array(
			'schedule' => array(
				'days' => is_array( $days ) ? $days : array(),
				'time' => $time,
				'destination' => $destination,
				'next_backup' => $next_backup,
			),
		) );
	}

	/**
	 * Save network backup schedule settings
	 *
	 * @return void
	 */
	public function json_save_network_backup_schedule() {
		if ( ! is_multisite() || ! is_network_admin() || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		check_ajax_referer( 'snapshot-network-backup-schedule' );

		$days = isset( $_POST['days'] ) ? (array) $_POST['days'] : array();
		$time = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '02:00';
		$destination = isset( $_POST['destination'] ) ? sanitize_text_field( wp_unslash( $_POST['destination'] ) ) : 'local';

		// Validate days
		$allowed_days = array( 0, 1, 2, 3, 4, 5, 6 );
		$days = array_filter( $days, function( $d ) use ( $allowed_days ) {
			$d = (int) $d;
			return in_array( $d, $allowed_days, true );
		} );

		if ( empty( $days ) ) {
			wp_send_json_error( array( 'message' => 'Keine gültigen Tage ausgewählt' ) );
		}

		// Sort and deduplicate days
		$days = array_values( array_unique( array_map( 'intval', $days ) ) );
		sort( $days );

		// Validate time format (HH:MM)
		if ( ! preg_match( '/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time ) ) {
			wp_send_json_error( array( 'message' => 'Ungültiges Zeitformat' ) );
		}

		// Calculate next backup time
		$next_backup = self::calculate_next_backup_time( $days, $time );

		$schedule = array(
			'days' => $days,
			'time' => $time,
			'destination' => $destination,
			'next_backup' => $next_backup,
		);

		// Save schedule
		update_site_option( 'snapshot_network_backup_schedule', $schedule );

		// Reschedule WP-Cron if needed
		self::schedule_network_backup_cron( $schedule );

		wp_send_json( array(
			'status' => true,
			'schedule' => $schedule,
		) );
	}

	/**
	 * Calculate next backup time based on schedule
	 *
	 * @param array  $days Days of week (0-6)
	 * @param string $time Time in HH:MM format
	 * @return string Formatted datetime string
	 */
	private static function calculate_next_backup_time( $days, $time ) {
		$time_parts = explode( ':', $time );
		$hour = (int) $time_parts[0];
		$minute = (int) $time_parts[1];

		$current_time = current_time( 'timestamp' );
		$current_date = date( 'Y-m-d', $current_time );
		$current_dow = (int) date( 'w', $current_time );

		// Check if today is a backup day and time hasn't passed
		if ( in_array( $current_dow, $days, true ) ) {
			$today_backup = strtotime( $current_date . ' ' . $time );
			if ( $today_backup > $current_time ) {
				return date_i18n( 'd.m.Y H:i', $today_backup );
			}
		}

		// Find next backup day
		for ( $i = 1; $i <= 7; $i++ ) {
			$check_time = $current_time + ( $i * 86400 );
			$check_dow = (int) date( 'w', $check_time );
			if ( in_array( $check_dow, $days, true ) ) {
				$backup_time = strtotime( date( 'Y-m-d', $check_time ) . ' ' . $time );
				return date_i18n( 'd.m.Y H:i', $backup_time );
			}
		}

		return '';
	}

	/**
	 * Schedule WP-Cron for network backup
	 *
	 * @param array $schedule Schedule configuration
	 * @return void
	 */
	private static function schedule_network_backup_cron( $schedule ) {
		// Clear any existing scheduled backups
		wp_clear_scheduled_hook( 'snapshot_network_backup_cron_event' );

		if ( empty( $schedule['days'] ) ) {
			return;
		}

		// Schedule daily check at the specified time
		$time_parts = explode( ':', $schedule['time'] );
		$hour = (int) $time_parts[0];
		$minute = (int) $time_parts[1];

		// Get next occurrence of the scheduled time today
		$current_time = current_time( 'timestamp' );
		$today = date( 'Y-m-d', $current_time );
		$scheduled_time = strtotime( $today . ' ' . sprintf( '%02d:%02d:00', $hour, $minute ) );

		// If time has passed today, schedule for tomorrow at that time
		if ( $scheduled_time <= $current_time ) {
			$scheduled_time = strtotime( '+1 day', $scheduled_time );
		}

		// Schedule the event
		wp_schedule_event( $scheduled_time, 'daily', 'snapshot_network_backup_cron_event', array( $schedule ) );
	}
}