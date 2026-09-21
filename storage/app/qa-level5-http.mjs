// Real-HTTP QA of the LEVEL 5 customer dashboard.
//
//   node storage/app/qa-level5-http.mjs
//
// Exits non-zero if any hard finding appears, so it can gate a build.
//
// WHY NOT THE BROWSER DRIVER
// --------------------------
// storage/app/HANDOFF.md established, with the user's own confirmation, that
// under the browser-automation driver `<script>` elements never execute and
// every page.evaluate result is read back through that same script-inert
// document. A click-based dashboard test would therefore land on an inert
// page and prove nothing about the app. So, exactly as with
// qa-register-http.mjs / qa-login-http.mjs at LEVEL 3, this drives the real
// server over raw HTTP with Node's built-in fetch and a hand-rolled cookie
// jar. That exercises the real middleware chain (auth -> active -> verified),
// real Blade rendering, real MySQL — and cannot be faked by the driver.
//
// It reports what the server actually returned, not what the code intends.

const BASE = process.env.GROWZA_BASE || "http://127.0.0.1:8125";
const EMAIL = process.env.QA_LEVEL5_EMAIL || "level5-verify@example.test";
const PASSWORD = process.env.QA_LEVEL5_PASSWORD || "correct-horse-99";

// --- Minimal cookie jar -----------------------------------------------------
const jar = new Map();

function storeCookies(res) {
  const raw = res.headers.getSetCookie ? res.headers.getSetCookie() : [];
  for (const line of raw) {
    const [pair] = line.split(";");
    const idx = pair.indexOf("=");
    if (idx > 0) jar.set(pair.slice(0, idx).trim(), pair.slice(idx + 1).trim());
  }
}

function cookieHeader() {
  return [...jar.entries()].map(([k, v]) => `${k}=${v}`).join("; ");
}

async function req(path, init = {}) {
  const headers = {
    "user-agent": "growza-level5-qa/1.0",
    ...(init.headers || {}),
  };
  const cookies = cookieHeader();
  if (cookies) headers.cookie = cookies;

  // Do NOT follow redirects automatically: the Location header IS the finding.
  const res = await fetch(BASE + path, {
    ...init,
    headers,
    redirect: "manual",
  });
  storeCookies(res);
  return res;
}

function csrfFrom(html) {
  const m = html.match(/name="_token"\s+value="([^"]+)"/);
  return m ? m[1] : null;
}

/*
 * Blade escapes text with htmlspecialchars(), so an apostrophe in a component
 * prop arrives as &#039; and a raw needle like "isn't" never matches. The first
 * run of this script reported "missing content" on referrals/support purely
 * because of that — a harness artefact, not a defect. Decode the entities once
 * before matching so the assertion is about the app's words, not its encoding.
 */
function decodeEntities(html) {
  return html
    .replace(/&#0?39;/g, "'")
    .replace(/&apos;/g, "'")
    .replace(/&quot;/g, '"')
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">")
    .replace(/&amp;/g, "&");
}

const out = { base: BASE, login: {}, pages: [], issues: [] };

// --- 1. A guest must be ejected from every dashboard URL --------------------
const DASHBOARD_PATHS = [
  "/dashboard",
  "/dashboard/profile",
  "/dashboard/services",
  "/dashboard/orders",
  "/dashboard/wallet",
  "/dashboard/transactions",
  "/dashboard/referrals",
  "/dashboard/support",
  "/dashboard/notifications",
  "/dashboard/settings",
];

out.guest = [];
for (const p of DASHBOARD_PATHS) {
  const res = await req(p);
  const loc = res.headers.get("location") || "";
  out.guest.push({ path: p, status: res.status, location: loc });
}

// --- 2. Log in for real, through the real form ------------------------------
const loginPage = await req("/login");
const loginHtml = await loginPage.text();
const loginToken = csrfFrom(loginHtml);

if (!loginToken) {
  out.issues.push(
    "could not scrape a CSRF token from /login — the login POST would 419",
  );
}

const loginRes = await req("/login", {
  method: "POST",
  headers: { "content-type": "application/x-www-form-urlencoded" },
  body: new URLSearchParams({
    _token: loginToken || "",
    email: EMAIL,
    password: PASSWORD,
  }).toString(),
});

out.login = {
  getStatus: loginPage.status,
  postStatus: loginRes.status,
  location: loginRes.headers.get("location"),
};
if (loginRes.status !== 302) {
  const body = await loginRes.text();
  const errs = [...body.matchAll(/<li>([^<]+)<\/li>/g)].map((m) => m[1]);
  out.login.validationErrors = errs.slice(0, 6);
}

