<?php
	$ajax_nonce = wp_create_nonce( "snapshot-save-key" );
?>

<div id="ss-show-apikey">

	<div id="wps-snapshot-key" class="snapshot-three wps-popup-modal"><?php // Use "show" class to show the popup, or else remove it to hide popup ?>

		<div class="wps-popup-mask"></div>

		<div class="wps-popup-content">

			<div class="wpmud-box">

				<div class="wpmud-box-title can-close">

					<h3><?php _e('Add Snapshot Key', SNAPSHOT_I18N_DOMAIN); ?></h3>

					<i class="wps-icon i-close"></i>

				</div>

				<div class="wpmud-box-content">

					<div class="row">

						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

							<?php if (isset( $apiKey ) && !empty( $apiKey )) : ?>

							<p><?php _e('This is your Snapshot API key. If you have any issues connecting to PSOURCE’s cloud servers, just reset your key. Don’t worry, resetting your key won’t affect your backups.', SNAPSHOT_I18N_DOMAIN); ?></p>

							<?php else : ?>

								<div class="wps-snapshot-popin-content wps-snapshot-popin-content-step-1">
						<p><?php _e('Please enter your API key below to enable advanced features.', SNAPSHOT_I18N_DOMAIN); ?></p>
								<div class="wps-snapshot-popin-content wps-snapshot-popin-content-step-2 hidden">
									<p><?php _e('Please wait while we verify your Snapshot key...', SNAPSHOT_I18N_DOMAIN); ?></p>
								</div>

								<div class="wps-snapshot-popin-content wps-snapshot-popin-content-step-3 hidden">
									<div class="wps-snapshot-error wpmud-box-gray">
										<p><?php printf(__('We couldn’t verify your Snapshot key. Try entering it again, or reset it for this website in <a target="_blank" href="%s">The Hub</a> over at PSOURCE.', SNAPSHOT_I18N_DOMAIN ), 'https://premium.psource.org/hub/' );?></p>
									</div>
								</div>

								<div class="wps-snapshot-popin-content wps-snapshot-popin-content-step-4 hidden">
									<p><?php _e('This is your Snapshot API key. If you have any issues connecting to PSOURCE’s cloud servers, just reset your key. Don’t worry, resetting your key won’t affect your backups.', SNAPSHOT_I18N_DOMAIN); ?></p>
								</div>


							<?php endif; ?>

							<form method="post" action="?page=snapshot_settings" data-security="<?php echo $ajax_nonce;?>">

								<div class="wps-snapshot-key wpmud-box-gray">

									<input type="text" name="secret-key" id="secret-key" value="<?php echo ( isset( $apiKey ) && !empty( $apiKey ) ) ? $apiKey : '' ?>"  data-url="<?php echo ( isset( $apiKeyUrl ) && !empty( $apiKeyUrl ) ) ? $apiKeyUrl : '' ?>" placeholder="<?php _e('Enter your key here', SNAPSHOT_I18N_DOMAIN); ?>">

									<?php if ( !isset( $apiKey ) || empty( $apiKey )) : ?>

									<button type="submit" name="activate" value="yes" class="button button-gray"><?php _e('Save Key', SNAPSHOT_I18N_DOMAIN); ?></button>

								<?php endif; ?>

									<?php /* Remote key reset removed - no longer using remote storage */ ?>



								</div>

							</form>

						</div>

					</div>

				</div>

			</div>

		</div>

	</div>

</div>