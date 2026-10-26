import { getStore } from "@netlify/blobs";
import { SERVICES } from "./services.mjs";

export default async () => {
  const db = (await getStore("monitor").get("db", { type: "json" })) || { h: {}, inc: [] };
  const now = Date.now();
  const services = SERVICES.map(s => {
    const h = db.h[s.id] || [], day = h.filter(x => x.t > now - 864e5);
    const ups = day.filter(x => x.up), last = h[h.length - 1];
    return {
      id: s.id, name: s.name, url: s.url,
      up: last ? last.up : null, ms: last ? last.ms : null, code: last ? last.code : null,
      uptime: day.length ? +(ups.length / day.length * 100).toFixed(2) : null,
      avg: ups.length ? Math.round(ups.reduce((a, x) => a + x.ms, 0) / ups.length) : null,
      history: h.slice(-48).map(x => ({ t: x.t, up: x.up, ms: x.ms })),
    };
  });
  return Response.json({ updated: now, services, incidents: db.inc.slice(0, 30) }, { headers: { "cache-control": "public, max-age=30" } });
};
