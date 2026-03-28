<template>
  <div class="stage-list">
    <div v-if="loading">Загрузка этапов...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <ul v-else>
      <li v-for="stage in stages" :key="stage.id" class="stage-card">
        <strong>{{ stage.title }}</strong>
        <p>{{ stage.description }}</p>
        <p class="meta">Тип: {{ stage.stage_type }} · Дедлайн: {{ stage.deadline_at || 'нет' }}</p>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stages = ref([]);
const loading = ref(true);
const error = ref(null);

const loadStages = async () => {
  try {
    const response = await axios.get('/api/stages');
    stages.value = response.data.data;
  } catch (err) {
    error.value = 'Не удалось получить этапы.';
  } finally {
    loading.value = false;
  }
};

onMounted(loadStages);
</script>

<style scoped>
.stage-list {
  margin-top: 16px;
}

.stage-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 18px;
  margin-bottom: 12px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  list-style: none;
}

.meta {
  color: #6b7280;
  font-size: 0.9rem;
}

.error {
  color: #b91c1c;
}
</style>
