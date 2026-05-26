const { teleportTarget } = require( 'mediawiki.page.ready' );
const Vue = require( "vue" );
const AllMaps = require( "./AllMaps.vue" );
const { createPinia } = require( "pinia" );

let app;

function init() {
	if ( app ) {
		return;
	}

	const mountEl = document.createElement( 'div' );
	mountEl.id = 'ext-wikimaps-allmaps-root';
	teleportTarget.appendChild( mountEl );

	app = Vue.createMwApp( AllMaps ).use( createPinia() ).mount( mountEl );
}

( function () {
	// Run our code to manipulate the DOM after the wikipage is ready and the hook is fired
	mw.hook( 'wikipage.content' ).add( init );
} )();
