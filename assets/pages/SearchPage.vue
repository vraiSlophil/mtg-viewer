<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { searchCardsByName } from '../services/cardService';

const route = useRoute();
const router = useRouter();

const searchTerm = ref('');
const cards = ref([]);
const loadingCards = ref(false);
const errorMessage = ref('');
const hasSearched = ref(false);

async function searchCards(name) {
    loadingCards.value = true;
    errorMessage.value = '';

    try {
        cards.value = await searchCardsByName(name);
        hasSearched.value = true;
    } catch (error) {
        cards.value = [];
        errorMessage.value = 'La recherche a échoué.';
        hasSearched.value = true;
    } finally {
        loadingCards.value = false;
    }
}

async function submitSearch() {
    const trimmedSearch = searchTerm.value.trim();

    if (trimmedSearch.length < 3) {
        errorMessage.value = 'Veuillez entrer au moins 3 caractères pour la recherche.';
        cards.value = [];
        hasSearched.value = false;
        return;
    }

    await router.replace({
        name: 'search-cards',
        query: trimmedSearch ? { name: trimmedSearch } : {},
    });

    await searchCards(trimmedSearch);
}

let debounceTimeout = null;
watch(searchTerm, () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        submitSearch();
    }, 500);
});

onMounted(async () => {
    const initialName = typeof route.query.name === 'string' ? route.query.name : '';

    searchTerm.value = initialName;

    if (!initialName) {
        return;
    }

    await searchCards(initialName);
});
</script>

<template>
    <div class="search-page">
        <h1>Rechercher une Carte</h1>
        <form class="search-form" @submit.prevent="submitSearch">
            <label class="search-form-label" for="card-name">Nom de la carte</label>
            <input
                id="card-name"
                v-model="searchTerm"
                class="search-form-input"
                type="search"
                name="name"
                placeholder="Ex: Lightning Bolt">
            <button class="search-form-submit" type="submit">Rechercher</button>
        </form>
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else-if="errorMessage">{{ errorMessage }}</div>
        <div v-else>
            <p v-if="hasSearched && cards.length === 0">Aucune carte trouvée.</p>
            <div v-for="card in cards" :key="card.uuid" class="card-result">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} <span>({{ card.uuid }})</span>
                </router-link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.search-page {
    margin-bottom: 24px;
}

.search-form {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: end;
}

.search-form-label {
    display: block;
    width: 100%;
    font-weight: 600;
}

.search-form-input {
    min-width: 280px;
    padding: 10px 12px;
}

.search-form-submit {
    padding: 10px 16px;
    cursor: pointer;
}
</style>
