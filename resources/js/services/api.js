import axios from 'axios';

/**
 * HospitALL API Service
 * Base URL is controlled via VITE_API_BASE_URL environment variable.
 * Set it in your .env file:
 *   VITE_API_BASE_URL=http://localhost:8000/api
 */
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor — attach Bearer token if available
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('hospitall_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor — handle common errors gracefully
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired — clear and redirect to login in the future
      localStorage.removeItem('hospitall_token');
    }
    return Promise.reject(error);
  }
);

export default api;
