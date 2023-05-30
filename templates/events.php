<?php if (count($events)): ?>
    <?php foreach ($events as $event): ?>
        <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
        <div style="padding: 10px 20px; border: 1px solid #ccc; overflow: auto;">
            <img src="<?php echo $event->get_poster_url(); ?>" width="100px" height="150px" style="float: left;" />
            <div style="float:left; margin-left: 20px;">
                <p>
                    <strong>
                        <?php echo $event->get_field('production', false)['name']; ?>
                    </strong>
                    <br>
                    <?php echo $event->get_date() ?> <?php echo $event->get_time(); ?>
                </p>
                <p>
                    <a href="<?php echo $event->get_checkout_url(); ?>">
                        <?php _e('Buy ticket', 'kinola'); ?>
                    </a>
                </p>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <?php _e('No upcoming events!', 'kinola'); ?>
<?php endif; ?>