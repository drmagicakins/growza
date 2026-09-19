// Real-browser QA of the LEVEL 2 marketing site.
//
// Usage:
//   node <skill-dir>/browser.mjs http://127.0.0.1:8125/ --script ./storage/app/qa-level2.mjs
//
// NOTE ON METHOD — this driver's page does not execute document script (see
// storage/app/HANDOFF.md). So this script asserts on the SERVED HTML and on the
// HTTP layer, which the driver cannot fake, and does NOT treat "no JS ran" as a
// finding about the app. Alpine-dependent behaviour is asserted server-side.
export default async function run(page) {
  const out = { pages: [] };

  const ROUTES = [
    "/",
    "/services",
    "/pricing",
    "/how-it-works",
    "/why-growza",
    "/faq",
    "/contact",
    "/legal/terms",
    "/legal/privacy",
    "/legal/refund-policy",
    "/legal/acceptable-use",
    "/legal/cookie-policy",
  ];

  for (const route of ROUTES) {
    const res = await page.goto("http://127.0.0.1:8125" + route, {
      waitUntil: "domcontentloaded",
      timeout: 30000,
    });

    const info = await page.evaluate(() => {
      const html = document.documentElement.outerHTML;
      const title = document.title;
      const desc = document
        .querySelector('meta[name="description"]')
        ?.getAttribute("content");
      const canonical = document
        .querySelector('link[rel="canonical"]')
        ?.getAttribute("href");
      const robots = document
        .querySelector('meta[name="robots"]')
        ?.getAttribute("content");
      const ogTitle = document
        .querySelector('meta[property="og:title"]')
        ?.getAttribute("content");
      return {
        title,
        descChars: desc ? desc.length : 0,
        canonical: canonical ? canonical.replace(location.origin, "") : null,
        robots,
        ogTitle,
        h1:
          document.querySelector("h1")?.textContent.trim().slice(0, 70) ?? null,
        // A skip link is the accessibility marker the layout promises.
        hasSkipLink: /skip to content/i.test(html),
        // Broken route() calls render as an exception page, not a normal page.
        looksLikeException:
          /Route \[.*\] not defined|Undefined variable|ViewException/i.test(
            html,
          ),
        bodyChars: document.body ? document.body.innerText.length : 0,
      };
    });

    out.pages.push({ route, status: res.status(), ...info });
  }

  // --- Contact form: the one real round-trip on the site --------------------
  await page.goto("http://127.0.0.1:8125/contact", {
    waitUntil: "domcontentloaded",
  });

  out.contactForm = await page.evaluate(() => {
    const form = document.querySelector("form");
    if (!form) return { found: false };
    const fields = [...form.querySelectorAll("input,textarea,select")].map(
      (f) => ({
        name: f.getAttribute("name"),
        type: f.getAttribute("type") || f.tagName.toLowerCase(),
        required: f.hasAttribute("required"),
      }),
    );
    return {
      found: true,
      action: form.getAttribute("action"),
      method: (form.getAttribute("method") || "get").toUpperCase(),
      // CSRF must be present or the POST will 419.
      hasCsrf: !!form.querySelector('input[name="_token"]'),
      // Honeypot: present, and must be visually hidden from humans.
      honeypot: !!form.querySelector('input[name="website"]'),
      fields,
    };
  });

  // Actually submit it, through the real form, and see where it lands.
  try {
    await page.fill('input[name="name"]', "QA Harness");
    await page.fill('input[name="email"]', "qa@example.test");
    await page.fill(
      'textarea[name="message"]',
      "This is an automated deliverability check submitted by the LEVEL 2 QA harness. It should persist to contact_messages and redirect back to /contact with a status message.",
    );
    const sel = await page.$('select[name="subject"]');
    if (sel) await page.selectOption('select[name="subject"]', "campaign");
    await Promise.all([
      page.waitForNavigation({ waitUntil: "domcontentloaded", timeout: 20000 }),
      page.click('button[type="submit"], input[type="submit"]'),
    ]);
    out.formSubmit = await page.evaluate(() => ({
      url: location.pathname,
      // The controller flashes 'status' and redirects to route('contact').
      hasStatusMessage: /with us|reply within|one business day/i.test(
        document.body.innerText,
      ),
      validationErrors: document.querySelectorAll(
        ".text-danger-600, [role='alert']",
      ).length,
    }));
  } catch (e) {
    out.formSubmit = { error: String(e.message || e).slice(0, 300) };
  }

  return out;
}
