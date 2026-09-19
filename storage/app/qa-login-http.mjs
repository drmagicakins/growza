// LEVEL 3 auth flows over raw HTTP: login, protected access, logout, and the
// verification gate. Zero dependencies (Node fetch + manual cookie jar).
export default async function run() {
  const out = {};
  const BASE = "http://127.0.0.1:8125";

  // Use the account the registration script just created.
  const email = process.env.QA_EMAIL;
  const password = "Correct-Horse-9x";
  out.email = email;

  let cookie = "";
  const capture = (res) => {
    for (const c of res.headers.getSetCookie?.() ?? []) {
      const [pair] = c.split(";");
      const name = pair.split("=")[0];
      cookie = cookie
        .split("; ")
        .filter((p) => p && !p.startsWith(name + "="))
        .concat(pair)
        .join("; ");
    }
  };
  const get = async (path) => {
    const r = await fetch(`${BASE}${path}`, {
      redirect: "manual",
      headers: cookie ? { Cookie: cookie } : {},
    });
    return r;
  };
  const tokenFrom = (html) =>
    html.match(/name="_token"\s+value="([^"]+)"/)?.[1] ?? null;

  // --- 1. Unauthenticated /dashboard redirects to /login ------------------
  const anon = await fetch(`${BASE}/dashboard`, { redirect: "manual" });
  out.anonDashboard = {
    status: anon.status,
    location: anon.headers.get("location"),
  };

  // --- 2. Log in with the right password -----------------------------------
  const loginPage = await get("/login");
  capture(loginPage);
  const loginHtml = await loginPage.text();
  const loginToken = tokenFrom(loginHtml);
  out.loginPageStatus = loginPage.status;

  const loginRes = await fetch(`${BASE}/login`, {
    method: "POST",
    redirect: "manual",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      Cookie: cookie,
    },
    body: new URLSearchParams({
      _token: loginToken,
      email,
      password,
    }).toString(),
  });
  capture(loginRes);
  out.loginStatus = loginRes.status;
  out.loginLocation = loginRes.headers.get("location");

  // --- 3. Authenticated /dashboard within the same session -----------------
  const dashRes = await get("/dashboard");
  const dashHtml = await dashRes.text();
  out.dashboard = {
    status: dashRes.status,
    location: dashRes.headers.get("location"),
    title: dashHtml.match(/<title>(.*?)<\/title>/)?.[1] ?? null,
    mentionsVerify: /verify your email|verification/i.test(dashHtml),
  };

  // --- 4. Settings > Security (2FA surface) --------------------------------
  const secRes = await get("/settings/security");
  const secHtml = await secRes.text();
  out.settingsSecurity = {
    status: secRes.status,
    location: secRes.headers.get("location"),
    title: secHtml.match(/<title>(.*?)<\/title>/)?.[1] ?? null,
    has2faMarkup: /two-factor|two factor|2fa/i.test(secHtml),
  };

  // --- 5. Log out, then confirm the session is really dead -----------------
  // NOTE: fetch /email/verify, not /dashboard. An unverified user is bounced,
  // so /dashboard never renders the logout form and scraping its token yields
  // null -> a 419. That is the harness's bug, not the app's.
  const logoutPage = await get("/email/verify");
  const loHtml = await logoutPage.text();
  const loToken = tokenFrom(loHtml);
  out.logoutTokenFound = !!loToken;
  const loRes = await fetch(`${BASE}/logout`, {
    method: "POST",
    redirect: "manual",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      Cookie: cookie,
    },
    body: new URLSearchParams({ _token: loToken }).toString(),
  });
  out.logoutStatus = loRes.status;
  out.logoutLocation = loRes.headers.get("location");

  const afterLogout = await get("/dashboard");
  out.dashboardAfterLogout = {
    status: afterLogout.status,
    location: afterLogout.headers.get("location"),
  };

  // --- 6. Wrong password must not authenticate -----------------------------
  const p2 = await fetch(`${BASE}/login`, { redirect: "manual" });
  let jar2 = (p2.headers.getSetCookie?.() ?? [])
    .map((c) => c.split(";")[0])
    .join("; ");
  const p2html = await p2.text();
  const badRes = await fetch(`${BASE}/login`, {
    method: "POST",
    redirect: "manual",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      Cookie: jar2,
    },
    body: new URLSearchParams({
      _token: tokenFrom(p2html),
      email,
      password: "wrong-password-x",
    }).toString(),
  });
  out.wrongPassword = {
    status: badRes.status,
    location: badRes.headers.get("location"),
  };

  return out;
}
