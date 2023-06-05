<?php
/**
 * This template is the output of [kinola_events] shortcode.
 * It displays a list of all upcoming events along with location and date filters.
 */
?>

<?php echo $filter; ?>
<?php if ( count( $events ) ): ?>
    <?php foreach ( $events as $event ): ?>
        <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
        <div style="padding: 10px 20px; border: 1px solid #ccc; overflow: auto;">
            <img src="<?php echo $event->get_field( 'production_poster' ); ?>" width="100px" height="150px"
                 style="float: left;"/>
            <div style="float:left; margin-left: 20px;">
                <p>
                    <strong>
                        <?php echo $event->get_field( 'production_title' ); ?>
                    </strong>
                    <br>
                    <?php echo $event->get_date() ?> <?php echo $event->get_time(); ?>
                </p>
                <p>
                    <a href="<?php echo $event->get_checkout_url(); ?>">
                        <?php _e( 'Buy ticket', 'kinola' ); ?>
                    </a>
                </p>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div>
        <?php _e( 'No upcoming events!', 'kinola' ); ?>
    </div>
<?php endif; ?>
