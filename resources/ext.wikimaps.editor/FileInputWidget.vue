<template>
	<div
		class="ext-wikimaps-file-input"
		:class="{ 'ext-wikimaps-file-input--dragging': isDragging }"
		@dragover.prevent="onDragOver"
		@dragleave.prevent="onDragLeave"
		@drop.prevent="onDrop"
	>
		<div v-if="store.imagePreviewUrl" class="ext-wikimaps-file-input__preview">
			<img
				:src="store.imagePreviewUrl"
				class="ext-wikimaps-file-input__image"
				alt=""
			/>
			<div class="ext-wikimaps-file-input__actions">
				<button type="button" @click="triggerSelect">
					Change
				</button>
				<button type="button" @click="store.clearImage()">
					Remove
				</button>
			</div>
		</div>
		<div v-else class="ext-wikimaps-file-input__dropzone" @click="triggerSelect">
			<span class="ext-wikimaps-file-input__label">
				Drag 'n drop or click to browse
			</span>
			<span class="ext-wikimaps-file-input__hint">
				PNG or JPEG, max {{ maxSize }}MB
			</span>
		</div>
		<input
			ref="fileInput"
			type="file"
			:accept="accept"
			class="ext-wikimaps-file-input__native"
			@change="onFileSelect"
		/>
	</div>
</template>

<script>
const { defineComponent, ref } = require( 'vue' );
const useMapStore = require( './store.js' );

module.exports = defineComponent( {
	name: 'FileInputWidget',
	props: {
		maxSize: {
			type: Number,
			default: 5
		},
		accept: {
			type: String,
			default: 'image/png, image/jpeg'
		}
	},
	setup( props ) {
		const store = useMapStore();
		const fileInput = ref( null );
		const isDragging = ref( false );

		function triggerSelect() {
			fileInput.value.click();
		}

		function validateFile( file ) {
			const sizeMB = file.size / 1024 / 1024;
			if ( sizeMB > props.maxSize ) {
				mw.notify(
					mw.msg( 'wikimaps-file-too-large', props.maxSize ),
					{ type: 'error' }
				);
				return false;
			}

			const allowedTypes = props.accept.split( ',' ).map( ( t ) => t.trim() );
			if ( !allowedTypes.includes( file.type ) ) {
				mw.notify(
					mw.msg( 'wikimaps-file-invalid-type' ),
					{ type: 'error' }
				);
				return false;
			}

			return true;
		}

		function onFileSelect( e ) {
			const file = e.target.files[ 0 ];
			if ( file && validateFile( file ) ) {
				store.setImage( file );
			}
			fileInput.value.value = '';
		}

		function onDragOver() {
			isDragging.value = true;
		}

		function onDragLeave() {
			isDragging.value = false;
		}

		function onDrop( e ) {
			isDragging.value = false;
			const file = e.dataTransfer.files[ 0 ];
			if ( file && validateFile( file ) ) {
				store.setImage( file );
			}
		}

		return {
			store,
			fileInput,
			isDragging,
			triggerSelect,
			onFileSelect,
			onDragOver,
			onDragLeave,
			onDrop
		};
	}
} );
</script>

<style lang="less">
@import 'mediawiki.skin.variables.less';

.ext-wikimaps-file-input__native {
	display: none;
}

.ext-wikimaps-file-input__dropzone {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	min-height: 120px;
	padding: @spacing-100;
	border: 2px dashed @border-color-interactive;
	border-radius: @border-radius-base;
	cursor: pointer;
	transition: border-color 0.15s, background-color 0.15s;

	&:hover {
		border-color: @border-color-progressive;
		background-color: @background-color-progressive-subtle;
	}
}

.ext-wikimaps-file-input--dragging .ext-wikimaps-file-input__dropzone {
	border-color: @border-color-progressive;
	background-color: @background-color-progressive-subtle;
}

.ext-wikimaps-file-input__label {
	color: @color-base;
	text-align: center;
}

.ext-wikimaps-file-input__hint {
	font-size: @font-size-small;
	color: @color-subtle;
	margin-top: @spacing-25;
}

.ext-wikimaps-file-input__image {
	display: block;
	width: 100%;
	border-radius: @border-radius-base;
	border: @border-width-base @border-style-base @border-color-base;
}

.ext-wikimaps-file-input__actions {
	display: flex;
	gap: @spacing-50;
	margin-top: @spacing-50;

	button {
		flex: 1;
		padding: @spacing-25 @spacing-50;
		border: @border-width-base @border-style-base @border-color-base;
		border-radius: @border-radius-base;
		background: @background-color-base;
		color: @color-base;
		cursor: pointer;
		font-size: @font-size-small;

		&:hover {
			background-color: @background-color-interactive;
		}
	}
}
</style>
