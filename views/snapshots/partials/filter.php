<div class="my-snapshots-filter">

	<div class="msf-left">

		<select name="action" class="bulk-action-selector-top">
			<option value="-1"><?php _e( "Massenaktionen","SNAPSHOT_I18N_DOMAIN");?>
			<option value="delete"><?php _e( "Löschen","SNAPSHOT_I18N_DOMAIN");?></option>

		</select>

		<input type="submit" id="doaction" class="button button-outline button-gray action" value="<?php _e( "Anwenden","SNAPSHOT_I18N_DOMAIN");?>">

	</div>

	<div class="msf-right <?php echo ( $results_count > $per_page ) ? 'pagination-enabled' : '' ?>">

		<span class="results-count"><?php echo $results_count ?> Ergebnisse</span>

		<?php if ( $results_count > $per_page ) : ?>

			<ul class="my-snapshot-pagination">

			 <?php Snapshot_Helper_UI::table_pagination($max_pages); ?>

			</ul>

		<?php endif; ?>

	</div>

</div>