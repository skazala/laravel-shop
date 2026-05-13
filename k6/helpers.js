import http from 'k6/http';

const BASE = 'http://nginx';

export function login(email, password) {
    // Fetch CSRF token first
    const loginPage = http.get(`${BASE}/login`);
    const csrf = loginPage.html()
        .find('input[name="_token"]')
        .first()
        .attr('value');

    http.post(`${BASE}/login`, {
        email,
        password,
        _token: csrf,
    }, { redirects: 5 });
}

export { BASE };