<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<section>
    <?php if ( count( $events ) ): ?>
        <h3><?php echo __( 'Screenings', 'kinola' ); ?></h3>
        <?php echo $rendered_filter; ?>
        <div class="kinola-film-screenings-wrap">
            <?php foreach ( $events as $event ): ?>
                <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
                <div class="kinola-film-screenings-item">
                    <div class="kinola-film-screenings-info">
                        <?php if ($event->has_program()): ?>
                            <div class="kinola-film-screenings-program">
                                <?php echo $event->get_program_name(); ?>
                            </div>
                        <?php endif; ?>
                        <div class="kinola-film-screenings-date">
                            <?php echo $event->get_date(); ?> - <?php echo $event->get_time(); ?>
                        </div>
                        <div class="kinola-item-venue">
                            <?php echo $event->get_venue_name(); ?>
                        </div>
                        <?php if ($event->get_note()): ?>
                            <div class="kinola-item-note">
                                <?php echo $event->get_note(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($event->get_free_seats()): ?>
                        <div class="kinola-film-screenings-tickets">
                            <a class="kinola-btn kinola-btn-small" href="<?php echo $event->get_checkout_url(); ?>">
                                <?php _e( 'Buy ticket', 'kinola' ); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="kinola-film-screenings-soldout">
                            <?php _e( 'Sold out', 'kinola' ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div>
            <h3><?php _e( 'No upcoming screenings', 'kinola' ); ?></h3>
        </div>
    <?php endif; ?>
</section>
