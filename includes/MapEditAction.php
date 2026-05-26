<?php

namespace Telepedia\Extensions\WikiMaps;

use Action;
use MediaWiki\Html\Html;

class MapEditAction extends Action {

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return 'mapedit';
	}

	/**
	 * @inheritDoc
	 */
	public function show() {
		$output = $this->getOutput();
		$title = $this->getArticle()->getTitle();
		$output->setPageTitle( $title->getPrefixedText() );

		$output->addHTML(
			Html::element(
				'div',
				[ 'id' => 'ext-wikimaps-editor' ]
			)
		);

		$output->addModules( [ 'ext.wikimaps.editor' ] );
	}
}