// --- 3. Every dashboard page, authenticated ---------------------------------
const EXPECTED = {
  "/dashboard": [
    "Wallet balance",
    "Total orders",
    "Active orders",
    "Completed orders",
    "Total spent",
    "Referral earnings",
    "No campaigns yet",
    "₦0.00",
  ],
  // The profile form posts to Fortify's own registered route, so assert on the
  // rendered action URL — a route NAME would not appear in the markup at all.
  "/dashboard/profile": [
    "Full name",
    "Email address",
    "Phone number",
    `<form method="POST" action="${BASE}/user/profile-information"`,
  ],
  "/dashboard/services": ["Ordering isn't live yet", "Ordering opens soon"],
  "/dashboard/orders": ["No orders yet"],
  "/dashboard/wallet": ["Wallet funding isn't live yet", "₦0.00"],
  "/dashboard/transactions": ["No transactions yet"],
  "/dashboard/referrals": ["referral program isn't live yet"],
  "/dashboard/support": ["Support tickets aren't live yet"],
  "/dashboard/notifications": ["No notifications yet"],
  "/dashboard/settings": ["Profile", "Security", "/settings/security"],
};

for (const p of DASHBOARD_PATHS) {
  const res = await req(p);
  const rawHtml = await res.text();
  const html = decodeEntities(rawHtml);
  const want = EXPECTED[p] || [];

  out.pages.push({
    path: p,
    status: res.status,
    bytes: rawHtml.length,
    title: (html.match(/<title>([^<]*)<\/title>/) || [])[1] || null,
    // Private areas must refuse indexing in every environment.
    noindex: /name="robots" content="noindex, nofollow"/.test(html),
    // A broken route()/Blade call surfaces as an exception page, not a page.
    looksLikeException:
      /Route \[.*\] not defined|Undefined variable|ViewException|whoops/i.test(
        html,
      ),
    // The full nav must be present, in both the sidebar and the drawer.
    navItems: (html.match(/href="[^"]*\/dashboard[^"]*"/g) || []).length,
    hasSidebar: html.includes("md:w-60"),
    hasMobileDrawer: html.includes("max-w-[85vw]"),
    claimsOrdering: /Ordering opens soon/.test(html),
    missing: want.filter((w) => !html.includes(w)),
  });
}

// --- 4. The deliberate 404 on a single order --------------------------------
// An Order model does not exist until LEVEL 7. A route bound to {order} now
// would be the "button that does nothing" the project rules forbid.
const orderOne = await req("/dashboard/orders/1");
out.singleOrderRoute = {
  path: "/dashboard/orders/1",
  status: orderOne.status,
  // 404 = deliberately not registered. 500 would be a real defect.
  verdict: orderOne.status === 404 ? "deliberate 404, correct" : "UNEXPECTED",
};

// --- 5. The settings hub's Security tile must be a real link ----------------
const settingsRes = await req("/dashboard/settings");
const settingsHtml = await settingsRes.text();
out.settingsHub = {
  profileHref: /href="[^"]*\/dashboard\/profile"/.test(settingsHtml),
  securityHref: /href="[^"]*\/settings\/security"/.test(settingsHtml),
};

// --- 6. A marketing page must still work (no regression from the layout) ----
const home = await req("/");
out.marketingRegression = {
  homeStatus: home.status,
  hasMarketingNav: (await home.text()).includes("/pricing"),
};

// --- 7. Log out and confirm the dashboard is closed again -------------------
const dashForToken = await req("/dashboard");
const dashHtml = await dashForToken.text();
const logoutToken = csrfFrom(dashHtml) || csrfFrom(settingsHtml) || loginToken;

if (logoutToken) {
  const logoutRes = await req("/logout", {
    method: "POST",
    headers: { "content-type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ _token: logoutToken }).toString(),
  });
  out.logout = {
    status: logoutRes.status,
    location: logoutRes.headers.get("location"),
  };

  const after = await req("/dashboard");
  out.logout.dashboardAfterLogout = {
    status: after.status,
    location: after.headers.get("location") || null,
  };
}

// --- 8. Verdict -------------------------------------------------------------
for (const g of out.guest) {
  if (g.status !== 302)
    out.issues.push(`guest ${g.path} returned ${g.status}, expected 302`);
}
for (const p of out.pages) {
  if (p.status !== 200) out.issues.push(`${p.path} returned ${p.status}`);
  if (p.looksLikeException)
    out.issues.push(`${p.path} rendered an exception page`);
  if (!p.noindex) out.issues.push(`${p.path} is missing noindex, nofollow`);
  if (!p.hasSidebar)
    out.issues.push(`${p.path} is missing the desktop sidebar`);
  if (!p.hasMobileDrawer)
    out.issues.push(`${p.path} is missing the mobile drawer`);
  if (p.missing.length)
    out.issues.push(
      `${p.path} missing expected content: ${p.missing.join(", ")}`,
    );
}
if (out.login.postStatus !== 302)
  out.issues.push(`login POST returned ${out.login.postStatus}, expected 302`);
if (out.singleOrderRoute.status !== 404)
  out.issues.push(
    `/dashboard/orders/1 returned ${out.singleOrderRoute.status}, expected a deliberate 404`,
  );
if (out.marketingRegression.homeStatus !== 200)
  out.issues.push("marketing homepage regressed");
if (out.logout && out.logout.dashboardAfterLogout.status !== 302) {
  out.issues.push("dashboard still reachable after logout");
}

out.verdict = out.issues.length === 0 ? "PASS" : "FAIL";
console.log(JSON.stringify(out, null, 2));
process.exit(out.issues.length === 0 ? 0 : 1);
