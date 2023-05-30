<?php if (count($films)): ?>
	<?php foreach ($films as $film): ?>
		<?php /* @var $film \Kinola\KinolaWp\Film */ ?>
		<div style="padding: 10px 20px; border: 1px solid #ccc; overflow: auto;">
			<img src="<?php echo $film->get_poster_url(); ?>" width="100px" height="150px" style="float: left;" />
			<div style="float:left; margin-left: 20px;">
				<p>
					<strong>
						<a href="<?php echo get_permalink($film->get_local_id()); ?>">
                            <?php echo $film->get_field('title'); ?>
                        </a>
					</strong>
					<br>
					<?php echo $film->get_field('title_original'); ?>
				</p>
			</div>
		</div>
	<?php endforeach; ?>
<?php else: ?>
	<?php _e('No films to display.', 'kinola'); ?>
<?php endif; ?>