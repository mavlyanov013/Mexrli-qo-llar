<template>
    <form class="space-y-4" @submit.prevent="submit">
        <AdminFormField :label="t('admin.name')">
            <input v-model="form.name" class="h-10 w-full rounded-lg border border-gray-200 px-3 text-sm" required />
        </AdminFormField>

        <AdminFormField :label="t('admin.email')">
            <input v-model="form.email" type="email" class="h-10 w-full rounded-lg border border-gray-200 px-3 text-sm" required />
        </AdminFormField>

        <AdminFormField :label="isEdit ? t('admin.usersModule.passwordOptional') : 'Password'">
            <input v-model="form.password" type="password" class="h-10 w-full rounded-lg border border-gray-200 px-3 text-sm" :required="!isEdit" />
        </AdminFormField>

        <AdminFormField :label="t('admin.role')">
            <select v-model="form.role" class="h-10 w-full rounded-lg border border-gray-200 px-3 text-sm">
                <option value="super_admin">super_admin</option>
                <option value="admin">admin</option>
                <option value="editor">editor</option>
                <option value="finance">finance</option>
            </select>
        </AdminFormField>

        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-[#2A7DE1] px-4 py-2 text-sm text-white">{{ t('admin.save') }}</button>
            <router-link to="/admin/users" class="rounded-lg border border-gray-200 px-4 py-2 text-sm">{{ t('admin.cancel') }}</router-link>
        </div>
    </form>
</template>

<script setup>
import { reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import AdminFormField from '@/admin/components/common/AdminFormField.vue'

const props = defineProps({
    initialValues: {
        type: Object,
        default: () => ({}),
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['submit'])
const { t } = useI18n()

const form = reactive({
    name: props.initialValues.name || '',
    email: props.initialValues.email || '',
    password: '',
    role: props.initialValues.role || 'editor',
})

const submit = () => {
    const payload = { ...form }
    if (props.isEdit && !payload.password) {
        delete payload.password
    }
    emit('submit', payload)
}
</script>
