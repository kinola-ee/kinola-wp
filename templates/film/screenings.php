<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<section class="kinola-film-screenings">
    <h3><?php echo __( 'Screenings', 'kinola' ); ?></h3>
    <?php echo $rendered_filter; ?>
    <?php if ( count( $events ) ): ?>
        <?php foreach ( $events as $event ): ?>
            <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
            <div class="kinola-film-screening">
                <p><?php echo esc_html( $event->get_venue_name() ); ?></p>
                <p><?php echo esc_html( $event->get_date() ); ?> <?php echo esc_html( $event->get_time() ); ?></p>
                <?php $free_seats = $event->get_free_seats(); // null = seat count unknown → not sold out; 0 = sold out ?>
                <?php if ( $event->is_coming_soon() ): ?>
                    <span class="kinola-screenings-coming-soon">
                        <?php _e( 'Coming soon', 'kinola' ); ?>
                    </span>
                <?php elseif ($free_seats === 0): ?>
                    <span class="kinola-screenings-tickets-link-sold-out">
                        <?php _e( 'Sold out', 'kinola' ); ?>
                    </span>
                <?php else: ?>
                    <?php if ( $event->is_free() && !$event->requires_registration() ): ?>
                        <span class="kinola-screenings-free">
                            <?php _e( 'Free', 'kinola' ); ?>
                        </span>
                    <?php else: ?>
                        <a class="kinola-screenings-tickets-link" href="<?php echo esc_url( $event->get_checkout_url() ); ?>">
                            <?php if ( $event->is_free() && $event->requires_registration() ): ?>
                                <?php _e( 'Register', 'kinola' ); ?>
                            <?php else: ?>
                                <?php _e( 'Buy ticket', 'kinola' ); ?>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <?php _e( 'No upcoming screenings', 'kinola' ); ?>
    <?php endif; ?>
</section>
