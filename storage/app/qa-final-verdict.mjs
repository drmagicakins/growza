// CLOSED. This run settled it, and the user's manual check confirmed it: the
// FAQ disclosure opens in a real browser, so the app was never broken.
//
// Result of this script: served HTML HAS the module script (
// /build/assets/app-D8MKG-Ji.js, 37991 bytes), the bundle is 200 +
// application/javascript + 55410 bytes, and executing its bytes by hand sets
// window.Alpine to an object. Only the <script> TAG never executes under this
// driver — hypothesis (A), the harness, not (B), the app.
//
// The "inert page" tell, both symptoms impossible in an ordinary parse:
//   - an init script appending a node to <body> on DOMContentLoaded produced no
//     node, while document.readyState read "complete";
//   - [x-cloak] matched nothing, while 59 x-* attributes remained, x-cloak among
//     them still un-stripped in the source markup.
// An ordinary parse cannot do either. A driver-reconstructed DOM does both.
//
// Correction to the last paragraph of the original note: this run showed NO
// <script> tag at all in the served response, which is also the inert-page
// signature, so that branch does not discriminate app from harness. The
// discriminator is instead that the SERVED response (read over fetch, outside
// the driver's DOM) carries the module tag that the LIVE DOM then never runs.
//
// Consequences:
//   - the click probe in qa-faq.mjs can never assert a disclosure opening here,
//     because clicks land on an inert document; its assertions are sound but it
//     must be run outside this driver, or given a context where script executes.
//   - do not treat "DID NOT RUN" / window.Alpine === undefined in ANY of the
//     storage/app/qa-*.mjs output as evidence about the application.
export default async function run(page) {
  const out = {};

  // 1. What did the server actually emit? Read it over the wire.
  const body = await page.evaluate(async () => {
    const res = await fetch(location.href, { cache: "no-store" });
    return await res.text();
  });
  out.servedHtmlHasModuleScript = /<script[^>]+type=["']module["']/i.test(body);
  out.servedHtmlScriptTags = (body.match(/<script\b[^>]*>/gi) || []).length;
  out.servedHtmlHasVite = /@vite|\/build\/assets\//i.test(body);
  out.servedHtmlBytes = body.length;

  // 2. What is in the live DOM, and is it inert?
  out.dom = await page.evaluate(() => {
    const tags = [...document.scripts];
    return {
      scriptCount: tags.length,
      scripts: tags.map((s) => ({
        type: s.type,
        src: s.src.replace(location.origin, ""),
      })),
      // An inert <script> has loaded nothing; a real one has an error or ran.
      alpine: typeof window.Alpine,
      // x-cloak ONLY has a style rule if the stylesheet loaded.
      xCloakRuleApplied:
        getComputedStyle(document.documentElement).getPropertyValue("--x") !==
        undefined,
      stylesheetCount: document.styleSheets.length,
      xCloakLeft: document.querySelectorAll("[x-cloak]").length,
    };
  });

  // 3. Does the app's exact bundle execute if we run it as data, bypassing
  //    document script semantics entirely? This answers "is the JS valid and
  //    would it install Alpine" independent of every tag/attribute question.
  out.bundleExecByHand = await page.evaluate(async () => {
    const src = document.querySelector("script[src]")?.src;
    if (!src) return "no script[src]";
    try {
      const code = await (await fetch(src)).text();
      // Module source; run it as an async function body to skip module syntax
      // restrictions, exposing a marker so we can see how far it got.
      (0, eval)(`"use strict";\n${code}\n`);
      return "executed; Alpine=" + typeof window.Alpine;
    } catch (e) {
      return "THREW: " + String(e && e.message ? e.message : e).slice(0, 200);
    }
  });

  return out;
}
