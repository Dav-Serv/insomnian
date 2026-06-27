/**
 * API Service — Tidur Nyenyak
 * Template untuk menghubungkan frontend Astro ke backend Laravel.
 *
 * ============================================================
 *  CARA PAKAI:
 *  1. Ganti BASE_URL dengan URL backend Laravel kamu
 *  2. Sesuaikan endpoint di ENDPOINTS jika berbeda
 *  3. Sesuaikan field response di auth.js jika format JSON berbeda
 * ============================================================
 */

// ==========================================
// ⚙️ KONFIGURASI — GANTI SESUAI BACKEND KAMU
// ==========================================

/** Base URL dari backend Laravel */
const BASE_URL = 'http://127.0.0.1:8000/api';
//                ^^^^^^^^^^^^^^^^^^^^^^^^
//                Ganti dengan URL backend kamu.
//                Contoh production: 'https://api.tidurnyenyak.com/api'

/** Daftar endpoint yang digunakan */
const ENDPOINTS = {
  register: '/register',    // POST — { name, email, password, password_confirmation }
  login:    '/login',       // POST — { email, password }
  logout:   '/logout',      // POST — (dengan Authorization header)
  user:     '/user',        // GET  — ambil data user yang sedang login
};
//
// Sesuaikan path endpoint di atas jika backend kamu menggunakan
// path yang berbeda, contoh: '/auth/register', '/auth/login', dll.

// ==========================================
// 🔧 HELPER — TIDAK PERLU DIUBAH
// ==========================================

/**
 * Fungsi utama untuk memanggil API.
 *
 * @param {string}  endpoint  - Path endpoint (dari ENDPOINTS)
 * @param {object}  options   - Opsi tambahan
 * @param {'GET'|'POST'|'PUT'|'DELETE'} options.method - HTTP method
 * @param {object}  [options.body]    - Request body (akan di-JSON.stringify)
 * @param {string}  [options.token]   - Bearer token untuk Authorization header
 * @returns {Promise<{ok: boolean, status: number, data: any}>}
 */
export async function apiCall(endpoint, { method = 'GET', body = null, token = null } = {}) {
  const url = `${BASE_URL}${endpoint}`;

  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  // Tambahkan Authorization header jika token tersedia
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
    // Network error / server tidak bisa dihubungi
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

/**
 * Fungsi khusus untuk memanggil API dengan FormData (untuk upload file/photo).
 *
 * @param {string}  endpoint  - Path endpoint
 * @param {FormData} formData - Data form berisi berkas
 * @param {string}  [token]   - Bearer token
 * @returns {Promise<{ok: boolean, status: number, data: any}>}
 */
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

// ==========================================
// 📡 AUTH API CALLS
// ==========================================

/**
 * Register user baru (Mendukung upload foto opsional via FormData)
 *
 * @param {FormData} formData - Form data berisi name, email, password, dan photo
 * @returns {Promise<{ok: boolean, status: number, data: any}>}
 */
export async function apiRegister(formData) {
  return apiCallFormData(ENDPOINTS.register, formData);
}

/**
 * Login user
 *
 * @param {object} params
 * @param {string} params.email    - Alamat email
 * @param {string} params.password - Kata sandi
 * @returns {Promise<{ok: boolean, status: number, data: any}>}
 *
 * Response yang diharapkan dari Laravel (jika berhasil):
 * {
 *   "user": { "id": 1, "name": "...", "email": "..." },
 *   "token": "1|xxxxxxxx..."
 * }
 *
 * Response jika gagal:
 * {
 *   "message": "Email atau kata sandi salah."
 * }
 */
export async function apiLogin({ email, password }) {
  return apiCall(ENDPOINTS.login, {
    method: 'POST',
    body: { email, password },
  });
}

/**
 * Logout user (revoke token di backend)
 *
 * @param {string} token - Bearer token
 * @returns {Promise<{ok: boolean, status: number, data: any}>}
 */
export async function apiLogout(token) {
  return apiCall(ENDPOINTS.logout, {
    method: 'POST',
    token,
  });
}

/**
 * Ambil data user yang sedang login
 *
 * @param {string} token - Bearer token
 * @returns {Promise<{ok: boolean, status: number, data: any}>}
 *
 * Response yang diharapkan:
 * { "id": 1, "name": "...", "email": "...", ... }
 */
export async function apiGetUser(token) {
  return apiCall(ENDPOINTS.user, {
    method: 'GET',
    token,
  });
}
