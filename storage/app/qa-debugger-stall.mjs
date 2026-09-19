// Is the automation driver PAUSING the page (thus never running script), or is
// the document genuinely script-dead?
//
// The init-script probe proved no document script executes at all in the driven
// context — yet the server log shows the module was requested, and the same QA
// scripts discussed a working page earlier. That pattern is what a debugger
// pause looks like from inside the page: DOM present, script frozen.
//
// A page that is paused still answers JS: attached listeners count, and any
// pending microtask/timer work either fires or does not. So:
//
//   - install a timer BEFORE navigation via addInitScript; if the page is
//     running, it fires and its side effect is observable;
//   - check whether a debugger/automation pause is attached at all.
export default async function run(page) {
  const out = {};

  // Any listeners on the document = the page's own scripts did attach.
  out.listeners = await page.evaluate(() => ({
    scriptCount: document.scripts.length,
    readyState: document.readyState,
    alpine: typeof window.Alpine,
    // If the bundle ran and threw, at least ONE of these would be set.
    hasAlpineData: !!document.querySelector("[x-data]"),
  }));

  // Timer liveness: does a scheduled callback in this context ever fire?
  out.timerFired = await page.evaluate(
    () =>
      new Promise((resolve) => {
        let fired = false;
        setTimeout(() => {
          fired = true;
        }, 200);
        setTimeout(() => resolve(fired), 1200);
      }),
  );

  // Microtask liveness (independent of the timer subsystem).
  out.microtaskFired = await page.evaluate(
    () =>
      new Promise((resolve) => {
        let fired = false;
        Promise.resolve().then(() => {
          fired = true;
        });
        setTimeout(() => resolve(fired), 50);
      }),
  );

  // CDP: is the page actually paused by the debugger?
  const cdp = await page.context().newCDPSession(page);
  await cdp.send("Debugger.enable").catch(() => {});
  out.paused = { note: "Debugger.enable issued without throwing if null" };

  return out;
}
