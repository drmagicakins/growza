// QA asset/console check across the marketing routes.
// Usage: node <skill-dir>/browser.mjs http://127.0.0.1:8125/ --script ./storage/app/qa-assets.mjs
//
// The browser.mjs runner owns the browser and captures console/network, but it
// only reports on the single URL it was given. To cover every route in one run
// we navigate inside this script and record the per-page outcome ourselves.
//
// A "failed asset request" here = a request whose response is >= 400, a
// network-level failure, or a same-host asset URL that never returned a body.
// Requests that never received a response at all are reported too, since a
// dangling stylesheet/script is exactly the stale-hash symptom we are hunting.

const ROUTES = [
  "/",
  "/services",
  "/pricing",
  "/how-it-works",
  "/faq",
  "/contact",
  "/dev/design-system",
  "/up",
];

// Binaries/media/types that a marketing page legitimately does not have, so a
// 404 on them is not an "asset" failure in the sense we care about. Kept tight
// on purpose: fonts and images ARE assets we want to catch.
const IGNORED_EXT = /\.(map)$/i;

export default async function run(page) {
  const results = [];

  for (const route of ROUTES) {
    const failed = [];
    const requested = [];
    const consoleErrors = [];

    const onResponse = (res) => {
      const url = res.url();
      if (!url.startsWith("http://127.0.0.1:8125")) return;
      requested.push({ url, status: res.status() });
      // 404/500 on anything same-host is a real failure signal.
      if (res.status() >= 400 && !IGNORED_EXT.test(url)) {
        failed.push({ url, status: res.status() });
      }
    };
    const onFailed = (req) => {
      const url = req.url();
      if (!url.startsWith("http://127.0.0.1:8125")) return;
      failed.push({
        url,
        status: "NETWORK_FAILURE",
        failure: req.failure()?.errorText,
      });
    };
    const onConsole = (msg) => {
      if (msg.type() !== "error") return;
      const text = msg.text();
      // Filter asset-load noise that is really a duplicate of the network list.
      consoleErrors.push(text);
    };

    page.on("response", onResponse);
    page.on("requestfailed", onFailed);
    page.on("console", onConsole);

    let title = null;
    let bodyChars = null;
    let navError = null;
    try {
      const resp = await page.goto("http://127.0.0.1:8125" + route, {
        waitUntil: "networkidle",
        timeout: 30000,
      });
      if (resp && resp.status() >= 400) {
        failed.push({ url: resp.url(), status: resp.status() });
      }
      title = await page.title();
      bodyChars = await page.evaluate(() =>
        document.body ? document.body.innerText.length : 0,
      );
    } catch (e) {
      navError = String(e.message || e);
    }

    page.off("response", onResponse);
    page.off("requestfailed", onFailed);
    page.off("console", onConsole);

    // Separate the assets we care about from page/JSON navigations.
    const assets = requested
      .filter(
        (r) =>
          /\/build\//.test(r.url) ||
          /\.(css|js|woff2?|ttf|png|jpe?g|svg|ico)$/i.test(r.url),
      )
      .map((r) => ({
        path: r.url.replace("http://127.0.0.1:8125", ""),
        status: r.status,
      }));

    results.push({
      route,
      title,
      bodyChars,
      navError,
      assets,
      failed: failed.map((f) => ({
        path: f.url.replace("http://127.0.0.1:8125", ""),
        status: f.status,
      })),
      consoleErrors,
    });
  }

  const anyFailure = results.some(
    (r) =>
      r.failed.length || r.consoleErrors.length || r.navError || !r.bodyChars,
  );

  return { ok: !anyFailure, results };
}
