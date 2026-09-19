// Does the FAQ disclosure actually open? This is the user-visible consequence
// of Alpine never initialising: the answers stay hidden and the click does
// nothing. Asserting on the live computed display, not on the x-show attribute.
export default async function run(page) {
  const out = {};
  // If Alpine never initialised, x-cloak never gets stripped and x-show never
  // gets resolved — so the raw directives are still sitting in the DOM.
  out.unprocessedDirectives = await page.evaluate(() =>
    [...document.querySelectorAll("*")]
      .map((el) => [...el.attributes].map((a) => a.name))
      .flat()
      .filter((n) => n.startsWith("x-") || n.startsWith(":"))
      .reduce((acc, n) => ((acc[n] = (acc[n] || 0) + 1), acc), {}),
  );

  const answersVisible = () =>
    page.evaluate(() => {
      const bodies = [...document.querySelectorAll("[x-show][x-cloak]")];
      return {
        answerCount: bodies.length,
        visibleCount: bodies.filter(
          (el) => getComputedStyle(el).display !== "none",
        ).length,
      };
    });

  const before = await answersVisible();

  // The FAQ question buttons live inside each [x-data] disclosure scope. The
  // nav bar's mobile toggle is ALSO a button[aria-expanded], and it is hidden
  // at desktop widths, so scope to the disclosure wrappers to avoid clicking it.
  const disclosure = page.locator("main [x-data] button[type='button']");
  const n = await disclosure.count();
  out.disclosureButtons = n;

  if (n === 0) {
    out.error = "no FAQ disclosure buttons found in main";
    return out;
  }

  const first = disclosure.first();
  out.labelBefore = (await first.innerText()).slice(0, 60);
  await first.click({ force: true });
  await page.waitForTimeout(500);

  out.answersBefore = before;
  out.answersAfterClick = await answersVisible();
  out.expandedAfterClick = await first.getAttribute("aria-expanded");
  out.concludedWorking =
    out.answersAfterClick.visibleCount > before.visibleCount;
  return out;
}
