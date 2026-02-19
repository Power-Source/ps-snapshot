<?php

/**
 * Overall full backup model
 */
class Snapshot_Model_Full_Backup extends Snapshot_Model_Full_Abstract {

	/**
	 * Local model instance reference
	 *
	 * @var object Snapshot_Model_Full_Local
	 */
	private $_local;

	/**
	 * Create a new model instance
	 *
	 * Also populates internal facade references
	 */
	public function __construct() {
		$this->_local = new Snapshot_Model_Full_Local;
	}

	/**
	 * Gets model type
	 *
	 * Used in filtering implementation
	 *
	 * @return string Model type tag
	 */
	public function get_model_type() {
		return 'backup';
	}

	/**
	 * Gets local handler instance
	 *
	 * @return Snapshot_Model_Full_Local Local handler instance
	 */
	public function local() {
		return $this->_local;
	}

	/**
	 * Check for existence of any errors
	 *
	 * @return bool
	 */
	public function has_errors() {
		if ( $this->_local->has_errors() ) {
			return true;
		}
		return ! empty( $this->_errors );
	}

	/**
	 * Get errors as array of strings ready for showing.
	 *
	 * @return array
	 */
	public function get_errors() {
		$from_local = $this->_local->get_errors();
		$errors = is_array( $this->_errors )
			? $this->_errors
			: array();
		return array_merge( $from_local, $errors );
	}

	/**
	 * Check if full backups are activated
	 *
	 * @return bool
	 */
	public function is_active() {
		return apply_filters(
			$this->get_filter( 'is_active' ),
			$this->get_config( 'active' )
		);
	}

	/**
	 * Gets a list of known scheduled frequencies
	 *
	 * @param bool $title_case if true, will return capitalized frequency names, otherwise, lowercase
	 *
	 * @return array List of frequencies as key => label pairs
	 */
	public function get_frequencies( $title_case = true ) {

		if ( $title_case ) {
			$frequencies = array(
				'daily' => __( 'Täglich', SNAPSHOT_I18N_DOMAIN ),
				'weekly' => __( 'Wöchentlich', SNAPSHOT_I18N_DOMAIN ),
				'monthly' => __( 'Monatlich', SNAPSHOT_I18N_DOMAIN ),
			);
		} else {
			$frequencies = array(
				'daily' => __( 'daily', SNAPSHOT_I18N_DOMAIN ),
				'weekly' => __( 'weekly', SNAPSHOT_I18N_DOMAIN ),
				'monthly' => __( 'monthly', SNAPSHOT_I18N_DOMAIN ),
			);
		}

		return apply_filters( $this->get_filter( 'schedule_frequencies' ), $frequencies, $title_case );
	}

	/**
	 * Gets the currently set schedule frequency
	 *
	 * @return string Schedule frequency key
	 */
	public function get_frequency() {
		$default = 'weekly';
		$value = $this->get_config( 'frequency', $default );
		$value = ! empty( $value ) ? $value : $default;
		return apply_filters(
			$this->get_filter( 'schedule_frequency' ),
			$value
		);
	}

	/**
	 * Gets a list of known schedule times
	 *
	 * @return array A list of schedule times, as key => label pairs
	 */
	public function get_schedule_times() {
		$times = array();
		$midnight = strtotime( date( "Y-m-d 00:00:00" ) );
		$tf = get_option( 'time_format' );
		$offset = Snapshot_Model_Time::get()->get_utc_diff();
		for ( $i = 0; $i < DAY_IN_SECONDS; $i += HOUR_IN_SECONDS ) {
			$seconds = $i - $offset; // Deal with seconds, not hours
			if ( $seconds < 0 ) {
				$seconds += DAY_IN_SECONDS;
			}
			if ( $seconds >= DAY_IN_SECONDS ) {
				$seconds -= DAY_IN_SECONDS;
			}
			if ( 0 == $seconds ) {
				$seconds = 1;
			} // Because 0 will show current time in Hub :(
			$times[ $seconds ] = date_i18n( $tf, $midnight + $i );
		}
		return apply_filters(
			$this->get_filter( 'schedule_times' ),
			$times
		);
	}

	/**
	 * Gets the currently set schedule time
	 *
	 * @return int Relative schedule time
	 */
	public function get_schedule_time() {
		$default = 3600;
		$value = $this->get_config( 'schedule_time', $default );
		$value = is_numeric( $value ) ? (int) $value : $default;
		return (int) apply_filters(
			$this->get_filter( 'schedule_time' ),
			$value
		);
	}

	/**
	 * Check if we have any backups here
	 *
	 * @return bool
	 */
	public function has_backups() {
		$backups = $this->get_backups();
		return apply_filters(
			$this->get_filter( 'has_backups' ),
			! empty( $backups )
		);
	}

	/**
	 * Gets a list of backups
	 *
	 * @return array A list of full backup items
	 */
	public function get_backups() {
		return apply_filters(
			$this->get_filter( 'get_backups' ),
			$this->_local->get_backups()
		);
	}

	/**
	 * Gets a (local) backup file instance
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return mixed Path to backup if local file exists, (bool)false otherwise
	 */
	public function get_backup( $timestamp ) {
		return $this->_local->get_backup( $timestamp );
	}

	/**
	 * Deletes a backup instance
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return bool
	 */
	public function delete_backup( $timestamp ) {
		return $this->_local->delete_backup( $timestamp );
	}

	/**
	 * Proxies local backups rotation
	 *
	 * @return bool
	 */
	public function rotate_local_backups() {
		return $this->_local->rotate_backups();
	}

	/**
	 * Gets the next scheduled automatic backup start
	 *
	 * @return mixed (int)UNIX timestamp on success, (bool)false on failure
	 */
	public function get_next_automatic_backup_start_time() {
		$cron = Snapshot_Controller_Full_Cron::get();
		$schedule = wp_next_scheduled( $cron->get_filter( 'start_backup' ) );

		return ! empty( $schedule )
			? $schedule
			: false;
	}

	/**
	 * Send backup to remote destination
	 *
	 * @param Snapshot_Helper_Backup $backup Backup instance to send
	 *
	 * @return bool True on success, false on failure
	 */
	public function send_backup( $backup ) {
		// Apply filter to allow remote sending implementation
		return apply_filters(
			$this->get_filter( 'send_backup' ),
			true,
			$backup
		);
	}

	/**
	 * Continue uploading a backup item
	 *
	 * @param int $timestamp Backup timestamp
	 *
	 * @return bool True when upload is complete, false if still in progress
	 */
	public function continue_item_upload( $timestamp ) {
		// Apply filter to allow remote upload continuation implementation
		return apply_filters(
			$this->get_filter( 'continue_item_upload' ),
			true,
			$timestamp
		);
	}

	/**
	 * Update remote schedule with backup timestamp
	 *
	 * @param int $timestamp Backup timestamp
	 *
	 * @return bool True on success, false on failure
	 */
	public function update_remote_schedule( $timestamp ) {
		// Apply filter to allow remote schedule update implementation
		return apply_filters(
			$this->get_filter( 'update_remote_schedule' ),
			true,
			$timestamp
		);
	}

	/**
	 * Filter/action name getter
	 *
	 * @param string $filter Filter name to convert
	 *
	 * @return string Full filter name
	 */
	public function get_filter( $filter = false ) {
		if ( empty( $filter ) ) {
			return false;
		}
		if ( ! is_string( $filter ) ) {
			return false;
		}
		return 'snapshot-model-full-backup-' . $filter;
	}

}