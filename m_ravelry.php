<?php if ( ! empty( $theme->ravelry ) ) : ?>
	<?php foreach( $theme->ravelry as $project ) : ?>
		<div class="rav_project">
			<img src="<?php echo $project->thumbnail ? $project->thumbnail->src : $theme->m_ravelry_placeholder; ?>" />
			<div class="rav_progress_bar_wrapper">
				<p><a href="<?php echo $project->url; ?>"><?php echo $project->name; ?></a></p>
				<div class="rav_progress_bar">
					<div class="rav_progress_filled" style="width: <?php echo $project->progress; ?>px"></div>
					<div class="rav_progress_text"><?php echo $project->progress; ?>%</div>
				</div>
			</div>
			<div style="clear:both;" ></div>
		</div>
	<?php endforeach; ?>
<?php endif; ?>