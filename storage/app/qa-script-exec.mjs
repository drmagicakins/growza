// The previous test showed that NO script of any kind executes in the page —
// not inline modules, not classic scripts, not a detached clone of the asset.
// That points away from anything about the asset tag and toward the document
// itself, or toward the automation driver's handling of the context.
//
// This run distinguishes those two:
//   A. the page really refuses script (document-level)
//   B. the injected-script probe was itself flawed, or the driver isolates it
export default async function run(page, ui) {
  const out = {};

  // Did ANY script on the real document execute? If Alpine's bundle ran even
  // partially it would leave globals; if the FAQ markup has no listeners then
  // nothing ran. Check for the framework's own side effects instead of ours.
  out.before = await page.evaluate(() => ({
    alpine: typeof window.Alpine,
    inlineProbe: window.__probe ?? null,
  }));

  // Use the RUNNER'S OWN eval (--eval path) rather than a nested evaluate, so
  // we are not depending on the same mechanism that may be failing.
  // Append an inline classic script directly into <head>.
  const r1 = await page.addScriptTag({
    content: "window.__inlineClassic = 'ran';",
  });
  await page.waitForTimeout(800);
  out.inlineClassic = await page.evaluate(
    () => window.__inlineClassic ?? "DID NOT RUN",
  );
  out.addScriptTagReturned = r1 ? "element returned" : "null";

  // Now via a real external file over http.
  const r2 = await page.addScriptTag({ url: "/build/assets/app-D8MKG-Ji.js" });
  await page.waitForTimeout(1200);
  out.alpineAfterAddScriptTag = await page.evaluate(() => typeof window.Alpine);
  out.externalReturned = r2 ? "element returned" : "null";

  // And check whether the ORIGINAL module script tag is even still in the DOM
  // and whether the browser has marked it as loaded.
  out.originalTag = await page.evaluate(() => {
    const s = document.querySelector("script[type=module]");
    if (!s) return "no module tag";
    return {
      src: s.src.replace(location.origin, ""),
      isConnected: s.isConnected,
      readyState: s.readyState ?? null,
      // <script> fires no error handler result we can read, but the element
      // remains; the tell is whether window.Alpine exists (it does not).
    };
  });

  return out;
}
