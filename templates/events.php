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
            <div class="flex flex-col gap-y-4">
                <?php if ( $event->get_date() ): ?>
                    <h3 class="mb-0">
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
                            <!-- <div class="absolute top-2 inset-x-2">
                                <div class="px-3 py-1.5 bg-white text-primary80 text-base border border-primary20 rounded-md">
                                    Filmiaasta nädalalõpp
                                </div>
                            </div> -->
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-base leading-6 text-primary100">
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
                            <span class="text-primary100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin size-4">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </span>
                            <span><?php echo $event->get_venue_name(); ?></span>
                        </div>
                    <?php endif; ?>
                    <!-- <div class="flex items-center gap-x-1.5 mt-1.5 font-semibold">
                        <span class="text-accentI100">
                            <svg viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-4">
                                <path d="M6.89998 17.35V10.925L1.32498 14.15L0.224976 12.225L5.79998 8.99999L0.224976 5.79999L1.32498 3.87499L6.89998 7.09999V0.649994H9.09998V7.09999L14.675 3.87499L15.775 5.79999L10.2 8.99999L15.775 12.225L14.675 14.15L9.09998 10.925V17.35H6.89998Z" fill="#5F0CE7"/>
                            </svg>
                        </span>
                        <span>Külas režissöör Rain Tolk</span>
                    </div> -->
                </div>
                <div class="mt-auto flex justify-center">
                    <?php if ($event->get_free_seats()): ?>
                        <a class="px-12 py-3 rounded-full bg-primary100 text-sm text-white font-semibold tracking-wide uppercase hover:bg-accentI100 active:bg-accentI80 focus-visible:bg-primary80 focus-visible:outline focus-visible:outline-accentI40 focus-visible:outline-6 focus-visible:outline-offset-0 transition" href="<?php echo $event->get_checkout_url(); ?>">
                            <?php _e( 'Buy ticket', 'kinola' ); ?>
                        </a>
                    <?php else: ?>
                        <span class="text-xl font-semibold px-4">
                            <?php _e( 'Sold out', 'kinola' ); ?>
                        </span>
                    <?php endif; ?>
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
