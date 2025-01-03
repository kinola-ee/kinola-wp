<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<?php if ( $film->get_field( 'embeddable_video' ) || $film->get_field( 'video' ) ): ?>
    <section class="">
        <?php if ( $film->get_field( 'embeddable_video' ) ): ?>
            <div class="aspect-video overflow-hidden">
                <?php echo $film->get_field( 'embeddable_video' ); ?>
            </div>
        <?php else: ?>
            <a href="<?php echo $film->get_field( 'video' ); ?>" target="_blank" class="px-12 py-3 rounded-full bg-primary100 text-sm text-white font-semibold tracking-wide uppercase hover:bg-accentI100 active:bg-accentI80 focus-visible:bg-primary80 focus-visible:outline focus-visible:outline-accentI40 focus-visible:outline-6 focus-visible:outline-offset-0 transition">
                <?php _e( 'Watch trailer', 'kinola' ); ?>
            </a>
        <?php endif; ?>
    </section>
<?php endif; ?>
