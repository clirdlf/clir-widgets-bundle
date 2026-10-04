<?php
/** Pure report comparison shared by the CLI and its regression tests. */
namespace Clir\Quality;

use RuntimeException;

final class Baseline {

	public static function decode( string $json ): array {
		$value = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
		if ( ! is_array( $value ) ) {
			throw new RuntimeException( 'Expected a JSON object.' );
		}
		return $value;
	}

	public static function key( array $parts ): string {
		return json_encode( $parts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR );
	}

	public static function counts( array $report, string $root ): array {
		if ( ! isset( $report['files'] ) || ! is_array( $report['files'] ) ) {
			throw new RuntimeException( 'PHPCS report has no valid files object.' );
		}
		$counts = array();
		$prefix = rtrim( $root, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
		foreach ( $report['files'] as $filename => $data ) {
			$path = realpath( $filename );
			if ( false === $path || ! str_starts_with( $path, $prefix ) ) {
				throw new RuntimeException( 'Report file is outside the project or missing: ' . $filename );
			}
			if ( ! isset( $data['messages'] ) || ! is_array( $data['messages'] ) ) {
				throw new RuntimeException( 'Invalid messages for ' . $filename );
			}
			$relative = str_replace( DIRECTORY_SEPARATOR, '/', substr( $path, strlen( $prefix ) ) );
			foreach ( $data['messages'] as $message ) {
				foreach ( array( 'source', 'type', 'message' ) as $field ) {
					if ( ! isset( $message[ $field ] ) || ! is_string( $message[ $field ] ) ) {
						throw new RuntimeException( 'Malformed PHPCS message.' );
					}
				}
				$key = self::key( array( $relative, $message['source'], $message['type'], $message['message'] ) );
				$counts[ $key ] = ( $counts[ $key ] ?? 0 ) + 1;
			}
		}
		ksort( $counts );
		return $counts;
	}

	public static function normalize( array $baseline ): array {
		$counts = array();
		foreach ( $baseline as $key => $count ) {
			$parts = self::decode( $key );
			if ( ! array_is_list( $parts ) || 4 !== count( $parts ) || ! is_int( $count ) || $count < 0 ) {
				throw new RuntimeException( 'Invalid baseline entry.' );
			}
			foreach ( $parts as $part ) {
				if ( ! is_string( $part ) ) {
					throw new RuntimeException( 'Invalid baseline fingerprint.' );
				}
			}
			$normalized = self::key( $parts );
			$counts[ $normalized ] = ( $counts[ $normalized ] ?? 0 ) + $count;
		}
		return $counts;
	}

	public static function additions( array $current, array $previous ): array {
		$additions = array();
		foreach ( $current as $key => $count ) {
			$difference = $count - ( $previous[ $key ] ?? 0 );
			if ( $difference > 0 ) {
				$additions[ $key ] = $difference;
			}
		}
		return $additions;
	}

	public static function report( int $status, string $stdout, string $stderr ): array {
		if ( ! in_array( $status, array( 0, 1, 2, 3 ), true ) ) {
			throw new RuntimeException( 'PHPCS failed: ' . substr( $stderr . $stdout, 0, 2000 ) );
		}
		return self::decode( $stdout );
	}
}
