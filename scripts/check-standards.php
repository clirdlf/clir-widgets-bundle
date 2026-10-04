<?php
/** Fail on findings beyond the reviewed PHPCS baseline, ignoring line shifts. */
require_once __DIR__ . '/Baseline.php';

use Clir\Quality\Baseline;

try {
	$arguments = array_slice( $argv, 1 );
	$mode      = array_shift( $arguments );
	if ( ! in_array( $mode, array( 'standards', 'compatibility' ), true ) || ( array() !== $arguments && array( '--update-baseline' ) !== $arguments ) ) {
		throw new RuntimeException( 'Usage: php scripts/check-standards.php standards|compatibility [--update-baseline]' );
	}
	$root    = dirname( __DIR__ );
	$ruleset = 'standards' === $mode ? 'phpcs.xml.dist' : 'phpcompatibility.xml.dist';
	$stdout  = tmpfile();
	$stderr  = tmpfile();
	if ( false === $stdout || false === $stderr ) {
		throw new RuntimeException( 'Cannot create checker output streams.' );
	}
	$process = proc_open(
		array( PHP_BINARY, $root . '/vendor/bin/phpcs', '--standard=' . $ruleset, '--report=json' ),
		array( 0 => array( 'pipe', 'r' ), 1 => $stdout, 2 => $stderr ),
		$pipes,
		$root
	);
	if ( ! is_resource( $process ) ) {
		throw new RuntimeException( 'Cannot start PHPCS.' );
	}
	fclose( $pipes[0] );
	$status = proc_close( $process );
	rewind( $stdout );
	rewind( $stderr );
	$report = Baseline::report( $status, stream_get_contents( $stdout ), stream_get_contents( $stderr ) );
	fclose( $stdout );
	fclose( $stderr );
	$counts = Baseline::counts( $report, $root );
	$build  = $root . '/build';
	if ( ! is_dir( $build ) && ! mkdir( $build, 0777, true ) ) {
		throw new RuntimeException( 'Cannot create report directory.' );
	}
	$encoded = json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) . "\n";
	if ( false === file_put_contents( $build . '/' . $mode . '-report.json', $encoded ) ) {
		throw new RuntimeException( 'Cannot write report.' );
	}
	$baseline = $root . '/quality/' . $mode . '-baseline.json';
	if ( array( '--update-baseline' ) === $arguments ) {
		$encoded = json_encode( (object) $counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) . "\n";
		if ( false === file_put_contents( $baseline, $encoded ) ) {
			throw new RuntimeException( 'Cannot write baseline.' );
		}
		printf( "Recorded %d %s findings in quality/%s-baseline.json\n", array_sum( $counts ), $mode, $mode );
		exit( 0 );
	}
	$previous  = Baseline::normalize( Baseline::decode( file_get_contents( $baseline ) ) );
	$additions = Baseline::additions( $counts, $previous );
	foreach ( $additions as $key => $count ) {
		printf( "+%d: %s\n", $count, $key );
	}
	printf( "%d current findings; %d beyond baseline\n", array_sum( $counts ), array_sum( $additions ) );
	exit( empty( $additions ) ? 0 : 1 );
} catch ( Throwable $error ) {
	fwrite( STDERR, $error->getMessage() . "\n" );
	exit( 1 );
}
