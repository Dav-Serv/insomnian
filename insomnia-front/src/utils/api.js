/**
 * API Service — Tidur Nyenyak
 * Template untuk menghubungkan frontend Astro ke backend Laravel.
 */

const BASE_URL = 'http://127.0.0.1:8000/api';

const ENDPOINTS = {
  register: '/register',
  login:    '/login',
  logout:   '/logout',
  user:     '/user',
  account:  '/account',
  updateAccount: '/account/update',
  soundscapes: '/soundscapes',
  favorites: '/favorites',
  home: '/home',
  mockSleep: '/home/mock-sleep',
  diary: '/diary',
  diaryTambah: '/diary-tambah',
  tools: '/tools',
  toolsCategory: '/tools/category',
};

export async function apiCall(endpoint, { method = 'GET', body = null, token = null } = {}) {
  const url = `${BASE_URL}${endpoint}`;

  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const config = {
    method,
    headers,
  };

  if (body && method !== 'GET') {
    config.body = JSON.stringify(body);
  }

  try {
    const response = await fetch(url, config);
    const data = await response.json();

    return {
      ok: response.ok,
      status: response.status,
      data,
    };
  } catch (error) {
    return {
      ok: false,
      status: 0,
      data: {
        message: 'Tidak dapat terhubung ke server. Pastikan backend sedang berjalan.',
        error: error.message,
      },
    };
  }
}

export async function apiCallFormData(endpoint, formData, token = null) {
  const url = `${BASE_URL}${endpoint}`;
  
  const headers = {
    'Accept': 'application/json',
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers,
      body: formData,
    });
    const data = await response.json();

    return {
      ok: response.ok,
      status: response.status,
      data,
    };
  } catch (error) {
    return {
      ok: false,
      status: 0,
      data: {
        message: 'Tidak dapat terhubung ke server. Pastikan backend sedang berjalan.',
        error: error.message,
      },
    };
  }
}

export async function apiRegister(formData) {
  return apiCallFormData(ENDPOINTS.register, formData);
}

export async function apiLogin({ email, password }) {
  return apiCall(ENDPOINTS.login, {
    method: 'POST',
    body: { email, password },
  });
}

export async function apiLogout(token) {
  return apiCall(ENDPOINTS.logout, {
    method: 'POST',
    token,
  });
}

export async function apiGetUser(token) {
  return apiCall(ENDPOINTS.user, {
    method: 'GET',
    token,
  });
}

export async function apiGetProfile(token) {
  return apiCall(ENDPOINTS.account, {
    method: 'GET',
    token,
  });
}

export async function apiUpdateProfile(formData, token) {
  return apiCallFormData(ENDPOINTS.updateAccount, formData, token);
}

export async function apiGetSoundscapes(category = 'all', token = null) {
  let endpoint = ENDPOINTS.soundscapes;
  if (category && category !== 'all') {
    endpoint += `?category=${category}`;
  }
  return apiCall(endpoint, {
    method: 'GET',
    token,
  });
}

export async function apiToggleFavorite(id, token) {
  return apiCall(`/soundscapes/${id}/favorite`, {
    method: 'POST',
    token,
  });
}

export async function apiGetFavorites(token) {
  return apiCall(ENDPOINTS.favorites, {
    method: 'GET',
    token,
  });
}

export async function apiGetHome(token) {
  return apiCall(ENDPOINTS.home, {
    method: 'GET',
    token,
  });
}

export async function apiGenerateMockSleep(token) {
  return apiCall(ENDPOINTS.mockSleep, {
    method: 'POST',
    token,
  });
}

export async function apiGetDiary(token) {
  return apiCall(ENDPOINTS.diary, {
    method: 'GET',
    token,
  });
}

export async function apiAddDiary(body, token) {
  return apiCall(ENDPOINTS.diaryTambah, {
    method: 'POST',
    body,
    token,
  });
}

export async function apiGetTools(token) {
  return apiCall(ENDPOINTS.tools, {
    method: 'GET',
    token,
  });
}

export async function apiGetToolsByCategory(slug, token) {
  return apiCall(`${ENDPOINTS.toolsCategory}/${slug}`, {
    method: 'GET',
    token,
  });
}

export async function apiGetToolDetail(id, token) {
  return apiCall(`${ENDPOINTS.tools}/${id}`, {
    method: 'GET',
    token,
  });
}

// ==========================================
// 👑 ADMIN API CALLS
// ==========================================

export async function apiGetAdminUsers(token, page = 1) {
  return apiCall(`/admin/users?page=${page}`, {
    method: 'GET',
    token,
  });
}

export async function apiCreateAdminUser(body, token) {
  return apiCall('/admin/users', {
    method: 'POST',
    body,
    token,
  });
}

export async function apiUpdateAdminUser(id, body, token) {
  return apiCall(`/admin/users/${id}`, {
    method: 'PUT',
    body,
    token,
  });
}

export async function apiDeleteAdminUser(id, token) {
  return apiCall(`/admin/users/${id}`, {
    method: 'DELETE',
    token,
  });
}

export async function apiGetAdminSoundscapes(token, page = 1) {
  return apiCall(`/admin/soundscapes?page=${page}`, {
    method: 'GET',
    token,
  });
}

export async function apiCreateAdminSoundscape(body, token) {
  return apiCall('/admin/soundscapes', {
    method: 'POST',
    body,
    token,
  });
}

export async function apiUpdateAdminSoundscape(id, body, token) {
  return apiCall(`/admin/soundscapes/${id}`, {
    method: 'PUT',
    body,
    token,
  });
}

export async function apiDeleteAdminSoundscape(id, token) {
  return apiCall(`/admin/soundscapes/${id}`, {
    method: 'DELETE',
    token,
  });
}



