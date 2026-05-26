<?php

namespace Telepedia\Extensions\WikiMaps;

use MediaWiki\MediaWikiServices;
use Telepedia\Extensions\WikiMaps\Services\TileGenerator;

return [
	'WikiMaps.TileGenerator' => static function ( MediaWikiServices $services ): TileGenerator {
		$repo = $services->getRepoGroup()->getLocalRepo();
		$backend = $repo->getBackend();
		$config = $services->getConfigFactory()->makeConfig( 'WikiMaps' );
		
		$publicZonePath = $repo->getZonePath( 'public' );
		$backendBasePath = $publicZonePath . '/wikimaps-tiles';

		return new TileGenerator(
			$services->getShellCommandFactory(),
			$backend,
			$backendBasePath,
			$config->get( 'WikiMapsVipsCommand' )
		);
	},
];
