<template>
  <div class="admin-panel">
    <div class="admin-top">
      <div>
        <h2>Админ панель</h2>
        <p>Управление этапами и заданиями внутри этапов.</p>
      </div>
      <button class="refresh-button" @click="loadStages">Обновить список</button>
    </div>

    <section class="user-panel">
      <div class="panel-header">
        <div>
          <h3>Пользователи</h3>
          <p>Создавайте новых участников и администраторов.</p>
        </div>
        <button class="small" @click="resetUserForm">Новый пользователь</button>
      </div>

      <div class="user-form-panel">
        <form @submit.prevent="saveUser" class="user-form">
          <h4>{{ userForm.id ? 'Редактировать пользователя' : 'Создать пользователя' }}</h4>
          <div class="row">
            <label>
              Имя
              <input v-model="userForm.name" type="text" placeholder="Имя" required />
            </label>
            <label>
              Email
              <input v-model="userForm.email" type="email" placeholder="Email" required />
            </label>
          </div>
          <div class="row">
            <label>
              Пароль {{ userForm.id ? '(оставьте пустым, чтобы не менять)' : '' }}
              <input v-model="userForm.password" type="password" :placeholder="userForm.id ? 'Новый пароль' : 'Пароль'" :required="!userForm.id" />
            </label>
            <label>
              Роль
              <select v-model="userForm.role">
                <option value="user">user</option>
                <option value="admin">admin</option>
              </select>
            </label>
          </div>
          <div class="row gap">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
              {{ savingUser ? 'Сохранение...' : (userForm.id ? 'Обновить пользователя' : 'Создать пользователя') }}
            </button>
            <button v-if="userForm.id" type="button" @click="resetUserForm" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
              Отмена
            </button>
            <span v-if="userError" class="text-red-600">{{ userError }}</span>
          </div>
        </form>
      </div>

      <div class="user-list-panel">
        <h4>Список пользователей</h4>
        <div v-if="loadingUsers" class="loading">Загрузка пользователей...</div>
        <ul v-else class="user-list">
          <li v-for="user in users" :key="user.id" class="user-item">
            <div>
              <strong>{{ user.name }}</strong> ({{ user.email }})
            </div>
            <div class="user-actions">
              <span class="badge">{{ user.role }}</span>
              <button class="small" @click="editUser(user)">Изменить</button>
              <button class="small danger" @click="deleteUser(user)">Удалить</button>
            </div>
          </li>
        </ul>
      </div>
    </section>

    <section class="team-management-panel">
      <div class="panel-header">
        <div>
          <h3>Управление командами</h3>
          <p>Добавляйте и удаляйте участников команд.</p>
        </div>
        <button class="small" @click="loadTeams">Обновить команды</button>
      </div>

      <div v-if="loadingTeams" class="loading">Загрузка команд...</div>
      <div v-else-if="teams.length === 0" class="text-center py-8">
        <p class="text-gray-600">Команд еще нет</p>
      </div>
      <div v-else class="teams-list">
        <div
          v-for="team in teams"
          :key="team.id"
          class="team-card"
        >
          <div class="team-header">
            <h4>{{ team.name }}</h4>
            <span class="member-count">{{ team.members_count }} участников</span>
            <button class="small" @click="toggleTeamMembers(team.id)">
              {{ teamMembers[team.id] ? 'Скрыть' : 'Показать' }} участников
            </button>
          </div>

          <div v-if="teamMembers[team.id]" class="team-members">
            <div v-if="loadingTeamMembers[team.id]" class="loading">Загрузка участников...</div>
            <div v-else>
              <div class="current-members">
                <h5>Текущие участники:</h5>
                <ul v-if="teamMembers[team.id].length > 0">
                  <li v-for="member in teamMembers[team.id]" :key="member.id" class="member-item">
                    {{ member.name }} ({{ member.email }})
                    <button class="small danger" @click="removeUserFromTeam(team.id, member.id)">Удалить</button>
                  </li>
                </ul>
                <p v-else class="text-gray-500">Нет участников</p>
              </div>

              <div class="add-member">
                <h5>Добавить участника:</h5>
                <select v-model="selectedUserForTeam[team.id]" class="member-select">
                  <option value="">Выберите пользователя</option>
                  <option
                    v-for="user in users"
                    :key="user.id"
                    :value="user.id"
                    :disabled="teamMembers[team.id]?.some(m => m.id === user.id)"
                  >
                    {{ user.name }} ({{ user.email }})
                  </option>
                </select>
                <button
                  class="small"
                  @click="addSelectedUserToTeam(team.id)"
                  :disabled="!selectedUserForTeam[team.id]"
                >
                  Добавить
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

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

      <!-- Управление контентом этапов -->
      <section class="content-management-panel">
        <div class="panel-header">
          <div>
            <h3>Контент этапов</h3>
            <p>Загрузите HTML контент для интерактивных этапов (1-5)</p>
          </div>
        </div>

        <div class="content-form-panel">
          <form @submit.prevent="saveStageContent" class="content-form">
            <h4>Редактировать контент этапа</h4>
            
            <label>
              Выберите этап
              <select v-model="contentForm.stageId" required @change="loadStageContent">
                <option value="">-- Выберите этап --</option>
                <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                  {{  stage.order }}. {{ stage.title }}
                </option>
              </select>
            </label>

            <label>
              HTML Контент
              <textarea 
                v-model="contentForm.html_content" 
                placeholder="Вставьте HTML контент для этапа" 
                rows="15"
                class="w-full font-mono text-sm"
              ></textarea>
              <small class="text-gray-600">Поддерживается полный HTML с styles и JavaScript</small>
            </label>

            <label>
              <input type="checkbox" v-model="contentForm.is_active" />
              Контент активен
            </label>

            <button type="submit" :disabled="savingContent">
              {{ savingContent ? 'Сохранение...' : 'Сохранить контент' }}
            </button>
            <button v-if="contentForm.stageId" type="button" @click="deleteStageContent" :disabled="savingContent" class="danger">
              Удалить контент
            </button>
          </form>
        </div>

        <div v-if="contentMessage" :class="['message', contentMessage.type]">
          {{ contentMessage.text }}
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { useAuthStore } from '../stores/auth';
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
const users = ref([]);
const loadingUsers = ref(false);
const savingUser = ref(false);
const userError = ref(null);
const teams = ref([]);
const loadingTeams = ref(false);
const teamMembers = ref({});
const loadingTeamMembers = ref({});
const userForm = reactive({
  id: null,
  name: '',
  email: '',
  password: '',
  role: 'user',
});
const selectedUserForTeam = ref({});
const authStore = useAuthStore();

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

