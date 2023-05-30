<div>
    <section>
        <?php /* @var $film \Kinola\KinolaWp\Film */ ?>
        <?php echo $film->get_field( 'description' ); ?>
    </section>
    <section>
        <?php if (count($film->get_events())): ?>
        <?php foreach ($film->get_events() as $event): ?>
            <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
            <div style="padding: 10px 20px; border: 1px solid #ccc;">
                <p><?php echo $event->get_date(); ?> <?php echo $event->get_time(); ?></p>
                <a href="<?php echo $event->get_checkout_url(); ?>">
                    <?php _e('Buy ticket', 'kinola'); ?>
                </a>
            </div>
        <?php endforeach; ?>
        <?php else: ?>
            <?php _e('No upcoming screenings', 'kinola'); ?>
        <?php endif; ?>
    </section>
</div>
