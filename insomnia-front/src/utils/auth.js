/**
 * Auth Utility — Tidur Nyenyak
 * Simulasi autentikasi menggunakan localStorage.
 * Siap diganti dengan API calls ke Laravel backend nanti.
 */

const AUTH_KEY = 'tidurnyenyak_user';
const TOKEN_KEY = 'tidurnyenyak_token';

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

/** Logout — hapus semua data auth */
export function logout() {
  localStorage.removeItem(AUTH_KEY);
  localStorage.removeItem(TOKEN_KEY);
}

/**
 * Simulasi Register
 * Nanti ganti dengan: POST /api/register
 */
export function register({ username, email, password }) {
  // Simpan user data ke localStorage
  const userData = {
    id: Date.now(),
    username,
    email,
    created_at: new Date().toISOString(),
  };
  const fakeToken = 'sim_' + btoa(email + ':' + Date.now());

  setUser(userData);
  setToken(fakeToken);

  return { success: true, user: userData, token: fakeToken };
}

/**
 * Simulasi Login
 * Nanti ganti dengan: POST /api/login
 */
export function login({ email, password }) {
  // Untuk simulasi, login selalu berhasil
  const userData = {
    id: Date.now(),
    username: email.split('@')[0],
    email,
    created_at: new Date().toISOString(),
  };
  const fakeToken = 'sim_' + btoa(email + ':' + Date.now());

  setUser(userData);
  setToken(fakeToken);

  return { success: true, user: userData, token: fakeToken };
}
