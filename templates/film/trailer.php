<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<?php if ( $film->get_field( 'embeddable_video' ) || $film->get_field( 'video' ) ): ?>
    <section class="kinola-film-trailer">
        <?php if ( $film->get_field( 'embeddable_video' ) ): ?>
            <?php
            // The embed is an <iframe> (YouTube/Vimeo) from the Kinola API. wp_kses with an
            // iframe-only allowlist keeps the player working while stripping any other markup —
            // notably a <script> — that a tampered/compromised payload could try to inject.
            echo wp_kses( $film->get_field( 'embeddable_video' ), [
                'iframe' => [
                    'src'             => true,
                    'width'           => true,
                    'height'          => true,
                    'frameborder'     => true,
                    'allow'           => true,
                    'allowfullscreen' => true,
                    'title'           => true,
                    'loading'         => true,
                    'referrerpolicy'  => true,
                    'style'           => true,
                    'class'           => true,
                ],
            ] );
            ?>
        <?php else: ?>
            <a href="<?php echo esc_url( $film->get_field( 'video' ) ); ?>" target="_blank">
                <?php _e( 'Watch trailer', 'kinola' ); ?>
            </a>
        <?php endif; ?>
    </section>
<?php endif; ?>
