#!/usr/bin/env python3
"""
Посты Instagram -> раздел «Возможности» (/opportunities).

Для каждой ссылки берётся публичная страница встраивания поста (вход в Instagram не нужен): текст подписи, первое фото и дата
(её можно вычислить из кода поста). Фото сохраняется на сайте в public/img/opportunities/<код>.webp (ссылки Instagram
со временем перестают работать), запись добавляется в resources/data/opportunities.json. Если запись уже есть, обновляются
только фото и исходный текст, а переводы остаются.

    pip install pillow
    python tools/instagram-post.py https://www.instagram.com/p/XXXX/ https://www.instagram.com/p/YYYY/

Названия, страна и короткие тексты на ru/en/ro вносятся в json отдельно (поле "caption" хранит оригинал подписи).
"""
import datetime
import html
import io
import json
import re
import sys
import urllib.request
from pathlib import Path

try:
    from PIL import Image
except ImportError:
    sys.exit('Нужен пакет: pip install pillow')

ROOT = Path(__file__).resolve().parent.parent
DATA = ROOT / 'resources' / 'data' / 'opportunities.json'
IMG = ROOT / 'public' / 'img' / 'opportunities'
ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_'
UA = {'User-Agent': 'Mozilla/5.0'}


def fetch(url: str) -> bytes:
    with urllib.request.urlopen(urllib.request.Request(url, headers=UA), timeout=40) as r:
        return r.read()


def date_from_code(code: str) -> str:
    """Код поста Instagram кодирует номер публикации, а в нём время в миллисекундах."""
    n = 0
    for ch in code:
        n = n * 64 + ALPHABET.index(ch)
    ms = (n >> 23) + 1314220021721
    return datetime.datetime.fromtimestamp(ms / 1000, datetime.timezone.utc).strftime('%Y-%m-%d')


def parse(code: str) -> dict:
    page = fetch(f'https://www.instagram.com/p/{code}/embed/captioned/').decode('utf-8', 'ignore')

    start = page.find('class="Caption"')
    end = page.find('</div>', start)  # подпись целиком лежит в одном div
    block = page[start:end] if start >= 0 else ''
    block = re.sub(r'<a class="CaptionUsername".*?</a>', '', block, flags=re.S)
    text = html.unescape(re.sub(r'<[^>]+>', '', re.sub(r'<br\s*/?>', '\n', block.split('>', 1)[-1])))
    text = re.sub(r'[ \t]+', ' ', text)
    text = re.sub(r'\n\s*\n\s*\n+', '\n\n', text).strip()

    m = re.search(r'EmbeddedMediaImage"[^>]*?src="([^"]+)"', page)
    if not m:
        raise RuntimeError(f'{code}: фото не найдено (пост закрыт или удалён?)')

    return {'caption': text, 'image_url': html.unescape(m.group(1))}


def main() -> None:
    if len(sys.argv) < 2:
        sys.exit(__doc__)

    data = json.loads(DATA.read_text(encoding='utf-8')) if DATA.exists() else {'items': []}
    IMG.mkdir(parents=True, exist_ok=True)

    for link in sys.argv[1:]:
        m = re.search(r'/(?:p|reel)/([A-Za-z0-9_-]+)', link)
        if not m:
            print('Пропуск, не похоже на ссылку на пост:', link)
            continue
        code = m.group(1)
        info = parse(code)

        img = Image.open(io.BytesIO(fetch(info['image_url']))).convert('RGB')
        img.thumbnail((900, 1200), Image.LANCZOS)  # 900 px хватает карточке и странице, а файл втрое легче
        img.save(IMG / f'{code}.webp', 'WEBP', quality=78, method=6)

        entry = next((e for e in data['items'] if e['code'] == code), None)
        if entry is None:
            entry = {'code': code, 'url': f'https://www.instagram.com/p/{code}/', 'date': date_from_code(code)}
            data['items'].append(entry)
            print(f'+ {code} добавлен ({entry["date"]}): впишите flag, country, name, text')
        else:
            print(f'= {code} обновлён')
        entry['image'] = f'{code}.webp'
        entry['image_ratio'] = round(img.width / img.height, 4)
        entry['caption'] = info['caption']

    data['items'].sort(key=lambda e: e['date'], reverse=True)
    DATA.write_text(json.dumps(data, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')


if __name__ == '__main__':
    main()
