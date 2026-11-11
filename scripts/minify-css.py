#!/usr/bin/env python3
# minify-css.py — safe, dependency-free CSS minifier for styles.css
# Usage: python scripts/minify-css.py            (in-place, writes .bak first)
#        python scripts/minify-css.py out.css   (write to out.css)
import sys, re, os, shutil

def minify_css(css: str) -> str:
    # 1. Strip /* ... */ comments (never inside a data: url, but our file has none)
    css = re.sub(r'/\*.*?\*/', '', css, flags=re.S)
    # 2. Remove whitespace around CSS structure tokens (safe after comments gone)
    css = re.sub(r'\s*([{}:;,>])\s*', r'\1', css)
    css = re.sub(r';}', '}', css)
    # 3. Collapse any remaining multi-space / newlines
    css = re.sub(r'[ \t]{2,}', ' ', css)
    css = re.sub(r'\s*\n\s*', '\n', css)
    css = re.sub(r'\n+', '\n', css)
    return css.strip()

src = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'styles.css')
out = sys.argv[1] if len(sys.argv) > 1 else src

css = open(src, encoding='utf-8').read()
mini = minify_css(css)

if out == src:
    if not os.path.exists(src + '.orig'):
        shutil.copy2(src, src + '.orig')  # pristine pre-minification backup
open(out, 'w', encoding='utf-8', newline='\n').write(mini)
print(f"styles.css: {len(css):,} -> {len(mini):,} bytes ({100 - 100*len(mini)//max(1,len(css))}% smaller)")
