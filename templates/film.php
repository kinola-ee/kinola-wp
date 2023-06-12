<?php
/**
 * This template is rendered inside the_content of a single Film post type.
 * It displays the detailed information of a film and all future screenings of that film.
 */
?>
<div>
    <section>
        <?php /* @var $film \Kinola\KinolaWp\Film */ ?>
        <?php echo $film->get_field( 'description' ); ?>
        <?php if ($film->get_field('gallery') && count($film->get_field('gallery'))): ?>
            <?php foreach ($film->get_field('gallery') as $image): ?>
                <a href="<?php echo $image['srcset']; ?>" target="_blank">
                    <img src="<?php echo $image['thumbnail']; ?>" alt="<?php echo $image['alt']; ?>" />
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <section>
        <?php if (count($film->get_events())): ?>
        <?php foreach ($film->get_events() as $event): ?>
            <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
            <div style="padding: 10px 20px; border: 1px solid #ccc;">
                <p><?php echo $event->get_venue_name(); ?></p>
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
