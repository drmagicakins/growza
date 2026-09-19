// Standalone: does the app bundle actually execute, and does Alpine install
// itself on window? Isolates "the bundle throws" from "Alpine is slow to init".
export default async function run(page) {
  const out = {};

  // 1. Does window.Alpine exist at all, and after how long?
  out.immediate = await page.evaluate(() => typeof window.Alpine);
  await page.waitForTimeout(1500);
  out.after1500ms = await page.evaluate(() => typeof window.Alpine);

  // 2. Load the exact bundle the page loaded, in the page's own context, and
  //    capture whatever it throws instead of letting it vanish.
  out.bundleLoad = await page.evaluate(async () => {
    const src = document.querySelector("script[src]")?.src;
    if (!src) return "no script[src] on page";
    try {
      const text = await (await fetch(src)).text();
      // Evaluating in global scope, not a module wrapper, mirrors what the
      // browser did with the real <script type=module> tag closely enough to
      // surface a throw.
      new Function(text)();
      return (
        "THREW-NO: executed without throwing, Alpine=" + typeof window.Alpine
      );
    } catch (e) {
      return "THREW: " + (e && e.message ? e.message.slice(0, 300) : String(e));
    }
  });

  out.afterManual = await page.evaluate(() => typeof window.Alpine);

  // 3. Did the element ever get walked by Alpine? x-cloak is stripped on init.
  out.xCloakRemaining = await page.evaluate(
    () => document.querySelectorAll("[x-cloak]").length,
  );
  out.hasXCloakAttr = await page.evaluate(() =>
    [...document.querySelectorAll("*")].some((e) => e.hasAttribute("x-cloak")),
  );

  return out;
}
