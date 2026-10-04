<?php
/** Use the installed WP-CLI bundle, not Composer's framework-only vendor/bin/wp. */
$root = dirname( __DIR__ );
$file = $argv[1] ?? '';
if ( ! in_array( $file, array( 'tests/wp-smoke.php', 'tests/multisite.php', 'tests/browser-fixture.php' ), true ) ) {
    throw new RuntimeException( 'Expected a supported WordPress test entry point.' );
}
$cli = getenv( 'WP_CLI_BIN' );
if ( ! $cli ) {
    foreach ( explode( PATH_SEPARATOR, getenv( 'PATH' ) ?: '' ) as $directory ) {
        $candidate = $directory . DIRECTORY_SEPARATOR . 'wp';
        $resolved = realpath( $candidate );
        if ( $resolved && ! str_starts_with( $resolved, $root . '/vendor/' ) && is_executable( $candidate ) ) {
            $cli = $candidate;
            break;
        }
    }
}
if ( ! $cli ) {
    throw new RuntimeException( 'Install the WP-CLI bundle or set WP_CLI_BIN to its executable.' );
}
$command = array_merge( array( $cli, 'eval-file', $root . '/' . $file ), array_slice( $argv, 2 ) );
$process = proc_open( $command, array( 0 => STDIN, 1 => STDOUT, 2 => STDERR ), $pipes, $root );
if ( ! is_resource( $process ) ) {
    throw new RuntimeException( 'Could not start WP-CLI.' );
}
exit( proc_close( $process ) );
