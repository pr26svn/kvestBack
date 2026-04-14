<template>
  <div class="stage-wrapper">
    <!-- Iframe для встроенного контента -->
    <iframe
      v-if="stageContentUrl"
      :src="stageContentUrl"
      class="stage-iframe"
      frameborder="0"
      scrolling="auto"
      @load="onIframeLoad"
    ></iframe>

    <!-- Для стадии 1 используем компонент -->
    <Stage1Component v-else-if="stage.id === 1" :stage="stage" @score="submitScore" />

    <!-- Загрузка -->
    <div v-if="loading" class="loading">
      <p>⏳ Загрузка этапа...</p>
    </div>

    <!-- Ошибка -->
    <div v-if="error" class="error">
      <p>❌ {{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  stage: { type: Object, required: true },
  userId: { type: Number, default: 1 },
});

const emit = defineEmits(['submit-score']);

const loading = ref(false);
const error = ref('');
const stage1Scores = ref(0);

// URL для встроенного контента
const stageContentUrl = computed(() => {
  const stageMap = {
    1: null, // для этапа 1 используем компонент
    2: '/stage-2.html',
    3: '/stage-3.html',
    4: '/stage-4.html',
    5: '/stage-5.html',
  };
  return stageMap[props.stage?.id] || null;
});

const onIframeLoad = () => {
  console.log('Iframe loaded for stage', props.stage?.id);
};

const submitScore = (score) => {
  emit('submit-score', {
    stage_id: props.stage.id,
    score: score,
  });
};

onMounted(() => {
  loading.value = false;
});
</script>

<style scoped>
.stage-wrapper {
  min-height: 400px;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  background: white;
  overflow: hidden;
}

.stage-iframe {
  width: 100%;
  height: 100%;
  min-height: 600px;
  border: none;
}

.loading,
.error {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 500px;
  font-size: 1.2em;
  font-weight: 600;
}

.loading {
  color: #667eea;
  background: rgba(102, 126, 234, 0.05);
}

.error {
  color: #ff6b6b;
  background: rgba(255, 107, 107, 0.05);
}
</style>
