<?php
/**
 * This template is the output of [kinola_events] shortcode.
 * It displays a list of all upcoming events along with venue, date and time filters.
 */
?>

<?php echo $rendered_filter; ?>
<?php if ( count( $events ) ): ?>
    <div class="w-full max-w-screen-1xl flex flex-col sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-x-3 gap-y-10 !mt-10">
        <?php foreach ( $events as $event ): ?>
            <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
            <div>
                <?php if ( $event->get_date() ): ?>
                    <h3>
                        <?php echo $event->get_date(); ?> - <?php echo $event->get_time(); ?>
                    </h3>
                <?php endif; ?>
                <div class="w-full aspect-4/3 overflow-hidden relative">
                    <?php if ( $event->get_field( 'production_poster' ) ): ?>
                        <div class="absolute -inset-4 bg-center blur-2xl">
                            <img class="w-full h-full object-cover object-center" src="<?php echo $event->get_field( 'production_poster' ); ?>" alt="<?php echo $event->get_field( 'production_title' ); ?>">
                        </div>
                        <div class="w-full h-full flex items-center justify-center relative backdrop-blur-xl bg-white/5">
                            <img class="w-full h-full object-center object-contain" src="<?php echo $event->get_field( 'production_poster' ); ?>" alt="<?php echo $event->get_field( 'production_title' ); ?>">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mt-4 text-base leading-6 text-neutral-900">
                    <?php if ( $event->get_field( 'production_title' ) ): ?>
                        <div class="text-1xl font-semibold mb-1">
                            <?php echo $event->get_field( 'production_title' ); ?>
                        </div>
                    <?php endif; ?>
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
                    <?php if ( $event->get_venue_name() ): ?>
                        <div class="flex items-center gap-x-1.5 mt-1.5 font-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin size-4">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span><?php echo $event->get_venue_name(); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="mt-3 flex justify-center">
                        <?php if ($event->get_free_seats()): ?>
                            <a class="px-12 py-3 rounded-full bg-neutral-900 text-sm text-white font-semibold tracking-wide uppercase hover:bg-accentI100 active:bg-accentI80 focus-visible:outline focus-visible:outline-accentI40 focus-visible:outline-6 focus-visible:outline-offset-0 transition" href="<?php echo $event->get_checkout_url(); ?>">
                                <?php _e( 'Buy ticket', 'kinola' ); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-xl font-semibold px-4">
                                <?php _e( 'Sold out', 'kinola' ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="text-center mt-10 lg:mt-28">
        <h2>
            <?php _e( 'No upcoming events.', 'kinola' ); ?>
        </h2>
    </div>
<?php endif; ?>
