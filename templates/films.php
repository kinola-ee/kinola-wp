<?php
/**
 * This template is the output of [kinola_films] shortcode.
 * It displays a list of all films.
 */
?>

<?php if ( count( $films ) ): ?>
    <div class="kinola-films">
        <?php foreach ( $films as $film ): ?>
            <?php /* @var $film \Kinola\KinolaWp\Film */ ?>
            <div class="kinola-film" style="padding: 10px 20px; border: 1px solid #ccc; overflow: auto;">
                <a href="<?php echo get_permalink( $film->get_local_id() ); ?>">
                    <img src="<?php echo $film->get_field( 'poster' ); ?>" width="100px" height="150px"
                         style="float: left;"/>
                </a>
                <div class="kinola-film-details" style="float:left; margin-left: 20px;">
                    <p>
                        <strong>
                            <a class="kinola-film-title" href="<?php echo get_permalink( $film->get_local_id() ); ?>">
                                <?php echo $film->get_field( 'title' ); ?>
                            </a>
                        </strong>
                        <br>
                        <span class="kinola-film-title-original">
					        <?php echo $film->get_field( 'title_original' ); ?>
                        </span>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <?php _e( 'No films to display.', 'kinola' ); ?>
<?php endif; ?>
