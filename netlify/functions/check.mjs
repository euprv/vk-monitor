import { getStore } from "@netlify/blobs";
import { SERVICES } from "./services.mjs";

export const config = { schedule: "*/5 * * * *" };

async function probe(s) {
  const t = Date.now(), c = new AbortController();
  const to = setTimeout(() => c.abort(), 8000);
  try {
    const r = await fetch(s.url, { redirect: "follow", signal: c.signal, headers: { "user-agent": "VKStatusMonitor/1.0" } });
    return { up: r.status < 500, code: r.status, ms: Date.now() - t };
  } catch { return { up: false, code: 0, ms: Date.now() - t }; }
  finally { clearTimeout(to); }
}

export default async () => {
  const store = getStore("monitor");
  const db = (await store.get("db", { type: "json" })) || { h: {}, inc: [] };
  const now = Date.now();
  const res = await Promise.all(SERVICES.map(probe));
  SERVICES.forEach((s, i) => {
    const r = res[i], h = db.h[s.id] || (db.h[s.id] = []), prev = h[h.length - 1];
    h.push({ t: now, up: r.up, ms: r.ms, code: r.code });
    if (h.length > 2016) h.shift(); // 7 суток при шаге 5 минут
    if (prev && prev.up && !r.up) db.inc.unshift({ id: s.id, name: s.name, from: now, to: null });
    if (prev && !prev.up && r.up) { const o = db.inc.find(x => x.id === s.id && !x.to); if (o) o.to = now; }
  });
  db.inc = db.inc.slice(0, 100);
  await store.setJSON("db", db);
};
