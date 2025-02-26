<?php
/**
 * This class handles the encryption/descryption of sensitive user data, like
 * the Rank Math API key.
 *
 * Credits to Felix Arntz @ https://felix-arntz.me/
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

defined( 'ABSPATH' ) || exit;

/**
 * The Data Encryption class.
 */
class Data_Encryption {

	/**
	 * Enryption available or not.
	 *
	 * @var bool
	 */
	private static $encryption_possible = null;

	/**
	 * Get encryption key.
	 *
	 * @return string Key.
	 */
	public static function get_key() {
		if ( defined( 'RANK_MATH_ENCRYPTION_KEY' ) && '' !== RANK_MATH_ENCRYPTION_KEY ) {
			return RANK_MATH_ENCRYPTION_KEY;
		}

		if ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
			return LOGGED_IN_KEY;
		}

		return '';
	}

	/**
	 * Get salt.
	 *
	 * @return string Salt.
	 */
	public static function get_salt() {
		if ( defined( 'RANK_MATH_ENCRYPTION_SALT' ) && '' !== RANK_MATH_ENCRYPTION_SALT ) {
			return RANK_MATH_ENCRYPTION_SALT;
		}

		if ( defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
			return LOGGED_IN_SALT;
		}

		return '';
	}

	/**
	 * Encrypt data.
	 *
	 * @param  mixed $value Original string.
	 * @return string       Encrypted string.
	 */
	public static function encrypt( $value ) {
		if ( ! self::is_available() ) {
			return $value;
		}

		$method  = 'aes-256-ctr';
		$ciphers = openssl_get_cipher_methods();
		if ( ! in_array( $method, $ciphers, true ) ) {
			$method = $ciphers[0];
		}

		$ivlen = openssl_cipher_iv_length( $method );
		$iv    = openssl_random_pseudo_bytes( $ivlen );

		$raw_value = openssl_encrypt( $value . self::get_salt(), $method, self::get_key(), 0, $iv );
		if ( ! $raw_value ) {
			return $value;
		}

		return base64_encode( $iv . $raw_value );  // phpcs:ignore -- Verified as safe usage.
	}

	/**
	 * Decrypt string.
	 *
	 * @param  string $raw_value Encrypted string.
	 * @return string            Decrypted string.
	 */
	public static function decrypt( $raw_value ) {
		if ( ! self::is_available() ) {
			return $raw_value;
		}

		$method  = 'aes-256-ctr';
		$ciphers = openssl_get_cipher_methods();
		if ( ! in_array( $method, $ciphers, true ) ) {
			$method = $ciphers[0];
		}

		$raw_value = base64_decode( $raw_value, true );  // phpcs:ignore -- Verified as safe usage.

		$ivlen = openssl_cipher_iv_length( $method );
		$iv    = substr( $raw_value, 0, $ivlen );

		$raw_value = substr( $raw_value, $ivlen );

		if ( ! $raw_value || strlen( $iv ) !== $ivlen ) {
			return $raw_value;
		}

		$salt = self::get_salt();

		$value = openssl_decrypt( $raw_value, $method, self::get_key(), 0, $iv );
		if ( ! $value || substr( $value, - strlen( $salt ) ) !== $salt ) {
			return $raw_value;
		}

		return substr( $value, 0, - strlen( $salt ) );
	}

	/**
	 * Recursively encrypt array of strings.
	 *
	 * @param  mixed $data Original strings.
	 * @return string       Encrypted strings.
	 */
	public static function deep_encrypt( $data ) {
		if ( is_array( $data ) ) {
			$encrypted = [];
			foreach ( $data as $key => $value ) {
				$encrypted[ self::encrypt( $key ) ] = self::deep_encrypt( $value );
			}

			return $encrypted;
		}

		return self::encrypt( $data );
	}

	/**
	 * Recursively decrypt array of strings.
	 *
	 * @param  string $data Encrypted strings.
	 * @return string       Decrypted strings.
	 */
	public static function deep_decrypt( $data ) {
		if ( is_array( $data ) ) {
			$decrypted = [];
			foreach ( $data as $key => $value ) {
				$decrypted[ self::decrypt( $key ) ] = self::deep_decrypt( $value );
			}

			return $decrypted;
		}

		return self::decrypt( $data );
	}

	/**
	 * Check if OpenSSL is available and encryption is not disabled with filter.
	 *
	 * @return bool Whether encryption is possible or not.
	 */
	public static function is_available() {
		static $encryption_possible;
		if ( null === $encryption_possible ) {
			$encryption_possible = extension_loaded( 'openssl' ) && apply_filters( 'rank_math/admin/sensitive_data_encryption', true ) && self::get_key() && self::get_salt();
		}

		return (bool) $encryption_possible;
	}
}
