<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import OffsetPaginationControls from '../components/OffsetPaginationControls.vue';
import { fetchAllCards } from '../services/cardService';

const route = useRoute();
const router = useRouter();

const cards = ref([]);
const loadingCards = ref(true);
const errorMessage = ref('');
const pagination = ref({
    items: [],
    offset: 0,
    limit: 10,
    total: 0,
    hasMore: false,
});

async function loadCards() {
    loadingCards.value = true;
    errorMessage.value = '';

    try {
        const response = await fetchAllCards({
            offset: pagination.value.offset,
            limit: pagination.value.limit,
        });
        cards.value = response.items;
        pagination.value = response;
    } catch (error) {
        errorMessage.value = 'Le chargement des cartes a échoué.';
        cards.value = [];
    } finally {
        loadingCards.value = false;
    }
}

async function goToOffset(offset) {
    pagination.value = {
        ...pagination.value,
        offset,
    };

    await router.replace({
        name: 'all-cards',
        query: offset > 0 ? { offset: offset.toString() } : {},
    });

    await loadCards();
}

function parseOffset(queryOffset) {
    const parsedOffset = Number.parseInt(queryOffset ?? '', 10);
    return Number.isNaN(parsedOffset) || parsedOffset < 0 ? 0 : parsedOffset;
}

onMounted(() => {
    pagination.value = {
        ...pagination.value,
        offset: parseOffset(typeof route.query.offset === 'string' ? route.query.offset : ''),
    };
    loadCards();
});

</script>

<template>
    <div>
        <h1>Toutes les cartes</h1>
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else-if="errorMessage">{{ errorMessage }}</div>
        <div v-else>
            <div class="card-result" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} <span>({{ card.uuid }})</span>
                </router-link>
            </div>
            <OffsetPaginationControls :offset="pagination.offset" :limit="pagination.limit" :total="pagination.total"
                :loading="loadingCards" @previous="goToOffset(Math.max(0, pagination.offset - pagination.limit))"
                @next="goToOffset(pagination.offset + pagination.limit)" />
        </div>
    </div>
</template>
