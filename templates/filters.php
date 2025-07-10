<?php
/**
 * This template displays the venue, date and time filters for events.
 */
?>

<div class="kinola-filters">
    <form
        class="js-kinola-filters-form" 
        <?php if ( $film_id ): ?> data-film="<?php echo $film_id; ?>" <?php endif; ?>
        method="GET"
        action=""
    >
        <input type="hidden" name="page_id" value="<?php echo get_the_ID(); ?>">
        <?php      
            $film_id 
                ? include_once __DIR__ . '/filters_film.php' 
                : include_once __DIR__ . '/filters_events.php'
        ?>
    </form>
</div>
