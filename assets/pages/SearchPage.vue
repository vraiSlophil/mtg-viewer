<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { fetchSetCodes, searchCards } from '../services/cardService';

const route = useRoute();
const router = useRouter();

const searchTerm = ref('');
const selectedSetCode = ref('');
const setCodes = ref([]);
const cards = ref([]);
const loadingCards = ref(false);
const loadingSetCodes = ref(false);
const errorMessage = ref('');
const hasSearched = ref(false);

async function loadSetCodes() {
    loadingSetCodes.value = true;

    try {
        setCodes.value = await fetchSetCodes();
    } catch (error) {
        errorMessage.value = 'Le chargement des extensions a échoué.';
    } finally {
        loadingSetCodes.value = false;
    }
}

async function loadCards() {
    const trimmedSearch = searchTerm.value.trim();
    const setCode = selectedSetCode.value;

    if (!setCode && trimmedSearch.length > 0 && trimmedSearch.length < 3) {
        errorMessage.value = 'Veuillez entrer au moins 3 caractères pour la recherche.';
        cards.value = [];
        hasSearched.value = false;
        return;
    }

    loadingCards.value = true;
    errorMessage.value = '';

    try {
        cards.value = await searchCards({
            name: trimmedSearch,
            setCode,
        });
        hasSearched.value = true;
    } catch (error) {
        cards.value = [];
        errorMessage.value = 'La recherche a échoué.';
        hasSearched.value = true;
    } finally {
        loadingCards.value = false;
    }
}

async function syncRouteAndSearch() {
    const trimmedSearch = searchTerm.value.trim();
    const setCode = selectedSetCode.value;

    const query = {};
    if (trimmedSearch) {
        query.name = trimmedSearch;
    }
    if (setCode) {
        query.setCode = setCode;
    }

    await router.replace({
        name: 'search-cards',
        query,
    });

    await loadCards();
}

let debounceTimeout = null;
watch([searchTerm, selectedSetCode], () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        syncRouteAndSearch();
    }, 500);
});

onMounted(async () => {
    await loadSetCodes();

    const initialName = typeof route.query.name === 'string' ? route.query.name : '';
    const initialSetCode = typeof route.query.setCode === 'string' ? route.query.setCode : '';

    searchTerm.value = initialName;
    selectedSetCode.value = initialSetCode;

    if (!initialName && !initialSetCode) {
        return;
    }

    await loadCards();
});
</script>

<template>
    <div class="search-page">
        <h1>Rechercher une Carte</h1>
        <form class="search-form" @submit.prevent="syncRouteAndSearch">
            <label class="search-form-label" for="card-name">Nom de la carte</label>
            <input
                id="card-name"
                v-model="searchTerm"
                class="search-form-input"
                type="search"
                name="name"
                placeholder="Ex: Lightning Bolt">
            <label class="search-form-label" for="card-set-code">Extension</label>
            <select
                id="card-set-code"
                v-model="selectedSetCode"
                class="search-form-select"
                name="setCode"
                :disabled="loadingSetCodes">
                <option value="">Toutes les extensions</option>
                <option
                    v-for="setCode in setCodes"
                    :key="setCode"
                    :value="setCode">
                    {{ setCode }}
                </option>
            </select>
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

.search-form-select {
    min-width: 200px;
    padding: 10px 12px;
}

.search-form-submit {
    padding: 10px 16px;
    cursor: pointer;
}
</style>
