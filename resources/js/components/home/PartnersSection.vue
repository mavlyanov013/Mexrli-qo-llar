<template>
    <section class="py-16 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <SectionHeader
                title="Trusted Partners"
                subtitle="Organizations that stand with us"
            />

            <div class="flex flex-wrap justify-center items-center gap-8 md:gap-12">
                <div
                    v-for="partner in partners"
                    :key="partner.id"
                    class="group cursor-pointer"
                >
                    <a
                        v-if="partner.website"
                        :href="partner.website"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <template v-if="partner.logo_url">
                            <img
                                :src="partner.logo_url"
                                :alt="partner.name"
                                class="h-10 md:h-12 object-contain grayscale group-hover:grayscale-0 opacity-50 group-hover:opacity-100 transition-all duration-300"
                            />
                        </template>

                        <template v-else>
                            <div class="px-6 py-3 bg-white rounded-xl border border-gray-200 text-sm font-semibold text-gray-400 group-hover:text-[#2A7DE1] group-hover:border-blue-200 transition-all">
                                {{ partner.name }}
                            </div>
                        </template>
                    </a>

                    <template v-else>
                        <template v-if="partner.logo_url">
                            <img
                                :src="partner.logo_url"
                                :alt="partner.name"
                                class="h-10 md:h-12 object-contain grayscale group-hover:grayscale-0 opacity-50 group-hover:opacity-100 transition-all duration-300"
                            />
                        </template>

                        <template v-else>
                            <div class="px-6 py-3 bg-white rounded-xl border border-gray-200 text-sm font-semibold text-gray-400 group-hover:text-[#2A7DE1] group-hover:border-blue-200 transition-all">
                                {{ partner.name }}
                            </div>
                        </template>
                    </template>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import SectionHeader from '../shared/SectionHeader.vue'

defineProps({
    partners: {
        type: Array,
        default: () => [],
    },
})
</script>
