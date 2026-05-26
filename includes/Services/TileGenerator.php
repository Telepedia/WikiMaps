<?php

namespace Telepedia\Extensions\WikiMaps\Services;

use MediaWiki\Shell\CommandFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Wikimedia\FileBackend\FileBackend;

class TileGenerator {

	public function __construct(
		private readonly CommandFactory $commandFactory,
		private readonly FileBackend $backend,
		private readonly string $backendBasePath,
		private readonly string $vipsCommand
	) {}

	/**
	 * Generate a set of tile maps from a source image using vips; these are stored locally and then batch uploaded
	 * to the backend (they are NOT uploaded to the wiki in the normal sense)
	 * @param string $sourcePath Local filesystem path to the source image
	 * @param string $sha1 SHA1 hash of the file (used as the tile directory key)
	 * @return array
	 */
	public function generateTiles( string $sourcePath, string $sha1 ): array {
		$maxZoom = $this->calculateMaxZoom( $sourcePath );

		$destDir = $this->backendBasePath . '/' . $sha1;
		$status = $this->backend->prepare( [ 'dir' => $destDir ] );
		if ( !$status->isGood() ) {
			throw new RuntimeException(
				'Failed to prepare tile storage: ' . $status->getMessage() // cba to use StatusFormatter rn
			);
		}

		// write our files to temp directory so we can batch upload them later on
		$tempDir = wfTempDir() . '/wikimaps-tiles-' . $sha1;
		if ( !is_dir( $tempDir ) ) {
			mkdir( $tempDir, 0755, true );
		}

		 $command = $this->commandFactory->create();
		 $command->params(
		     $this->vipsCommand,
			 'dzsave',
		     $sourcePath,
		     $tempDir . '/tiles',
		     '--layout', 'google',
		     '--suffix', '.png',
		 );
		 $result = $command->execute();

		 if ( $result->getExitCode() !== 0 ) {
		     $this->cleanupTempDir( $tempDir );
		     throw new RuntimeException(
		         'Tile generation failed: ' . $result->getStderr()
		     );
		 }

		 $this->storeTiles( $tempDir, $sha1 );
		 $this->cleanupTempDir( $tempDir );

		return [
			'sha1' => $sha1,
			'minZoom' => 0,
			'maxZoom' => $maxZoom,
		];
	}

	/**
	 * Batch all tile files from the temp location into the backend
	 * @param string $tempDir Local temp directory containing generated tiles
	 * @param string $sha1 SHA1 hash used as the directory key
	 */
	private function storeTiles( string $tempDir, string $sha1 ): void {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $tempDir, RecursiveDirectoryIterator::SKIP_DOTS )
		);

		$ops = [];
		foreach ( $iterator as $file ) {
			// skip if XML to avoid uploading the vips-properties.xml file :(
			if ( !$file->isFile() || $file->getExtension() === 'xml' ) {
				continue;
			}

			$relativePath = substr( $file->getPathname(), strlen( $tempDir ) + 1 );
			$storagePath = $this->backendBasePath . '/' . $sha1 . '/' . $relativePath;

			$dir = dirname( $storagePath );
			$this->backend->prepare( [ 'dir' => $dir ] );

			$ops[] = [
				'op' => 'store',
				'src' => $file->getPathname(),
				'dst' => $storagePath,
			];
		}

		if ( $ops ) {
			$status = $this->backend->doQuickOperations( $ops );
			if ( !$status->isGood() ) {
				throw new RuntimeException(
					'Failed to store tiles: ' . $status->getMessage()
				);
			}
		}
	}

	/**
	 * Check whether tiles already exist for a given image.
	 * @param string $sha1
	 * @return bool
	 */
	public function tilesExist( string $sha1 ): bool {
		$path = $this->backendBasePath . '/' . $sha1;
		return $this->backend->directoryExists( [ 'dir' => $path ] );
	}

	/**
	 * Get the storage path for a specific tile.
	 * @param string $sha1
	 * @param int $z zoom level
	 * @param int $x col
	 * @param int $y row
	 * @return string backend storage path
	 */
	public function getTilePath( string $sha1, int $z, int $x, int $y ): string {
		return $this->backendBasePath . '/' . $sha1 . '/' . $z . '/' . $x . '/' . $y . '.png';
	}

	/**
	 * Read a single tile's contents
	 * @param string $sha1
	 * @param int $z
	 * @param int $x
	 * @param int $y
	 * @return string|false File contents or false if not found
	 */
	public function getTileContents( string $sha1, int $z, int $x, int $y ) {
		$path = $this->getTilePath( $sha1, $z, $x, $y );
		return $this->backend->getFileContents( [ 'src' => $path ] );
	}

	/**
	 * cleanup temp directory after batch store completes
	 * @param string $tempDir
	 */
	private function cleanupTempDir( string $tempDir ): void {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $tempDir, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isDir() ) {
				rmdir( $file->getPathname() );
			} else {
				unlink( $file->getPathname() );
			}
		}

		rmdir( $tempDir );
	}

	/**
	 * Calculate the maximum zoom level based on image dimensions.
	 * Each zoom level halves the image; max zoom is where tiles
	 * cover the full image at 256px per tile.
	 * @param string $sourcePath
	 * @return int
	 */
	private function calculateMaxZoom( string $sourcePath ): int {
		$size = getimagesize( $sourcePath );
		if ( $size === false ) {
			return 5;
		}

		$maxDimension = max( $size[0], $size[1] );
		return max( 0, (int)ceil( log( $maxDimension / 256, 2 ) ) );
	}
}
