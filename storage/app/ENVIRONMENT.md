# HANDOFF — environment setup for running Growza locally

Companion to `storage/app/HANDOFF.md` (which covers the QA-harness
script-inertness finding). This file is about the **machine**, not the code.
Written during LEVEL 3. Read it before concluding that something is broken.

---

## 1. The exact command that runs the app

**Preferred — do not hand-roll this.** `storage/app/serve-and-qa.ps1` starts the
server if it is not already up, polls the port until it is genuinely listening,
runs the real-HTTP QA probe, and exits with the probe's status:

```powershell
& storage\app\serve-and-qa.ps1              # serve (if needed) + probe
& storage\app\serve-and-qa.ps1 -Seed        # also migrate --force + seed
& storage\app\serve-and-qa.ps1 -Stop        # stop the server afterwards
```

The two traps in §1 are why that script exists: a hand-rolled launch needs
`-WorkingDirectory` **and** a wait-for-port loop, and forgetting either produces
the same `fetch failed` / `Unable to connect` symptom.

If you do need it by hand:

```powershell
cd c:\Users\DELL\growza\growza
& "C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe" artisan serve --host=127.0.0.1 --port=8125
```

The CLI PHP lives at that Downloads path and is **not on PATH**. XAMPP's PHP 8.2
cannot run this project at all — `vendor/composer/platform_check.php` hard-fails
below 8.4.1.

To start it detached (so it survives the shell). `-WorkingDirectory` is
**mandatory**: `artisan` must run with the repo as its CWD, or it exits
immediately and the next command reports `Unable to connect` — which looks
exactly like "the server was never started":

```powershell
Start-Process -FilePath "C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.exe" `
  -ArgumentList "artisan","serve","--host=127.0.0.1","--port=8125" `
  -WorkingDirectory "c:\Users\DELL\growza\growza" -WindowStyle Hidden
```

After launching detached, **poll the port before using it** — a fixed
`Start-Sleep` is the bug, not the fix. `artisan serve` is a parent process that
spawns a child `php -S 127.0.0.1:8125` which actually holds the port:

```powershell
while (-not (Get-NetTCPConnection -LocalPort 8125 -State Listen -ErrorAction SilentlyContinue)) {
  Start-Sleep -Milliseconds 250
}
```

## 2. Three environment traps that look like code bugs

Each of these produced a hard failure during LEVEL 3 that had nothing to do with
the application. Check them first.

### 2a. `curl.cainfo` / `openssl.cafile` unset -> every registration 500s

PHP's bundled `php.ini` at
`C:\Users\DELL\Downloads\php-8.4.12-nts-Win32-vs17-x64\php.ini` ships with both
of these **commented out**, and the directory contains **no `cacert.pem`**. With
no CA bundle, any HTTPS call fails with cURL error 60.

That matters because `CreateNewUser` runs Laravel's `uncompromised()` password
rule, which calls `https://api.pwnedpasswords.com`. So every valid registration
threw a certificate error and returned 500.

**Fix applied:** downloaded a CA bundle to `storage/app/cacert.pem` and set both
keys in `php.ini` to point at it. Confirmed working — PHP fetched 98,561 bytes
from HaveIBeenPwned.

**Production needs none of this.** A Linux host has a system CA store. This is a
Windows-dev-only papercut.

### 2b. `MAIL_MAILER=smtp` on port 2525 -> registration fails sending the email

`.env` defaults to SMTP on `127.0.0.1:2525`, which assumes a Mailpit/Mailhog
container that is not running here. Registration fails at the verification-email
step (500) even though everything before it succeeded.

**Fix applied:** set `MAIL_MAILER=log` in `.env`. Mail then renders into
`storage/logs/laravel.log` and the flow completes.

**Tests are unaffected** — `phpunit.xml` already sets `MAIL_MAILER=array`.

### 2c. Stale compiled Blade in `storage/framework/views/`

After any source change, old compiled views can be served over new code.
Symptom: an exception naming a route or variable that the current source no
longer references (this bit me twice — once with `route('marketing.home')`, once
with the register form).

**Fix:** clear it whenever output looks impossible:

```powershell
Get-ChildItem storage\framework\views -Filter "*.php" | Remove-Item -Force
```

`php artisan view:clear` also works.

## 3. Verifying auth without fighting the QA driver

The browser-automation driver's page does not execute document script (see
`storage/app/HANDOFF.md`), so **native form submission via a click never
fires**. A click-based auth test will silently stall and look like an app bug.

Two workable approaches, both used at LEVEL 3:

- `storage/app/qa-register-http.mjs` — registration over raw HTTP with Node's
  built-in `fetch` plus a hand-rolled cookie jar. Zero dependencies. Reports the
  status, `Location` header and any validation errors.
- `storage/app/qa-login-http.mjs` — login, protected access, 2FA surface,
  logout, and wrong-password rejection over the same mechanism. Driven by the
  `QA_EMAIL` environment variable.

These proved the following over real HTTP, with no browser in the loop:

| flow                        | result                                |
| --------------------------- | ------------------------------------- |
| anon `GET /dashboard`       | 302 -> `/login`                       |
| `POST /register` (valid)    | 302 -> `/dashboard`, row persisted    |
| `POST /login` (correct)     | 302 -> `/dashboard`                   |
| unverified `GET /dashboard` | 302 -> `/email/verify`                |
| `GET /settings/security`    | 200, 2FA markup present               |
| `POST /logout`              | 302 -> `/` and session genuinely dead |
| `POST /login` (wrong)       | rejected                              |
| `POST /login` x6            | 6th -> **429**                        |

**Gotcha found while writing these:** an unverified user is redirected away from
`/dashboard`, so scraping a CSRF token from that page yields `null` and the
subsequent POST returns **419**. Scrape the token from `/email/verify` instead.
The 419 was the harness's fault, not the app's — worth remembering before
reporting a CSRF bug.

## 4. Registration requires a `terms` checkbox

`CreateNewUser` validates `'terms' => ['accepted']`. A POST without
`terms=1` is correctly rejected with a redirect back to `/register`. That is
proper behaviour, but it looks like a mysterious silent failure if you forget it.

## 5. Rule for the next level

> Before reporting any 500, 419 or "registration does nothing": check 2a, 2b and
> 2c above. All three have already cost real debugging time and none of them are
> application defects.
