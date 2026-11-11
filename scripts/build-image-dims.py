#!/usr/bin/env python3
# Build config/image_dims.json — a width/height map for every image the site
# can reference, so <img> tags can carry explicit dimensions (CLS fix).
import glob, os, json, re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
os.chdir(ROOT)

def dims_of(p):
    low = p.lower()
    try:
        if low.endswith(('.webp', '.png', '.jpg', '.jpeg')):
            from PIL import Image
            with Image.open(p) as im:
                return im.size
        if low.endswith('.svg'):
            txt = open(p, encoding='utf-8', errors='ignore').read()[:6000]
            m = re.search(r'viewBox="[^"]*?([0-9.]+)\s+([0-9.]+)"', txt)
            if m:
                return int(float(m.group(1))), int(float(m.group(2)))
            m = re.search(r'width="([0-9]+)"\s+height="([0-9]+)"', txt)
            if m:
                return int(m.group(1)), int(m.group(2))
            return 24, 24
    except Exception:
        pass
    return None

mapping = {}
exts = ['**/*.webp', '**/*.png', '**/*.jpg', '**/*.jpeg', '**/*.svg']
dirs = ['uploads', 'vikaas_inputs', 'public']
for d in dirs:
    for pat in exts:
        for p in glob.glob(os.path.join(d, pat), recursive=True):
            dd = dims_of(p)
            if dd:
                key = '/' + p.replace(os.sep, '/')
                mapping[key] = {'w': dd[0], 'h': dd[1]}
for p in glob.glob('*.webp') + glob.glob('*.svg') + glob.glob('*.png'):
    dd = dims_of(p)
    if dd:
        mapping['/' + p] = {'w': dd[0], 'h': dd[1]}

# Stable ordering
mapping = {k: mapping[k] for k in sorted(mapping)}
with open('config/image_dims.json', 'w') as f:
    json.dump(mapping, f, separators=(',', ':'))
print(f"mapped {len(mapping)} images -> config/image_dims.json")
