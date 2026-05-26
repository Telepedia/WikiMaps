const { defineStore } = require( 'pinia' );

const useMapStore = defineStore( 'map', {
	state: () => ( {
		title: '',
		description: '',
		imageFile: null,
		imagePreviewUrl: '',
		isLoading: false,
		isNewMap: true
	} ),
	actions: {
		async load() {
			this.title = mw.config.get( 'wgTitle' );
			this.isLoading = true;

			try {
				const api = new mw.Api();
				const response = await api.get( {
					action: 'query',
					titles: mw.config.get( 'wgPageName' ),
					prop: 'revisions',
					rvprop: 'content',
					rvslots: 'main',
					formatversion: 2
				} );

				const page = response.query.pages[ 0 ];
				if ( page.missing ) {
					return;
				}

				const data = JSON.parse( page.revisions[ 0 ].slots.main.content );
				this.description = data.description || '';
				this.isNewMap = false;

				if ( data.image ) {
					await this.resolveImageUrl( data.image );
				}
			} catch ( e ) {
				// Page doesn't exist or content isn't valid JSON — 'tis a new map!
			} finally {
				this.isLoading = false;
			}
		},

		async resolveImageUrl( fileTitle ) {
			const api = new mw.Api();
			const response = await api.get( {
				action: 'query',
				titles: fileTitle,
				prop: 'imageinfo',
				iiprop: 'url',
				formatversion: 2
			} );

			const page = response.query.pages[ 0 ];
			if ( page.imageinfo && page.imageinfo[ 0 ] ) {
				this.imagePreviewUrl = page.imageinfo[ 0 ].url;
			}
		},

		setImage( file ) {
			if ( this.imageFile ) {
				URL.revokeObjectURL( this.imagePreviewUrl );
			}
			this.imageFile = file;
			this.imagePreviewUrl = file ? URL.createObjectURL( file ) : '';
		},

		clearImage() {
			if ( this.imageFile ) {
				URL.revokeObjectURL( this.imagePreviewUrl );
			}
			this.imageFile = null;
			this.imagePreviewUrl = '';
		}
	}
} );

module.exports = useMapStore;
