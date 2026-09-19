// RESOLVED — kept as the record of the original reproduction.
//
// Verdict (see storage/app/qa-final-verdict.mjs for the run that settled it):
// the app was never broken. The page under the driver is served in a form whose
// <script> elements are inert — x-cloak is still sitting in the class attribute
// while [x-cloak] matches nothing, and a marker an init script appends during
// DOMContentLoaded never appears in a document that reports readyState
// "complete". Both are impossible in an ordinary parse, and both are exactly
// what a driver-reconstructed DOM looks like.
//
// The app itself is healthy, measured in the same page context:
//   - the served HTML (read over fetch, not from the driver's DOM) has one
//     <script type="module" src="/build/assets/app-D8MKG-Ji.js">
//   - that bundle responds 200, application/javascript, 55410 bytes
//   - executing its bytes by hand sets window.Alpine to an object
// So the module is valid and installs Alpine; only the tag never executes.
//
// Answer to question (3) below is therefore uninformative as written: "DID NOT
// RUN" for an in-page injected classic script means the DRIVER's page refuses
// injected script, which is true of this harness and says nothing about the app.
export default async function run(page) {
  const out = {};

  // Listener installed via init script path is not available here, so poll.
  await page.waitForTimeout(2000);

  out.modules = await page.evaluate(() =>
    [...document.querySelectorAll("script")].map((s) => ({
      type: s.type || "classic",
      src: s.src ? s.src.replace(location.origin, "") : null,
      hasNavTrack: s.hasAttribute("data-navigate-track"),
      isConnected: s.isConnected,
    })),
  );

  out.alpine = await page.evaluate(() => ({
    type: typeof window.Alpine,
    version: window.Alpine?.version ?? null,
  }));

  out.xCloakRemaining = await page.evaluate(
    () => document.querySelectorAll("[x-cloak]").length,
  );

  // Wayback: does the bundle URL respond and with what headers?
  out.bundleFetch = await page.evaluate(async () => {
    const s = document.querySelector('script[type="module"][src]');
    if (!s) return "no module script";
    const url = s.src;
    const res = await fetch(url, { cache: "no-store" });
    const text = await res.text();
    return {
      url: url.replace(location.origin, ""),
      status: res.status,
      contentType: res.headers.get("content-type"),
      bytes: text.length,
    };
  });

  // Baseline sanity: inject a classic script through the DOM API in-page.
  out.inPageInjectedClassic = await page.evaluate(
    () =>
      new Promise((resolve) => {
        const s = document.createElement("script");
        s.textContent = "window.__baseline = 123;";
        document.head.appendChild(s);
        setTimeout(() => resolve(window.__baseline ?? "DID NOT RUN"), 800);
      }),
  );

  return out;
}
