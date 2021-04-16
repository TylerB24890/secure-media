<?php
/**
 * Secure Media utility functions
 *
 * @since   1.0
 * @package secure-media
 */

namespace SecureMedia\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Whether plugin is network activated
 *
 * Determines whether plugin is network activated or just on the local site.
 *
 * @since 1.0
 * @param string $plugin the plugin base name.
 * @return bool True if network activated or false.
 */
function is_network_activated( $plugin ) {
	$plugins = get_site_option( 'active_sitewide_plugins' );

	if ( is_multisite() && isset( $plugins[ $plugin ] ) ) {
		return true;
	}

	return false;
}

/**
 * Get plugin settings
 *
 * @param string $setting_key Setting key
 * @return array
 */
function get_settings( $setting_key = null ) {
	$default_bucket = ( SM_IS_NETWORK ) ? network_site_url() : site_url();
	$default_bucket = preg_replace( '#https?://(www\.)?#i', '', $default_bucket );
	$default_bucket = preg_replace( '#[^\w]#', '-', trim( $default_bucket ) );

	$defaults = [
		's3_secret_access_key' => '',
		's3_access_key_id'     => '',
		's3_bucket'            => $default_bucket,
		's3_region'            => 'us-west-1',
		's3_serve_from_wp'     => true,
	];

	$settings = ( SM_IS_NETWORK ) ? get_site_option( 'sm_settings', [] ) : get_option( 'sm_settings', [] );
	$settings = wp_parse_args( $settings, $defaults );

	if ( empty( $settings['s3_bucket'] ) ) {
		$settings['s3_bucket'] = $default_bucket;
	}

	if ( ! empty( $setting_key ) ) {
		return $settings[ $setting_key ];
	}

	return $settings;
}

/**
 * Determine if we should authenticate with an instance IAM Role.
 * This requires an EC2 Instance with an assumed IAM Role with S3 Bucket Access.
 *
 * Define `define( 'SM_SERVER_AUTH', true );` in wp-config.php
 *
 * @see https://docs.aws.amazon.com/IAM/latest/UserGuide/id_roles_use_switch-role-ec2.html
 * @return boolean
 */
function use_server_auth() {
	return ( defined( 'SM_SERVER_AUTH' ) ) && SM_SERVER_AUTH;
}

/**
 * Get the encryption key from the constant
 *
 * Define `define( 'SM_ENCRYPTION_KEY', '[key]' );` in wp-config.php.
 * Replace "[key]" with the encyrption key provided by AWS.
 *
 * @see https://docs.aws.amazon.com/AmazonS3/latest/API/API_HeadObject.html
 * @see https://docs.aws.amazon.com/AmazonS3/latest/userguide/ServerSideEncryptionCustomerKeys.html
 * @return mixed SM_ENCRYPTION_KEY or False if no key provided
 */
function get_encrypted_key() {
	if ( defined( 'SM_ENCRYPTION_KEY' ) && ! empty( SM_ENCRYPTION_KEY ) ) {
		return SM_ENCRYPTION_KEY;
	}

	return false;
}

/**
 * Hash the encryption key using MD5 hashing algorithm.
 * This is required for passing encryption to S3 buckets
 *
 * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#getobject
 * @param  mixed $key Encryption key to hash or false for default
 * @return string
 */
function hash_encrypted_key( $key = false ) {
	if ( ! $key ) {
		$key = get_encrypted_key();
	}

	return md5( $key );
}

/**
 * Get the encryption algorithm from the constant
 *
 * Define `define( 'SM_ENCRYPTION_ALGORITHM', '[algorithm]' );` in wp-config.php
 * Replace "[algorithm]" with the encryption algorithm set in S3 bucket
 *
 * @return mixed SM_ENCRYPTION_ALGORITHM or False if no algorithm provided
 */
function get_encrypted_algorithm() {
	if ( defined( 'SM_ENCRYPTION_ALGORITHM' ) && ! empty( SM_ENCRYPTION_ALGORITHM ) ) {
		return SM_ENCRYPTION_ALGORITHM;
	}

	return false;
}
