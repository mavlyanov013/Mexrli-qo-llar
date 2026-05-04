<template>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        class="text-left p-4"
                    >
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="row in rows"
                    :key="row[rowKey]"
                    class="border-t"
                >
                    <td
                        v-for="column in columns"
                        :key="`${row[rowKey]}-${column.key}`"
                        class="p-4"
                    >
                        <slot
                            :name="`cell-${column.key}`"
                            :row="row"
                            :value="row[column.key]"
                        >
                            {{ row[column.key] }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    columns: {
        type: Array,
        default: () => [],
    },
    rows: {
        type: Array,
        default: () => [],
    },
    rowKey: {
        type: String,
        default: 'id',
    },
})
</script>
