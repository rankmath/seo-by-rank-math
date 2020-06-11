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
	 * Holds the encryption key.
	 *
	 * @var string
	 */
	private $key;

	/**
	 * Holds the salt.
	 *
	 * @var string
	 */
	private $salt;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->key  = $this->get_default_key();
		$this->salt = $this->get_default_salt();
	}

	/**
	 * Get encryption key.
	 *
	 * @return string Key.
	 */
	private function get_default_key() {
		if ( defined( 'RANK_MATH_ENCRYPTION_KEY' ) && '' !== GOOGLESITEKIT_ENCRYPTION_KEY ) {
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
	private function get_default_salt() {
		if ( defined( 'RANK_MATH_ENCRYPTION_SALT' ) && '' !== GOOGLESITEKIT_ENCRYPTION_SALT ) {
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
	public function encrypt( $value ) {
		if ( ! extension_loaded( 'openssl' ) ) {
			return $value;
		}

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = openssl_random_pseudo_bytes( $ivlen );

		$raw_value = openssl_encrypt( $value . $this->salt, $method, $this->key, 0, $iv );
		if ( ! $raw_value ) {
			return false;
		}

		return base64_encode( $iv . $raw_value );
	}

	/**
	 * Decrypt string.
	 *
	 * @param  string $raw_value Encrypted string.
	 * @return string            Decrypted string.
	 */
	public function decrypt( $raw_value ) {
		if ( ! extension_loaded( 'openssl' ) ) {
			return $raw_value;
		}

		$raw_value = base64_decode( $raw_value, true );

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = substr( $raw_value, 0, $ivlen );

		$raw_value = substr( $raw_value, $ivlen );

		if ( ! $raw_value || strlen( $iv ) !== $ivlen ) {
			return false;
		}

		$value = openssl_decrypt( $raw_value, $method, $this->key, 0, $iv );
		if ( ! $value || substr( $value, - strlen( $this->salt ) ) !== $this->salt ) {
			return false;
		}

		return substr( $value, 0, - strlen( $this->salt ) );
	}

	/**
	 * Recursively encrypt array of strings.
	 *
	 * @param  mixed $value Original strings.
	 * @return string       Encrypted strings.
	 */
	public function deep_encrypt( $data ) {
		if ( is_array( $data ) ) {
			$encrypted = [];
			foreach ( $data as $key => $value ) {
				$encrypted[ $this->encrypt( $key ) ] = $this->deep_encrypt( $value );
			}

			return $encrypted;
		}

		return $this->encrypt( $data );
	}

	/**
	 * Recursively decrypt array of strings.
	 *
	 * @param  string $raw_value Encrypted strings.
	 * @return string            Decrypted strings.
	 */
	public function deep_decrypt( $data ) {
		if ( is_array( $data ) ) {
			$decrypted = [];
			foreach ( $data as $key => $value ) {
				$decrypted[ $this->decrypt( $key ) ] = $this->deep_decrypt( $value );
			}

			return $decrypted;
		}

		return $this->decrypt( $data );
	}
}
