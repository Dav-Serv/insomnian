/**
 * Auth Utility — Tidur Nyenyak
 * Mengelola state autentikasi dan memanggil API backend Laravel.
 */

import { apiRegister, apiLogin, apiLogout } from './api.js';

const AUTH_KEY = 'tidurnyenyak_user';
const TOKEN_KEY = 'tidurnyenyak_token';

// ==========================================
// 📦 LOCAL STORAGE HELPERS
// ==========================================

/** Cek apakah user sudah login */
export function isLoggedIn() {
  return localStorage.getItem(TOKEN_KEY) !== null;
}

/** Ambil data user yang tersimpan */
export function getUser() {
  const data = localStorage.getItem(AUTH_KEY);
  return data ? JSON.parse(data) : null;
}

/** Simpan data user */
export function setUser(userData) {
  localStorage.setItem(AUTH_KEY, JSON.stringify(userData));
}

/** Ambil token */
export function getToken() {
  return localStorage.getItem(TOKEN_KEY);
}

/** Simpan token */
export function setToken(token) {
  localStorage.setItem(TOKEN_KEY, token);
}

/** Hapus semua data auth dari localStorage */
function clearAuth() {
  localStorage.removeItem(AUTH_KEY);
  localStorage.removeItem(TOKEN_KEY);
}

// ==========================================
// 🔐 AUTH ACTIONS (memanggil API)
// ==========================================

/**
 * Register user baru via API
 */
export async function register({ username, email, password, photo = null }) {
  const formData = new FormData();
  formData.append('name', username);
  formData.append('email', email);
  formData.append('password', password);
  if (photo) {
    formData.append('photo', photo);
  }

  const result = await apiRegister(formData);

  if (result.ok) {
    const user = result.data.user;
    const token = result.data.access_token;

    setUser({
      id: user.id,
      username: user.name,
      email: user.email,
      photo: user.photo,
    });
    setToken(token);

    return { success: true };
  } else {
    const errors = result.data.errors;
    let message = result.data.message || 'Registrasi gagal.';

    if (errors) {
      const firstKey = Object.keys(errors)[0];
      if (firstKey && errors[firstKey].length > 0) {
        message = errors[firstKey][0];
      }
    }

    return { success: false, message };
  }
}

/**
 * Login user via API
 */
export async function login({ email, password }) {
  const result = await apiLogin({ email, password });

  if (result.ok) {
    const user = result.data.user;
    const token = result.data.access_token;

    setUser({
      id: user.id,
      username: user.name,
      email: user.email,
      photo: user.photo,
    });
    setToken(token);

    return { success: true };
  } else {
    const message = result.data.message || 'Email atau kata sandi salah.';
    return { success: false, message };
  }
}

/**
 * Logout user
 */
export async function logout() {
  const token = getToken();

  if (token) {
    await apiLogout(token).catch(() => {});
  }

  clearAuth();
}
