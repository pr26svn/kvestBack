<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-800">Команды</h2>
        <p class="text-gray-600">Выполняйте задания вместе с командой</p>
      </div>
      <button
        v-if="authStore.isAdmin"
        @click="showCreateForm = !showCreateForm"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
      >
        + Создать команду
      </button>
    </div>

    <div v-if="showCreateForm && authStore.isAdmin" class="bg-white p-6 rounded-lg shadow-lg">
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
            {{ creatingTeam ? 'Создание...' : 'Создать' }}
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

    <div v-if="loading" class="text-center py-8">
      <p class="text-gray-600">Загрузка команд...</p>
    </div>

    <div v-else-if="error" class="bg-red-100 text-red-700 p-4 rounded-lg">
      {{ error }}
    </div>

    <div v-else-if="teams.length === 0" class="text-center py-8">
      <p class="text-gray-600">Команд еще нет</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div
        v-for="team in teams"
        :key="team.id"
        class="bg-white p-6 rounded-lg shadow-lg cursor-pointer hover:shadow-xl transition"
        @click="selectTeam(team)"
      >
        <div class="flex justify-between items-start mb-3">
          <div>
            <h3 class="text-xl font-semibold text-gray-800">{{ team.name }}</h3>
            <p class="text-sm text-gray-600">{{ team.members_count }} участников</p>
          </div>
          <span class="text-2xl">👥</span>
        </div>
        <p class="text-gray-700 mb-4">{{ team.description }}</p>
        <div class="flex gap-2">
          <button
            @click.stop="joinTeam(team)"
            class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition"
          >
            Присоединиться
          </button>
          <button
            v-if="authStore.isAdmin"
            @click.stop="deleteTeam(team)"
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition"
          >
            Удалить
          </button>
        </div>
      </div>
    </div>

    <div v-if="selectedTeam" class="mt-8">
      <TeamProgress :team="selectedTeam" @back="selectedTeam = null" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import TeamProgress from './TeamProgress.vue';
import axios from 'axios';

const authStore = useAuthStore();
const teams = ref([]);
const selectedTeam = ref(null);
const loading = ref(false);
const error = ref('');
const showCreateForm = ref(false);
const creatingTeam = ref(false);
const newTeam = ref({ name: '', description: '' });

const loadTeams = async () => {
  loading.value = true;
  error.value = '';
  try {
    const response = await axios.get('/api/teams', {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    teams.value = response.data.data;
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
    newTeam.value = { name: '', description: '' };
    showCreateForm.value = false;
  } catch (err) {
    error.value = 'Ошибка при создании команды';
  } finally {
    creatingTeam.value = false;
  }
};

const joinTeam = async (team) => {
  try {
    await axios.post(`/api/teams/${team.id}/join`, {}, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    loadTeams();
  } catch (err) {
    error.value = 'Ошибка при присоединении к команде';
  }
};

const deleteTeam = async (team) => {
  if (!confirm('Вы уверены?')) return;
  try {
    await axios.delete(`/api/teams/${team.id}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    teams.value = teams.value.filter(t => t.id !== team.id);
  } catch (err) {
    error.value = 'Ошибка при удалении команды';
  }
};

const selectTeam = (team) => {
  selectedTeam.value = team;
};

onMounted(() => {
  loadTeams();
});
</script>
