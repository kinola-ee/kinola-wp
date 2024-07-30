# Kinola WordPress plugin
This plugin integrates your WordPress site with [Kinola](https://kinola.ee) web app.
It imports films and events from Kinola, displays them on your site and allows customers to buy tickets to events.

## Getting started
### Requirements
- PHP >=7.4
- WP >= 6.0

### Setup
1. In your wp-config.php, add the following constant:
`define( 'KINOLA_URL', 'https://your-cinema.kinola.ee' );`

Make sure you replace `your-cinema` with the name of your cinema. It should be the same as in the URL you're using to access Kinola.

2. Create a Page for Films. In the content, add the following shortcode:
`[kinola_films]`

3. Create a Page for Events. In the content, add the following shortcode:
`[kinola_events]`

4. Ensure your WP site's language, time and date formats and time zones are set properly.

5. Flush permalinks: Go to Settings > Permalinks and click "Save."

6. (Recommended) In your wp-config.php, add link to terms and conditions:
`define( 'KINOLA_TERMS_LINK', 'https://[YOUR_URL_HERE]' );`

7. (Optional) In your wp-config.php, add constant to display newsletter checkbox in checkout:
`define( 'KINOLA_SHOW_NEWSLETTER_CHECKBOX', true );`

8. (Optional) In your wp-config.php, add constant to change default newsletter checked status in checkout:
`define( 'KINOLA_NEWSLETTER_CHECKED_BY_DEFAULT', true );`

### Using the plugin
The plugin creates two menu items in the admin menu - Films and Events. New Film or Event posts cannot be created or edited via WordPress - they must be imported
from Kinola instead. This way, there is a single source of truth for both. If you need to make any changes to an already imported Film or Event, the changes must be made inside Kinola and then re-imported to WordPress.

Events are imported automatically every 15 minutes using WP's own task scheduling system. Please note that only future events
are imported; past events are ignored.

Films are imported automatically in two situations:
1. Every time an event which doesn't already have an existing film in WP is imported,
2. Every day, all films will be imported/updated which have been changed in Kinola in the last 48 hours.

Films can also be imported manually via Films > Import and also from the individual film's admin page.

### Shortcodes
`[kinola_films]`
Outputs a list of all films.

`[kinola_events]`
Outputs a list of all upcoming events as well as the events filter.

To control the number of events displayed, use the `limit` attribute in the shortcode. Examples: `limit="10"` or `limit="all"`. Default: `25`.

To show only today's events, use `show_dates` attribute in the shortcode with the value set to `today`. Example: `show_dates="today"`.

`[kinola_film_screenings film="YOUR_FILM_ID"]`
Outputs all upcoming screenings of the film with the given WordPress post ID, as well as the events filter (you can find the post ID of a film by opening up the edit view of that film in WordPress and checking your browser's address bar).

To show only today's events, use the following attribute in the shortcode: `show_dates="today"`

## Debugging
If you run into problems, follow these steps:
1. Ensure you've followed *all* steps outlined above under the big heading that says "Setup"
2. Double-check your WP site's language, time and date format and time zone settings.
3. In wp-config.php, add the following:
`define( 'WP_DEBUG_LOG', true );`
`define( 'KINOLA_DEBUG_LOG', true );`

NB! KINOLA_DEBUG_LOG will log a _ton_ of information in your debug.log file. **Remove it after debugging is completed.**

4. Delete all events and films from WP. Empty both trashes. Run import again.
5. Send an email to andres at elektriteater.ee, describe the problem and attach the log file.

## Technical stuff
The plugin creates two custom post types - `production` and `event`. `create_posts` capability on these posts has been disabled.

The plugin overrides content on Production single post templates using `the_content` filter.

Checkout and payments are handled by a custom React component in `checkout.php` template which is rendered via a custom endpoint.
The component communicates with Kinola to book seats and process payments, so technically, all payment-related functionality
is handled by Kinola itself (and therefore cannot be customized).

### Customization
The plugin is - or _should be_ - fully customizable. If there is something you'd like to change, but cannot do that:
- open an issue
- or make a pull request (see details below)
- or contact us (see details below)

### Templates
All templates used by the plugin are overrideable. To do so, create a folder called `kinola` in your theme and simply copy-paste
the template you wish to override from the plugin `templates` folder. Follow the same folder structure as in the plugin's `templates` folder.

There are 4 main templates of interest:
* `films.php` - displays a list of all films
* `film.php` - displays a single film data
* `events.php` - displays a list of upcoming events along with a venue, date and time filter
* `filters.php` - displays the venue, date and time filters

### Development
All event and film data is stored as postmeta.
The `films`, `film` and `events` templates are all passed the corresponding instance(s) of `\Kinola\KinolaWp\Event` or `\Kinola\KinolaWp\Film` objects.
You can use the `get_fields()` function of either of those classes to get all data that has been saved from the API, e.g.
`$event->get_fields()`

The Event and Film classes contain a number of useful public functions. Some examples:

```php
<?php

use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Event;

// Get a film by its WP Post ID
$film = Film::find_by_local_id( $post_id );

// Or get a film by its Kinola ID instead
$film = Film::find_by_remote_id( 'c8f92b84-cd6f-4c09-a0a9-176d201e2c91' );

// Both Film and Event classes extend the \Kinola\KinolaWp\Model class which provides some useful functions, for example:

// Get a field from post meta:
$poster = $film->get_field( 'poster' );

// Get ALL fields from post meta:
$fields = $film->get_fields();

// Get the WP Post object of the Film:
$film_post = $film->get_post();

// If you have configured custom frields for film objekt in Kinola admin
// you can access them from Film object:
$custom_fields = $film->get_custom_fields()

// There's more in the Model class.

// Get all upcoming screenings of a film:
$events = $film->get_events(); // This returns an array of Event objects.

// You can get an Event the same way as with Films:
$event = Event::find_by_local_id( $post_id );
$event = Event::find_by_remote_id( 'c8f92b84-cd6f-4c09-a0a9-176d201e2c91' );

// Get the title of an event:
$title = $event->get_title(); // Uses WP Post's title

// Get the URL to buy a ticket:
$url = $event->get_checkout_url();

// Working with dates and times:
$date = $event->get_date()  // Uses the format defined in WP and also corrects the date according to the locally defined time zone
$time = $event->get_time(); // Same as above

// Keep in mind that the datetime is stored in the database in UTC time zone.
// So if you get the `time` field directly from database using `get_field()`, it's UTC.
// If you need to display an UTC date or time in your locally defined time zone, you can use a helper function:
$utc_event_time = $event->get_field( 'time' );
$formatted_date_in_your_timezone = \Kinola\KinolaWp\Helpers::format_datetime( $utc_event_time );

```

### Actions
`kinola/checkout/before_content`
Use this action to display your own custom content before the contents of the Checkout page.

`kinola/checkout/after_content`
Use this action to display your own custom content after the contents of the Checkout page.

### Filters
`kinola/language`
This filter allows you to set the site language. Use a 2-letter ISO language code, e.g. 'en' or 'et'.

`kinola/assets/css`
Use this filter to disable loading Kinola CSS.

`kinola/assets/photoswipe`
Use this filter to disable loading PhotoSwipe styles on single film page.

`kinola/assets/select2`
Use this filter to disable loading select2 styles and scripts in case the theme already loads it.

`kinola/checkout/show_title`
This filter can be used to control whether or not the default title is displayed on Checkout page.

`kinola/post_type/film`
This filter allows you to modify the name of the Film post type.

`kinola/post_type/film/supports`
This filter allows you to modify the `supports` parameter of Film post type.

`kinola/post_type/event`
This filter allows you to modify the name of the Event post type.

`kinola/post_type/event/supports`
This filter allows you to modify the `supports` parameter of Event post type.

`kinola/checkout/slug`
This filter allows you to modify the URL slug of the checkout page.

`kinola/template_directories`
This filter allows you to modify which folders are used to look for Kinola templates.

`kinola/template`
This filter runs every time a Kinola template is loaded. Using it, you can completely customize which templates are loaded and from where.

### Kinola API
The plugin uses Kinola public API for fetching film and event data.
Should you need to implement something that's not provided by the plugin, you can find the API docs here: https://YOUR_KINOLA_URL/api/public/v1/documentation

### Advanced
Technically, further customization (modifying/removing the plugin's own actions, for example) is possible
using the globals defined in `kinola.php` - for example:
```php
remove_action( 'init', [ $GLOBALS['KINOLA_ADMIN'], 'register_films_post_type' ], 1 );
add_action( 'init', 'register_your_own_films_post_type' );
```

**However**, please keep in mind that any changes you make like this might easily break after updates to the plugin source.
If you need to change something this way, instead consider making a pull request to add filters to the relevant parts of the code base.

### Translations
The plugin is fully translatable - a translation template is located in `translations` folder.
If you translate the plugin to your language, please make a pull request and share the translation po file!

Note that many translatable strings have context "Admin" - these are strings that the end user will never see,
so you may not want spend time on translating them.

## Contact
andres at elektriteater.ee

## Contributing
Please follow the WordPress coding standards.
