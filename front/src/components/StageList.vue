<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-3xl font-bold text-gray-800">📚 Этапы квеста</h2>
      <button
        @click="loadStages"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
      >
        🔄 Обновить
      </button>
    </div>

    <div v-if="loading" class="bg-blue-50 border-l-4 border-blue-600 p-6 rounded-lg">
      <p class="text-blue-800 font-semibold">⏳ Загрузка этапов...</p>
    </div>

    <div v-else-if="error" class="bg-red-100 border-l-4 border-red-600 p-6 rounded-lg">
      <p class="text-red-800 font-semibold">❌ {{ error }}</p>
    </div>

    <div v-else-if="stages.length === 0" class="bg-gray-100 border-l-4 border-gray-600 p-6 rounded-lg">
      <p class="text-gray-800 font-semibold">📭 Этапов не найдено</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div
        v-for="stage in stages"
        :key="stage.id"
        class="card cursor-pointer hover:-translate-y-1 transform transition"
        @click="selectStage(stage)"
      >
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="text-xl font-bold text-gray-800">{{ stage.title }}</h3>
            <p class="text-sm text-gray-600">Этап {{ stage.order || '—' }}</p>
          </div>
          <span class="text-3xl">🎯</span>
        </div>

        <p class="text-gray-700 mb-4">{{ stage.description }}</p>

        <div class="bg-gray-100 p-3 rounded-lg space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Тип:</span>
            <span class="badge bg-blue-100 text-blue-800">{{ stage.stage_type || 'online' }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Дедлайн:</span>
            <span class="font-semibold text-gray-800">
              {{ stage.deadline_at ? new Date(stage.deadline_at).toLocaleDateString('ru-RU') : '—' }}
            </span>
          </div>
        </div>

        <button class="mt-4 w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-2 rounded-lg hover:opacity-90 transition font-semibold">
          Начать этап →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['select-stage']);
const stages = ref([]);
const loading = ref(true);
const error = ref(null);

const selectStage = (stage) => {
  emit('select-stage', stage);
};

const loadStages = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/stages');
    stages.value = response.data.data;
  } catch (err) {
    error.value = 'Не удалось получить этапы.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(loadStages);
</script>
