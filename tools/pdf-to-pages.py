#!/usr/bin/env python3
"""
PDF -> страницы для листалки на странице /publications/<slug>.

Каждая страница PDF превращается в лёгкую WebP-картинку (public/img/publications/<slug>/001.webp, 002.webp, ...),
плюс обложка-миниатюра cover.webp для карточки в списке. В resources/data/publications.json создаётся (или обновляется)
запись с числом страниц и пропорцией листа; названия и описание на трёх языках нужно вписать вручную.

Запуск (один раз на документ, на своём компьютере; на сервере PDF не нужен):

    pip install pymupdf pillow
    python tools/pdf-to-pages.py "user/публикации/2025 INSPIRE СБОРНИК.pdf" inspire-2025-collection

Необязательно: --width 1240 (ширина страницы в пикселях), --quality 74.
Результат кладётся в git как обычные картинки сайта.
"""
import argparse
import io
import json
import shutil
import sys
from pathlib import Path

try:
    import pymupdf
    from PIL import Image
except ImportError:
    sys.exit('Нужны пакеты: pip install pymupdf pillow')

ROOT = Path(__file__).resolve().parent.parent
DATA = ROOT / 'resources' / 'data' / 'publications.json'


def main() -> None:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument('pdf')
    ap.add_argument('slug', help='короткое имя латиницей: будет в адресе /publications/<slug>')
    ap.add_argument('--width', type=int, default=1240)
    ap.add_argument('--quality', type=int, default=74)
    args = ap.parse_args()

    out = ROOT / 'public' / 'img' / 'publications' / args.slug
    # страницы, убранные из издания ("skip" в publications.json), заново не создаются
    known = json.loads(DATA.read_text(encoding='utf-8')) if DATA.exists() else {'items': []}
    skip = set(next((e.get('skip', []) for e in known['items'] if e['slug'] == args.slug), []))
    if out.exists():
        shutil.rmtree(out)
    out.mkdir(parents=True)

    doc = pymupdf.open(args.pdf)
    total = len(doc)
    first = doc[0].rect
    ratio = round(first.width / first.height, 4)
    size = 0

    for i, page in enumerate(doc, start=1):
        if i in skip:
            continue
        zoom = args.width / page.rect.width
        pix = page.get_pixmap(matrix=pymupdf.Matrix(zoom, zoom), alpha=False)
        img = Image.frombytes('RGB', (pix.width, pix.height), pix.samples)
        buf = io.BytesIO()
        img.save(buf, 'WEBP', quality=args.quality, method=6)
        (out / f'{i:03d}.webp').write_bytes(buf.getvalue())
        size += len(buf.getvalue())

        if i == 1:
            thumb = img.resize((600, round(600 / ratio)), Image.LANCZOS)
            thumb.save(out / 'cover.webp', 'WEBP', quality=82, method=6)

        print(f'\r{i}/{total}', end='', flush=True)

    print(f'\nГотово: {total} стр., {size / 1048576:.1f} МБ, пропорция {ratio} -> {out}')

    data = json.loads(DATA.read_text(encoding='utf-8')) if DATA.exists() else {'items': []}
    entry = next((e for e in data['items'] if e['slug'] == args.slug), None)
    if entry is None:
        entry = {
            'slug': args.slug,
            'year': 0,
            'title': {'ru': '', 'en': '', 'ro': ''},
            'description': {'ru': '', 'en': '', 'ro': ''},
        }
        data['items'].append(entry)
        print('Добавлена запись в resources/data/publications.json: впишите название, описание и год.')
    entry['pages'] = total
    entry['ratio'] = ratio
    entry['cover'] = 'cover.webp'
    DATA.write_text(json.dumps(data, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')


if __name__ == '__main__':
    main()
