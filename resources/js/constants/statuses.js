export const PAYMENT_STATUSES = {
    pending: { labelKey: 'common.status.pending', tone: 'warning' },
    success: { labelKey: 'common.status.success', tone: 'success' },
    completed: { labelKey: 'common.status.success', tone: 'success' },
    failed: { labelKey: 'common.status.failed', tone: 'danger' },
    cancelled: { labelKey: 'common.status.cancelled', tone: 'danger' },
    funded: { labelKey: 'common.status.funded', tone: 'info' },
}

export const DONATION_STATUSES = {
    pending: { labelKey: 'common.status.pending', tone: 'warning' },
    success: { labelKey: 'common.status.success', tone: 'success' },
    failed: { labelKey: 'common.status.failed', tone: 'danger' },
}

export const VOLUNTEER_STATUSES = {
    new: { labelKey: 'common.status.new', tone: 'info' },
    reviewed: { labelKey: 'common.status.reviewed', tone: 'warning' },
    accepted: { labelKey: 'common.status.accepted', tone: 'success' },
    rejected: { labelKey: 'common.status.rejected', tone: 'danger' },
}
