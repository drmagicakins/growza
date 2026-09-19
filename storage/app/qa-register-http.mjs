// Registration over raw HTTP, zero dependencies: Node's built-in fetch plus a
// hand-rolled cookie jar. Removes every driver/browser variable and asks only
// whether the app accepts a valid registration and persists it.
export default async function run() {
  const out = {};
  const BASE = "http://127.0.0.1:8125";
  const email = `qa-http-${Date.now()}@example.test`;
  out.email = email;

  let cookie = "";

  const captureCookies = (res) => {
    const set = res.headers.getSetCookie?.() ?? [];
    for (const c of set) {
      const [pair] = c.split(";");
      const [name] = pair.split("=");
      const kept = cookie
        .split("; ")
        .filter((p) => p && !p.startsWith(name + "="));
      kept.push(pair);
      cookie = kept.join("; ");
    }
  };

  // 1. GET /register -> CSRF token + session cookie.
  const getRes = await fetch(`${BASE}/register`, { redirect: "manual" });
  captureCookies(getRes);
  const html = await getRes.text();
  out.getStatus = getRes.status;
  const token = html.match(/name="_token"\s+value="([^"]+)"/)?.[1] ?? null;
  out.tokenFound = !!token;
  out.cookieNames = cookie.split("; ").map((c) => c.split("=")[0]);

  if (!token) {
    out.htmlSnippet = html.slice(0, 300);
    return out;
  }

  // 2. POST the registration, no auto-redirect so the 302 is visible.
  const body = new URLSearchParams({
    _token: token,
    name: "QA Http",
    email,
    phone: "+2348099" + String(Date.now()).slice(-6),
    password: "Correct-Horse-9x",
    password_confirmation: "Correct-Horse-9x",
    // Required by CreateNewUser: 'terms' => ['accepted']. Omitting it is
    // correctly rejected - which is how this harness failed the first time.
    terms: "1",
  });

  const postRes = await fetch(`${BASE}/register`, {
    method: "POST",
    redirect: "manual",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      Cookie: cookie,
    },
    body: body.toString(),
  });

  out.postStatus = postRes.status;
  out.location = postRes.headers.get("location");
  const postBody = await postRes.text();
  out.postBytes = postBody.length;

  out.validationErrors = [...postBody.matchAll(/text-danger[^>]*>([^<]+)</g)]
    .map((m) => m[1].trim())
    .slice(0, 8);
  out.redirected = out.postStatus >= 300 && out.postStatus < 400;

  return out;
}
