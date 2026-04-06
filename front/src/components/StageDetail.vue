<template>
  <div class="space-y-6">
    <!-- Заголовок -->
    <div>
      <button
        @click="$emit('back')"
        class="flex items-center text-blue-600 hover:text-blue-800 font-semibold mb-4"
      >
        ← Назад к этапам
      </button>

      <div class="bg-white p-8 rounded-lg shadow-lg">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h2 class="text-4xl font-bold text-gray-800 mb-2">{{ stage.title }}</h2>
            <p class="text-gray-600 text-lg">{{ stage.description }}</p>
          </div>
          <span class="text-5xl">🎯</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200">
          <div>
            <p class="text-sm text-gray-600">Тип раздела</p>
            <p class="text-lg font-bold text-blue-600">{{ stage.stage_type || 'online' }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Всего задач</p>
            <p class="text-lg font-bold text-purple-600">{{ tasks.length }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Завершено</p>
            <p class="text-lg font-bold text-green-600">{{ completedCount }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Дедлайн</p>
            <p class="text-lg font-bold text-orange-600">
              {{ stage.deadline_at ? new Date(stage.deadline_at).toLocaleDateString('ru-RU') : '—' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Прогресс -->
    <div class="bg-white p-6 rounded-lg shadow-lg">
      <div class="flex justify-between items-center mb-3">
        <h3 class="text-lg font-bold text-gray-800">Ваш прогресс</h3>
        <span class="text-2xl font-bold text-blue-600">{{ progressPercent }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-3">
        <div
          class="bg-gradient-to-r from-blue-600 to-purple-600 h-3 rounded-full transition-all duration-300"
          :style="{ width: progressPercent + '%' }"
        ></div>
      </div>
    </div>

    <!-- Загрузка -->
    <div v-if="loading" class="bg-blue-100 border-l-4 border-blue-600 p-6 rounded-lg">
      <p class="text-blue-800 font-semibold">⏳ Загрузка задач...</p>
    </div>

    <!-- Ошибка -->
    <div v-else-if="error" class="bg-red-100 border-l-4 border-red-600 p-6 rounded-lg">
      <p class="text-red-800 font-semibold">❌ {{ error }}</p>
    </div>

    <!-- Все задачи завершены -->
    <div v-else-if="completedCount === tasks.length" class="bg-green-100 border-l-4 border-green-600 p-8 rounded-lg text-center">
      <p class="text-3xl mb-2">🎉</p>
      <p class="text-green-800 font-bold text-lg">Отлично! Вы завершили все задания этапа</p>
      <p class="text-green-700 mt-2">Ожидайте оценки от экспертов</p>
    </div>

    <!-- Задачи -->
    <div v-else class="space-y-6">
      <!-- Список всех задач -->
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <h3 class="text-xl font-bold text-gray-800 mb-4">📋 Все задачи этапа</h3>
        <div class="space-y-3">
          <div
            v-for="task in tasks"
            :key="task.id"
            :class="[
              'p-4 rounded-lg border-2 cursor-pointer transition',
              getTaskClass(task)
            ]"
            @click="activeTaskId = task.id"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <p class="font-bold text-sm">
                  {{ task.order }}. {{ task.title }}
                </p>
                <p class="text-sm text-gray-600 mt-1">{{ task.description }}</p>
              </div>
              <span :class="['px-3 py-1 rounded-full text-xs font-bold', getTaskBadgeClass(task)]">
                {{ getTaskLabel(task) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Активная задача (для заполнения) -->
      <div v-if="activeTask" class="bg-white p-8 rounded-lg shadow-lg border-2 border-blue-600">
        <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ activeTask.title }}</h3>
        <p class="text-gray-600 mb-6">{{ activeTask.description }}</p>

        <!-- Таблица или текст ответа -->
        <div class="space-y-4 mb-6">
          <!-- Single Choice -->
          <div v-if="isSingleChoice" class="space-y-3">
            <p class="font-semibold text-gray-700">Выберите правильный ответ:</p>
            <div v-for="choice in choices" :key="choice.value" class="flex items-center">
              <input
                type="radio"
                :id="'choice-' + choice.value"
                :value="choice.value"
                v-model="selectedOption"
                class="w-4 h-4 text-blue-600 cursor-pointer"
              />
              <label
                :for="'choice-' + choice.value"
                class="ml-3 cursor-pointer flex-1 p-3 border border-gray-200 rounded-lg hover:bg-blue-50 transition"
              >
                {{ choice.label || choice.value }}
              </label>
            </div>
          </div>

          <!-- Multiple Choice -->
          <div v-else-if="isMultipleChoice" class="space-y-3">
            <p class="font-semibold text-gray-700">Выберите все верные ответы:</p>
            <div v-for="choice in choices" :key="choice.value" class="flex items-center">
              <input
                type="checkbox"
                :id="'checkbox-' + choice.value"
                :value="choice.value"
                :checked="selectedOptions.includes(choice.value)"
                @change="toggleChoice(choice.value)"
                class="w-4 h-4 text-blue-600 cursor-pointer"
              />
              <label
                :for="'checkbox-' + choice.value"
                class="ml-3 cursor-pointer flex-1 p-3 border border-gray-200 rounded-lg hover:bg-blue-50 transition"
              >
                {{ choice.label || choice.value }}
              </label>
            </div>
          </div>

          <!-- Text Answer -->
          <div v-else class="space-y-2">
            <p class="font-semibold text-gray-700">Введите ваш ответ:</p>
            <textarea
              v-model="answer"
              placeholder="Напишите ваше решение или комментарий..."
              rows="6"
              class="input-field"
            ></textarea>
          </div>
        </div>

        <!-- Кнопка отправки -->
        <button
          @click="submitTask"
          :disabled="submitting || !canSubmit"
          class="w-full btn-primary py-3 text-lg font-bold disabled:opacity-60"
        >
          {{ submitting ? '⏳ Отправка...' : '✓ Отправить ответ' }}
        </button>

        <p class="text-sm text-gray-600 mt-3 text-center">
          💡 После отправки ваш ответ отправится на проверку
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  stage: { type: Object, required: true },
  userId: { type: Number, default: 1 },
});

const emit = defineEmits(['back']);

const tasks = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);
const activeTaskId = ref(null);
const answer = ref('');
const selectedOption = ref('');
const selectedOptions = ref([]);

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
    activeTaskId.value = null;
    answer.value = '';
    selectedOption.value = '';
    selectedOptions.value = [];
  } catch (err) {
    error.value = 'Не удалось загрузить задачи этапа.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

watch(() => props.stage.id, loadTasks, { immediate: true });

const completedCount = computed(() => tasks.value.filter(t => t.completed).length);
const progressPercent = computed(() => {
  return tasks.value.length > 0 ? Math.round((completedCount.value / tasks.value.length) * 100) : 0;
});

const activeTask = computed(() => {
  if (activeTaskId.value) {
    return tasks.value.find(t => t.id === activeTaskId.value);
  }
  return tasks.value.find(t => t.active) || null;
});

const isSingleChoice = computed(() => activeTask.value?.task_type === 'single_choice');
const isMultipleChoice = computed(() => activeTask.value?.task_type === 'multiple_choice');
const choices = computed(() => activeTask.value?.payload?.choices || []);

const canSubmit = computed(() => {
  if (!activeTask.value) return false;
  if (isSingleChoice.value) return !!selectedOption.value;
  if (isMultipleChoice.value) return selectedOptions.value.length > 0;
  return answer.value.trim().length > 0;
});

const getTaskClass = (task) => {
  if (task.completed) return 'bg-green-50 border-green-400 hover:bg-green-100';
  if (task.active) return 'bg-blue-50 border-blue-400 hover:bg-blue-100';
  return 'bg-gray-50 border-gray-300 opacity-60';
};

const getTaskBadgeClass = (task) => {
  if (task.completed) return 'bg-green-200 text-green-800';
  if (task.active) return 'bg-blue-200 text-blue-800';
  return 'bg-gray-200 text-gray-800';
};

const getTaskLabel = (task) => {
  if (task.completed) return '✓ Выполнено';
  if (task.active) return '→ Активная';
  return '🔒 Заблокирована';
};

const toggleChoice = (value) => {
  const idx = selectedOptions.value.indexOf(value);
  if (idx > -1) {
    selectedOptions.value.splice(idx, 1);
  } else {
    selectedOptions.value.push(value);
  }
};

const submitTask = async () => {
  if (!activeTask.value || !canSubmit.value) return;

  const payload = {
    quest_task_id: activeTask.value.id,
    user_id: props.userId,
    status: 'submitted',
    answer_text: null,
    answer_data: null,
  };

  if (isSingleChoice.value) {
    payload.answer_data = { selected: selectedOption.value };
    const choice = choices.value.find(c => c.value === selectedOption.value) || {};
    payload.answer_text = choice.label || choice.value;
  } else if (isMultipleChoice.value) {
    payload.answer_data = { selected: selectedOptions.value };
    payload.answer_text = selectedOptions.value.join(', ');
  } else {
    payload.answer_text = answer.value.trim();
    payload.answer_data = { response: answer.value.trim() };
  }

  submitting.value = true;

  try {
    await axios.post('/api/submissions', payload);
    answer.value = '';
    selectedOption.value = '';
    selectedOptions.value = [];
    await loadTasks();
  } catch (err) {
    error.value = 'Не удалось отправить решение. Попробуйте еще раз.';
    console.error(err);
  } finally {
    submitting.value = false;
  }
};
</script>
</style>
