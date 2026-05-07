export const PAYMENT_STATUSES = {
    pending: { labelKey: 'common.status.pending', tone: 'warning' },
    success: { labelKey: 'common.status.success', tone: 'success' },
    completed: { labelKey: 'common.status.success', tone: 'success' },
    failed: { labelKey: 'common.status.failed', tone: 'danger' },
    cancelled: { labelKey: 'common.status.cancelled', tone: 'danger' },
    funded: { labelKey: 'common.status.funded', tone: 'info' },
}

export const CASE_STATUSES = {
    draft: { labelKey: 'common.status.draft', tone: 'info' },
    active: { labelKey: 'common.status.active', tone: 'success' },
    paused: { labelKey: 'common.status.paused', tone: 'warning' },
    completed: { labelKey: 'common.status.completed', tone: 'success' },
    closed: { labelKey: 'common.status.closed', tone: 'danger' },
}

export const DONATION_STATUSES = {
    pending: { labelKey: 'common.status.pending', tone: 'warning' },
    success: { labelKey: 'common.status.success', tone: 'success' },
    completed: { labelKey: 'common.status.success', tone: 'success' },
    failed: { labelKey: 'common.status.failed', tone: 'danger' },
}

export const VOLUNTEER_STATUSES = {
    new: { labelKey: 'common.status.new', tone: 'info' },
    reviewed: { labelKey: 'common.status.reviewed', tone: 'warning' },
    accepted: { labelKey: 'common.status.accepted', tone: 'success' },
    rejected: { labelKey: 'common.status.rejected', tone: 'danger' },
}

export const MESSAGE_STATUSES = {
    new: { labelKey: 'common.status.new', tone: 'info' },
    read: { labelKey: 'common.status.read', tone: 'warning' },
    replied: { labelKey: 'common.status.replied', tone: 'success' },
}

export const CASE_STATUS_OPTIONS = ['draft','active','paused','completed','closed']
export const PAYMENT_STATUS_OPTIONS = ['pending', 'success', 'failed', 'cancelled', 'completed']
export const VOLUNTEER_STATUS_OPTIONS = ['new', 'reviewed', 'accepted', 'rejected']
