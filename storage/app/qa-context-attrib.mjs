// Is the page "script-dead", or is only `page.evaluate` lying?
//
// Every previous probe reported results through page.evaluate, so if the DRIVER
// isolates injected script rather than the document refusing script, all of them
// would read identically. This run breaks that dependency two ways:
//
//   1. register an init script BEFORE any document exists, which appends its
//      observation to <body> as a DOM node (a side channel that does not need
//      evaluate to have executed anything);
//   2. install a MutationObserver in-page and let it also write to the DOM.
//
// Then read the DOM back a third way (--eval in the runner) and compare. If the
// document inserted the DOM node, script DOES run; if evaluate's read-back of
// that same node says it is missing, evaluate is the broken limb.
export default async function run(page, ui) {
  const out = {};

  // (1) Init script: runs on document creation, before the page's own scripts.
  await page.addInitScript(() => {
    window.__initRan = true;
    window.addEventListener("DOMContentLoaded", () => {
      const d = document.createElement("div");
      d.id = "__init_marker";
      d.textContent = "init-ran";
      document.body.appendChild(d);
    });
    // A second marker written synchronously from the bundle's own execution
    // window: if the app bundle ran, Alpine would be here instead.
    document.addEventListener("readystatechange", () => {
      window.__readyState = document.readyState;
    });
  });

  // (2) In-page observer, written after load.
  out.observed = await page.evaluate(() => {
    window.__mutations = 0;
    const o = new MutationObserver((m) => {
      window.__mutations += m.length;
    });
    o.observe(document.body, { childList: true, subtree: true });
    // Inject a node via a path that does NOT need script execution: innerHTML
    // on a detached element. This must work in ANY live document.
    const probe = document.createElement("div");
    probe.id = "__html_marker";
    document.body.appendChild(probe);
    return {
      bodyChildren: document.body.children.length,
      hasHtmlMarker: !!document.getElementById("__html_marker"),
      hasInitMarker: !!document.getElementById("__init_marker"),
      initRan: window.__initRan ?? null,
    };
  });

  await page.waitForTimeout(1000);

  // (3) Read the same DOM back through evaluate again.
  out.reRead = await page.evaluate(() => ({
    hasHtmlMarker: !!document.getElementById("__html_marker"),
    hasInitMarker: !!document.getElementById("__init_marker"),
    initRan: window.__initRan ?? null,
    mutations: window.__mutations ?? null,
    bodyChildren: document.body.children.length,
    alpine: typeof window.Alpine,
  }));

  return out;
}
