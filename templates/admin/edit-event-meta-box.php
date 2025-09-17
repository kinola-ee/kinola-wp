<?php
/**
 * This template is rendered on the Edit Event admin page.
 * It displays event details.
 */
?>

<?php /* @var $event \Kinola\KinolaWp\Event */ ?>
<table>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Film', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <a href="<?php echo $event->get_film_url(); ?>" target="_blank">
                <?php echo $event->get_film()->get_title(); ?>
            </a>
        </td>
    </tr>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Date', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php echo date( 'd.m.Y', strtotime( $event->get_field( 'time' ) ) ); ?>
        </td>
    </tr>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Time', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php echo date( 'H:i', strtotime( $event->get_field( 'time' ) ) ); ?>
        </td>
    </tr>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Venue', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php echo $event->get_venue_name(); ?>
        </td>
    </tr>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Room', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php echo $event->get_field( 'room' ); ?>
        </td>
    </tr>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Program', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php echo $event->get_field( 'program' ) ?: '-'; ?>
        </td>
    </tr>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Event Type', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php
            $event_type = $event->get_field( 'event_type' ) ?: 'paid';
            $type_labels = [
                'paid' => __( 'Paid', 'kinola' ),
                'free_registered' => __( 'Free with Registration', 'kinola' ),
                'free_public' => __( 'Free Public', 'kinola' ),
            ];
            echo $type_labels[$event_type] ?? $event_type;
            ?>
        </td>
    </tr>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Visibility', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php
            $visibility = $event->get_visibility();
            $visibility_labels = [
                'public' => __( 'Public', 'kinola' ),
                'coming_soon' => __( 'Coming Soon', 'kinola' ),
                'hidden_link' => __( 'Hidden Link', 'kinola' ),
                'private' => __( 'Private', 'kinola' ),
            ];
            echo $visibility_labels[$visibility] ?? $visibility;
            ?>
        </td>
    </tr>
    <!--
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Languages', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php echo $event->get_field( 'languages' ); ?>
        </td>
    </tr>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Subtitles', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php echo $event->get_field( 'subtitles' ); ?>
        </td>
    </tr>
    -->
</table>
<?php if ( WP_DEBUG ): ?>
    <table style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 20px">
        <tr>
            <td colspan="2"><strong>Debug</strong></td>
        </tr>
        <tr>
            <td class="kinola-admin__table_field_name">
                <?php _ex( 'Event ID in Kinola', 'Admin', 'kinola' ); ?>
            </td>
            <td class="kinola-admin__table_field_value">
                <a href="<?php echo $event->get_api_url(); ?>" target="_blank">
                    <?php echo $event->get_remote_id(); ?>
                </a>
            </td>
        </tr>
        <tr>
            <td class="kinola-admin__table_field_name">
                <?php _ex( 'Film ID in Kinola', 'Admin', 'kinola' ); ?>
            </td>
            <td class="kinola-admin__table_field_value">
                <a href="<?php echo $event->get_film()->get_api_url(); ?>" target="_blank">
                    <?php echo $event->get_field( 'film_id' ); ?>
                </a>
            </td>
        </tr>
    </table>
<?php endif; ?>
