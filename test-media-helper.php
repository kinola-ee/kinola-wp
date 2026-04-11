<?php
/**
 * Test script for Media_Helper class
 *
 * This script tests the Media_Helper functionality for importing images
 * from external URLs to the WordPress media library.
 *
 * Usage via WP-CLI (recommended):
 *   wp eval-file test-media-helper.php
 *
 * Or via browser (place in WordPress root):
 *   - Move this file to WordPress root directory
 *   - Access via: http://yourdomain.com/test-media-helper.php
 *   - Make sure you're logged in as admin
 *
 * @package Kinola
 */

// If running via browser, load WordPress
if ( ! defined( 'ABSPATH' ) ) {
	// Adjust this path if needed - points to wp-load.php
	require_once __DIR__ . '/wp-load.php';

	// Security check - only allow admins to run this
	if ( ! current_user_can( 'manage_options' ) ) {
		die( 'Access denied. Admin privileges required.' );
	}

	echo '<pre>';
}

use Kinola\KinolaWp\Helpers\Media_Helper;

echo "=================================================\n";
echo "Media_Helper Test Script\n";
echo "=================================================\n\n";

// Test 1: Import a single image
echo "TEST 1: Import single image\n";
echo "-------------------------------------------------\n";

$test_image_url = 'https://myyri.kinola.ee/storage/myyri.kinola.ee/2920/project-hail-mary_poster.jpg';
echo "Image URL: {$test_image_url}\n";

$attachment_id = Media_Helper::import_image_to_media_library(
	$test_image_url,
	0, // No parent post
	'Test Image from Lorem Picsum',
	'A random test image for media import'
);

if ( $attachment_id ) {
	echo "✓ SUCCESS! Image imported.\n";
	echo "  Attachment ID: {$attachment_id}\n";
	echo "  Full size URL: " . wp_get_attachment_url( $attachment_id ) . "\n";

	// Show generated sizes
	$metadata = wp_get_attachment_metadata( $attachment_id );
	if ( ! empty( $metadata['sizes'] ) ) {
		echo "  Generated sizes:\n";
		foreach ( $metadata['sizes'] as $size_name => $size_data ) {
			echo "    - {$size_name}: {$size_data['width']}x{$size_data['height']}\n";
		}
	}

	echo "  Edit URL: " . admin_url( "post.php?post={$attachment_id}&action=edit" ) . "\n";
} else {
	echo "✗ FAILED to import image\n";
}

echo "\n";

// Test 2: Test duplicate prevention
echo "TEST 2: Duplicate prevention (re-import same URL)\n";
echo "-------------------------------------------------\n";

$attachment_id_2 = Media_Helper::import_image_to_media_library(
	$test_image_url,
	0,
	'This should not create a duplicate',
	'Duplicate test'
);

if ( $attachment_id === $attachment_id_2 ) {
	echo "✓ SUCCESS! Returned same attachment ID: {$attachment_id_2}\n";
	echo "  No duplicate was created.\n";
} else {
	echo "✗ WARNING: Different attachment ID returned: {$attachment_id_2}\n";
	echo "  This might indicate a duplicate was created.\n";
}

echo "\n";

// Test 3: Import array of images (gallery)
echo "TEST 3: Import gallery array\n";
echo "-------------------------------------------------\n";

$gallery_images = array(
	array(
		'src'   => 'https://myyri.kinola.ee/storage/myyri.kinola.ee/2922/Eo77z8o41t5uo9XkBpyViZbHDhsVUQ-metaUEhNXzQ2NTQ1X1IuanBn-.jpg',
		'alt'   => 'Gallery image 1',
		'title' => 'First gallery image',
	),
	array(
		'src'   => 'https://myyri.kinola.ee/storage/myyri.kinola.ee/2869/1cerwKaKskYQZQJFRlmcsKMyNCNcjg-metaRFNDMDY2MzEgZm90b2QgS3JlZXRlIEtpdHNlLmpwZw==-.jpg',
		'alt'   => 'Gallery image 2',
		'title' => 'Second gallery image',
	),
	array(
		'src'   => 'https://myyri.kinola.ee/storage/myyri.kinola.ee/2869/1cerwKaKskYQZQJFRlmcsKMyNCNcjg-metaRFNDMDY2MzEgZm90b2QgS3JlZXRlIEtpdHNlLmpwZw==-.jpg',
		'alt'   => 'Gallery image 3',
		'title' => 'Third gallery image',
	),
);

echo "Importing " . count( $gallery_images ) . " images...\n";

$gallery_attachment_ids = Media_Helper::import_images_array( $gallery_images, 0 );

$success_count = count( array_filter( $gallery_attachment_ids ) );
echo "✓ Successfully imported {$success_count}/" . count( $gallery_images ) . " images\n";

foreach ( $gallery_attachment_ids as $index => $att_id ) {
	if ( $att_id ) {
		echo "  [{$index}] Attachment ID: {$att_id} - " . wp_get_attachment_url( $att_id ) . "\n";
	} else {
		echo "  [{$index}] ✗ Failed to import\n";
	}
}

echo "\n";

// Test 4: Invalid URL handling
echo "TEST 4: Invalid URL handling\n";
echo "-------------------------------------------------\n";

$invalid_url = 'not-a-valid-url';
echo "Attempting to import invalid URL: {$invalid_url}\n";

$failed_attachment = Media_Helper::import_image_to_media_library( $invalid_url, 0 );

if ( $failed_attachment === null ) {
	echo "✓ SUCCESS! Correctly returned null for invalid URL\n";
	echo "  (Check debug.log for error message if WP_DEBUG_LOG is enabled)\n";
} else {
	echo "✗ UNEXPECTED: Returned attachment ID {$failed_attachment} for invalid URL\n";
}

echo "\n";

// Test 5: Get attachment by source URL
echo "TEST 5: Get attachment by source URL\n";
echo "-------------------------------------------------\n";

$found_attachment = Media_Helper::get_attachment_id_by_source_url( $test_image_url );

if ( $found_attachment === $attachment_id ) {
	echo "✓ SUCCESS! Found attachment ID {$found_attachment} for source URL\n";
} else {
	echo "✗ FAILED to find attachment by source URL\n";
	echo "  Expected: {$attachment_id}, Got: " . ( $found_attachment ?? 'null' ) . "\n";
}

echo "\n";

// Summary
echo "=================================================\n";
echo "Test Summary\n";
echo "=================================================\n";
echo "All tests completed!\n\n";

echo "To verify the imported images:\n";
echo "1. Go to: " . admin_url( 'upload.php' ) . "\n";
echo "2. Look for recently added images\n";
echo "3. Check that multiple sizes were generated for each image\n\n";

echo "To clean up test images:\n";
echo "1. Go to Media Library\n";
echo "2. Delete the test images created by this script\n";
echo "   OR run: wp eval 'wp_delete_attachment({$attachment_id}, true);'\n";

echo "\n";

if ( ! defined( 'WP_CLI' ) ) {
	echo '</pre>';
}
