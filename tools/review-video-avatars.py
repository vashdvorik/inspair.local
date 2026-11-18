#!/usr/bin/env python3
"""
Кружки для ленты видео-отзывов на главной: кадр из YouTube Shorts -> квадрат 240 px WebP в public/img/reviews/video/<id>.webp.
Список роликов лежит в resources/data/review-videos.json. Запуск: python tools/review-video-avatars.py
"""
import io, json, sys, urllib.request
from pathlib import Path
from PIL import Image, ImageStat

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / 'public' / 'img' / 'reviews' / 'video'
OUT.mkdir(parents=True, exist_ok=True)

for item in json.loads((ROOT / 'resources' / 'data' / 'review-videos.json').read_text(encoding='utf-8'))['items']:
    vid = item['id']
    # YouTube держит три кадра ролика (oar1-oar3); берём самый светлый, чтобы не попасть на чёрный экран
    frames = []
    for n in (2, 1, 3):
        try:
            raw = urllib.request.urlopen(urllib.request.Request(f'https://i.ytimg.com/vi/{vid}/oar{n}.jpg', headers={'User-Agent': 'Mozilla/5.0'}), timeout=30).read()
            frames.append(Image.open(io.BytesIO(raw)).convert('RGB'))
        except Exception:
            pass
    im = max(frames, key=lambda f: ImageStat.Stat(f.resize((32, 32)).crop((0, 0, 32, 20))).mean[0])
    w, h = im.size
    top = int(h * 0.04)  # лицо в вертикальном кадре почти всегда в верхней половине
    im = im.crop((0, top, w, top + w)).resize((240, 240), Image.LANCZOS)
    im.save(OUT / f'{vid}.webp', 'WEBP', quality=82, method=6)
    print('ok', vid)
