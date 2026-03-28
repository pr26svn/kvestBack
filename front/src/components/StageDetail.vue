<template>
  <div class="stage-detail">
    <button class="back-button" @click="$emit('back')">← Назад к этапам</button>

    <header class="stage-header">
      <h2>{{ stage.title }}</h2>
      <p>{{ stage.description }}</p>
      <p class="meta">Тип: {{ stage.stage_type }} · Дата начала: {{ stage.start_at || '—' }} · Дедлайн: {{ stage.deadline_at || 'нет' }}</p>
    </header>

    <section class="task-summary">
      <p>Всего задач: {{ tasks.length }}</p>
      <p>Выполнено: {{ completedCount }}</p>
      <p>Следующая доступная задача: {{ activeTask ? activeTask.title : 'Нет активных задач' }}</p>
    </section>

    <section v-if="loading" class="loading">Загрузка задач...</section>
    <section v-else-if="error" class="error">{{ error }}</section>

    <section v-else class="tasks-section">
      <h3>Задачи этапа</h3>
      <ul class="tasks-list">
        <li v-for="task in tasks" :key="task.id" :class="['task-item', { completed: task.completed, active: task.active, locked: task.locked }]">
          <div class="task-title">
            <strong>{{ task.order }}. {{ task.title }}</strong>
            <span class="status" :class="taskStatusClass(task)">{{ taskStatusLabel(task) }}</span>
          </div>
          <p>{{ task.description }}</p>
        </li>
      </ul>
    </section>

    <section v-if="activeTask" class="submission-panel">
      <h3>Текущая задача</h3>
      <p><strong>{{ activeTask.title }}</strong></p>
      <p>{{ activeTask.description }}</p>

      <div v-if="isSingleChoice || isMultipleChoice" class="choice-panel">
        <p class="choice-hint">Выберите {{ isSingleChoice ? 'один' : 'одни или несколько' }} вариант{{ isMultipleChoice ? 'ов' : '' }}:</p>
        <ul class="choice-list">
          <li v-for="choice in choices" :key="choice.value" class="choice-item">
            <label>
              <input
                v-if="isSingleChoice"
                type="radio"
                :value="choice.value"
                v-model="selectedOption"
              />
              <input
                v-else
                type="checkbox"
                :value="choice.value"
                :checked="selectedOptions.includes(choice.value)"
                @change="toggleChoice(choice.value)"
              />
              <span>{{ choice.label || choice.value }}</span>
            </label>
          </li>
        </ul>
      </div>

      <div v-else>
        <textarea v-model="answer" placeholder="Напишите решение или комментарий" rows="6"></textarea>
      </div>

      <button
        @click="submitTask"
        :disabled="submitting || (isSingleChoice && !selectedOption) || (isMultipleChoice && selectedOptions.length === 0) || (!isSingleChoice && !isMultipleChoice && !answer.trim())"
      >
        {{ submitting ? 'Отправка...' : 'Отправить решение' }}
      </button>
      <p class="hint">После отправки прогресс сохранится, следующая задача станет доступной.</p>
    </section>

    <section v-else class="complete-note">
      <p>Все задания этого этапа выполнены.</p>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  stage: { type: Object, required: true },
  userId: { type: Number, default: 1 },
});

const tasks = ref([]);
const loading = ref(true);
const error = ref(null);
const answer = ref('');
const selectedOption = ref('');
const selectedOptions = ref([]);
const submitting = ref(false);

const loadTasks = async () => {
  if (!props.stage?.id) {
    tasks.value = [];
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const response = await axios.get(`/api/stages/${props.stage.id}/tasks`, {
      params: { userId: props.userId },
    });

    tasks.value = response.data.data || [];
    answer.value = '';
    selectedOption.value = '';
    selectedOptions.value = [];
  } catch (err) {
    error.value = 'Не удалось загрузить задачи этапа.';
  } finally {
    loading.value = false;
  }
};

