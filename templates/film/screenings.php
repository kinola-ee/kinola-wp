<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<section class="kinola-film-screenings">
    <h3><?php echo __( 'Screenings', 'kinola' ); ?></h3>
    <?php echo $rendered_filter; ?>
    <?php if ( count( $events ) ): ?>
        <?php foreach ( $events as $event ): ?>
            <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
            <div class="kinola-film-screening">
                <p><?php echo $event->get_venue_name(); ?></p>
                <p><?php echo $event->get_date(); ?> <?php echo $event->get_time(); ?></p>
                <?php if ($event->get_free_seats()): ?>
                    <a class="kinola-screenings-tickets-link" href="<?php echo $event->get_checkout_url(); ?>">
                        <?php _e( 'Buy ticket', 'kinola' ); ?>
                    </a>
                <?php else: ?>
                    <span class="kinola-screenings-tickets-link-sold-out">
                        <?php _e( 'Sold out', 'kinola' ); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <?php _e( 'No upcoming screenings', 'kinola' ); ?>
    <?php endif; ?>
</section>
