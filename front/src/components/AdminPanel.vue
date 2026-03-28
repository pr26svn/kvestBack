<template>
  <div class="admin-panel">
    <div class="admin-top">
      <div>
        <h2>Админ панель</h2>
        <p>Управление этапами и заданиями внутри этапов.</p>
      </div>
      <button class="refresh-button" @click="loadStages">Обновить список</button>
    </div>

    <div class="admin-grid">
      <section class="stage-list-panel">
        <div class="panel-header">
          <h3>Этапы</h3>
          <button @click="openNewStage">Новый этап</button>
        </div>

        <div v-if="loadingStages" class="loading">Загрузка этапов...</div>
        <div v-else-if="stageError" class="error">{{ stageError }}</div>

        <ul v-else class="stage-list">
          <li
            v-for="stage in stages"
            :key="stage.id"
            :class="['stage-item', { selected: selectedStage && selectedStage.id === stage.id } ]"
          >
            <button class="stage-link" @click="selectStage(stage)">{{ stage.title }}</button>
            <div class="stage-actions">
              <button class="small" @click="editStage(stage)">Изменить</button>
              <button class="small danger" @click="deleteStage(stage)">Удалить</button>
            </div>
          </li>
        </ul>
      </section>

      <section class="editor-panel">
        <div class="editor-section">
          <h3>{{ stageForm.id ? 'Редактировать этап' : 'Новый этап' }}</h3>
          <form @submit.prevent="saveStage">
            <label>
              Код этапа
              <input v-model="stageForm.code" type="text" placeholder="stage-1" required />
            </label>
            <label>
              Название
              <input v-model="stageForm.title" type="text" placeholder="Название этапа" required />
            </label>
            <label>
              Описание
              <textarea v-model="stageForm.description" rows="3" placeholder="Описание этапа"></textarea>
            </label>
            <div class="row">
              <label>
                Порядок
                <input v-model.number="stageForm.order" type="number" min="0" />
              </label>
              <label>
                Тип
                <input v-model="stageForm.stage_type" type="text" placeholder="online" />
              </label>
            </div>
            <label>
              Дедлайн
              <input v-model="stageForm.deadline_at" type="date" />
            </label>
            <button type="submit" :disabled="savingStage">{{ savingStage ? 'Сохранение...' : 'Сохранить этап' }}</button>
          </form>
        </div>

        <div class="editor-section" v-if="selectedStage">
          <div class="panel-header">
            <div>
              <h3>Задания этапа «{{ selectedStage.title }}»</h3>
              <p>Управляйте заданиями для выбранного этапа.</p>
            </div>
            <button @click="openNewTask">Новое задание</button>
          </div>

          <div v-if="loadingTasks" class="loading">Загрузка заданий...</div>
          <div v-else-if="taskError" class="error">{{ taskError }}</div>

          <ul v-else class="task-list">
            <li v-for="task in tasks" :key="task.id" class="task-card">
              <div class="task-row">
                <strong>{{ task.order || '—' }}. {{ task.title }}</strong>
                <div class="task-actions">
                  <button class="small" @click="editTask(task)">Изменить</button>
                  <button class="small danger" @click="deleteTask(task)">Удалить</button>
                </div>
              </div>
              <p>{{ task.description }}</p>
            </li>
          </ul>

          <div class="task-form" v-if="selectedStage">
            <h4>{{ taskForm.id ? 'Редактировать задание' : 'Новое задание' }}</h4>
            <form @submit.prevent="saveTask">
              <label>
                Название
                <input v-model="taskForm.title" type="text" placeholder="Название задания" required />
              </label>
              <label>
                Описание
                <textarea v-model="taskForm.description" rows="3" placeholder="Описание задания"></textarea>
              </label>
              <div class="row">
                <label>
                  Тип
                  <select v-model="taskForm.task_type">
                    <option value="essay">Открытый ответ</option>
                    <option value="single_choice">Тест: один вариант</option>
                    <option value="multiple_choice">Тест: несколько вариантов</option>
                  </select>
                </label>
                <label>
                  Сложность
                  <input v-model="taskForm.difficulty" type="text" placeholder="easy" />
                </label>
              </div>
              <div class="row">
                <label>
                  Порядок
                  <input v-model.number="taskForm.order" type="number" min="0" />
                </label>
                <label>
                  Балл
                  <input v-model.number="taskForm.max_score" type="number" min="0" />
                </label>
              </div>
              <label class="checkbox-row">
                <input type="checkbox" v-model="taskForm.required" /> Обязательное
              </label>

              <div v-if="taskForm.task_type === 'single_choice' || taskForm.task_type === 'multiple_choice'" class="choices-section">
                <h4>Варианты ответа</h4>
                <div v-for="(choice, index) in taskForm.payload.choices" :key="choice.value" class="choice-row">
                  <input v-model="choice.label" placeholder="Текст варианта" required />
                  <button type="button" class="small danger" @click="removeChoice(index)">Удалить</button>
                </div>
                <button type="button" class="small" @click="addChoice">Добавить вариант</button>
              </div>
              <button type="submit" :disabled="savingTask">{{ savingTask ? 'Сохранение...' : 'Сохранить задание' }}</button>
            </form>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import axios from 'axios';

