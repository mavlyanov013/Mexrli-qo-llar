<template>
  <span ref="elementRef" class="text-3xl md:text-4xl font-bold text-gray-900">
    {{ prefix }}{{ formattedValue }}{{ suffix }}
  </span>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { formatAmount } from '@/utils/formatAmount'

const props = defineProps({
    target: {
        type: Number,
        required: true,
    },
    prefix: {
        type: String,
        default: '',
    },
    suffix: {
        type: String,
        default: '',
    },
})

const displayValue = ref(0)
const formattedValue = computed(() => formatAmount(displayValue.value))
const elementRef = ref(null)
const isVisible = ref(false)

let observer = null
let animationFrame = null

const startAnimation = () => {
    let start = 0
    const duration = 2000

    const step = (timestamp) => {
        if (!start) start = timestamp

        const progress = Math.min((timestamp - start) / duration, 1)
        const eased = 1 - Math.pow(1 - progress, 3)

        displayValue.value = Math.floor(eased * props.target)

        if (progress < 1) {
            animationFrame = requestAnimationFrame(step)
        }
    }

    animationFrame = requestAnimationFrame(step)
}

watch(isVisible, (value) => {
    if (value) startAnimation()
})

onMounted(() => {
    observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                isVisible.value = true
            }
        },
        { threshold: 0.3 }
    )

    if (elementRef.value) {
        observer.observe(elementRef.value)
    }
})

onBeforeUnmount(() => {
    if (observer) observer.disconnect()
    if (animationFrame) cancelAnimationFrame(animationFrame)
})
</script>
