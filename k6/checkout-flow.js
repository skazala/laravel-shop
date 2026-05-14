import http from 'k6/http';
import { check, sleep } from 'k6';
import { CookieJar } from 'k6/http';

const BASE = 'http://nginx';

export const options = {
    stages: [
        { duration: '20s', target: 10 },
        { duration: '60s', target: 10 },
        { duration: '20s', target: 0  },
    ],
    thresholds: {
        http_req_duration: ['p(95)<3000'],
        http_req_failed:   ['rate<0.02'],
    },
};

function login(jar, email) {
    const params = { jar, redirects: 0 };

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

    if (!login(jar, email)) return;

    // View cart before checkout
    let res = http.get(`${BASE}/cart`, { jar });
    check(res, { 'cart loads': r => r.status === 200 });
    sleep(2);

    // Get CSRF token from cart page
    const csrfMatch = res.body.match(/<meta name="csrf-token" content="([^"]+)"/);
    if (!csrfMatch) {
        console.log('No CSRF token on cart page');
        return;
    }

    // Hit checkout endpoint — Stripe will redirect, we just measure response time
    res = http.post(
        `${BASE}/checkout`,
        {},
        {
            jar,
            redirects: 0,   // don't follow Stripe redirect
            headers: {
                'X-CSRF-TOKEN':     csrfMatch[1],
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }
    );
    console.log(`Checkout status: ${res.status} ${res.body.substring(0, 100)}`);
    check(res, {
        'checkout initiates': r => r.status === 302 || r.status === 200 || r.status === 422,
    });
    sleep(3);
}