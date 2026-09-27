import api from './axios';

export const invoicesApi = {
  list: () => api.get('/invoices').then((r) => r.data),
  get: (id) => api.get(`/invoices/${id}`).then((r) => r.data),
  create: (data) => api.post('/invoices', data).then((r) => r.data),
  update: (id, data) => api.put(`/invoices/${id}`, data).then((r) => r.data),
  delete: (id) => api.delete(`/invoices/${id}`).then((r) => r.data),
  markPaid: (id) =>
    api.post(`/invoices/${id}/mark-paid`).then((r) => r.data),
  downloadPdf: async (id, number) => {
    const response = await api.get(`/invoices/${id}/pdf`, {
      responseType: 'blob',
    });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `${number}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  },
};