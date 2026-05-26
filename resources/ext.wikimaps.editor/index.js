( function () {
	const Vue = require( 'vue' );
	const MapEditor = require( `./MapEditor.vue` );
	Vue.createMwApp( MapEditor ).mount( '#ext-wikimaps-editor' );
}() );
