<script setup>
import { computed } from 'vue';

const props = defineProps({
    offset: {
        type: Number,
        required: true,
    },
    limit: {
        type: Number,
        required: true,
    },
    total: {
        type: Number,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['previous', 'next']);

const currentPage = computed(() => Math.floor(props.offset / props.limit) + 1);
const totalPages = computed(() => Math.max(1, Math.ceil(props.total / props.limit)));
const hasPrevious = computed(() => props.offset > 0);
const hasNext = computed(() => props.offset + props.limit < props.total);
</script>

<template>
    <div v-if="total > 0" class="pagination-controls">
        <p class="pagination-summary">
            Page {{ currentPage }} / {{ totalPages }} <span>({{ total }} résultats)</span>
        </p>
        <div class="pagination-actions">
            <button
                type="button"
                class="pagination-button"
                :disabled="loading || !hasPrevious"
                @click="emit('previous')">
                Précédent
            </button>
            <button
                type="button"
                class="pagination-button"
                :disabled="loading || !hasNext"
                @click="emit('next')">
                Suivant
            </button>
        </div>
    </div>
</template>

<style scoped>
.pagination-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-top: 24px;
}

.pagination-summary {
    margin: 0;
}

.pagination-actions {
    display: flex;
    gap: 8px;
}

.pagination-button {
    padding: 10px 16px;
    cursor: pointer;
}
</style>
