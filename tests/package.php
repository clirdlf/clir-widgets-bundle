<?php
/** Verify the release ZIP preserves runtime assets and excludes development files. */
$root = dirname( __DIR__ );
$zip  = new ZipArchive();
if ( true !== $zip->open( $root . '/build/clir-widgets-bundle.zip', ZipArchive::CHECKCONS ) ) {
	throw new RuntimeException( 'Invalid plugin ZIP.' );
}

$expected = array( 'clir-widgets-bundle/clir-widgets-bundle.php' );
foreach ( array( 'lib', 'js', 'custom-post-types' ) as $directory ) {
	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root . '/' . $directory, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $files as $file ) {
		if ( $file->isFile() && in_array( $file->getExtension(), array( 'php', 'js', 'jpg', 'jpeg', 'png', 'gif' ), true ) ) {
			$expected[] = 'clir-widgets-bundle/' . str_replace( DIRECTORY_SEPARATOR, '/', substr( $file->getPathname(), strlen( $root ) + 1 ) );
		}
	}
}

$actual = array();
for ( $index = 0; $index < $zip->numFiles; $index++ ) {
	$actual[] = $zip->getNameIndex( $index );
}
$zip->close();
sort( $actual );
sort( $expected );
if ( $actual !== $expected ) {
	throw new RuntimeException(
		'ZIP differs from the runtime files. Missing: ' . json_encode( array_values( array_diff( $expected, $actual ) ) ) .
		'; unexpected: ' . json_encode( array_values( array_diff( $actual, $expected ) ) )
	);
}
printf( "ZIP verified: %d runtime files; no development files.\n", count( $actual ) );
