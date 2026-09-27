import axios from 'axios';

const publicApi = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export const authApi = {
  forgotPassword: (email) =>
    publicApi.post('/forgot-password', { email }).then((r) => r.data),
  resetPassword: (data) =>
    publicApi.post('/reset-password', data).then((r) => r.data),
};