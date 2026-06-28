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
