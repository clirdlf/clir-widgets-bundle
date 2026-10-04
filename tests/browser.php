<?php
/** Run a controlled fixture in headless Chrome; no Node build dependency. */
$root = dirname( __DIR__ );
$directory = $root . '/build/browser';
if ( ! file_exists( $directory . '/index.html' ) ) {
    throw new RuntimeException( 'Generate the browser fixture with composer test:browser-fixture first.' );
}
$chrome = getenv( 'CHROME_BIN' ) ?: 'google-chrome';
$profile = sys_get_temp_dir() . '/clir-chrome-' . bin2hex( random_bytes( 8 ) );
$browser = null;
$stop = static function ( $process ) {
    if ( ! is_resource( $process ) ) {
        return;
    }
    proc_terminate( $process );
    $deadline = microtime( true ) + 3;
    while ( proc_get_status( $process )['running'] && microtime( true ) < $deadline ) {
        usleep( 100000 );
    }
    if ( proc_get_status( $process )['running'] ) {
        proc_terminate( $process, 9 );
    }
    proc_close( $process );
};
$server = proc_open( array( PHP_BINARY, '-S', '127.0.0.1:8081', '-t', $directory ), array( 0 => array( 'pipe', 'r' ), 1 => array( 'file', $directory . '/server.log', 'a' ), 2 => array( 'file', $directory . '/server.log', 'a' ) ), $server_pipes );
if ( ! is_resource( $server ) ) {
    throw new RuntimeException( 'Could not start fixture server.' );
}
fclose( $server_pipes[0] );
try {
    $ready = false;
    for ( $attempt = 0; $attempt < 50; ++$attempt ) {
        if ( ! proc_get_status( $server )['running'] ) {
            throw new RuntimeException( 'Fixture server exited; check port 8081 and build/browser/server.log.' );
        }
        $connection = @fsockopen( '127.0.0.1', 8081, $errno, $error, 0.1 );
        if ( $connection ) {
            fclose( $connection );
            $ready = true;
            break;
        }
        usleep( 100000 );
    }
    if ( ! $ready ) {
        throw new RuntimeException( 'Fixture server was not ready.' );
    }
    $browser = proc_open( array( $chrome, '--headless', '--disable-gpu', '--no-first-run', '--user-data-dir=' . $profile, '--timeout=15000', '--virtual-time-budget=5000', '--dump-dom', 'http://127.0.0.1:8081/' ), array( 0 => array( 'pipe', 'r' ), 1 => array( 'file', $directory . '/result.html', 'w' ), 2 => array( 'file', $directory . '/chrome.log', 'w' ) ), $browser_pipes );
    if ( ! is_resource( $browser ) ) {
        throw new RuntimeException( 'Could not launch Chrome. Set CHROME_BIN to its executable.' );
    }
    fclose( $browser_pipes[0] );
    $deadline = microtime( true ) + 25;
    do {
        $dom = file_get_contents( $directory . '/result.html' );
        if ( str_contains( $dom, '<pre id="clir-browser-result">' ) || ! proc_get_status( $browser )['running'] ) {
            break;
        }
        usleep( 100000 );
    } while ( microtime( true ) < $deadline );
    if ( ! str_contains( $dom, '<pre id="clir-browser-result">PASS</pre>' ) ) {
        throw new RuntimeException( 'Browser checks failed. See build/browser/result.html and chrome.log.' );
    }
    echo "Browser image loading, dimensions, caption, iframe and email checks passed.\n";
} finally {
    $stop( $browser );
    $stop( $server );
    if ( is_dir( $profile ) ) {
        $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $profile, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
        foreach ( $files as $file ) {
            if ( $file->isDir() && ! $file->isLink() ) {
                rmdir( $file->getPathname() );
            } else {
                unlink( $file->getPathname() );
            }
        }
        rmdir( $profile );
    }
}
