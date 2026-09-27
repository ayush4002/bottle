import json

with open('scratch/manifest_enriched.json', 'r') as f:
    items = json.load(f)

def escape_sql(val):
    if val is None:
        return 'NULL'
    s = str(val).replace('\\', '\\\\').replace("'", "\\'")
    return f"'{s}'"

prod_rows = []
img_rows = []

for idx, it in enumerate(items, 1):
    sku = escape_sql(it['sku'])
    name = escape_sql(it['name'])
    slug = escape_sql(it['slug'])
    cslug = escape_sql(it['category_slug'])
    cname = escape_sql(it['category_name'])
    subcat = escape_sql(it.get('subcategory', 'General'))
    sdesc = escape_sql(it.get('short_desc', ''))
    desc = escape_sql(it.get('description', ''))
    cap = escape_sql(it.get('capacity', 'N/A'))
    neck = escape_sql(it.get('neck_finish', 'N/A'))
    mat = escape_sql(it.get('material', 'PET'))
    weight = escape_sql(it.get('weight', 'N/A'))
    moq = escape_sql(it.get('moq', '5,000 pcs'))
    stitle = escape_sql(it.get('seo_title', ''))
    sdesc_seo = escape_sql(it.get('seo_description', ''))
    
    prod_rows.append(f"({idx}, {sku}, {name}, {slug}, {cslug}, {cname}, {subcat}, 'Unisex', {sdesc}, {desc}, 0.00, NULL, 1000, 'in_stock', 'published', 0, {cap}, {neck}, {mat}, {weight}, {moq}, {stitle}, {sdesc_seo}, '2026-09-27 22:20:00', '2026-09-27 22:20:00', NULL)")
    
    img_url = escape_sql(it['image_url'])
    img_rows.append(f"({idx}, {idx}, {img_url}, 1, 0)")

prod_sql = 'INSERT INTO `products` VALUES \n' + ',\n'.join(prod_rows) + ';\n'
img_sql = 'INSERT INTO `product_images` VALUES \n' + ',\n'.join(img_rows) + ';\n'

for sql_path in ['truenorth_db.sql', 'config/truenorth_db.sql']:
    with open(sql_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Replace products dump
    p_marker = 'LOCK TABLES `products` WRITE;\n/*!40000 ALTER TABLE `products` DISABLE KEYS */;'
    if p_marker in content:
        parts = content.split(p_marker)
        rest = parts[1].split('/*!40000 ALTER TABLE `products` ENABLE KEYS */;')
        content = parts[0] + p_marker + '\n' + prod_sql + '/*!40000 ALTER TABLE `products` ENABLE KEYS */;' + rest[1]

    # Replace product_images dump
    i_marker = 'LOCK TABLES `product_images` WRITE;\n/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;'
    if i_marker in content:
        parts = content.split(i_marker)
        rest = parts[1].split('/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;')
        content = parts[0] + i_marker + '\n' + img_sql + '/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;' + rest[1]

    with open(sql_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f'Updated {sql_path} with {len(items)} products and images.')
