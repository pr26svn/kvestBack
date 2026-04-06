<template>
  <div class="min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg border-b-4 border-blue-600">
      <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            🎯 Платформа квеста наставничества
          </h1>
        </div>
        <div class="flex items-center gap-6">
          <div class="text-sm">
            <p class="font-semibold text-gray-800">{{ authStore.user?.name }}</p>
            <p class="text-gray-600">{{ authStore.user?.email }}</p>
          </div>
          <div class="flex items-center gap-2">
            <span v-if="authStore.isAdmin" class="badge bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-bold">
              🔑 Администратор
            </span>
            <button
              @click="logout"
              class="btn-danger py-2"
            >
              Выход
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Основной контент -->
    <main class="max-w-7xl mx-auto px-4 py-8">
      <!-- Табы навигации -->
      <div class="flex gap-4 mb-8 border-b border-gray-200">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'px-6 py-3 font-semibold transition border-b-2',
            activeTab === tab.id
              ? 'text-blue-600 border-blue-600'
              : 'text-gray-600 border-transparent hover:text-gray-800'
          ]"
        >
          {{ tab.name }}
        </button>
      </div>

      <!-- Содержимое табов -->
      <div class="min-h-96">
        <!-- Участник -->
        <div v-if="activeTab === 'user'" class="space-y-6">
          <div v-if="!selectedStage">
            <StageList @select-stage="openStage" />
          </div>
          <div v-else>
            <button
              @click="selectedStage = null"
              class="mb-4 text-blue-600 hover:text-blue-800 font-semibold"
            >
              ← Назад к этапам
            </button>
            <StageDetail :stage="selectedStage" :userId="authStore.user?.id" @back="selectedStage = null" />
          </div>
        </div>

        <!-- Команды -->
        <div v-if="activeTab === 'teams'">
          <TeamsList />
        </div>

        <!-- Админ панель (закрыто без авторизации) -->
        <div v-if="activeTab === 'admin'">
          <div v-if="!authStore.isAdmin" class="bg-red-100 border-l-4 border-red-600 p-6 rounded-lg">
            <h3 class="text-lg font-bold text-red-800 mb-2">⚠️ Доступ запрещен</h3>
            <p class="text-red-700">У вас нет прав администратора для доступа к этому разделу.</p>
          </div>
          <div v-else>
            <AdminPanel />
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import StageList from './StageList.vue';
import StageDetail from './StageDetail.vue';
import TeamsList from './TeamsList.vue';
import AdminPanel from './AdminPanel.vue';

const authStore = useAuthStore();
const router = useRouter();

const activeTab = ref('user');
const selectedStage = ref(null);

const tabs = [
  { id: 'user', name: '📚 Этапы и задания' },
  { id: 'teams', name: '👥 Мои команды' },
  ...(authStore.isAdmin ? [{ id: 'admin', name: '⚙️ Администрирование' }] : []),
];

const openStage = (stage) => {
  selectedStage.value = stage;
};

const logout = () => {
  authStore.logout();
  router.push('/login');
};
</script>
