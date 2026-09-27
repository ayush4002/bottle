import os, sys, time, json, re
from PIL import Image
import numpy as np
import rembg

def main():
    print("Starting TrueNorth Product & Image Ingestion Pipeline...")
    t_start = time.time()

    with open('scratch/manifest.json', 'r') as f:
        items = json.load(f)

    total = len(items)
    print(f"Loaded {total} products from manifest.")

    session = rembg.new_session('u2netp')
    print("AI Background removal session (u2netp) initialized.")

    processed_count = 0
    skipped_count = 0

    base_upload = os.path.join(os.getcwd(), 'uploads', 'products')
    public_upload = os.path.join(os.getcwd(), 'public', 'uploads', 'products')

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

        # Check if already processed and valid
        if os.path.exists(dst1) and os.path.getsize(dst1) > 1000 and os.path.exists(dst2):
            skipped_count += 1
            continue

        src = item['source_img']
        try:
            with Image.open(src) as im:
                # Convert CMYK/P to RGBA/RGB
                if im.mode == 'CMYK':
                    im = im.convert('RGB')
                
                w, h = im.size
                scale = min(1.0, 1200 / max(w, h))
                if scale < 1.0:
                    im_resized = im.resize((int(w * scale), int(h * scale)), Image.Resampling.LANCZOS)
                else:
                    im_resized = im

                # AI Background Removal
                out = rembg.remove(im_resized, session=session)

                # Crop transparent padding
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

                # Save as transparent WebP
                out.save(dst1, 'WEBP', quality=90, method=6)
                out.save(dst2, 'WEBP', quality=90, method=6)
                processed_count += 1

        except Exception as e:
            print(f"Error processing {src}: {e}")

        if i % 25 == 0 or i == total:
            elapsed = time.time() - t_start
            print(f"[{i}/{total}] Processed: {processed_count}, Cached: {skipped_count}, Elapsed: {elapsed:.1f}s")

    print(f"\nAll images processed! Newly converted: {processed_count}, Already cached: {skipped_count}.")
    print(f"Total time for images: {time.time() - t_start:.1f}s")

    # Save enriched manifest
    with open('scratch/manifest_enriched.json', 'w') as f:
        json.dump(items, f, indent=2)
    print("Saved scratch/manifest_enriched.json")

if __name__ == '__main__':
    main()
