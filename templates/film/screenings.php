<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<section class="kinola-film-screenings">
    <?php if ( count( $events ) ): ?>
        <h3><?php echo __( 'Screenings', 'kinola' ); ?></h3>
        <?php echo $rendered_filter; ?>
        <div class="mt-6">
            <?php foreach ( $events as $event ): ?>
                <?php /* @var $event \Kinola\KinolaWp\Event */ ?>
                <div class="py-4 border-t border-stone-300 flex items-center gap-4">
                    <div class="flex flex-col gap-y-1">
                        <div class="font-semibold">
                            <?php echo $event->get_date(); ?> - <?php echo $event->get_time(); ?>
                        </div>
                        <div class="flex items-center gap-x-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin size-4">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span><?php echo $event->get_venue_name(); ?></span>
                        </div>
                    </div>
                    <?php if ($event->get_free_seats()): ?>
                        <div class="ml-auto">
                            <a class="px-4 py-2 rounded-full bg-neutral-900 text-sm text-white font-semibold tracking-wide uppercase hover:bg-accentI100 active:bg-accentI80 focus-visible:outline focus-visible:outline-accentI40 focus-visible:outline-4 focus-visible:outline-offset-0 transition" href="<?php echo $event->get_checkout_url(); ?>">
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
