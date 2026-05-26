<template>
	<cdx-dialog
		v-model:open="open"
		title="Create a new interactive map"
		:use-close-button="true"
		:primary-action="primaryAction"
		:default-action="defaultAction"
		@primary="onPrimaryAction"
		@default="open = false"
	>
		<cdx-field>
			<cdx-text-input v-model="inputValue"></cdx-text-input>
			<template #label>
				Title
			</template>
			<template #help-text>
				Enter the title of the map you wish to create – we will need to check whether a map with this title already exists.
			</template>
		</cdx-field>
	</cdx-dialog>
</template>

<script>
const { defineComponent, ref } = require( 'vue' );
const { CdxDialog, CdxTextInput, CdxField } = require( '../codex.js' );

module.exports = defineComponent( {
	name: 'AllMaps',
	components: {
		CdxDialog,
		CdxTextInput,
		CdxField
	},
	setup() {
		const open = ref( false );
		const inputValue = ref( '' );

		const addButton = document.querySelector( '#ext-wikimaps-actions-create' );

		addButton.addEventListener( 'click', () => {
			open.value = true;
		});

		const primaryAction = {
			label: 'Create Map',
			actionType: 'progressive'
		};

		const defaultAction = {
			label: 'Cancel'
		};

		function onPrimaryAction() {
			if ( inputValue.value.trim() !== '' ) {
				open.value = false;
				const pageTitle = 'Map:' + inputValue.value.trim();
				window.location.href = mw.util.getUrl( pageTitle, { action: 'mapedit' } );
			}
		}

		return {
			open,
			primaryAction,
			defaultAction,
			onPrimaryAction,
			inputValue
		};

	}
} );
</script>

<style lang="less">
</style>
