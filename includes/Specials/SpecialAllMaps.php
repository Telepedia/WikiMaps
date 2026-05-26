<?php

namespace Telepedia\Extensions\WikiMaps\Specials;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;

class SpecialAllMaps extends SpecialPage {

	public function __construct() {
		parent::__construct( 'AllMaps' );
	}

	public function execute( $subPage ) {
		$this->setHeaders();
		$output = $this->getOutput();

		$output->addWikiMsg( 'ext-wikimaps-subtitle' );

		$output->addHTML(
			Html::rawElement(
				'div',
				[ 'id' => 'ext-wikimaps-actions' ],
				Html::element(
					'button',
					[ 'id' => 'ext-wikimaps-actions-create' ],
					'Create new Map'
				)
			)
		);

		$output->addModules( [ 'ext.wikimaps.specials' ] );
	}
}
