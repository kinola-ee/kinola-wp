<?php
/**
 * This template is the output of [kinola_films] shortcode.
 * It displays a list of all films.
 */
?>

<?php if ( count( $films ) ): ?>
    <div class="kinola-films w-full max-w-screen-1xl flex flex-col sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-x-3 gap-y-12 !mt-10">
        <?php foreach ( $films as $film ): ?>
            <?php /* @var $film \Kinola\KinolaWp\Film */ ?>
            <a href="<?php echo get_permalink( $film->get_local_id() ); ?>" class="kinola-film">
                <div class="flex items-center justify-center w-full aspect-[1.34/1] bg-gray-100 overflow-hidden relative">
                    <img src="<?php echo $film->get_field( 'poster' ); ?>" class="w-full h-full object-center object-cover absolute inset-0 blur-3xl">
                    <img src="<?php echo $film->get_field( 'poster' ); ?>" class="max-w-full max-h-full object-center object-contain relative">
                </div>
                <div class="mt-3 text-base leading-6">
                    <div class="kinola-film-title text-neutral-900 text-2xl font-semibold">
                        <?php echo $film->get_field( 'title' ); ?>
                    </div>
                    <div>
                        <?php echo $film->get_field( 'runtime' ); ?> <?php _ex( 'min', 'minutes', 'kinola' ); ?>
                    </div>
                    <div>
                        <?php _e('Language', 'kinola'); ?>: <?php echo $film->get_field('languages'); ?>
                    </div>
                    <div>
                        <?php _e('Subtitles', 'kinola'); ?>: <?php echo $film->get_field('subtitles'); ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <?php _e( 'No films to display.', 'kinola' ); ?>
<?php endif; ?>
