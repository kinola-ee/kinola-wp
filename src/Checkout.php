<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Api\Kinola_Api;

class Checkout {
    public static function get_title(): string {
        return get_bloginfo();
    }

    public static function get_plugin_api_base_url(): string {
        return Kinola_Api::get_plugin_api_base_url();
    }

    public static function get_kinola_js_url(): string {
        return 'https://plugin.kinola.ee/index.js';
    }

    public static function get_event_id() {
        global $wp_query;

        return $wp_query->query_vars[ Helpers::get_checkout_url_slug() ];
    }

    public static function get_strings(): array {
        return [
            'timer_label' => __('Session expires in:', 'kinola'),
            'session_expired' => __('Session expired', 'kinola'),
            'floorplan_screen' => __('Screen', 'kinola'),
            'floorplan_entry' => __('entrance', 'kinola'),
            'floorplan_stage' => __('stage', 'kinola'),
            'floorplan_balcony' => __('balcony', 'kinola'),
            'floorplan_sideBalcony' => __('side balcony', 'kinola'),
            'floorplan_free_seat' => __('free seat', 'kinola'),
            'floorplan_booked_seat' => __('booked seat', 'kinola'),
            'floorplan_taken_seat' => __('taken seat', 'kinola'),
            'floorplan_disabled_seat' => __('buffer seat', 'kinola'),
            'floorplan_limited_visibility_seat' => __('seat with limited visibility', 'kinola'),
            'intro_freeSeats' => __('Free seats', 'kinola'),
            'tickets_title' => __('Choose tickets', 'kinola'),
            'tickets_serialTicket' => __('Serial ticket {number}', 'kinola'),
            'tickets_giftCard' => __('Gift card {number}', 'kinola'),
            'tickets_amountLeft' => __('Amount left', 'kinola'),
            'tickets_max' => __('Sorry, the maximum number of tickets is limited', 'kinola'),
            'tickets_total' => __('Total', 'kinola'),
            'products_title' => __('Selected products', 'kinola'),
            'cart_title' => __('Shopping basket', 'kinola'),
            'cards_title' => __('Client card and gift cards', 'kinola'),
            'cards_label' => __('Client card or gift card number', 'kinola'),
            'cards_button' => __('Confirm number', 'kinola'),
            'cards_notFound' => __('We couldn\'t find a card with this number', 'kinola'),
            'cards_alreadyAdded' => __('This card has already been added to this session', 'kinola'),
            'cards_notApplicable' => __('This discount does not apply to this order', 'kinola'),
            'contact_title' => __('Contact', 'kinola'),
            'contact_description' => __('We\'ll use your contact info to e-mail you your tickets and contact you in the case of unexpected changes to the screening.',
                'kinola'),
            'contact_name' => __('name', 'kinola'),
            'contact_name_invalid' => __('Please enter your name', 'kinola'),
            'contact_email' => __('e-mail', 'kinola'),
            'contact_email_invalid' => __('Please enter a valid e-mail address', 'kinola'),
            'contact_email_card_mismatch' => __('The e-mail address you entered does not match the e-mail address on your card',
                'kinola'),
            'contact_phone' => __('phone', 'kinola'),
            'contact_newsletter' => __('Sign up to our newsletter', 'kinola'),
            'terms' => __('By buying I agree with terms and conditions.', 'kinola'),
            'banks_title' => __('Choose payment', 'kinola'),
            'banks_paymentFailed' => __('Your payment was not successful. Please try again.', 'kinola'),
            'banks_required' => __('Please choose your payment method.', 'kinola'),
            'error_title' => __('Something went wrong!', 'kinola'),
            'submit_register' => __('Register', 'kinola'),
            'submit_pay' => __('Pay', 'kinola'),
            'schedule' => __('Schedule', 'kinola'),
            'completed_title' => __('Purchase successful!', 'kinola'),
            'completed_startNew' => __('Buy more tickets', 'kinola'),
            'soldOut_title' => __('Sold out', 'kinola'),
            'soldOut_text' => __('Sorry, all tickets have been sold out.', 'kinola'),
            'temporarilyUnavailable_title' => __('Sorry, tickets are temporarily unavailable.', 'kinola'),
            'pending_title' => __('Payment pending', 'kinola'),
            'pending_text' => __('Please complete your payment.', 'kinola'),
            'sale_expired' => __('Ticket sale has ended.', 'kinola'),
            'giftCard_amountPresets_text' => __('Please choose how many cinema visits can be made with this gift card.',
                'kinola'),
            'giftCard_oneTicket' => __('One ticket (€ {price})', 'kinola'),
            'giftCard_twoTickets' => __('Two tickets (€ {price})', 'kinola'),
            'giftCard_sixTickets' => __('Six tickets (€ {price})', 'kinola'),
            'giftCard_tenTickets' => __('10 tickets (€ {price})', 'kinola'),
            'giftCard_customAmount' => __('Or enter a custom amount.', 'kinola'),
            'giftCard_amount_invalid' => __('Please enter a valid amount', 'kinola'),
            'giftCard_amount_min' => __('The gift card amount must be at least {min} €. Please choose a larger amount.',
                'kinola'),
            'giftCard_amount_max' => __('Unfortunately, you cannot buy such an expensive gift card through the website. If you wish, please contact the cinema and we will find a solution.',
                'kinola'),
            'giftCard_email_label' => __('Your email', 'kinola'),
            'giftCard_completed_title' => __('Gift card purchased!', 'kinola'),
            'giftCard_code' => __('Your gift card code is:', 'kinola'),
            'giftCard_usage' => __('You can use it both on the cinema website and at the cashier.', 'kinola'),
            'serialTicket_type_label' => __('Serial ticket type', 'kinola'),
            'serialTicket_type_uses' => __('{count} uses', 'kinola'),
            'serialTicket_type_expires' => __('Valid until {date}', 'kinola'),
            'serialTicket_email_label' => __('Your email', 'kinola'),
            'serialTicket_completed_title' => __('Serial ticket purchased!', 'kinola'),
            'serialTicket_code' => __('Your serial ticket code is:', 'kinola'),
            'serialTicket_usage' => __('You can use it both on the cinema website and at the cashier.', 'kinola'),
            'progressBar_tickets' => __('Tickets', 'kinola'),
            'progressBar_products' => __('Products', 'kinola'),
            'progressBar_summary' => __('Confirm and pay', 'kinola'),
            'chooseProducts' => __('Choose products and pay', 'kinola'),
            'completePurchase' => __('Complete purchase and pay', 'kinola'),
            'back' => __('Back', 'kinola'),
            'allProducts' => __('All products', 'kinola'),
            'searchProduct' => __('Search for a product...', 'kinola'),
            'uncategorizedProducts' => __('Other products', 'kinola'),
            'myTickets' => __('My tickets', 'kinola'),
            'change' => __('Change', 'kinola'),
            'products_noResults' => __('No results found for your search.', 'kinola'),
            'products_tryAnotherSearch' => __('Try another search term or', 'kinola'),
            'products_removeFilters' => __('remove filters', 'kinola'),
            'products_disabled' => __('Ordering products is currently disabled.', 'kinola'),
            'products_total' => __('Total', 'kinola'),
            'products_sidebar_title' => __('Add products', 'kinola'),
            'products_sidebar_firstParagraph' => __('Order food and drinks to your seat!', 'kinola'),
            'products_sidebar_textBeforeLink' => __('If you haven\'t bought a ticket yet, check out our ', 'kinola'),
            'products_sidebar_linkText' => __('schedule', 'kinola'),
            'products_sidebar_textAfterLink' => __('.', 'kinola'),
            'eventSelection_chooseEventTitle' => __('Choose the event you bought a ticket for', 'kinola'),
            'eventSelection_chooseSeatTitle' => __('Choose the seat you bought a ticket for', 'kinola'),
            'eventSelection_chooseProduction' => __('Choose the movie', 'kinola'),
            'eventSelection_chooseEvent' => __('Choose the screening', 'kinola'),
            'eventSelection_chooseRow' => __('Choose the row', 'kinola'),
            'eventSelection_chooseSeat' => __('Choose the seat', 'kinola'),
            'eventSelection_noNumberedSeats' => __('This screening does not have numbered seats. The ordered products will be associated with the buyer\'s name.',
                'kinola'),
            'row' => __('Row', 'kinola'),
            'seat' => __('Seat', 'kinola'),
            'cartEmpty' => __('Your cart is empty. Add some products to your cart to place an order.', 'kinola'),
            'tickets_text' => __('You can ask for your tickets from the cashier.', 'kinola'),
            'completed_downloadNote' => __('You can also download your tickets here:', 'kinola'),
            'completed_downloadButton' => __('Download tickets', 'kinola'),
        ];
    }
}
