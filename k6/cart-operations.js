import http from 'k6/http';
import { check, sleep } from 'k6';
import { CookieJar } from 'k6/http';

const BASE = 'http://nginx';

export const options = {
    stages: [
        { duration: '30s', target: 30 },
        { duration: '60s', target: 30 },
        { duration: '20s', target: 0  },
    ],
    thresholds: {
        http_req_duration: ['p(95)<1500'],
        http_req_failed:   ['rate<0.01'],
    },
};

function login(jar, email) {
    const params = { jar, redirects: 0 };

    // Get CSRF token
    const loginPage = http.get(`${BASE}/login`, params);
    const tokenMatch = loginPage.body.match(/<meta name="csrf-token" content="([^"]+)"/);
    if (!tokenMatch) {
        console.log('No CSRF meta tag found');
        return false;
    }

    const res = http.post(
        `${BASE}/test/login`,
        JSON.stringify({ email }),
        {
            jar,
            headers: {
                'Content-Type':     'application/json',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     tokenMatch[1],
                'X-Requested-With': 'XMLHttpRequest',
            },
        }
    );

    if (res.status !== 200) {
        console.log(`Login failed: ${res.status} ${res.body.substring(0, 100)}`);
        return false;
    }
    return true;
}

export default function () {
    const jar    = new CookieJar();
    const userId = (__VU % 200) + 1;
    const email  = `user${userId}@example.com`;

    if (!login(jar, email, 'password')) return;

    // View cart
    let res = http.get(`${BASE}/cart`, { jar });
    check(res, { 'cart page 200': r => r.status === 200 });
    sleep(1);

    // Get fresh CSRF token from cart page meta tag
    const csrfMatch = res.body.match(/<meta name="csrf-token" content="([^"]+)"/);
    if (!csrfMatch) {
        console.log('No CSRF token on cart page');
        return;
    }
    const csrf = csrfMatch[1];

    // Add to cart via test route
    const productId = Math.floor(Math.random() * 500) + 1;
    res = http.post(
        `${BASE}/test/add-to-cart/${productId}`,
        {},
        {
            jar,
            headers: {
                'X-CSRF-TOKEN':     csrf,
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }
    );
    console.log(`Add to cart: ${res.status} ${res.body.substring(0, 100)}`);
    check(res, { 'add to cart ok': r => r.status === 200 });
    sleep(2);
}