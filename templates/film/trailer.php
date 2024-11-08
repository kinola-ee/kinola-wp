<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<?php if ( $film->get_field( 'embeddable_video' ) || $film->get_field( 'video' ) ): ?>
    <section class="aspect-video overflow-hidden">
        <?php if ( $film->get_field( 'embeddable_video' ) ): ?>
            <?php echo $film->get_field( 'embeddable_video' ); ?>
        <?php else: ?>
            <a href="<?php echo $film->get_field( 'video' ); ?>" target="_blank">
                <?php _e( 'Watch trailer', 'kinola' ); ?>
            </a>
        <?php endif; ?>
    </section>
<?php endif; ?>
