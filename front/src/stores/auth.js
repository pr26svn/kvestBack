import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('authToken') || null);
  const isAuthenticated = computed(() => !!token.value);

  const login = async (email, password) => {
    try {
      const response = await fetch('/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password }),
      });

      if (!response.ok) throw new Error('Неверные учетные данные');

      const data = await response.json();
      token.value = data.token;
      user.value = data.user;
      localStorage.setItem('authToken', data.token);
      
      return data;
    } catch (error) {
      console.error('Ошибка при входе:', error);
      throw error;
    }
  };

  const logout = () => {
    token.value = null;
    user.value = null;
    localStorage.removeItem('authToken');
  };

  const isAdmin = computed(() => user.value?.role === 'admin');

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    login,
    logout,
  };
});
