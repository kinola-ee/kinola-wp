<?php
/**
 * This template is the output of [kinola_events] shortcode.
 * It displays a list of all upcoming events along with location and date filters.
 */
?>

<?php echo $rendered_filter; ?>
<?php if ( count( $events ) ): ?>
    <div class="kinola-events">
        <?php foreach ( $events as $event ): ?>
            <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
            <div class="kinola-event" style="padding: 10px 20px; border: 1px solid #ccc; overflow: auto;">
                <img src="<?php echo $event->get_field( 'production_poster' ); ?>" width="100px" height="150px"
                     style="float: left;"/>
                <div class="kinola-event-details" style="float:left; margin-left: 20px;">
                    <p>
                        <a class="kinola-event-title" href="<?php echo $event->get_film_url(); ?>">
                            <strong>
                                <?php echo $event->get_field( 'production_title' ); ?>
                            </strong>
                        </a>
                        <br>
                        <span class="kinola-event-location">
                            <?php echo $event->get_venue_name(); ?>
                        </span>
                        <br>
                        <span class="kinola-event-date">
                            <?php echo $event->get_date() . ' ' . $event->get_time(); ?>
                        </span>
                    </p>
                    <p>
                        <a class="kinola-event-tickets-link" href="<?php echo $event->get_checkout_url(); ?>">
                            <?php _e( 'Buy ticket', 'kinola' ); ?>
                        </a>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div>
        <?php _e( 'No upcoming events!', 'kinola' ); ?>
    </div>
<?php endif; ?>
