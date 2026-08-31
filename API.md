# API.md

**Status: placeholder — the API is built at LEVEL 19.**

No endpoints exist yet. `routes/api.php` reserves the `/api/v1` prefix. See `ARCHITECTURE.md` §14 for the planned endpoint list, auth model (Sanctum tokens with explicit abilities/scopes), rate-limiting approach, and response envelope (§52 of the master prompt: `{success, message, data}` / `{success, message, errors}`).

This file will be filled in with real endpoint-by-endpoint documentation generated from actual route/FormRequest definitions once LEVEL 19 lands — not before, per the "documentation must reflect the actual implementation" rule (LEVEL 46).
