import api from './api'

export const getFinancialReports = async () => {
    const response = await api.get('/financial-reports')
    return response.data
}

export const getLatestFinancialReport = async () => {
    const response = await api.get('/financial-reports/latest')
    return response.data
}

export const getFinancialReportById = async (id) => {
    const response = await api.get(`/financial-reports/${id}`)
    return response.data
}
