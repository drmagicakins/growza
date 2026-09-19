// Test the data-navigate-track hypothesis directly.
//
// Laravel's @vite emits `data-navigate-track="reload"` on the asset tags when
// Vite's `refresh: true` is set. The suspicion is that this attribute (or
// something else about the emitted tag) is what stops the module executing.
//
// Method: load the real page, then create a SECOND script element with the
// identical src but WITHOUT data-navigate-track, and see whether that one runs.
// Same URL, same bytes, same document — the only difference is the attribute.
// If the clone executes, the attribute is implicated. If neither executes, the
// attribute is exonerated and the cause is elsewhere.
export default async function run(page) {
  const out = {};

  const alpine = () => page.evaluate(() => typeof window.Alpine);
  out.alpineFromPageLoad = await alpine();

  // Mark the document so we can tell whether the injected script ran at all.
  await page.evaluate(() => {
    window.__probe = { attrLess: false, withAttr: false };
  });

  // 1. Clone WITHOUT data-navigate-track.
  out.cloneWithoutAttr = await page.evaluate(async () => {
    const src = document.querySelector('script[type="module"]')?.src;
    if (!src) return "no module script found";
    return await new Promise((resolve) => {
      const s = document.createElement("script");
      s.type = "module";
      s.src = src + "?probe-noattr=1"; // distinct URL so the module registry re-runs it
      s.onload = () => resolve("LOADED");
      s.onerror = () => resolve("ERROR EVENT");
      document.head.appendChild(s);
      setTimeout(() => resolve("TIMED OUT (no load, no error)"), 4000);
    });
  });
  out.alpineAfterAttrLessClone = await alpine();

  // 2. Sanity check: does a completely ordinary module script execute at all in
  //    this page? If even an inline module cannot run, nothing about the asset
  //    tag is the issue — the document/module mode is.
  out.inlineModuleRuns = await page.evaluate(async () => {
    return await new Promise((resolve) => {
      const s = document.createElement("script");
      s.type = "module";
      s.textContent = "window.__probe.inline = true;";
      document.head.appendChild(s);
      setTimeout(() => resolve(window.__probe.inline === true), 1500);
    });
  });

  // 3. And a classic (non-module) script, for comparison.
  out.classicScriptRuns = await page.evaluate(async () => {
    return await new Promise((resolve) => {
      const s = document.createElement("script");
      s.textContent = "window.__probe.classic = true;";
      document.head.appendChild(s);
      setTimeout(() => resolve(window.__probe.classic === true), 1200);
    });
  });

  out.probe = await page.evaluate(() => window.__probe);
  return out;
}
