function buildCardCollectionQuery(filters = {}) {
    const params = new URLSearchParams();

    if (filters.name) {
        params.set('name', filters.name);
    }

    if (filters.setCode) {
        params.set('setCode', filters.setCode);
    }

    if (typeof filters.offset === 'number' && filters.offset > 0) {
        params.set('offset', filters.offset.toString());
    }

    if (typeof filters.limit === 'number' && filters.limit > 0) {
        params.set('limit', filters.limit.toString());
    }

    return params.toString();
}

export async function fetchAllCards(filters = {}) {
    const queryString = buildCardCollectionQuery(filters);
    const response = await fetch(`/api/card${queryString ? `?${queryString}` : ''}`);

    if (!response.ok) throw new Error('Failed to fetch cards');

    return response.json();
}

export async function searchCards(filters = {}) {
    const queryString = buildCardCollectionQuery(filters);
    const response = await fetch(`/api/card${queryString ? `?${queryString}` : ''}`);

    if (!response.ok) throw new Error('Failed to search cards');

    return response.json();
}

export async function fetchSetCodes() {
    const response = await fetch('/api/card/set-codes');

    if (!response.ok) throw new Error('Failed to fetch set codes');

    return response.json();
}

export async function fetchCard(uuid) {
    const response = await fetch(`/api/card/${uuid}`);
    if (response.status === 404) return null;
    if (!response.ok) throw new Error('Failed to fetch card');
    const card = await response.json();
    card.text = card.text.replaceAll('\\n', '\n');
    return card;
}