const resetUserForm = () => {
  userForm.id = null;
  userForm.name = '';
  userForm.email = '';
  userForm.password = '';
  userForm.role = 'user';
};

const loadUsers = async () => {
  loadingUsers.value = true;
  userError.value = null;

  try {
    const response = await axios.get('/api/admin/users', {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    users.value = response.data.data;
  } catch (err) {
    userError.value = 'Не удалось загрузить пользователей.';
  } finally {
    loadingUsers.value = false;
  }
};

const loadTeams = async () => {
  loadingTeams.value = true;

  try {
    const response = await axios.get('/api/teams', {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    teams.value = response.data.data;
  } catch (err) {
    console.error('Не удалось загрузить команды:', err);
  } finally {
    loadingTeams.value = false;
  }
};

const loadTeamMembers = async (teamId) => {
  loadingTeamMembers.value[teamId] = true;

  try {
    const response = await axios.get(`/api/teams/${teamId}/members`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    teamMembers.value[teamId] = response.data.data;
  } catch (err) {
    console.error('Не удалось загрузить участников команды:', err);
  } finally {
    loadingTeamMembers.value[teamId] = false;
  }
};

const addUserToTeam = async (teamId, userId) => {
  try {
    // Используем тот же API, что и для присоединения, но для админа
    await axios.post(`/api/admin/teams/${teamId}/add-user`, { user_id: userId }, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    await loadTeamMembers(teamId);
  } catch (err) {
    console.error('Не удалось добавить пользователя в команду:', err);
  }
};

const removeUserFromTeam = async (teamId, userId) => {
  try {
    await axios.delete(`/api/admin/teams/${teamId}/remove-user/${userId}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    await loadTeamMembers(teamId);
  } catch (err) {
    console.error('Не удалось удалить пользователя из команды:', err);
  }
};

const saveUser = async () => {
  if (!authStore.isAdmin) {
    userError.value = 'У вас нет прав администратора.';
    return;
  }

  if (!authStore.token) {
    userError.value = 'Сессия истекла. Пожалуйста, войдите снова.';
    return;
  }

  savingUser.value = true;
  userError.value = null;

  try {
    const payload = {
      name: userForm.name,
      email: userForm.email,
      role: userForm.role,
    };

    // Добавляем пароль только если он указан (для редактирования)
    if (userForm.password) {
      payload.password = userForm.password;
    }

    if (userForm.id) {
      // Обновление пользователя
      await axios.put(`/api/admin/users/${userForm.id}`, payload, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      });
    } else {
      // Создание пользователя
      payload.password = userForm.password; // Пароль обязателен для создания
      await axios.post('/api/admin/users', payload, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      });
    }

    resetUserForm();
    await loadUsers();
  } catch (err) {
    if (err.response?.status === 401) {
      userError.value = 'Сессия истекла. Пожалуйста, войдите снова.';
      authStore.logout();
    } else if (err.response?.status === 403) {
      userError.value = 'У вас нет прав администратора.';
    } else {
      userError.value = userForm.id ? 'Не удалось обновить пользователя.' : 'Не удалось создать пользователя.';
    }
  } finally {
    savingUser.value = false;
  }
};

const editUser = (user) => {
  userForm.id = user.id;
  userForm.name = user.name;
  userForm.email = user.email;
  userForm.password = ''; // Не показываем пароль
  userForm.role = user.role;
};

const deleteUser = async (user) => {
  if (!confirm(`Удалить пользователя "${user.name}" (${user.email})?`)) {
    return;
  }

  try {
    await axios.delete(`/api/admin/users/${user.id}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    await loadUsers();
  } catch (err) {
    userError.value = 'Не удалось удалить пользователя.';
  }
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
    const response = await axios.get('/api/stages', {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
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
      response = await axios.put(`/api/admin/stages/${stageForm.id}`, payload, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      });
    } else {
      response = await axios.post('/api/admin/stages', payload, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      });
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
    await axios.delete(`/api/admin/stages/${stage.id}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
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
    const response = await axios.get(`/api/stages/${selectedStage.value.id}/tasks`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
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
      await axios.put(`/api/admin/tasks/${taskForm.id}`, payload, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      });
    } else {
      await axios.post('/api/admin/tasks', payload, {
        headers: { Authorization: `Bearer ${authStore.token}` }
      });
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
    await axios.delete(`/api/admin/tasks/${task.id}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    await loadTasks();
  } catch (err) {
    taskError.value = 'Не удалось удалить задание.';
  }
};

const toggleTeamMembers = async (teamId) => {
  if (teamMembers.value[teamId]) {
    // Скрываем участников
    teamMembers.value[teamId] = null;
  } else {
    // Загружаем участников
    await loadTeamMembers(teamId);
  }
};

const addSelectedUserToTeam = async (teamId) => {
  const userId = selectedUserForTeam.value[teamId];
  if (!userId) return;

  try {
    await axios.post(`/api/admin/teams/${teamId}/add-user`, {
      user_id: userId
    }, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });

    selectedUserForTeam.value[teamId] = '';
    await loadTeamMembers(teamId);
    await loadTeams(); // Обновляем счетчик участников
  } catch (err) {
    alert('Не удалось добавить пользователя в команду.');
  }
};

// Content Management
const contentForm = reactive({
  stageId: null,
  html_content: '',
  is_active: true,
});

const savingContent = ref(false);
const contentMessage = ref(null);

const resetContentForm = () => {
  contentForm.stageId = null;
  contentForm.html_content = '';
  contentForm.is_active = true;
  contentMessage.value = null;
};

const loadStageContent = async () => {
  if (!contentForm.stageId) return;

  try {
    const response = await axios.get(`/api/admin/stages/${contentForm.stageId}/content`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    
    if (response.data.data) {
      contentForm.html_content = response.data.data.html_content || '';
      contentForm.is_active = response.data.data.is_active !== false;
    } else {
      contentForm.html_content = '';
      contentForm.is_active = true;
    }
  } catch (err) {
    // Content not found - it's okay, show empty form
    contentForm.html_content = '';
    contentForm.is_active = true;
  }
};

const saveStageContent = async () => {
  if (!contentForm.stageId) {
    contentMessage.value = { type: 'error', text: 'Выберите этап' };
    return;
  }

  if (!contentForm.html_content.trim()) {
    contentMessage.value = { type: 'error', text: 'Контент не может быть пустым' };
    return;
  }

  savingContent.value = true;
  contentMessage.value = null;

  try {
    const response = await axios.post(
      `/api/admin/stages/${contentForm.stageId}/content`,
      {
        html_content: contentForm.html_content,
        content_type: 'html',
        is_active: contentForm.is_active,
      },
      {
        headers: { Authorization: `Bearer ${authStore.token}` }
      }
    );

    contentMessage.value = { type: 'success', text: 'Контент сохранен успешно!' };
    setTimeout(() => {
      contentMessage.value = null;
    }, 3000);
  } catch (err) {
    contentMessage.value = { type: 'error', text: 'Ошибка при сохранении контента' };
    console.error(err);
  } finally {
    savingContent.value = false;
  }
};

const deleteStageContent = async () => {
  if (!confirm('Вы уверены? Это удалит контент этапа.')) return;

  savingContent.value = true;

  try {
    await axios.delete(
      `/api/admin/stages/${contentForm.stageId}/content`,
      {
        headers: { Authorization: `Bearer ${authStore.token}` }
      }
    );

    contentMessage.value = { type: 'success', text: 'Контент удален!' };
    resetContentForm();
  } catch (err) {
    contentMessage.value = { type: 'error', text: 'Ошибка при удалении контента' };
    console.error(err);
  } finally {
    savingContent.value = false;
  }
};

loadStages();
loadUsers();
loadTeams();
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

.user-panel {
  background: #ffffff;
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
  margin-bottom: 22px;
}

.user-form-panel,
.user-list-panel {
  margin-top: 18px;
}

.user-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.user-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  margin-bottom: 10px;
}

.user-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-form label {
  display: block;
  margin-bottom: 12px;
}

.user-form input,
.user-form select {
  width: 100%;
  padding: 10px;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  margin-top: 6px;
}

.row.gap {
  display: flex;
  align-items: center;
  gap: 16px;
}

.badge {
  background: #e0f2fe;
  color: #0369a1;
  padding: 6px 12px;
  border-radius: 9999px;
  font-size: 0.9rem;
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

.team-management-panel {
  background: #ffffff;
  border-radius: 18px;
  padding: 22px;
  margin-bottom: 24px;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
}

.teams-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.team-card {
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 16px;
}

.team-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.team-header h4 {
  margin: 0;
  color: #111827;
}

.member-count {
  color: #6b7280;
  font-size: 0.9rem;
}

.team-members {
  border-top: 1px solid #e5e7eb;
  padding-top: 12px;
}

.current-members h5,
.add-member h5 {
  margin: 8px 0;
  color: #374151;
  font-size: 0.95rem;
}

.member-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #f9fafb;
  border-radius: 8px;
  margin-bottom: 6px;
}

.member-select {
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 8px 12px;
  margin-right: 8px;
  min-width: 200px;
}

.add-member {
  margin-top: 16px;
  padding-top: 12px;
  border-top: 1px solid #f3f4f6;
}

.content-management-panel {
  background: #ffffff;
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
  margin-bottom: 22px;
}

.content-form-panel {
  margin-top: 18px;
}

.content-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.content-form h4 {
  margin: 0 0 12px 0;
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
}

.content-form label {
  display: flex;
  flex-direction: column;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.content-form textarea {
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-family: 'Courier New', monospace;
  margin: 8px 0;
}

.content-form small {
  margin-top: 4px;
  font-size: 12px;
}

.message {
  padding: 12px 16px;
  border-radius: 8px;
  margin-top: 12px;
  font-size: 14px;
  font-weight: 500;
}

.message.success {
  background-color: #dcfce7;
  color: #166534;
  border: 1px solid #86efac;
}

.message.error {
  background-color: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}
</style>
