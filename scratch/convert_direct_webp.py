import os, sys, time, json, re, shutil
from PIL import Image

def slugify(text):
    return re.sub(r'[^a-zA-Z0-9]+', '-', str(text).strip().lower()).strip('-')

def main():
    t0 = time.time()
    manifest_path = 'scratch/manifest.json'
    with open(manifest_path, 'r') as f:
        items = json.load(f)

    total = len(items)
    print(f"Loaded {total} products from manifest.")

    base_upload = os.path.join(os.getcwd(), 'uploads', 'products')
    public_upload = os.path.join(os.getcwd(), 'public', 'uploads', 'products')

    converted = 0
    errors = 0

    products_for_json = []

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

        src = item['source_img']
        try:
            with Image.open(src) as im:
                # Handle color modes
                if im.mode == 'CMYK':
                    im = im.convert('RGB')
                elif im.mode == 'P':
                    im = im.convert('RGBA')

                # Resize if larger than 1200px
                w, h = im.size
                scale = min(1.0, 1200 / max(w, h))
                if scale < 1.0:
                    im = im.resize((int(w * scale), int(h * scale)), Image.Resampling.LANCZOS)

                # Save as clean WebP directly (preserving original background)
                im.save(dst1, 'WEBP', quality=90, method=4)
                im.save(dst2, 'WEBP', quality=90, method=4)
                converted += 1

        except Exception as e:
            print(f"Error on {slug}: {e}")
            errors += 1

        subcat = item.get('subcategory', 'General')
        series_slug = slugify(subcat)
        prod = {
            'db_id': i,
            'id': item['sku'],
            'name': item['name'],
            'slug': item['slug'],
            'fullTitle': item['name'],
            'articleNo': item['sku'],
            'websiteNo': item['sku'],
            'categorySlug': item['category_slug'],
            'categoryName': item['category_name'],
            'series': subcat,
            'seriesSlug': series_slug,
            'shape': 'Standard',
            'regularPrice': 0.0,
            'salePrice': None,
            'stockQty': 1000,
            'stockStatus': 'in_stock',
            'status': 'published',
            'isFeatured': False,
            'image': rel_url,
            'images': [rel_url],
            'gallery': [rel_url],
            'shortDescription': item.get('short_desc', ''),
            'description': item.get('description', ''),
            'seoTitle': item.get('seo_title', ''),
            'seoDescription': item.get('seo_description', ''),
            'specs': {
                'capacity': item.get('capacity', 'N/A'),
                'neck': item.get('neck_finish', 'N/A'),
                'material': item.get('material', 'PET'),
                'weight': item.get('weight', 'N/A'),
                'moq': item.get('moq', '5,000 pcs')
            },
            'updatedAt': '2026-09-27 22:25:00'
        }
        products_for_json.append(prod)

        if i % 50 == 0 or i == total:
            print(f"[{i}/{total}] Converted: {converted}, Errors: {errors}, Elapsed: {time.time()-t0:.1f}s")

    print(f"\nAll {converted} images converted to WebP in {time.time()-t0:.1f}s! Errors: {errors}")

    # Write enriched manifest
    with open('scratch/manifest_enriched.json', 'w') as f:
        json.dump(items, f, indent=2)

    # Write default_products.json
    with open('config/default_products.json', 'w') as f:
        json.dump(products_for_json, f, indent=2)
    print(f"Saved config/default_products.json with {len(products_for_json)} products.")

if __name__ == '__main__':
    main()
