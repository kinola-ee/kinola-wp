<?php /* @var $event \Kinola\KinolaWp\Event */ ?>
<table>
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
            <?php echo $event->get_field( 'venue' ); ?>
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
            <?php echo $event->get_field( 'program' ); ?>
        </td>
    </tr>
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
</table>
<?php if ( WP_DEBUG ): ?>
    <table style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 20px">
        <tr>
            <td colspan="2"><strong>Debug</strong></td>
        </tr>
        <tr>
            <td class="kinola-admin__table_field_name">
                <?php _ex( 'ID', 'Admin', 'kinola' ); ?>
            </td>
            <td class="kinola-admin__table_field_value">
                <?php echo $event->get_remote_id(); ?>
            </td>
        </tr>
        <tr>
            <td class="kinola-admin__table_field_name">
                <?php _ex( 'Film ID', 'Admin', 'kinola' ); ?>
            </td>
            <td class="kinola-admin__table_field_value">
                <?php echo $event->get_field( 'production', false )['id']; ?>
            </td>
        </tr>
    </table>
<?php endif; ?>
