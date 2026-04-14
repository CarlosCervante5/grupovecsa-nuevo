#!/usr/bin/env python3
"""
Exporta entradas (post) y opcionalmente eventos (tribe_events) de vecsaexperience.com
vía la API REST pública de WordPress.

Documentación: https://developer.wordpress.org/rest-api/reference/posts/

Uso:
  python3 tools/export_vecsaexperience_wp.py
  python3 tools/export_vecsaexperience_wp.py --events
  python3 tools/export_vecsaexperience_wp.py --base https://vecsaexperience.com --out ruta/salida.json
"""
from __future__ import annotations

import argparse
import json
import re
import sys
import urllib.error
import urllib.request
from html import unescape
from pathlib import Path


def strip_tags(html: str) -> str:
    if not html:
        return ""
    t = re.sub(r"<[^>]+>", " ", html)
    t = unescape(re.sub(r"\s+", " ", t).strip())
    return t


def featured_url(post: dict) -> str | None:
    emb = post.get("_embedded") or {}
    media = emb.get("wp:featuredmedia") or []
    if not media:
        return None
    m0 = media[0]
    return m0.get("source_url") or (m0.get("media_details") or {}).get("sizes", {}).get("large", {}).get("source_url")


def category_names(post: dict) -> list[str]:
    emb = post.get("_embedded") or {}
    terms = emb.get("wp:term") or []
    names: list[str] = []
    for group in terms:
        if not isinstance(group, list):
            continue
        for t in group:
            if isinstance(t, dict) and t.get("taxonomy") == "category":
                names.append(t.get("name") or "")
    return [n for n in names if n]


def simplify_post(raw: dict) -> dict:
    title = (raw.get("title") or {}).get("rendered") or ""
    return {
        "id": raw.get("id"),
        "date": raw.get("date"),
        "modified": raw.get("modified"),
        "slug": raw.get("slug"),
        "link": raw.get("link"),
        "title": strip_tags(title),
        "title_html": title,
        "excerpt": strip_tags((raw.get("excerpt") or {}).get("rendered") or ""),
        "content_html": (raw.get("content") or {}).get("rendered") or "",
        "categories": category_names(raw),
        "featured_image": featured_url(raw),
    }


def fetch_all(base: str, rest_path: str, per_page: int = 100) -> list[dict]:
    """rest_path ej. wp/v2/posts o wp/v2/tribe_events"""
    base = base.rstrip("/")
    out: list[dict] = []
    page = 1
    while True:
        url = f"{base}/{rest_path}?per_page={per_page}&page={page}&status=publish&_embed=1"
        req = urllib.request.Request(url, headers={"User-Agent": "GrupoVecsaExport/1.0"})
        try:
            with urllib.request.urlopen(req, timeout=60) as resp:
                body = resp.read().decode("utf-8")
        except urllib.error.HTTPError as e:
            if e.code == 400 and page > 1:
                break
            raise
        chunk = json.loads(body)
        if not chunk:
            break
        out.extend(chunk)
        if len(chunk) < per_page:
            break
        page += 1
    return out


def main() -> int:
    p = argparse.ArgumentParser(description="Exportar posts WP de vecsaexperience.com")
    p.add_argument("--base", default="https://vecsaexperience.com", help="URL del sitio (sin barra final)")
    p.add_argument(
        "--out",
        type=Path,
        default=Path(__file__).resolve().parent / "data" / "vecsaexperience-posts.json",
        help="Archivo JSON de salida",
    )
    p.add_argument("--events", action="store_true", help="También exportar tribe_events a vecsaexperience-events.json")
    args = p.parse_args()

    posts_raw = fetch_all(args.base, "wp-json/wp/v2/posts")
    simplified = [simplify_post(x) for x in posts_raw]

    args.out.parent.mkdir(parents=True, exist_ok=True)
    args.out.write_text(json.dumps(simplified, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"Posts: {len(simplified)} → {args.out}")

    if args.events:
        ev_path = args.out.with_name("vecsaexperience-events.json")
        events_raw = fetch_all(args.base, "wp-json/wp/v2/tribe_events")
        ev_out = [simplify_post(x) for x in events_raw]
        ev_path.write_text(json.dumps(ev_out, ensure_ascii=False, indent=2), encoding="utf-8")
        print(f"Eventos: {len(ev_out)} → {ev_path}")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