watch(() => props.stage.id, loadTasks, { immediate: true });

const activeTask = computed(() => tasks.value.find((task) => task.active));
const completedCount = computed(() => tasks.value.filter((task) => task.completed).length);
const isSingleChoice = computed(() => activeTask.value?.task_type === 'single_choice');
const isMultipleChoice = computed(() => activeTask.value?.task_type === 'multiple_choice');
const choices = computed(() => activeTask.value?.payload?.choices || []);

const taskStatusLabel = (task) => {
  if (task.completed) return 'Выполнено';
  if (task.active) return 'Доступно сейчас';
  return 'Заблокировано';
};

const taskStatusClass = (task) => {
  if (task.completed) return 'status-completed';
  if (task.active) return 'status-active';
  return 'status-locked';
};

const choiceLabel = (choice) => choice.label || choice.value;

const toggleChoice = (value) => {
  if (selectedOptions.value.includes(value)) {
    selectedOptions.value = selectedOptions.value.filter((item) => item !== value);
  } else {
    selectedOptions.value.push(value);
  }
};

const submitTask = async () => {
  if (!activeTask.value) {
    return;
  }

  let payload = {
    quest_task_id: activeTask.value.id,
    user_id: props.userId,
    status: 'submitted',
    answer_text: null,
    answer_data: null,
  };

  if (isSingleChoice.value) {
    if (!selectedOption.value) {
      error.value = 'Выберите вариант ответа.';
      return;
    }

    payload.answer_data = { selected: selectedOption.value };
    payload.answer_text = choiceLabel(choices.value.find((choice) => choice.value === selectedOption.value) || {});
  } else if (isMultipleChoice.value) {
    if (selectedOptions.value.length === 0) {
      error.value = 'Выберите хотя бы один вариант ответа.';
      return;
    }

    payload.answer_data = { selected: selectedOptions.value };
    payload.answer_text = selectedOptions.value.join(', ');
  } else {
    if (!answer.value.trim()) {
      return;
    }

    payload.answer_text = answer.value.trim();
    payload.answer_data = { response: answer.value.trim() };
  }

  submitting.value = true;

  try {
    await axios.post('/api/submissions', payload);

    answer.value = '';
    await loadTasks();
  } catch (err) {
    error.value = 'Не удалось отправить решение. Попробуйте еще раз.';
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
.stage-detail {
  background: #ffffff;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
}

.back-button {
  border: none;
  background: transparent;
  color: #2563eb;
  cursor: pointer;
  font-size: 0.95rem;
  margin-bottom: 16px;
}

.stage-header {
  margin-bottom: 22px;
}

.meta {
  color: #6b7280;
  font-size: 0.95rem;
}

.task-summary {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}

.task-summary p,
.loading,
.error,
.complete-note {
  margin: 0;
}

.tasks-section {
  margin-bottom: 24px;
}

.tasks-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.task-item {
  padding: 16px;
  border-radius: 16px;
  margin-bottom: 12px;
  border: 1px solid #e5e7eb;
}

.task-item.completed {
  background: #ecfdf5;
  border-color: #6ee7b7;
}

.task-item.active {
  background: #eff6ff;
  border-color: #60a5fa;
}

.task-item.locked {
  opacity: 0.7;
}

.task-title {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
}

.status {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.8rem;
}

.status-completed {
  background: #dcfce7;
  color: #166534;
}

.status-active {
  background: #dbeafe;
  color: #1d4ed8;
}

.status-locked {
  background: #f3f4f6;
  color: #6b7280;
}

.submission-panel textarea {
  width: 100%;
  margin-top: 12px;
  padding: 14px;
  border-radius: 12px;
  border: 1px solid #d1d5db;
  font-size: 0.95rem;
}

.submission-panel button {
  margin-top: 12px;
  padding: 12px 18px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
}

.submission-panel button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.hint {
  margin-top: 10px;
  color: #6b7280;
}

.error {
  color: #b91c1c;
}
</style>
