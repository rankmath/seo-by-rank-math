<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel;

use WPMedia_ConsumerStrategies_AbstractConsumer;

/**
 * Consumes messages and sends them to a host/endpoint using WordPress's HTTP API
 */
class WPConsumer extends WPMedia_ConsumerStrategies_AbstractConsumer {

	/**
	 * The host to connect to (e.g. api.mixpanel.com)
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * The host-relative endpoint to write to (e.g. /engage)
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * The maximum number of seconds to allow the call to execute. Default is 30 seconds.
	 *
	 * @var int
	 */
	protected $timeout;

	/**
	 * The protocol to use for the cURL connection
	 *
	 * @var string
	 */
	protected $protocol;

	/**
	 * Creates a new WPConsumer and assigns properties from the $options array
	 *
	 * @param array{host:string, endpoint:string, timeout?: int, use_ssl?: bool} $options Options for the consumer.
	 */
	public function __construct( $options ) {
		parent::__construct( $options );

		$this->host     = $options['host'];
		$this->endpoint = $options['endpoint'];
		$this->timeout  = isset( $options['timeout'] ) ? $options['timeout'] : 30;
		$this->protocol = isset( $options['use_ssl'] ) && ( true === $options['use_ssl'] ) ? 'https' : 'http';
	}

	/**
	 * Send post request to the given host/endpoint using WordPress's HTTP API
	 *
	 * @param mixed[] $batch Batch of data to send to mixpanel.
	 *
	 * @return bool
	 */
	public function persist( $batch ) {
		if ( count( $batch ) <= 0 ) {
			return true;
		}

		$url  = $this->protocol . '://' . $this->host . $this->endpoint;
		$data = 'data=' . $this->_encode( $batch );

		$response = wp_remote_post(
			$url,
			[
				'timeout' => $this->timeout,
				'body'    => $data,
			]
		);

		if ( is_wp_error( $response ) ) {
			$this->_handleError( $response->get_error_code(), $response->get_error_message() );
			return false;
		}

		return true;
	}
}
