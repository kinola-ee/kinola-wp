<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<?php if ( $film->get_field( 'embeddable_video' ) || $film->get_field( 'video' ) ): ?>
    <div>
        <?php if ( $film->get_field( 'embeddable_video' ) ): ?>
            <div class="kinola-film-trailer-wrap">
                <?php echo $film->get_field( 'embeddable_video' ); ?>
            </div>
        <?php else: ?>
            <a href="<?php echo $film->get_field( 'video' ); ?>" target="_blank" class="kinola-btn">
                <?php _e( 'Watch trailer', 'kinola' ); ?>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
