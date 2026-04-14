<template>
  <div class="space-y-6">
    <!-- Заголовок и кнопка создания -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-800">👥 Мои команды</h2>
        <p class="text-gray-600">Выполняйте задания вместе с командой и следите за рейтингом</p>
      </div>
      <button
        v-if="authStore.isAdmin"
        @click="showCreateForm = !showCreateForm"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
      >
        ➕ Создать команду
      </button>
    </div>

    <!-- Форма создания команды -->
    <div v-if="showCreateForm && authStore.isAdmin" class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-600">
      <h3 class="text-lg font-semibold mb-4">Новая команда</h3>
      <form @submit.prevent="createTeam" class="space-y-4">
        <input
          v-model="newTeam.name"
          type="text"
          placeholder="Название команды"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
        <textarea
          v-model="newTeam.description"
          placeholder="Описание"
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        ></textarea>
        <div class="flex gap-2">
          <button
            type="submit"
            :disabled="creatingTeam"
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 disabled:opacity-50"
          >
            {{ creatingTeam ? '⏳ Создание...' : '✓ Создать' }}
          </button>
          <button
            type="button"
            @click="showCreateForm = false"
            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400"
          >
            Отмена
          </button>
        </div>
      </form>
    </div>

    <!-- Загрузка -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-gray-600 font-semibold">⏳ Загрузка команд...</p>
    </div>

    <!-- Ошибка -->
    <div v-else-if="error" class="bg-red-100 border-l-4 border-red-600 text-red-700 p-4 rounded-lg">
      ❌ {{ error }}
    </div>

    <!-- Нет команд -->
    <div v-else-if="myTeams.length === 0 && allTeams.length === 0" class="text-center py-8">
      <p class="text-gray-600">Команд еще нет. Создайте первую команду!</p>
    </div>

    <!-- Мои команды -->
    <div v-if="myTeams.length > 0" class="space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b-2 border-blue-600">
        <span class="text-xl">⭐</span>
        <h3 class="text-xl font-bold text-gray-800">Мои команды ({{ myTeams.length }})</h3>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div
          v-for="team in myTeams"
          :key="'my-' + team.id"
          class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg shadow-lg border-2 border-blue-600 cursor-pointer hover:shadow-xl transition"
          @click="selectTeam(team)"
        >
          <div class="flex justify-between items-start mb-3">
            <div>
              <h3 class="text-xl font-bold text-blue-900">{{ team.name }}</h3>
              <p class="text-sm text-blue-700">👥 {{ team.members_count }} участников</p>
            </div>
            <span class="text-3xl">👑</span>
          </div>
          <p class="text-blue-800 mb-4">{{ team.description }}</p>
          
          <!-- Статистика команды -->
          <div class="bg-white bg-opacity-80 p-3 rounded-lg mb-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-blue-700">Мой прогресс:</span>
              <span class="font-bold text-blue-900">{{ getMyTeamProgress(team) }}%</span>
            </div>
            <div class="w-full bg-blue-200 rounded-full h-2">
              <div
                class="bg-gradient-to-r from-blue-600 to-blue-400 h-2 rounded-full"
                :style="{ width: getMyTeamProgress(team) + '%' }"
              ></div>
            </div>
          </div>

          <div class="flex gap-2">
            <button
              @click.stop="selectTeam(team)"
              class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-semibold"
            >
              Посмотреть детали
            </button>
            <button
              v-if="isTeamCreator(team)"
              @click.stop="deleteTeam(team)"
              class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition"
            >
              🗑️
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Все остальные команды (Рейтинг) -->
    <div v-if="otherTeams.length > 0" class="space-y-4 mt-8">
      <div class="flex items-center gap-2 pb-3 border-b-2 border-yellow-600">
        <span class="text-xl">🏆</span>
        <h3 class="text-xl font-bold text-gray-800">Рейтинг команд</h3>
      </div>
      
      <div class="space-y-3">
        <div
          v-for="(team, idx) in otherTeams"
          :key="'all-' + team.id"
          class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition cursor-pointer flex items-center gap-4"
          @click="selectTeam(team)"
        >
          <!-- Место в рейтинге -->
          <div class="flex-shrink-0">
            <span v-if="idx === 0" class="text-3xl">🥇</span>
            <span v-else-if="idx === 1" class="text-3xl">🥈</span>
            <span v-else-if="idx === 2" class="text-3xl">🥉</span>
            <span v-else class="text-2xl font-bold text-gray-500 w-8 h-8 flex items-center justify-center">{{ idx + 1 }}</span>
          </div>

          <!-- Информация команды -->
          <div class="flex-1">
            <div class="flex justify-between items-start mb-1">
              <h4 class="text-lg font-bold text-gray-800">{{ team.name }}</h4>
              <span class="text-xs font-bold bg-yellow-100 text-yellow-800 px-2 py-1 rounded">{{ team.total_score || 0 }} баллов</span>
            </div>
            <p class="text-sm text-gray-600 mb-2">{{ team.members_count }} участников</p>
            
            <!-- Прогресс -->
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-gradient-to-r from-yellow-400 to-orange-400 h-2 rounded-full"
                :style="{ width: team.progress_percent + '%' }"
              ></div>
            </div>
            <p class="text-xs text-gray-600 mt-1">{{ team.progress_percent }}% завершено</p>
          </div>

          <!-- Кнопки -->
          <div class="flex gap-1 flex-shrink-0">
            <button
              v-if="!isInTeam(team)"
              @click.stop="joinTeam(team)"
              class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm font-semibold"
            >
              Присоединиться
            </button>
            <span v-else class="bg-green-100 text-green-800 px-4 py-2 rounded text-sm font-semibold">
              ✓ Участник
            </span>
            <button
              v-if="authStore.isAdmin"
              @click.stop="deleteTeam(team)"
              class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition text-sm"
            >
              Удалить
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Детали команды -->
    <div v-if="selectedTeam" class="mt-8">
      <TeamProgress :team="selectedTeam" @back="selectedTeam = null" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import TeamProgress from './TeamProgress.vue';
