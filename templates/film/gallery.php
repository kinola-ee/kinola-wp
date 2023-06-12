<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<?php if ( $film->get_field( 'gallery' ) && count( $film->get_field( 'gallery' ) ) ): ?>
    <section class="kinola-film-gallery">
        <?php foreach ( $film->get_field( 'gallery' ) as $image ): ?>
            <a href="<?php echo $image['srcset']; ?>" target="_blank">
                <img src="<?php echo $image['thumbnail']; ?>" alt="<?php echo $image['alt']; ?>"/>
            </a>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
