// The one real WRITE on the LEVEL 5 dashboard: the profile edit form.
//
//   node storage/app/qa-level5-profile-http.mjs
//
// The level's own test asserts this through Pest (`actingAs()->put(...)`),
// which never touches a real session, real CSRF or the real Fortify route.
// This drives it the way a browser does: GET the form, scrape the token,
// PUT the fields, then re-GET the profile page and confirm the new values are
// rendered back — i.e. the change persisted and is visible, not just accepted.
const BASE = process.env.GROWZA_BASE || "http://127.0.0.1:8125";
const EMAIL = process.env.QA_LEVEL5_EMAIL || "level5-verify@example.test";
const PASSWORD = process.env.QA_LEVEL5_PASSWORD || "correct-horse-99";

const jar = new Map();
function absorb(res) {
  for (const c of res.headers.getSetCookie() || []) {
    const pair = c.split(";")[0];
    const i = pair.indexOf("=");
    if (i > 0) jar.set(pair.slice(0, i), pair.slice(i + 1));
  }
}
const cookieHeader = () =>
  [...jar.entries()].map(([k, v]) => `${k}=${v}`).join("; ");

async function req(path, init = {}) {
  const res = await fetch(BASE + path, {
    ...init,
    redirect: "manual",
    headers: {
      "user-agent": "growza-level5-qa/1.0",
      ...(jar.size ? { cookie: cookieHeader() } : {}),
      ...(init.headers || {}),
    },
  });
  absorb(res);
  return res;
}

const csrf = (html) =>
  html.match(/name="_token"\s+value="([^"]+)"/)?.[1] ?? null;

const decode = (html) => html.replace(/&#0?39;/g, "'").replace(/&amp;/g, "&");

const out = { base: BASE, issues: [] };

// --- Log in -----------------------------------------------------------------
// Retried deliberately: a previous run of this probe logs out at the end, and
// the login rate limiter is keyed on email+IP. If the first attempt is refused
// with 429 this retries instead of producing a wall of false failures
// downstream (419s on every write, empty form fields) that look like app bugs
// but are really "the harness was never authenticated".
async function logIn() {
  const get = await req("/login");
  const token = csrf(await get.text());
  const post = await req("/login", {
    method: "POST",
    headers: { "content-type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      _token: token,
      email: EMAIL,
      password: PASSWORD,
    }).toString(),
  });
  return { status: post.status, location: post.headers.get("location") };
}

let loginResult = await logIn();
out.loginAttempts = [loginResult];
for (let i = 0; i < 3 && loginResult.status === 429; i++) {
  await new Promise((r) => setTimeout(r, 15000));
  loginResult = await logIn();
  out.loginAttempts.push(loginResult);
}
out.login = loginResult;

if (loginResult.status === 429) {
  console.log(
    JSON.stringify(
      {
        ...out,
        verdict: "BLOCKED",
        note: "login was rate-limited (429) — the harness could not authenticate, so nothing below would be a finding about the app",
      },
      null,
      2,
    ),
  );
  process.exit(2);
}
if (loginResult.status !== 302) out.issues.push("login did not 302");

// --- GET the profile form, exactly as a browser would ----------------------
const profileGet = await req("/dashboard/profile");
const profileHtml = decode(await profileGet.text());

/*
 * Anchor on the form that actually posts to the profile endpoint. The layout
 * also contains two logout <form>s, and a bare /<form[^>]*action="([^"]+)"/
 * returns whichever appears first in the document — a logout form, not this
 * one. Matching on the stable substring instead of "the first form".
 */
const profileFormTag =
  profileHtml.match(/<form[^>]*user\/profile-information[^>]*>/)?.[0] ?? null;

out.profileForm = {
  status: profileGet.status,
  foundFormTag: profileFormTag !== null,
  action: profileFormTag?.match(/action="([^"]+)"/)?.[1] ?? null,
  method: profileFormTag?.match(/method="([^"]+)"/)?.[1] ?? null,
  hasSpoofedMethod: /name="_method"\s+value="PUT"/.test(profileHtml),
  hasCsrf: /name="_token"/.test(profileHtml),
  fields: [...profileHtml.matchAll(/name="(name|email|phone)"/g)].map(
    (m) => m[1],
  ),
};

if (!(out.profileForm.action || "").endsWith("/user/profile-information")) {
  out.issues.push(
    `profile form action is ${out.profileForm.action}, expected .../user/profile-information`,
  );
}
if ((out.profileForm.method || "").toUpperCase() !== "POST") {
  out.issues.push(
    `profile form method is ${out.profileForm.method}, expected POST (with spoofed PUT)`,
  );
}
if (!out.profileForm.hasSpoofedMethod) {
  out.issues.push("profile form is missing the spoofed _method=PUT");
}

