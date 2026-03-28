<template>
  <div class="app-shell">
    <header class="app-header">
      <h1>Платформа квеста «Стратегия успешного наставничества»</h1>
      <p>Фронтенд на Vue для управления этапами, заданиями и прогрессом.</p>
    </header>

    <main>
      <section class="app-tabs">
        <button :class="{ active: view === 'user' }" @click="view = 'user'">Участник</button>
        <button :class="{ active: view === 'admin' }" @click="view = 'admin'">Админ</button>
      </section>

      <section>
        <template v-if="view === 'user'">
          <template v-if="!selectedStage">
            <h2>Этапы квеста</h2>
            <StageList @select-stage="openStage" />
          </template>

          <template v-else>
            <StageDetail :stage="selectedStage" :userId="currentUserId" @back="selectedStage = null" />
          </template>
        </template>

        <template v-else>
          <AdminPanel />
        </template>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import StageList from './components/StageList.vue';
import StageDetail from './components/StageDetail.vue';
import AdminPanel from './components/AdminPanel.vue';

const view = ref('user');
const selectedStage = ref(null);
const currentUserId = 1;

const openStage = (stage) => {
  selectedStage.value = stage;
};
</script>

<style>
body {
  margin: 0;
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #f4f6fb;
}

.app-shell {
  max-width: 1000px;
  margin: 0 auto;
  padding: 24px;
}

.app-header {
  background: #ffffff;
  border-radius: 18px;
  padding: 24px;
  box-shadow: 0 16px 48px rgba(15, 23, 42, 0.08);
}

h1 {
  margin: 0 0 8px;
  font-size: 2rem;
}

h2 {
  margin-top: 32px;
}

.app-tabs {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
}

.app-tabs button {
  border: 1px solid #cbd5e1;
  background: #ffffff;
  border-radius: 999px;
  padding: 10px 18px;
  cursor: pointer;
  color: #334155;
}

.app-tabs button.active {
  background: #2563eb;
  color: white;
  border-color: #2563eb;
}
</style>
