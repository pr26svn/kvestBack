## Plan: Fix login JSON response

TL;DR: The frontend dev server is on port 5173 and currently proxies `/api` to `http://127.0.0.1:80` via `front/vite.config.js`. So a browser request to `http://localhost:5173/api/login` is expected in dev mode. If you want the backend request to actually hit port 80 directly, the frontend should use a backend base URL or the proxy must be confirmed working.

**Steps**
1. Confirm the login fetch request in `front/src/stores/auth.js` includes `Accept: application/json` and transparently uses `/api/login` or a dedicated backend URL.
2. Verify `front/vite.config.js` proxy works by ensuring Vite dev server proxy forwards `/api` to `http://127.0.0.1:80`.
3. If direct backend host is desired instead of proxy, switch the frontend to an absolute base URL like `http://localhost/api/login` or use a `VITE_API_URL` env var.
4. Ensure `front/src/components/Login.vue` sends actual values from the reactive form state (`reactive` or `form.value`).
5. Test the login request: if using proxy, request URL in browser will still be `5173`, but response should come from backend JSON; if using direct URL, it should show port 80.

**Relevant files**
- `front/vite.config.js` — dev proxy configuration
- `front/src/stores/auth.js` — fetch request target and headers
- `front/src/components/Login.vue` — form state handling and login flow

**Verification**
1. Validate `Request Payload` contains `email` and `password`.
2. Confirm backend response is JSON, not HTML.
3. If using proxy, confirm backend request is forwarded from 5173 to 80; if using direct URL, confirm target is port 80.

**Decision**
- Prefer fixing the proxy setup in dev and keeping `fetch('/api/login')`, because that is the correct Vite dev workflow. Use absolute backend URL only if proxy is not desired or cannot be used.
