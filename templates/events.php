<?php
/**
 * This template is the output of [kinola_events] shortcode.
 * It displays a list of all upcoming events along with venue, date and time filters.
 */
?>

<h1><?php the_title(); ?></h1>

<?php echo $rendered_filter; ?>

<?php if ( count( $events ) ): ?>
    <div class="kinola-items">
        <?php foreach ( $events as $event ): ?>
            <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
            <div class="kinola-item">
                <a href="<?php echo $event->get_film_url() ?>" class="kinola-item-link">
                    <?php if ( $event->get_date() ): ?>
                        <h3>
                            <?php echo $event->get_date(); ?> - <?php echo $event->get_time(); ?>
                        </h3>
                    <?php endif; ?>
                    <div class="kinola-item-photo-wrapper">
                        <?php if ( $event->get_field( 'production_poster' ) ): ?>
                            <div class="kinola-item-photo-bg">
                                <img class="kinola-item-photo-bg-img" src="<?php echo $event->get_field( 'production_poster' ); ?>" alt="<?php echo $event->get_field( 'production_title' ); ?>">
                            </div>
                            <div class="kinola-item-photo">
                                <img class="kinola-item-photo-img" src="<?php echo $event->get_field( 'production_poster' ); ?>" alt="<?php echo $event->get_field( 'production_title' ); ?>">
                            </div>
                        <?php else: ?>
                            <div class="kinola-item-photo-placeholder"></div>
                        <?php endif; ?>
                        <?php if ($event->has_program()): ?>
                            <div class="kinola-item-program">
                                <div class="kinola-item-program-name">
                                    <?php echo $event->get_program_name(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="kinola-item-info">
                        <?php if ( $event->get_field( 'production_title' ) ): ?>
                            <h4>
                                <?php echo $event->get_field( 'production_title' ); ?>
                            </h4>
                        <?php endif; ?>
                        <div>
                            <?php if ( $event->get_film()->get_field( 'runtime' ) ): ?>
                                <div>
                                    <?php echo $event->get_film()->get_field( 'runtime' ); ?> <?php _ex( 'min', 'minutes', 'kinola' ); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( $event->get_film()->get_field( 'languages' ) ): ?>
                                <div>
                                    <?php _e('Language', 'kinola'); ?>: <?php echo $event->get_film()->get_field( 'languages' ); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( $event->get_film()->get_field( 'subtitles' ) ): ?>
                                <div>
                                    <?php _e('Subtitles', 'kinola'); ?>: <?php echo $event->get_film()->get_field( 'subtitles' ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ( $event->get_venue_name() ): ?>
                            <div class="kinola-item-venue">
                                <?php echo $event->get_venue_name(); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($event->get_note()): ?>
                            <div class="kinola-item-note">
                                <?php echo $event->get_note(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
                <div class="kinola-item-tickets">
                    <?php if ($event->get_free_seats()): ?>
                        <a class="kinola-btn" href="<?php echo $event->get_checkout_url(); ?>">
                            <?php _e( 'Buy ticket', 'kinola' ); ?>
                        </a>
                    <?php else: ?>
                        <span class="kinola-item-soldout">
                            <?php _e( 'Sold out', 'kinola' ); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="kinola-items-none">
        <h2><?php _e( 'No upcoming events.', 'kinola' ); ?></h2>
    </div>
<?php endif; ?>
