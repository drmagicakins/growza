// QA the Alpine.js interactivity on /dev/design-system.
// Usage: node <skill-dir>/browser.mjs http://127.0.0.1:8125/dev/design-system --script ./storage/app/qa-alpine.mjs
//
// PROJECT_STATE.md flags Alpine as "not exercised in a real browser" — modal
// open/close, dropdown, dismissible alert. This drives each one and records
// whether the DOM actually changed, rather than assuming x-data works because
// the markup looks right.
//
// The assertions read the LIVE computed state (x-show's resolved display, the
// real aria-expanded attribute), not the x-data attribute string, so an
// Alpine that never booted reads as false instead of passing.

export default async function run(page) {
  const out = {};

  // Alpine registers itself on window once it boots. If this is undefined the
  // whole page's interactivity is dead and every check below is meaningless.
  out.alpineLoaded = await page.evaluate(
    () => typeof window.Alpine !== "undefined",
  );
  out.alpineVersion = await page.evaluate(() => window.Alpine?.version ?? null);

  const visible = (selector) =>
    page.evaluate((sel) => {
      const el = document.querySelector(sel);
      if (!el) return { found: false };
      const cs = getComputedStyle(el);
      return {
        found: true,
        display: cs.display,
        visible: cs.display !== "none" && cs.visibility !== "hidden",
      };
    }, selector);

  // The modal wrapper is x-show'd, so its inline style carries the verdict.
  const modalSel = "[x-on\\:open-modal-demo\\.window]";
  out.modalBeforeClick = await visible(modalSel);

  // --- Modal: open via the dispatching button -----------------------------
  const openBtn = page.getByRole("button", { name: "Open modal" });
  if ((await openBtn.count()) === 0) {
    out.error = "no 'Open modal' button found";
    return out;
  }
  await openBtn.click();
  await page.waitForTimeout(400);
  out.modalAfterClick = await visible(modalSel);

  // The dialog itself only becomes visible when `open` is true.
  out.dialogVisibleWhenOpen = await page.evaluate(() => {
    const d = document.querySelector('[role="dialog"]');
    if (!d) return { found: false };
    return { found: true, visible: getComputedStyle(d).display !== "none" };
  });

  // --- Close by the window-level Escape handler --------------------------
  await page.keyboard.press("Escape");
  await page.waitForTimeout(400);
  out.modalAfterEscape = await visible(modalSel);

  // --- Dropdown: toggle open, then click-outside to close ----------------
  const dropdownMenu = () =>
    visible(".relative.inline-block [class*='shadow-raised']");
  out.dropdownBefore = await dropdownMenu();

  const trigger = page.getByRole("button", { name: /Actions/ });
  if ((await trigger.count()) > 0) {
    await trigger.first().click();
    await page.waitForTimeout(400);
    out.dropdownAfterClick = await dropdownMenu();
    // :aria-expanded is bound to `open` — proves Alpine's reactive binding ran.
    out.dropdownAriaExpanded = await page.evaluate(() => {
      const t = [...document.querySelectorAll("[aria-expanded]")].find((el) =>
        /Actions/.test(el.innerText || ""),
      );
      return t ? t.getAttribute("aria-expanded") : null;
    });

    // x-on:click.outside should close it when we click elsewhere on the page.
    await page.mouse.click(5, 5);
    await page.waitForTimeout(400);
    out.dropdownAfterOutsideClick = await dropdownMenu();
  } else {
    out.error = "no 'Actions' dropdown trigger found";
  }

  // --- Dismissible alert -------------------------------------------------
  const alertBefore = await page.evaluate(
    () => document.querySelectorAll("[role='alert']").length,
  );
  const dismiss = page.getByRole("button", { name: /dismiss|close/i });
  out.dismissButtons = await dismiss.count();
  if ((await dismiss.count()) > 0) {
    // The alert's own close button, not the modal's.
    await dismiss.last().click();
    await page.waitForTimeout(300);
  }
  out.alertsBefore = alertBefore;
  out.alertsAfter = await page.evaluate(
    () => document.querySelectorAll("[role='alert']").length,
  );

  // --- Tailwind tokens actually compiled (the safelist concern) ----------
  out.swatchClasses = await page.evaluate(() => {
    const wanted = [
      "bg-ink-50",
      "bg-ink-500",
      "bg-ink-950",
      "bg-ember-500",
      "bg-ember-900",
    ];
    const res = {};
    for (const w of wanted) res[w] = document.querySelectorAll("." + w).length;
    return res;
  });

  return out;
}