import axios from 'axios';

const authStore = useAuthStore();

// Состояние
const teams = ref([]); // Все команды
const myTeamsList = ref([]); // Мои команды
const selectedTeam = ref(null);
const loading = ref(false);
const error = ref('');
const showCreateForm = ref(false);
const creatingTeam = ref(false);
const newTeam = ref({ name: '', description: '' });

// Вычисляемые свойства
const myTeams = computed(() => {
  return teams.value.filter(t => myTeamsList.value.some(mt => mt.id === t.id));
});

const otherTeams = computed(() => {
  return teams.value
    .filter(t => !myTeamsList.value.some(mt => mt.id === t.id))
    .sort((a, b) => (b.total_score || 0) - (a.total_score || 0));
});

// Методы
const loadTeams = async () => {
  loading.value = true;
  error.value = '';
  try {
    // Загружаем мои команды
    const myResponse = await axios.get('/api/users/teams', {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    myTeamsList.value = myResponse.data.data || [];

    // Загружаем рейтинг всех команд
    const allResponse = await axios.get('/api/teams/rankings', {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    teams.value = allResponse.data.data || [];
  } catch (err) {
    error.value = 'Не удалось загрузить команды';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const createTeam = async () => {
  creatingTeam.value = true;
  try {
    const response = await axios.post('/api/teams', newTeam.value, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    teams.value.push(response.data.data);
    myTeamsList.value.push(response.data.data);
    newTeam.value = { name: '', description: '' };
    showCreateForm.value = false;
  } catch (err) {
    error.value = 'Ошибка при создании команды';
    console.error(err);
  } finally {
    creatingTeam.value = false;
  }
};

const joinTeam = async (team) => {
  try {
    await axios.post(`/api/teams/${team.id}/join`, {}, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    myTeamsList.value.push(team);
    error.value = '';
  } catch (err) {
    error.value = 'Ошибка при присоединении к команде';
    console.error(err);
  }
};

const deleteTeam = async (team) => {
  if (!confirm('Вы уверены? Эта команда будет удалена безвозвратно.')) return;
  try {
    await axios.delete(`/api/teams/${team.id}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    teams.value = teams.value.filter(t => t.id !== team.id);
    myTeamsList.value = myTeamsList.value.filter(t => t.id !== team.id);
    error.value = '';
  } catch (err) {
    error.value = 'Ошибка при удалении команды';
    console.error(err);
  }
};

const selectTeam = (team) => {
  selectedTeam.value = team;
};

const isInTeam = (team) => {
  return myTeamsList.value.some(t => t.id === team.id);
};

const isTeamCreator = (team) => {
  return team.created_by === authStore.user?.id || authStore.isAdmin;
};

const getMyTeamProgress = (team) => {
  return team.progress_percent || 0;
};

onMounted(() => {
  loadTeams();
});
</script>
