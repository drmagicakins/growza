// The decisive test for the ONE thing all previous probes shared.
//
// Established:
//   - document.readyState === "complete"           => the document did finish
//   - a timer and a microtask scheduled by evaluate both FIRE => the context
//     is alive and running script, so "no script runs" is a false lead
//   - DOM injection from evaluate works (marker node present)
//   - the module script tag is connected, src 200, content-type ok
//   - window.Alpine is undefined, x-cloak is gone, 59 x-* attributes remain
//
// The last point is the odd one: x-cloak disappearing without Alpine means the
// class was stripped by something OTHER than Alpine. And an init script that
// appended a marker to <body> during DOMContentLoaded produced NO node, while
// document.readyState is "complete" — those two cannot both be true of an
// ordinary DOM. They ARE both true of a document whose markup the driver has
// snapshotted and re-served: the init script ran against the ORIGINAL parse,
// then the document was replaced, discarding its DOM effects.
//
// So: does document.URL still match, do our earlier in-page writes survive, and
// does a fresh init script (registered now, before a re-navigation) survive
// across that navigation? That last one isolates parse-time vs after-load.
export default async function run(page) {
  const out = {};

  // Marker written by the PREVIOUS probe run should be gone (fresh browser),
  // but a marker written now and re-read after an in-page reload should persist
  // if the document is a normal one.
  await page.evaluate(() => {
    window.__survived = "written-before-reload";
  });

  out.beforeReload = await page.evaluate(() => ({
    url: location.href,
    readyState: document.readyState,
    alpine: typeof window.Alpine,
  }));

  // Reload and see whether a value written to window survives (it should NOT
  // across a real navigation) and whether SCRIPT finally runs on a document
  // the driver did not have to synthesise from the first response.
  await page.reload({ waitUntil: "load" });
  await page.waitForTimeout(2500);

  out.afterReload = await page.evaluate(() => ({
    url: location.href,
    readyState: document.readyState,
    alpine: typeof window.Alpine,
    survived: window.__survived ?? "GONE (expected across a real navigation)",
    xCloak: document.querySelectorAll("[x-cloak]").length,
    xAttrs: [...document.querySelectorAll("*")].filter((e) =>
      [...e.attributes].some((a) => a.name.startsWith("x-")),
    ).length,
    scriptCount: document.scripts.length,
  }));

  return out;
}
