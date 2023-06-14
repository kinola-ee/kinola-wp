<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<?php if ( $film->get_field( 'gallery' ) && count( $film->get_field( 'gallery' ) ) ): ?>
    <section class="kinola-film-gallery" id="kinola-film-gallery">
        <?php foreach ( $film->get_field( 'gallery' ) as $image ): ?>
            <a
                href="<?php echo $image['srcset']; ?>"
                data-pswp-width="<?php echo $image['width']; ?>"
                data-pswp-height="<?php echo $image['height']; ?>"
                target="_blank"
            >
                <img
                    src="<?php echo $image['thumbnail']; ?>"
                    alt="<?php echo $image['alt']; ?>"
                />
            </a>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<script type="module">
    import PhotoSwipeLightbox from '<?php echo \Kinola\KinolaWp\Helpers::get_assets_url('scripts/photoswipe/photoswipe-lightbox.esm.min.js') ?>';

    const lightbox = new PhotoSwipeLightbox({
        gallery: '#kinola-film-gallery',
        children: 'a',
        pswpModule: () => import('<?php echo \Kinola\KinolaWp\Helpers::get_assets_url('scripts/photoswipe/photoswipe.esm.min.js') ?>')
    });
    lightbox.init();
</script>
