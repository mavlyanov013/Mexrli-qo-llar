<template>
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <SectionHeader title="How It Works" />

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div
                    v-for="(step, index) in steps"
                    :key="index"
                    class="text-center relative"
                >
                    <div
                        v-if="index < 3"
                        class="hidden md:block absolute top-8 left-[60%] w-[80%] h-px border-t-2 border-dashed border-gray-200"
                    />

                    <div
                        :class="[
              'w-16 h-16 mx-auto mb-5 rounded-2xl flex items-center justify-center relative z-10 text-2xl',
              step.color
            ]"
                    >
                        {{ step.icon }}
                    </div>

                    <div class="w-8 h-8 mx-auto -mt-3 mb-3 rounded-full bg-white border-2 border-gray-200 flex items-center justify-center text-sm font-bold text-gray-400 relative z-10">
                        {{ index + 1 }}
                    </div>

                    <h3 class="font-bold text-gray-900 mb-2">
                        {{ step.title }}
                    </h3>

                    <p class="text-sm text-gray-500 leading-relaxed">
                        {{ step.desc }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import SectionHeader from '../shared/SectionHeader.vue'

const steps = [
    {
        icon: '📄',
        title: 'Apply for Support',
        desc: 'Families submit an application with medical documents and their story.',
        color: 'bg-blue-50 text-[#2A7DE1]',
    },
    {
        icon: '🛡',
        title: 'Verification',
        desc: 'Our team verifies all documents and medical records thoroughly.',
        color: 'bg-green-50 text-[#4CAF50]',
    },
    {
        icon: '📣',
        title: 'Fundraising',
        desc: 'We launch a transparent campaign and connect with donors.',
        color: 'bg-orange-50 text-[#FF9800]',
    },
    {
        icon: '❤',
        title: 'Treatment',
        desc: 'Funds are directed to medical facilities for treatment.',
        color: 'bg-purple-50 text-purple-600',
    },
]
</script>