const stages = ref([]);
const selectedStage = ref(null);
const tasks = ref([]);
const loadingStages = ref(false);
const loadingTasks = ref(false);
const savingStage = ref(false);
const savingTask = ref(false);
const stageError = ref(null);
const taskError = ref(null);

const stageForm = reactive({
  id: null,
  code: '',
  title: '',
  description: '',
  order: 0,
  stage_type: '',
  deadline_at: '',
});

const selectedTask = ref(null);

const taskForm = reactive({
  id: null,
  quest_stage_id: null,
  title: '',
  description: '',
  task_type: 'essay',
  difficulty: '',
  order: 0,
  max_score: 0,
  required: false,
  payload: { choices: [] },
});

const resetStageForm = () => {
  stageForm.id = null;
  stageForm.code = '';
  stageForm.title = '';
  stageForm.description = '';
  stageForm.order = 0;
  stageForm.stage_type = '';
  stageForm.deadline_at = '';
};

const resetTaskForm = () => {
  taskForm.id = null;
  taskForm.quest_stage_id = selectedStage?.value?.id || null;
  taskForm.title = '';
  taskForm.description = '';
  taskForm.task_type = 'essay';
  taskForm.difficulty = '';
  taskForm.order = 0;
  taskForm.max_score = 0;
  taskForm.required = false;
  taskForm.payload = { choices: [] };
};

const addChoice = () => {
  taskForm.payload.choices.push({ label: '', value: `choice-${Date.now()}-${taskForm.payload.choices.length}` });
};

const removeChoice = (index) => {
  taskForm.payload.choices.splice(index, 1);
};

const loadStages = async () => {
  loadingStages.value = true;
  stageError.value = null;

  try {
    const response = await axios.get('/api/stages');
    stages.value = response.data.data;
  } catch (err) {
    stageError.value = 'Не удалось загрузить этапы.';
  } finally {
    loadingStages.value = false;
  }
};

const selectStage = (stage) => {
  selectedStage.value = stage;
  resetTaskForm();
};

const openNewStage = () => {
  selectedStage.value = null;
  resetStageForm();
};

const editStage = (stage) => {
  selectedStage.value = stage;
  stageForm.id = stage.id;
  stageForm.code = stage.code || '';
  stageForm.title = stage.title || '';
  stageForm.description = stage.description || '';
  stageForm.order = stage.order ?? 0;
  stageForm.stage_type = stage.stage_type || '';
  stageForm.deadline_at = stage.deadline_at ? stage.deadline_at.slice(0, 10) : '';
};

const saveStage = async () => {
  savingStage.value = true;
  stageError.value = null;

  try {
    const payload = {
      code: stageForm.code,
      title: stageForm.title,
      description: stageForm.description,
      order: stageForm.order,
      stage_type: stageForm.stage_type,
      deadline_at: stageForm.deadline_at || null,
    };

    let response;

    if (stageForm.id) {
      response = await axios.put(`/api/admin/stages/${stageForm.id}`, payload);
    } else {
      response = await axios.post('/api/admin/stages', payload);
    }

    await loadStages();
    const updated = response.data.data;
    selectedStage.value = updated;
    stageForm.id = updated.id;
  } catch (err) {
    stageError.value = 'Не удалось сохранить этап. Проверьте данные и повторите.';
  } finally {
    savingStage.value = false;
  }
};

const deleteStage = async (stage) => {
  if (!confirm(`Удалить этап «${stage.title}»? Это удалит все связанные задания.`)) {
    return;
  }

  try {
    await axios.delete(`/api/admin/stages/${stage.id}`);
    if (selectedStage.value && selectedStage.value.id === stage.id) {
      selectedStage.value = null;
      resetStageForm();
      tasks.value = [];
    }
    await loadStages();
  } catch (err) {
    stageError.value = 'Не удалось удалить этап.';
  }
};

