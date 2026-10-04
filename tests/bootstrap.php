<?php
/** Shared helpers for disposable WordPress integration installations. */
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    throw new RuntimeException( 'Run integration tests with wp eval-file.' );
}
if ( '1' !== getenv( 'CLIR_TEST_ENV' ) ) {
    throw new RuntimeException( 'These tests create posts, uploads and sites. Use a disposable WordPress installation and set CLIR_TEST_ENV=1.' );
}
error_reporting( E_ALL );
set_error_handler( static function ( $severity, $message, $file, $line ) {
    if ( error_reporting() & $severity ) {
        throw new ErrorException( $message, 0, $severity, $file, $line );
    }
    return false;
} );
function clir_test_assert( $condition, $message ) {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
}
function clir_test_element( $html, $tag ) {
    $parser = new WP_HTML_Tag_Processor( $html );
    clir_test_assert( $parser->next_tag( $tag ), 'Missing expected element: ' . $tag );
    return $parser;
}
