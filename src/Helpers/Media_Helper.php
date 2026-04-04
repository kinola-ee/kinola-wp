<?php
  /**
 * Media Helper Class
 *
 * Handles importing external images (single or batch) to WordPress media library
 * with automatic size generation, duplicate prevention, post attachment management,
 * and metadata handling (alt text, titles).
 *
 * @package Kinola
 */

namespace Kinola\KinolaWp\Helpers;

/**
 * Class Media_Helper
 */
class Media_Helper {

	/**
	 * Import an external image to WordPress media library.
	 *
	 * Downloads the image, creates attachment post, generates thumbnails,
	 * and prevents duplicate downloads by checking source URL.
	 *
	 * @param string $image_url The external image URL to import.
	 * @param int    $post_id   Post ID to attach the image to (0 for no parent).
	 * @param string $title     Optional. Title for the attachment. Default empty.
	 * @param string $alt_text  Optional. Alt text for the image. Default empty.
	 *
	 * @return int|null Attachment ID on success, null on failure.
	 */
	public static function import_image_to_media_library( $image_url, $post_id = 0, $title = '', $alt_text = '' ) {
		// Validate URL.
		if ( empty( $image_url ) || ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			self::log_error( 'Invalid image URL provided: ' . $image_url );
			return null;
		}

		// Check if image already exists in media library.
		$existing_attachment_id = self::get_attachment_id_by_source_url( $image_url );
		if ( $existing_attachment_id ) {
			return $existing_attachment_id;
		}

		// Ensure required WordPress functions are loaded.
		self::require_wordpress_media_functions();

		// Download and import the image.
		$attachment_id = media_sideload_image( $image_url, $post_id, $title, 'id' );

		// Handle errors.
		if ( is_wp_error( $attachment_id ) ) {
			self::log_error(
				'Failed to import image from URL: ' . $image_url .
				' | Error: ' . $attachment_id->get_error_message()
			);
			return null;
		}

		// Set alt text if provided.
		if ( ! empty( $alt_text ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt_text ) );
		}

		// Store the source URL for future duplicate checking.
		// Note: media_sideload_image() automatically stores this in _source_url since WP 5.4,
		// but we ensure it's set for older versions.
		if ( ! get_post_meta( $attachment_id, '_source_url', true ) ) {
			update_post_meta( $attachment_id, '_source_url', esc_url_raw( $image_url ) );
		}

		return $attachment_id;
	}

	/**
	 * Import an array of images (e.g., gallery images).
	 *
	 * @param array $images  Array of image data. Each item should have 'src' key.
	 *                       Optional keys: 'alt', 'title'.
	 * @param int   $post_id Post ID to attach images to.
	 *
	 * @return array Array of attachment IDs indexed by original array keys.
	 *               Returns empty array element if import failed for that image.
	 */
	public static function import_images_array( $images, $post_id = 0 ) {
		$attachment_ids = array();

		if ( ! is_array( $images ) || empty( $images ) ) {
			return $attachment_ids;
		}

		foreach ( $images as $key => $image ) {
			// Handle both simple URL strings and arrays with 'src' key.
			if ( is_string( $image ) ) {
				$image_url = $image;
				$alt_text  = '';
				$title     = '';
			} elseif ( is_array( $image ) && ! empty( $image['src'] ) ) {
				$image_url = $image['src'];
				$alt_text  = $image['alt'] ?? '';
				$title     = $image['title'] ?? '';
			} else {
				// Invalid format, skip this image.
				$attachment_ids[ $key ] = null;
				continue;
			}

			// Import the image.
			$attachment_id = self::import_image_to_media_library(
				$image_url,
				$post_id,
				$title,
				$alt_text
			);

			$attachment_ids[ $key ] = $attachment_id;
		}

		return $attachment_ids;
	}

	/**
	 * Get attachment ID by source URL.
	 *
	 * Checks if an image with the given source URL already exists
	 * in the media library to prevent duplicate downloads.
	 *
	 * @param string $source_url The original external image URL.
	 *
	 * @return int|null Attachment ID if found, null otherwise.
	 */
	public static function get_attachment_id_by_source_url( $source_url ) {
		if ( empty( $source_url ) ) {
			return null;
		}

		// Query for attachments with matching _source_url meta.
		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => '_source_url',
						'value'   => esc_url_raw( $source_url ),
						'compare' => '=',
					),
				),
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $attachments ) ) {
			return $attachments[0];
		}

		return null;
	}

	/**
	 * Require WordPress media functions.
	 *
	 * Ensures the necessary WordPress admin files are loaded
	 * for media handling functions.
	 */
	private static function require_wordpress_media_functions() {
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
	}

	/**
	 * Log error message.
	 *
	 * Logs errors to WordPress debug log if WP_DEBUG_LOG is enabled.
	 *
	 * @param string $message Error message to log.
	 */
	private static function log_error( $message ) {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[Kinola WP Plugin] ' . $message );
		}
	}
}
