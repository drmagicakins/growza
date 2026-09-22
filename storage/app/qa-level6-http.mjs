// Real-HTTP QA of the LEVEL 6 service catalogue.
//
//   node storage/app/qa-level6-http.mjs
//
// Exits non-zero if any hard finding appears, so it can gate a build.
//
// WHY NOT THE BROWSER DRIVER
// --------------------------
// Same reason as qa-level5-http.mjs: storage/app/HANDOFF.md established, with
// the user's own confirmation, that under the browser-automation driver
// `<script>` elements never execute and every page.evaluate result is read back
// through that same script-inert document. So the catalogue is driven over raw
// HTTP with Node's built-in fetch and a hand-rolled cookie jar, which exercises
// the real middleware chain, real Blade rendering and real MySQL.
//
// It reports what the server actually returned, not what the code intends.
//
// NOTE ON ENTITY ENCODING (the LEVEL 5 lesson, HANDOFF-LEVEL5.md §2a):
// Blade escapes with htmlspecialchars(), so a service named "Instagram &
// Facebook Ad Campaign" is served as "Instagram &amp; Facebook Ad Campaign".
// Every response below is decoded before matching, so a raw-needle mismatch can
// never be misread as missing content.

const BASE = process.env.GROWZA_BASE || "http://127.0.0.1:8125";

const issues = [];
const notes = [];

function ok(label, detail = "") {
  notes.push(`PASS  ${label}${detail ? " — " + detail : ""}`);
}
function bad(label, detail = "") {
  issues.push(`FAIL  ${label}${detail ? " — " + detail : ""}`);
}

function decode(html) {
  return html
    .replace(/&amp;/g, "&")
    .replace(/&#0?39;/g, "'")
    .replace(/&quot;/g, '"')
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">");
}

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
    "user-agent": "growza-level6-qa/1.0",
    ...(init.headers || {}),
  };
  const cookies = cookieHeader();
  if (cookies) headers.cookie = cookies;
  const res = await fetch(BASE + path, {
    ...init,
    headers,
    redirect: "manual",
  });
  storeCookies(res);
  return res;
}

async function main() {
  let home;
  try {
    home = await req("/");
  } catch (e) {
    // A closed port reaches Node as a bare "TypeError: fetch failed" with the
    // real reason buried in e.cause. Surface the code, and say plainly that this
    // is an environment fault — the probe has not measured the app at all.
    const code = e.cause?.code || e.code || "";
    console.log(
      `FAIL  server not reachable at ${BASE} — ${e.message}` +
        (code ? ` (${code})` : ""),
    );
    if (code === "ECONNREFUSED") {
      console.log(
        "      nothing is listening on that port. Start the server first\n" +
          "      (HANDOFF-LEVEL6.md §9): php artisan serve --host=127.0.0.1 --port=8125\n" +
          "      This is an environment/ordering fault, NOT an application defect.",
      );
    }
    process.exit(1);
  }

  if (home.status !== 200) bad("GET /", `expected 200, got ${home.status}`);
  else ok("GET /", "200");

  const homeHtml = decode(await home.text());

  // SoundCloud exists ONLY in the platforms table — it was never in the config
  // array that LEVEL 6 removed. So its presence proves the DB is the source.
  if (homeHtml.includes("SoundCloud")) {
    ok("homepage shows DB-sourced platform list", "found SoundCloud");
  } else {
    bad("homepage shows DB-sourced platform list", "SoundCloud absent");
  }
  if (homeHtml.includes("Instagram & Facebook Ad Campaign")) {
    ok("homepage shows DB-sourced service card", "found seeded service name");
  } else {
    bad("homepage shows DB-sourced service card", "seeded service name absent");
  }

  const servicesRes = await req("/services");
  const servicesHtml = decode(await servicesRes.text());
  if (servicesRes.status !== 200)
    bad("GET /services", `expected 200, got ${servicesRes.status}`);
  else ok("GET /services", "200");

  for (const category of [
    "Paid Social",
    "Content Strategy",
    "Music Promotion",
    "Creator Partnerships",
    "Search & Discovery",
    "Analytics & Reporting",
  ]) {
    if (servicesHtml.includes(category))
      ok(`/services lists category "${category}"`);
    else bad(`/services lists category "${category}"`, "absent");
  }

  const detailPaths = [
    ...new Set(
      [...servicesHtml.matchAll(/href="([^"]*\/services\/[^"\/]+)"/g)].map(
        (m) => m[1],
      ),
    ),
  ];

  if (detailPaths.length === 10)
    ok("services listing links 10 detail pages", `${detailPaths.length} links`);
  else
    bad(
      "services listing links 10 detail pages",
      `found ${detailPaths.length}`,
    );

  let detailOk = 0;
  let sawFixedPrice = false;
  let sawBudgetRange = false;

  for (const path of detailPaths) {
    const url = path.startsWith("http") ? path.replace(BASE, "") : path;
    const res = await req(url);
    if (res.status !== 200) {
      bad(`detail page ${url}`, `expected 200, got ${res.status}`);
      continue;
    }
    const html = decode(await res.text());
    detailOk++;

    if (html.includes("one-time fee")) sawFixedPrice = true;
    if (html.includes("campaign budget, you choose within this range"))
      sawBudgetRange = true;
    if (!html.includes("Ordering opens soon")) {
      bad(
        `detail page ${url} ordering state`,
        "no 'Ordering opens soon' notice",
      );
    }
  }

  if (detailPaths.length > 0 && detailOk === detailPaths.length) {
    ok(
      "all linked detail pages render 200",
      `${detailOk}/${detailPaths.length}`,
    );
  }
  if (sawFixedPrice) ok("a fixed-price detail page shows a flat fee");
  else
    bad(
      "a fixed-price detail page shows a flat fee",
      "no 'one-time fee' found",
    );
  if (sawBudgetRange) ok("a budget-range detail page shows a range");
  else bad("a budget-range detail page shows a range", "no range copy found");

  const missing = await req("/services/not-a-real-service");
  if (missing.status === 404) ok("unknown service slug", "404");
  else bad("unknown service slug", `expected 404, got ${missing.status}`);

  const sitemap = await req("/sitemap.xml");
  const sitemapBody = sitemap.status === 200 ? await sitemap.text() : "";
  if (sitemap.status === 200 && sitemapBody.includes("/services/")) {
    ok("sitemap.xml includes service detail URLs");
  } else {
    bad("sitemap.xml includes service detail URLs", `status ${sitemap.status}`);
  }

  // The dashboard services page must also read the catalogue. Anonymous access
  // is expected to redirect; the point here is that it is not a 500.
  const dash = await req("/dashboard/services");
  if (dash.status === 302)
    ok("GET /dashboard/services anonymous", "302 to login (auth intact)");
  else
    bad(
      "GET /dashboard/services anonymous",
      `expected 302, got ${dash.status}`,
    );

  console.log("");
  for (const n of notes) console.log(n);
  console.log("");
  for (const i of issues) console.log(i);

  console.log("");
  console.log(
    JSON.stringify(
      {
        verdict: issues.length ? "FAIL" : "PASS",
        checked: notes.length,
        issues,
      },
      null,
      2,
    ),
  );
  process.exit(issues.length ? 1 : 0);
}

main().catch((e) => {
  console.log(`FAIL  probe crashed — ${e.stack}`);
  process.exit(1);
});
