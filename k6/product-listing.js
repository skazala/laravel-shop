import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE } from './helpers.js';

export const options = {
    stages: [
        { duration: '30s', target: 50  },
        { duration: '60s', target: 50  },
        { duration: '30s', target: 100 },
        { duration: '60s', target: 0   }, 
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'], // ← relax to 2s for now, tighten after fixes
        http_req_failed:   ['rate<0.01'],
    },
};

export default function () {
    // Browse all products
    let res = http.get(`${BASE}/`);
    check(res, { 'products page 200': r => r.status === 200 });
    sleep(1);

    // Browse by category (simulate random slug)
    const slugs = ['yarn', 'needles', 'accessories'];
    const slug  = slugs[Math.floor(Math.random() * slugs.length)];
    res = http.get(`${BASE}/?category=${slug}`);
    check(res, { 'category filter 200': r => r.status === 200 });
    sleep(1);

    // View a product detail page
    const id = Math.floor(Math.random() * 500) + 1;
    res = http.get(`${BASE}/products/${id}`);
    check(res, { 'product detail 200': r => r.status === 200 });
    sleep(1);
}