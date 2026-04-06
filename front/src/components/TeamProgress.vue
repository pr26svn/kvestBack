<template>
  <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h3 class="text-2xl font-bold text-gray-800">{{ team.name }}</h3>
        <p class="text-gray-600">Прогресс команды</p>
      </div>
      <button
        @click="$emit('back')"
        class="text-gray-600 hover:text-gray-800"
      >
        ✕
      </button>
    </div>

    <div v-if="loading" class="text-center py-8">
      <p class="text-gray-600">Загрузка прогресса...</p>
    </div>

    <div v-else-if="error" class="bg-red-100 text-red-700 p-4 rounded-lg">
      {{ error }}
    </div>

    <div v-else class="space-y-6">
      <!-- Статистика команды -->
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
          <p class="text-sm text-gray-600">Участников</p>
          <p class="text-3xl font-bold text-blue-600">{{ teamMembers.length }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
          <p class="text-sm text-gray-600">Завершенно этапов</p>
          <p class="text-3xl font-bold text-green-600">{{ completedStages }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
          <p class="text-sm text-gray-600">Всего этапов</p>
          <p class="text-3xl font-bold text-purple-600">{{ totalStages }}</p>
        </div>
      </div>

      <!-- Прогресс эта­пов -->
      <div>
        <h4 class="text-lg font-semibold mb-4 text-gray-800">Прогресс по этапам</h4>
        <div class="space-y-3">
          <div
            v-for="stage in stages"
            :key="stage.id"
            class="bg-gray-50 p-4 rounded-lg"
          >
            <div class="flex justify-between items-center mb-2">
              <p class="font-semibold text-gray-800">{{ stage.title }}</p>
              <span class="text-sm font-bold text-gray-600">{{ stageProgress[stage.id] || 0 }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                :style="{ width: (stageProgress[stage.id] || 0) + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Участники и их прогресс -->
      <div>
        <h4 class="text-lg font-semibold mb-4 text-gray-800">Участники команды</h4>
        <div class="space-y-3">
          <div
            v-for="member in teamMembers"
            :key="member.id"
            class="flex items-center justify-between bg-gray-50 p-4 rounded-lg"
          >
            <div class="flex-1">
              <p class="font-semibold text-gray-800">{{ member.name }}</p>
              <p class="text-sm text-gray-600">{{ member.email }}</p>
            </div>
            <div class="text-right">
              <p class="text-2xl font-bold text-green-600">{{ memberProgress[member.id] || 0 }}%</p>
              <p class="text-xs text-gray-600">Прогресс</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Рейтинг команд (если есть другие команды) -->
      <div v-if="teamRankings.length > 0">
        <h4 class="text-lg font-semibold mb-4 text-gray-800">Рейтинг команд</h4>
        <div class="space-y-2">
          <div
            v-for="(ranking, index) in teamRankings"
            :key="ranking.id"
            :class="[
              'flex items-center justify-between p-3 rounded-lg',
              ranking.id === team.id ? 'bg-blue-100 border-2 border-blue-600' : 'bg-gray-50'
            ]"
          >
            <div class="flex items-center gap-3">
              <span class="text-xl font-bold" :class="getRankingColor(index)">
                {{ index + 1 }}
              </span>
              <span class="font-semibold">{{ ranking.name }}</span>
            </div>
            <span class="text-lg font-bold text-gray-800">{{ ranking.progress }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const props = defineProps({
  team: {
    type: Object,
    required: true
  }
});

const authStore = useAuthStore();
const loading = ref(false);
const error = ref('');
const teamMembers = ref([]);
const stages = ref([]);
const stageProgress = ref({});
const memberProgress = ref({});
const teamRankings = ref([]);

const completedStages = computed(() => {
  return Object.values(stageProgress.value).filter(p => p === 100).length;
});

const totalStages = computed(() => stages.value.length);

const loadTeamProgress = async () => {
  loading.value = true;
  error.value = '';
  try {
    const [membersRes, stagesRes, progressRes, rankingsRes] = await Promise.all([
      axios.get(`/api/teams/${props.team.id}/members`, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      }),
      axios.get('/api/stages', {
        headers: { Authorization: `Bearer ${authStore.token}` }
      }),
      axios.get(`/api/teams/${props.team.id}/progress`, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      }),
      axios.get('/api/teams/rankings', {
        headers: { Authorization: `Bearer ${authStore.token}` }
      })
    ]);

    teamMembers.value = membersRes.data.data;
    stages.value = stagesRes.data.data;
    
    // Обработка прогресса по этапам и участникам
    progressRes.data.data.forEach(item => {
      stageProgress.value[item.stage_id] = item.stage_progress || 0;
      memberProgress.value[item.user_id] = item.user_progress || 0;
    });

    teamRankings.value = rankingsRes.data.data;
  } catch (err) {
    error.value = 'Не удалось загрузить прогресс';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const getRankingColor = (index) => {
  if (index === 0) return 'text-yellow-600';
  if (index === 1) return 'text-gray-400';
  if (index === 2) return 'text-orange-600';
  return 'text-gray-600';
};

onMounted(() => {
  loadTeamProgress();
});
</script>
