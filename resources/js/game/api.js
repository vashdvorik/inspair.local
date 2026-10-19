const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

async function request(method, url, body) {
    const res = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: body ? JSON.stringify(body) : undefined,
    });
    let data = null;
    try { data = await res.json(); } catch (e) { /* пустой ответ */ }
    if (!res.ok) {
        const err = new Error((data && data.message) || 'Ошибка запроса');
        err.status = res.status;
        err.data = data;
        throw err;
    }
    return data;
}

export const get = (url) => request('GET', url);
export const post = (url, body) => request('POST', url, body || {});

export const initials = (name = '') =>
    name.split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0]).join('').toUpperCase() || '?';
