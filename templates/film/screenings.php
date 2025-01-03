<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<section class="kinola-film-screenings">
    <?php if ( count( $events ) ): ?>
        <h3><?php echo __( 'Screenings', 'kinola' ); ?></h3>
        <?php echo $rendered_filter; ?>
        <div class="mt-6">
            <?php foreach ( $events as $event ): ?>
                <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
                <div class="py-4 border-t border-primary20 flex items-center gap-4">
                    <div class="flex flex-col gap-y-2">
                        <!-- <div class="px-3 py-1.5 bg-white text-primary80 text-base border border-primary20 rounded-md">
                            Filmiaasta nädalalõpp
                        </div> -->
                        <div class="font-semibold">
                            <?php echo $event->get_date(); ?> - <?php echo $event->get_time(); ?>
                        </div>
                        <div class="flex items-center gap-x-1.5">
                            <span class="text-primary100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin size-4">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </span>
                            <span><?php echo $event->get_venue_name(); ?></span>
                        </div>
                        <!-- <div class="flex items-center gap-x-1.5">
                            <span class="text-accentI100">
                                <svg viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-4">
                                    <path d="M6.89998 17.35V10.925L1.32498 14.15L0.224976 12.225L5.79998 8.99999L0.224976 5.79999L1.32498 3.87499L6.89998 7.09999V0.649994H9.09998V7.09999L14.675 3.87499L15.775 5.79999L10.2 8.99999L15.775 12.225L14.675 14.15L9.09998 10.925V17.35H6.89998Z" fill="#5F0CE7"/>
                                </svg>
                            </span>
                            <span>Külas režissöör Rain Tolk</span>
                        </div> -->
                    </div>
                    <?php if ($event->get_free_seats()): ?>
                        <div class="ml-auto">
                            <a class="px-4 py-2 rounded-full bg-primary100 text-sm text-white font-semibold tracking-wide uppercase hover:bg-accentI100 active:bg-accentI80 focus-visible:bg-primary80 focus-visible:outline focus-visible:outline-accentI40 focus-visible:outline-4 focus-visible:outline-offset-0 transition" href="<?php echo $event->get_checkout_url(); ?>">
                                <?php _e( 'Buy ticket', 'kinola' ); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="ml-auto">
                            <span class="text-xl font-semibold px-4">
                                <?php _e( 'Sold out', 'kinola' ); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center">
            <h3>
                <?php _e( 'No upcoming screenings', 'kinola' ); ?>
            </h3>
        </div>
    <?php endif; ?>
</section>