const loadTasks = async () => {
  if (!selectedStage.value) {
    tasks.value = [];
    return;
  }

  loadingTasks.value = true;
  taskError.value = null;

  try {
    const response = await axios.get(`/api/stages/${selectedStage.value.id}/tasks`);
    tasks.value = response.data.data;
  } catch (err) {
    taskError.value = 'Не удалось загрузить задания.';
  } finally {
    loadingTasks.value = false;
  }
};

watch(selectedStage, loadTasks);

const openNewTask = () => {
  if (!selectedStage.value) {
    taskError.value = 'Выберите сначала этап.';
    return;
  }
  selectedTask.value = null;
  resetTaskForm();
};

const editTask = (task) => {
  selectedTask.value = task;
  taskForm.id = task.id;
  taskForm.quest_stage_id = task.quest_stage_id;
  taskForm.title = task.title || '';
  taskForm.description = task.description || '';
  taskForm.task_type = task.task_type || 'essay';
  taskForm.difficulty = task.difficulty || '';
  taskForm.order = task.order ?? 0;
  taskForm.max_score = task.max_score ?? 0;
  taskForm.required = task.required ?? false;
  taskForm.payload = task.payload || { choices: [] };
};

const saveTask = async () => {
  if (!selectedStage.value) {
    taskError.value = 'Выберите этап перед сохранением задания.';
    return;
  }

  savingTask.value = true;
  taskError.value = null;

  try {
    const payload = {
      quest_stage_id: selectedStage.value.id,
      title: taskForm.title,
      description: taskForm.description,
      task_type: taskForm.task_type,
      difficulty: taskForm.difficulty,
      order: taskForm.order,
      max_score: taskForm.max_score,
      required: taskForm.required,
      payload: null,
    };

    if (taskForm.task_type === 'single_choice' || taskForm.task_type === 'multiple_choice') {
      payload.payload = {
        choices: taskForm.payload.choices
          .filter((choice) => choice.label && choice.label.trim())
          .map((choice, index) => ({
            label: choice.label.trim(),
            value: choice.value || `choice-${index + 1}`,
          })),
      };
    }

    if (taskForm.id) {
      await axios.put(`/api/admin/tasks/${taskForm.id}`, payload);
    } else {
      await axios.post('/api/admin/tasks', payload);
    }

    await loadTasks();
    resetTaskForm();
  } catch (err) {
    taskError.value = 'Не удалось сохранить задание. Проверьте данные.';
  } finally {
    savingTask.value = false;
  }
};

const deleteTask = async (task) => {
  if (!confirm(`Удалить задание «${task.title}»?`)) {
    return;
  }

  try {
    await axios.delete(`/api/admin/tasks/${task.id}`);
    await loadTasks();
  } catch (err) {
    taskError.value = 'Не удалось удалить задание.';
  }
};

loadStages();
</script>

<style scoped>
.admin-panel {
  width: 100%;
}

.admin-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.admin-grid {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 22px;
}

.stage-list-panel,
.editor-panel {
  background: #ffffff;
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.stage-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.stage-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  margin-bottom: 10px;
}

.stage-item.selected {
  border-color: #2563eb;
  background: #eff6ff;
}

.stage-link {
  background: transparent;
  border: none;
  text-align: left;
  width: 100%;
  cursor: pointer;
  font-size: 1rem;
  color: #111827;
}

.stage-actions {
  display: flex;
  gap: 8px;
}

.small {
  font-size: 0.85rem;
  padding: 6px 12px;
  background: #f3f4f6;
  border: 1px solid transparent;
  border-radius: 10px;
  cursor: pointer;
}

.small.danger {
  background: #fee2e2;
  color: #b91c1c;
}

.task-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.task-card {
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 14px;
  margin-bottom: 10px;
}

.task-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.task-actions {
  display: flex;
  gap: 8px;
}

.editor-section {
  margin-bottom: 24px;
}

label {
  display: block;
  margin-bottom: 12px;
  font-size: 0.95rem;
  color: #334155;
}

input,
textarea {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 12px;
  padding: 10px 12px;
  margin-top: 6px;
  font-size: 0.95rem;
}

.row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.checkbox-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

button[type="submit"],
.refresh-button {
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 12px 18px;
  cursor: pointer;
}

.loading,
.error {
  color: #475569;
}

.error {
  color: #b91c1c;
}
</style>