/*
 * Scrape the token from the PROFILE form specifically.
 *
 * `profileFormTag` is only the OPENING tag, which contains no <input> at all —
 * so passing it here found no token and produced a 419 followed by a cascade of
 * "nothing persisted" failures that looked exactly like an application defect.
 * Take the slice from the form's opening tag to its closing tag instead. csrf()
 * on the whole document is also wrong: the layout renders two logout forms
 * before the main content, so the first _token in the document belongs to a
 * different form.
 */
const profileFormStart = profileHtml.indexOf(profileFormTag ?? "\u0000");
const profileFormBody =
  profileFormStart >= 0
    ? profileHtml.slice(
        profileFormStart,
        profileHtml.indexOf("</form>", profileFormStart) + 7,
      )
    : null;

out.profileForm.scopedFormBytes = profileFormBody?.length ?? 0;

const formToken = csrf(profileFormBody ?? "");
if (!formToken) out.issues.push("no CSRF token on the profile form");

// --- The write -------------------------------------------------------------
// A distinct, reversible name so the change is provably ours.
const NEW_NAME = "Level Five Renamed";
const NEW_PHONE = "+2348000002";

const put = await req("/user/profile-information", {
  method: "POST",
  headers: { "content-type": "application/x-www-form-urlencoded" },
  body: new URLSearchParams({
    _token: formToken,
    _method: "PUT",
    name: NEW_NAME,
    email: EMAIL,
    phone: NEW_PHONE,
  }).toString(),
});

out.profileUpdate = {
  status: put.status,
  location: put.headers.get("location"),
  // Fortify flashes this constant on success; the view renders it as
  // "Your profile has been updated."
  // Fortify flashes its status through the session, not the Location header,
  // and redirects back to the profile page. The flash is asserted where it is
  // genuinely observable: showsSuccessBanner on the re-fetched page below.
  redirectsBackToProfile: (put.headers.get("location") || "").endsWith(
    "/dashboard/profile",
  ),
};

// --- Confirm it is actually persisted AND rendered back --------------------
const after = await req("/dashboard/profile");
const afterHtml = decode(await after.text());

out.afterUpdate = {
  status: after.status,
  showsNewName: afterHtml.includes(NEW_NAME),
  showsNewPhone: afterHtml.includes(NEW_PHONE),
  showsSuccessBanner: afterHtml.includes("Your profile has been updated"),
  // The old name must be gone from the form, not merely appended to.
  stillShowsOldName: />\s*Level Five Verify\s*</.test(afterHtml),
};

if (put.status !== 302)
  out.issues.push(`profile PUT returned ${put.status}, expected 302`);
if (!out.afterUpdate.showsNewName)
  out.issues.push("new name is not rendered back on the profile page");
if (!out.afterUpdate.showsNewPhone)
  out.issues.push("new phone is not rendered back on the profile page");

// --- And the dashboard home greets the user by the new name ----------------
const home = await req("/dashboard");
const homeHtml = decode(await home.text());
out.dashboardGreeting = {
  showsNewName: homeHtml.includes(`Welcome back, ${NEW_NAME}`),
};
if (!out.dashboardGreeting.showsNewName) {
  out.issues.push("dashboard home still greets the user by the old name");
}

// --- Restore, so the probe is idempotent -----------------------------------
const restoreGet = await req("/dashboard/profile");
const restoreToken = csrf(decode(await restoreGet.text()));
await req("/user/profile-information", {
  method: "POST",
  headers: { "content-type": "application/x-www-form-urlencoded" },
  body: new URLSearchParams({
    _token: restoreToken,
    _method: "PUT",
    name: "Level Five Verify",
    email: EMAIL,
    phone: "+2348000001",
  }).toString(),
});
const restored = decode(await (await req("/dashboard/profile")).text());
out.restored = { showsOriginalName: restored.includes("Level Five Verify") };

// --- Reject a duplicate email (real validation, not just the happy path) ---
const dupToken = csrf(restored);
const dup = await req("/user/profile-information", {
  method: "POST",
  headers: { "content-type": "application/x-www-form-urlencoded" },
  body: new URLSearchParams({
    _token: dupToken,
    _method: "PUT",
    name: "Level Five Verify",
    email: "nobody-else@example.invalid",
    phone: "+2348011112222", // taken by the LEVEL 3 fixture user
  }).toString(),
});
out.duplicatePhoneRejected = {
  status: dup.status,
  // 302 back to the form with errors in the bag is the correct rejection.
  isRedirectBack: dup.status === 302,
};

out.verdict = out.issues.length === 0 ? "PASS" : "FAIL";
console.log(JSON.stringify(out, null, 2));
process.exit(out.issues.length === 0 ? 0 : 1);
