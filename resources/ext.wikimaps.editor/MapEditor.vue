<template>
	<div class="ext-wikimaps-editor">
		<aside class="ext-wikimaps-editor__sidebar">
			<cdx-accordion open separation="none">
				<template #title>
					Map Metadata
				</template>
				<cdx-field>
					<cdx-text-input v-model="store.title" disabled></cdx-text-input>
					<template #label>
						Title
					</template>
					<template #help-text>
						Give your map a title that describes what this map shows; this will also be the title of the page.
					</template>
				</cdx-field>
				<cdx-field>
					<cdx-text-input v-model="store.description"></cdx-text-input>
					<template #label>
						Description
					</template>
					<template #help-text>
						A short description of what this map shows.
					</template>
				</cdx-field>
				<cdx-field>
					<file-input-widget></file-input-widget>
					<template #label>
						Map Image
					</template>
				</cdx-field>
			</cdx-accordion>
		</aside>
		<div class="ext-wikimaps-editor__preview">
			<div class="ext-wikimaps-editor__missing">
				<div>
					<div class="ext-wikimaps-editor__missing-icon">
						<cdx-icon :icon="cdxIconMap"></cdx-icon>
					</div>
					<h2 class="ext-wikimaps-editor__missing-title">Create an interactive map</h2>
					<p>Get started by uploading an image to use as the base for your map; later you can add markers and points of interest.</p>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
const { defineComponent, onMounted } = require( 'vue' );
const { CdxAccordion, CdxField, CdxTextInput, CdxIcon } = require( '../codex.js' );
const { cdxIconMap } = require( './../icons.json' );
const FileInputWidget = require( './FileInputWidget.vue' );
const useMapStore = require( './store.js' );

module.exports = defineComponent( {
	name: 'MapEditor',
	components: {
		CdxAccordion,
		CdxField,
		CdxTextInput,
		FileInputWidget,
		CdxIcon
	},
	setup() {
		const store = useMapStore();

		onMounted( () => {
			store.load();
		} );

		return {
			store,
			cdxIconMap
		};
	}
} );
</script>

<style lang="less">
@import 'mediawiki.skin.variables.less';
.ext-wikimaps-editor {
	border: 1px solid @border-color-base;
	display: flex;
	flex-direction: row;
}

.ext-wikimaps-editor__sidebar {
	width: 320px;
	background: @background-color-neutral;
	padding: @spacing-75;
	max-width: 320px;
}

.ext-wikimaps-editor__missing-title.ext-wikimaps-editor__missing-title {
	border-bottom: 0;
	font-family: 'Quicksand', Helvetica, Arial, sans-serif;
	font-weight: 700;
}

.ext-wikimaps-editor__missing {
	align-items: center;
	display: flex;
	height: 100%;
	justify-content: center;
	text-align: center;
	width: 100%;
}

.ext-wikimaps-editor__preview {
	display: flex;
	flex-direction: column;
	position: relative;
	width: 100%;
}

.ext-wikimaps-editor__missing-icon {
	border-radius: 50%;
	background: @background-color-neutral;
	height: 48px;
	width: 48px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto;
}

.ext-wikimaps-editor__missing p {
	font-family: 'Quicksand', Helvetica, Arial, sans-serif;
	font-weight: 500;
}
</style>
