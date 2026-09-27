import os, sys, time, json, re, gc
import numpy as np
from PIL import Image
from scipy.ndimage import label, binary_fill_holes

os.environ["OPENBLAS_NUM_THREADS"] = "1"
os.environ["MKL_NUM_THREADS"] = "1"

def remove_background(im):
    if im.mode == 'CMYK':
        im = im.convert('RGB')
    elif im.mode not in ('RGB', 'RGBA'):
        im = im.convert('RGBA')

    arr = np.array(im)
    h, w = arr.shape[:2]
    rgb = arr[:, :, :3].astype(np.int16)

    is_bg_candidate = np.zeros((h, w), dtype=bool)

    # 1. White / Off-white background check
    diff_white = np.sqrt(np.sum((255 - rgb)**2, axis=-1))
    is_bg_candidate |= (diff_white < 42) | np.all(rgb > 245, axis=-1)

    # 2. Sample from border & corners
    corners = [(0, 0), (0, w-1), (h-1, 0), (h-1, w-1),
               (0, w//2), (h-1, w//2), (h//2, 0), (h//2, w-1)]
    for y, x in corners:
        col = rgb[y, x]
        dist = np.sqrt(np.sum((rgb - col)**2, axis=-1))
        is_bg_candidate |= (dist < 42)

    # 3. Connectivity check to outside borders
    structure = np.array([[0, 1, 0], [1, 1, 1], [0, 1, 0]], dtype=bool)
    labeled, _ = label(is_bg_candidate, structure=structure)

    border_labels = set(np.unique(labeled[0, :])) | \
                    set(np.unique(labeled[-1, :])) | \
                    set(np.unique(labeled[:, 0])) | \
                    set(np.unique(labeled[:, -1]))
    border_labels.discard(0)

    bg_mask = np.isin(labeled, list(border_labels))
    fg_mask = binary_fill_holes(~bg_mask)

    alpha = np.where(fg_mask, 255, 0).astype(np.uint8)

    # Respect any existing alpha channel in source image
    if arr.shape[2] == 4:
        alpha = np.minimum(alpha, arr[:, :, 3])

    result = np.dstack([arr[:, :, :3].astype(np.uint8), alpha])
    out = Image.fromarray(result, 'RGBA')

    # Trim excess transparent border with 20px padding
    bbox = out.getbbox()
    if bbox:
        pad = 20
        crop_box = (
            max(0, bbox[0] - pad),
            max(0, bbox[1] - pad),
            min(out.width, bbox[2] + pad),
            min(out.height, bbox[3] + pad)
        )
        out = out.crop(crop_box)

    return out

def main():
    t0 = time.time()
    manifest_path = 'scratch/manifest.json'
    with open(manifest_path, 'r') as f:
        items = json.load(f)

    total = len(items)
    print(f"Loaded {total} products from manifest.", flush=True)

    base_upload = os.path.join(os.getcwd(), 'uploads', 'products')
    public_upload = os.path.join(os.getcwd(), 'public', 'uploads', 'products')

    processed = 0
    cached = 0
    errors = 0

    for i, item in enumerate(items, 1):
        cat_slug = item['category_slug']
        slug = item['slug']
        webp_name = f"{slug}.webp"

        dst1 = os.path.join(base_upload, cat_slug, webp_name)
        dst2 = os.path.join(public_upload, cat_slug, webp_name)
        rel_url = f"/uploads/products/{cat_slug}/{webp_name}"
        item['image_url'] = rel_url

        os.makedirs(os.path.dirname(dst1), exist_ok=True)
        os.makedirs(os.path.dirname(dst2), exist_ok=True)

        if os.path.exists(dst1) and os.path.getsize(dst1) > 1000 and os.path.exists(dst2):
            cached += 1
            continue

        src = item['source_img']
        try:
            with Image.open(src) as raw_im:
                # Downscale to max 1000px using fast bilinear
                w, h = raw_im.size
                scale = min(1.0, 1000 / max(w, h))
                if scale < 1.0:
                    im_scaled = raw_im.resize((int(w * scale), int(h * scale)), Image.Resampling.BILINEAR)
                else:
                    im_scaled = raw_im.copy()

                clean_im = remove_background(im_scaled)
                clean_im.save(dst1, 'WEBP', quality=88, method=4)
                clean_im.save(dst2, 'WEBP', quality=88, method=4)
                processed += 1

        except Exception as e:
            print(f"Error on {slug}: {e}", flush=True)
            errors += 1

        gc.collect()

        if i % 25 == 0 or i == total:
            print(f"[{i}/{total}] Converted: {processed}, Cached: {cached}, Errors: {errors}, Elapsed: {time.time()-t0:.1f}s", flush=True)

    print(f"\nCompleted! Newly converted: {processed}, Cached: {cached} in {time.time()-t0:.1f}s! Errors: {errors}", flush=True)

    with open('scratch/manifest_enriched.json', 'w') as f:
        json.dump(items, f, indent=2)
    print("Saved scratch/manifest_enriched.json", flush=True)

if __name__ == '__main__':
    main()
