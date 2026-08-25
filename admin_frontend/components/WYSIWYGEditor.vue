<template>
  <div
          class="wysiwyg-wrapper"
          :class="{ 'full-screen': fullScreen, focused: focused }"
  >
    <div class="dply-felx mb-15">
      <label class="mb-0">{{ title }}</label>
      <div class="custom-toolbar">
        <button class="outline-btn" @click.prevent="toggleEditAsHtml">
          {{
          $t('error.ea', {
          type: !editAsHtml ? $t('profile.html') : $t('profile.wysi'),
          })
          }}
        </button>
        <button class="outline-btn" @click.prevent="toggleFullScreen">
          {{
          $t('error.scrn', {
          type: !fullScreen ? $t('error.full') : $t('error.nrm'),
          })
          }}
        </button>
        <button
                class="ml-10 close-btn"
                v-if="fullScreen"
                @click.prevent="toggleFullScreen"
        >
          <i class="icon close-icon" />
        </button>
      </div>
    </div>

    <p v-if="editAsHtml" class="info-msg mb-20 mb-sm-15">
      {{ $t('error.st') }}: h1, h2...h6, p, strong, em, u, s, blockquote, pre, ul,
      li, ol, br, a, img, video. {{ $t('error.mkSr') }}
    </p>


    <quill-editor
            v-if="!editAsHtml"
            :modules="modules"
            :placeholder="$t('ship.wh')"
            toolbar="full"
            contentType="html"
            v-model:content="productDescription"
            @focus="focused = true"
            @blur="focused = false"
            @ready="onEditorReady"
    />

    <textarea
            v-else
            v-model="productDescription"
            :placeholder="$t('ship.wh')"
            @change="descriptionChange"
    />
  </div>
</template>

<script setup>

  import { ref, watch, onMounted } from 'vue';
  import DOMPurify from 'dompurify';
  import {useI18n} from "vue-i18n";
  const { t } = useI18n()
  import ImageUploader from 'quill-image-uploader';

  // Props
  const props = defineProps({
    title: {
      type: String
    },
    type: {
      type: Number,
      required: false,
    },
    description: {
      type: String,
      default: '',
    },
  });

  let editor = null;


  const modules = ref({
    name: "imageUploader",
    module: ImageUploader,
  });

  const title = props.title || t('prod.desc');

  // Emits
  const emit = defineEmits(['change', 'file']);

  // Reactive State
  const acceptedTags = ['iframe'];
  const productDescription = ref('');
  const editAsHtml = ref(false);
  const fullScreen = ref(false);
  let focused = ref(false);


  // Methods
  const toggleEditAsHtml = () => {
    editAsHtml.value = !editAsHtml.value;
  };

  const toggleFullScreen = () => {
    fullScreen.value = !fullScreen.value;
  };

  const getImgUrls = (delta) => {
    return delta.ops.filter(i => i.insert && i.insert.image).map(i => i.insert.image);
  }

  function imageHandler() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = async () => {
      const file = input.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('file', file);

      try {
        const cursorLocation = editor.getSelection()?.index || 0;
        handleImageAdded(file, editor, cursorLocation)
      } catch (error) {
        console.error('Image upload failed:', error);
      }
    };
  }


  // Event triggered when the editor is ready
  const onEditorReady = (editorInstance)  => {
    editor = editorInstance; // Save the editor instance

    // Attach the text-change listener
    editor.on('text-change', descriptionChange);

    const toolbar = editor.getModule('toolbar');
    toolbar.addHandler('image', imageHandler);
  }

  const descriptionChange = (delta, oldContents, source) => {
      if (source !== 'user' && source !== 'api') return;

      const deleted = getImgUrls(editor.getContents().diff(oldContents));

      if(deleted.length) {
        const cursorLocation = editor.getSelection()?.index || 0;
        handleImageRemoved(deleted[0], editor, cursorLocation)
      }

      const sanitizedContent = DOMPurify.sanitize(productDescription.value, {
        ADD_TAGS: acceptedTags,
      });

      emit('change', sanitizedContent);
  };

  const handleImageAdded = (file, Editor, cursorLocation, resetUploader = null) => {

    handleDescriptionImage(false, file, Editor, cursorLocation, resetUploader);
  };

  const handleImageRemoved = (file, Editor, cursorLocation, resetUploader= null) => {
    handleDescriptionImage(true, file, Editor, cursorLocation, resetUploader);
  };

  const handleDescriptionImage = (deleted, file, Editor, cursorLocation, resetUploader= null) => {
    emit('file', { deleted, file, Editor, cursorLocation, resetUploader });
  };

  // Watchers
  watch(() => props.description, (newVal) => {
    productDescription.value = DOMPurify.sanitize(newVal, {ADD_TAGS: acceptedTags,});}
  );

  // Lifecycle Hooks
  onMounted(() => {
    productDescription.value = DOMPurify.sanitize(props.description);
  });
</script>

