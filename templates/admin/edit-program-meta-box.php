<?php
/**
 * This template is rendered on the Edit Program admin page.
 * It displays program details.
 */
?>

<?php /* @var $program \Kinola\KinolaWp\Program */ ?>
<table>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Name', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php echo esc_html( $program->get_name() ) ?: '-'; ?>
        </td>
    </tr>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Description', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php
            $description = $program->get_description();
            if ( $description ) {
                echo wp_kses_post( wp_trim_words( $description, 50 ) );
            } else {
                echo '-';
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Upcoming Events', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php
            $events = $program->get_events();
            echo count( $events );
            if ( count( $events ) > 0 ) {
                echo ' ' . _ex( 'event(s)', 'Admin', 'kinola' );
            }
            ?>
        </td>
    </tr>
    <?php
    $custom_fields = $program->get_custom_fields();
    if ( $custom_fields && is_array( $custom_fields ) && count( $custom_fields ) > 0 ):
    ?>
    <tr>
        <td class="kinola-admin__table_field_name">
            <?php _ex( 'Custom Fields', 'Admin', 'kinola' ); ?>
        </td>
        <td class="kinola-admin__table_field_value">
            <?php
            foreach ( $custom_fields as $field_name => $field_data ) {
                if ( is_array( $field_data ) && isset( $field_data['label'] ) ) {
                    echo '<strong>' . esc_html( $field_data['label'] ) . ':</strong> ';
                    if ( isset( $field_data['value'] ) && $field_data['value'] ) {
                        if ( $field_data['type'] === 'image' && is_string( $field_data['value'] ) ) {
                            echo '<a href="' . esc_url( $field_data['value'] ) . '" target="_blank">' . _ex( 'View Image', 'Admin', 'kinola' ) . '</a>';
                        } else {
                            echo esc_html( $field_data['value'] );
                        }
                    } else {
                        echo '-';
                    }
                    echo '<br>';
                }
            }
            ?>
        </td>
    </tr>
    <?php endif; ?>
</table>

<br>
<div>
    <em>
        <?php _ex(
            'Program data is downloaded from Kinola web app. To change something, edit the program in Kinola and re-import.',
            'Admin',
            'kinola'
        ); ?>
    </em>
</div>

<?php if ( WP_DEBUG ): ?>
    <br>
    <hr><br>
    <table>
        <tr>
            <td colspan="2"><strong>Debug</strong></td>
        </tr>
        <tr>
            <td class="kinola-admin__table_field_name">
                <?php _ex( 'Program ID in Kinola', 'Admin', 'kinola' ); ?>
            </td>
            <td class="kinola-admin__table_field_value">
                <a href="<?php echo esc_url( $program->get_api_url() ); ?>" target="_blank">
                    <?php echo esc_html( $program->get_remote_id() ); ?>
                </a>
            </td>
        </tr>
    </table>
<?php endif; ?>
