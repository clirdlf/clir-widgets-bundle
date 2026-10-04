<?php
/** Run independent regression fixtures without requiring WordPress or PHPUnit. */
require_once dirname( __DIR__ ) . '/scripts/Baseline.php';

use Clir\Quality\Baseline;

$root    = dirname( __DIR__ );
$parts   = array( 'clir-widgets-bundle.php', 'Example.Output', 'ERROR', 'Escape output' );
$key     = Baseline::key( $parts );
$message = array( 'source' => $parts[1], 'type' => $parts[2], 'message' => $parts[3], 'line' => 10 );
$make_report = static function ( array $messages ) use ( $root ): array {
	return array( 'files' => array( $root . '/clir-widgets-bundle.php' => array( 'messages' => $messages ) ) );
};
$expect = static function ( bool $condition, string $name ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'Failed: ' . $name );
	}
	echo "Passed: $name\n";
};
$expect_failure = static function ( callable $callback, string $name ) use ( $expect ): void {
	try {
		$callback();
	} catch ( Throwable $error ) {
		$expect( true, $name );
		return;
	}
	$expect( false, $name );
};

$moved         = $message;
$moved['line'] = 200;
$current       = Baseline::counts( $make_report( array( $moved ) ), $root );
$expect( array() === Baseline::additions( $current, array( $key => 1 ) ), 'line movement passes' );
$expect( array() === Baseline::additions( $current, array( $key => 2 ) ), 'removed findings pass' );
$added = Baseline::counts( $make_report( array( $message, $message ) ), $root );
$expect( array( $key => 1 ) === Baseline::additions( $added, array( $key => 1 ) ), 'added findings fail' );
$different           = $message;
$different['source'] = 'Different.Output';
$expect( 1 === array_sum( Baseline::additions( Baseline::counts( $make_report( array( $different ) ), $root ), array( $key => 1 ) ) ), 'different sniff cannot borrow budget' );

// Existing baseline keys use spaced JSON. Preserve that baseline without regenerating it.
$legacy_key = '["clir-widgets-bundle.php", "Example.Output", "ERROR", "Escape output"]';
$expect( array( $key => 1 ) === Baseline::normalize( array( $legacy_key => 1 ) ), 'existing baseline encoding is preserved' );
$expect_failure( static fn() => Baseline::report( 4, '', 'Ruleset unavailable' ), 'checker errors fail closed' );
$expect_failure( static fn() => Baseline::report( 0, 'not JSON', '' ), 'invalid JSON fails closed' );
$expect_failure( static fn() => Baseline::counts( array(), $root ), 'missing report fields fail closed' );
$expect_failure( static fn() => Baseline::normalize( array( $legacy_key => -1 ) ), 'invalid baseline counts fail closed' );
echo "All 9 quality gate regression checks passed.\n";
