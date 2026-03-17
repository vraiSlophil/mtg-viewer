export async function fetchAllCards() {
    const response = await fetch('/api/card');
    if (!response.ok) throw new Error('Failed to fetch cards');
    const result = await response.json();
    return result;
}

export async function searchCardsByName(name) {
    const params = new URLSearchParams();

    if (name) {
        params.set('name', name);
    }

    const queryString = params.toString();
    const response = await fetch(`/api/card${queryString ? `?${queryString}` : ''}`);

    if (!response.ok) throw new Error('Failed to search cards');

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
