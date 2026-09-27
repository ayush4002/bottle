import os, re, shutil, tempfile, json
import openpyxl

GDIR = r'C:\Users\Ayuh\.gemini\antigravity-ide\brain\9c7f0ec6-15cc-4d55-996e-20e157d4246c\scratch\gdrive_download'

def clean_key(s):
    if not s: return ''
    return re.sub(r'[^a-z0-9]', '', str(s).lower())

def slugify(text):
    text = re.sub(r'[^a-zA-Z0-9]+', '-', str(text).strip().lower()).strip('-')
    return text

def parse_all():
    items = []

    # -------------------------------------------------------------
    # 1. CAPS
    # -------------------------------------------------------------
    cap_dir = os.path.join(GDIR, 'Cap photos with Spec Sheet')
    cap_img_dir = os.path.join(cap_dir, 'Photos_High Quality')
    cap_wb = openpyxl.load_workbook(os.path.join(cap_dir, 'Caps_Specs.xlsx'), data_only=True)
    ws = cap_wb.active
    for r in range(2, ws.max_row + 1):
        code = ws.cell(r, 1).value
        if not code: continue
        code = str(code).strip()
        neck = str(ws.cell(r, 3).value or '').strip()
        img_name = f"{code}.png"
        img_path = os.path.join(cap_img_dir, img_name)
        if not os.path.exists(img_path):
            print(f"Warning: Cap img not found: {img_path}")
        
        name = f"Caps & Closures {code}"
        slug = slugify(f"cap-{code}")
        items.append({
            'source_img': img_path,
            'sku': f"CAP-{code}",
            'name': name,
            'slug': slug,
            'category_slug': 'caps',
            'category_name': 'Caps',
            'subcategory': 'Caps & Closures',
            'capacity': 'N/A',
            'neck_finish': neck if neck and neck != 'None' else 'Various',
            'material': 'PP / High-Grade Polymer',
            'weight': 'N/A',
            'moq': '10,000 pcs',
            'short_desc': f"Engineered precision closure ({code}) designed for leak-proof sealing across neck finishes {neck}.",
            'description': f"High-performance {code} cap engineered for premium consumer and pharmaceutical packaging. Manufactured to tight dimensional tolerances with superior thread integrity and sealing performance.",
            'seo_title': f"{name} | Engineered Packaging Closures",
            'seo_description': f"Buy {name} wholesale from TrueNorth Group. Suitable for neck finishes {neck}. Premium quality, leak-proof design."
        })

    # -------------------------------------------------------------
    # 2. PUMPS
    # -------------------------------------------------------------
    pump_dir = os.path.join(GDIR, 'Pump photos with spec sheet')
    pump_img_dir = os.path.join(pump_dir, 'Photos_High Quality')
    pump_wb = openpyxl.load_workbook(os.path.join(pump_dir, 'Pumps_Specs.xlsx'), data_only=True)
    ws = pump_wb.active
    for r in range(2, ws.max_row + 1):
        code = ws.cell(r, 1).value
        if not code: continue
        code = str(code).strip()
        series = str(ws.cell(r, 3).value or '').strip()
        ptype = str(ws.cell(r, 4).value or 'Lotion Pump').strip()
        variant = str(ws.cell(r, 5).value or '').strip()
        neck = str(ws.cell(r, 6).value or '').strip()
        output = str(ws.cell(r, 7).value or '').strip()
        packing = str(ws.cell(r, 8).value or '').strip()
        options = str(ws.cell(r, 9).value or '').strip()

        img_name = f"{code}.png"
        img_path = os.path.join(pump_img_dir, img_name)
        if not os.path.exists(img_path):
            print(f"Warning: Pump img not found: {img_path}")

        cat_slug = 'foamer-pumps' if 'foam' in ptype.lower() else 'pumps'
        cat_name = 'Foamer pumps' if cat_slug == 'foamer-pumps' else 'Pumps'
        name = f"{ptype} {code}"
        slug = slugify(f"{ptype}-{code}")
        neck_val = neck if neck and neck != 'None' else (options if options and options != 'None' else 'Standard')
        if len(neck_val) > 50:
            neck_val = neck_val[:47] + '...'

        items.append({
            'source_img': img_path,
            'sku': f"PUMP-{code}",
            'name': name,
            'slug': slug,
            'category_slug': cat_slug,
            'category_name': cat_name,
            'subcategory': ptype,
            'capacity': output if output and output != 'None' else '2.0cc dosage',
            'neck_finish': neck_val,
            'material': 'Polypropylene (PP) / PE / Stainless Steel Spring',
            'weight': 'N/A',
            'moq': '10,000 pcs',
            'short_desc': f"{ptype} {code} with {output} dosage output. {options if options and options != 'None' else ''}".strip(),
            'description': f"TrueNorth {name} series dispenser. Precision mechanical actuation with smooth stroke and instant priming. Compatible with personal care formulations, sanitizers, lotions, and liquid soaps. Options: {options}.",
            'seo_title': f"{name} | Precision Dispensing Pump",
            'seo_description': f"Source {name} in bulk. Output: {output}. Manufactured by TrueNorth Group for premium cosmetic and personal care brands."
        })

    # -------------------------------------------------------------
    # 3. SPRAYS
    # -------------------------------------------------------------
    spray_dir = os.path.join(GDIR, 'Spray Photos and Spec Sheet')
    spray_img_dir = os.path.join(spray_dir, 'Photos_High Quality')
    spray_wb = openpyxl.load_workbook(os.path.join(spray_dir, 'Sprays_Specs.xlsx'), data_only=True)
    ws = spray_wb.active
    for r in range(2, ws.max_row + 1):
        code = ws.cell(r, 1).value
        if not code: continue
        code = str(code).strip()
        photo_field = str(ws.cell(r, 2).value or '').strip()
        series = str(ws.cell(r, 3).value or '').strip()
        ptype = str(ws.cell(r, 4).value or 'Sprayer').strip()
        variant = str(ws.cell(r, 5).value or '').strip()
        neck_cap = str(ws.cell(r, 6).value or '').strip()
        output = str(ws.cell(r, 7).value or '').strip()
        packing = str(ws.cell(r, 8).value or '').strip()
        notes = str(ws.cell(r, 9).value or '').strip()

        # Check image file
        img_name = os.path.basename(photo_field) if photo_field else f"{code}.png"
        img_path = os.path.join(spray_img_dir, img_name)
        if not os.path.exists(img_path):
            img_path = os.path.join(spray_img_dir, f"{code}.png")
        if not os.path.exists(img_path):
            print(f"Warning: Spray img not found: {img_path}")

        cat_slug = 'trigger-sprayers' if 'trigger' in ptype.lower() else 'finger-sprayers'
        cat_name = 'Trigger sprayer' if cat_slug == 'trigger-sprayers' else 'Finger sprayer'
        name = f"{ptype} {code}"
        slug = slugify(f"{ptype}-{code}")
        neck_val = neck_cap if neck_cap and neck_cap != 'None' else 'Standard'
        if len(neck_val) > 50:
            neck_val = neck_val[:47] + '...'

        items.append({
            'source_img': img_path,
            'sku': f"SPRAY-{code}",
            'name': name,
            'slug': slug,
            'category_slug': cat_slug,
            'category_name': cat_name,
            'subcategory': ptype,
            'capacity': neck_cap if ('ml' in neck_cap.lower() or 'cc' in neck_cap.lower()) else 'N/A',
            'neck_finish': neck_val,
            'material': 'Polypropylene (PP) / PE Atomizer Nozzle',
            'weight': 'N/A',
            'moq': '10,000 pcs',
            'short_desc': f"{ptype} {code} with output {output}. {notes if notes and notes != 'None' else ''}".strip(),
            'description': f"Engineered {name} for ultra-fine particle atomization and consistent spray pattern. Ideal for pharmaceutical antiseptics, body mist, room fragrances, and surface disinfectants.",
            'seo_title': f"{name} | Fine Mist & Atomizer Sprayer",
            'seo_description': f"Purchase {name} wholesale. Precision fine mist spray head from TrueNorth Group. Output: {output}."
        })

    # -------------------------------------------------------------
    # 4. AGRO BOTTLES
    # -------------------------------------------------------------
    agro_dir = os.path.join(GDIR, 'Agro Bottles with spec sheets')
    with tempfile.NamedTemporaryFile(suffix='.xlsx', delete=False) as tf:
        shutil.copy2(os.path.join(agro_dir, 'Agro Bottles Specifications_10.09.26'), tf.name)
        wb = openpyxl.load_workbook(tf.name, data_only=True)
        ws = wb.active
        tf.close()
        os.unlink(tf.name)

    agro_imgs = [f for f in os.listdir(agro_dir) if f.lower().endswith(('.png', '.jpg', '.jpeg', '.webp'))]
    spec_rows = []
    for r in range(2, ws.max_row + 1):
        pname = ws.cell(r, 2).value
        if not pname: continue
        spec_rows.append({
            'name': str(pname).strip(),
            'size': str(ws.cell(r, 3).value or '').strip(),
            'weight': str(ws.cell(r, 4).value or '').strip(),
            'height': str(ws.cell(r, 5).value or '').strip(),
            'volume': str(ws.cell(r, 6).value or '').strip(),
            'dia': str(ws.cell(r, 7).value or '').strip(),
            'neck': str(ws.cell(r, 8).value or '').strip(),
        })

    for img in agro_imgs:
        stem = os.path.splitext(img)[0]
        k = clean_key(stem)
        matched = next((s for s in spec_rows if clean_key(s['name']) == k), None)
        pname = matched['name'] if matched else stem
        size = matched['size'] if matched else ''
        weight = matched['weight'] if matched else 'N/A'
        height = matched['height'] if matched else 'N/A'
        volume = matched['volume'] if matched else 'N/A'
        dia = matched['dia'] if matched else 'N/A'
        neck = matched['neck'] if matched else '46 mm'

        name = f"{pname} PET Bottle"
        slug = slugify(f"agro-{stem}")
        items.append({
            'source_img': os.path.join(agro_dir, img),
            'sku': f"AGRO-{slugify(stem).upper()}",
            'name': name,
            'slug': slug,
            'category_slug': 'pet-bottles',
            'category_name': 'PET bottles',
            'subcategory': 'Agro & Pesticide PET Bottles',
            'capacity': size if size else volume,
            'neck_finish': neck,
            'material': 'Heavy-Duty Chemical Resistant PET / HDPE',
            'weight': weight,
            'moq': '5,000 pcs',
            'short_desc': f"Chemical-resistant {size} agro bottle with {neck} tamper-evident neck. Height: {height}, Dia: {dia}.",
            'description': f"TrueNorth Group heavy-duty agrochemical bottle engineered for bio-fertilizers, pesticides, crop chemicals, and industrial concentrates. Features high structural rigidity and leak-proof tamper-evident closure compatibility.",
            'seo_title': f"{name} ({size}) | Chemical & Agro Packaging",
            'seo_description': f"Buy {name} ({size}) wholesale. High chemical resistance, 46mm neck finish, ideal for pesticides and agricultural liquids."
        })

    # -------------------------------------------------------------
    # 5. COSMETIC PHOTOS
    # -------------------------------------------------------------
    cos_dir = os.path.join(GDIR, 'Cosmetic Photos with Spec Sheet')
    with tempfile.NamedTemporaryFile(suffix='.xlsx', delete=False) as tf:
        shutil.copy2(os.path.join(cos_dir, 'Cosmetics Bottles Specifications_10.09.26'), tf.name)
        wb = openpyxl.load_workbook(tf.name, data_only=True)
        ws = wb.active
        tf.close()
        os.unlink(tf.name)

    cos_specs = []
    for r in range(2, ws.max_row + 1):
        pname = ws.cell(r, 2).value
        if not pname: continue
        cos_specs.append({
            'name': str(pname).strip(),
            'size': str(ws.cell(r, 3).value or '').strip(),
            'weight': str(ws.cell(r, 4).value or '').strip(),
            'height': str(ws.cell(r, 5).value or '').strip(),
            'volume': str(ws.cell(r, 6).value or '').strip(),
            'dia': str(ws.cell(r, 7).value or '').strip(),
            'neck': str(ws.cell(r, 8).value or '').strip(),
        })

    cos_imgs = [f for f in os.listdir(cos_dir) if f.lower().endswith(('.png', '.jpg', '.jpeg', '.webp'))]
    for img in cos_imgs:
        stem = os.path.splitext(img)[0]
        k = clean_key(stem)
        matched = next((s for s in cos_specs if clean_key(s['name']) == k), None)
        if not matched and 'boston' in k and '60ml' in k:
            matched = next((s for s in cos_specs if 'boston' in clean_key(s['name']) and '60ml' in clean_key(s['name'])), None)
        
        pname = matched['name'] if matched else stem
        size = matched['size'] if matched else ''
        weight = matched['weight'] if matched else 'N/A'
        height = matched['height'] if matched else 'N/A'
        volume = matched['volume'] if matched else 'N/A'
        dia = matched['dia'] if matched else 'N/A'
        neck = matched['neck'] if matched else 'N/A'

        name = f"{pname} Cosmetic Bottle"
        slug = slugify(f"cosmetic-{stem}")
        items.append({
            'source_img': os.path.join(cos_dir, img),
            'sku': f"COS-{slugify(stem).upper()}",
            'name': name,
            'slug': slug,
            'category_slug': 'pet-bottles',
            'category_name': 'PET bottles',
            'subcategory': 'Cosmetic & Personal Care PET',
            'capacity': size if size else volume,
            'neck_finish': neck,
            'material': '100% rPET / Virgin Food-Grade PET',
            'weight': weight,
            'moq': '5,000 pcs',
            'short_desc': f"Premium cosmetic PET container ({size}, {weight}) with {neck} neck. Height: {height}, Dia: {dia}.",
            'description': f"Elegant {name} designed for hair oils, body lotions, face toners, luxury serums, and hotel amenities. Superb glass-like clarity, high shatter resistance, and compatibility with fine mist sprayers or dispensing pumps.",
            'seo_title': f"{name} ({size}) | Cosmetic PET Packaging",
            'seo_description': f"Order {name} ({size}) direct from manufacturer TrueNorth Group. High clarity, luxury finish for cosmetic and personal care brands."
        })

    # -------------------------------------------------------------
    # 6. JARS
    # -------------------------------------------------------------
    jar_dir = os.path.join(GDIR, 'Jar Images with Spec Sheet')
    with tempfile.NamedTemporaryFile(suffix='.xlsx', delete=False) as tf:
        shutil.copy2(os.path.join(jar_dir, 'Jar Bottles Specifications_10.09.26'), tf.name)
        wb = openpyxl.load_workbook(tf.name, data_only=True)
        ws = wb.active
        tf.close()
        os.unlink(tf.name)

    jar_specs = []
    for r in range(2, ws.max_row + 1):
        pname = ws.cell(r, 2).value
        if not pname: continue
        jar_specs.append({
            'name': str(pname).strip(),
            'size': str(ws.cell(r, 3).value or '').strip(),
            'weight': str(ws.cell(r, 4).value or '').strip(),
            'height': str(ws.cell(r, 5).value or '').strip(),
            'volume': str(ws.cell(r, 6).value or '').strip(),
            'dia': str(ws.cell(r, 7).value or '').strip(),
            'neck': str(ws.cell(r, 8).value or '').strip(),
        })

    jar_imgs = [f for f in os.listdir(jar_dir) if f.lower().endswith(('.png', '.jpg', '.jpeg', '.webp'))]
    for img in jar_imgs:
        stem = os.path.splitext(img)[0]
        k = clean_key(stem)
        matched = next((s for s in jar_specs if clean_key(s['name']) == k), None)
        pname = matched['name'] if matched else stem
        size = matched['size'] if matched else ''
        weight = matched['weight'] if matched else 'N/A'
        height = matched['height'] if matched else 'N/A'
        volume = matched['volume'] if matched else 'N/A'
        dia = matched['dia'] if matched else 'N/A'
        neck = matched['neck'] if matched else '38 mm'

        name = f"{pname} Round PET Jar"
        slug = slugify(f"jar-{stem}")
        items.append({
            'source_img': os.path.join(jar_dir, img),
            'sku': f"JAR-{slugify(stem).upper()}",
            'name': name,
            'slug': slug,
            'category_slug': 'pet-jars',
            'category_name': 'PET jars',
            'subcategory': 'Nutraceutical & Round PET Jars',
            'capacity': size if size else volume,
            'neck_finish': neck,
            'material': 'Food-Grade PET (FDA Compliant)',
            'weight': weight,
            'moq': '5,000 pcs',
            'short_desc': f"Wide-mouth {size} PET jar ({weight}) with {neck} neck finish. Height: {height}, Dia: {dia}.",
            'description': f"TrueNorth Group wide-mouth {name} container engineered for nutraceutical capsules, dietary supplements, protein powders, body scrubs, and confectionery.",
            'seo_title': f"{name} ({size}) | Nutraceutical & Tablet Jars",
            'seo_description': f"Source {name} in bulk. Wide mouth {neck} neck finish, superior oxygen barrier, food-grade PET manufacturing."
        })

    # -------------------------------------------------------------
    # 7. PHARMA BOTTLES
    # -------------------------------------------------------------
    pharma_root = os.path.join(GDIR, 'Pharma Bottles')
    pharma_img_dir = os.path.join(pharma_root, 'Pharma Bottles Images_03.09.26')
    with tempfile.NamedTemporaryFile(suffix='.xlsx', delete=False) as tf:
        shutil.copy2(os.path.join(pharma_root, 'Pharma  Bottles Specifications_03.09.26'), tf.name)
        wb = openpyxl.load_workbook(tf.name, data_only=True)
        ws = wb.active
        tf.close()
        os.unlink(tf.name)

    pharma_specs = []
    for r in range(2, ws.max_row + 1):
        pname = ws.cell(r, 2).value
        if not pname: continue
        pharma_specs.append({
            'name': str(pname).strip(),
            'size': str(ws.cell(r, 3).value or '').strip(),
            'weight': str(ws.cell(r, 4).value or '').strip(),
            'height': str(ws.cell(r, 5).value or '').strip(),
            'volume': str(ws.cell(r, 6).value or '').strip(),
            'dia': str(ws.cell(r, 7).value or '').strip(),
            'neck': str(ws.cell(r, 8).value or '').strip(),
        })

    pharma_imgs = [f for f in os.listdir(pharma_img_dir) if f.lower().endswith(('.png', '.jpg', '.jpeg', '.webp'))]
    for img in pharma_imgs:
        stem = os.path.splitext(img)[0]
        k = clean_key(stem)
        matched = next((s for s in pharma_specs if clean_key(s['name']) == k), None)
        if not matched:
            if '15mlround' == k:
                matched = next((s for s in pharma_specs if '15mlroundpet' in clean_key(s['name'])), None)
            elif '400mlbrute' in k:
                matched = {'name': '400ml Brute PET', 'size': '400 ml', 'weight': '37 g', 'height': '170 mm', 'volume': '420 ml', 'dia': '68 mm', 'neck': '28 mm'}

        pname = matched['name'] if matched else stem
        size = matched['size'] if matched else ''
        weight = matched['weight'] if matched else 'N/A'
        height = matched['height'] if matched else 'N/A'
        volume = matched['volume'] if matched else 'N/A'
        dia = matched['dia'] if matched else 'N/A'
        neck = matched['neck'] if matched else '22 mm'

        name = f"{pname}" if 'pet' in pname.lower() else f"{pname} PET Bottle"
        slug = slugify(f"pharma-{stem}")
        items.append({
            'source_img': os.path.join(pharma_img_dir, img),
            'sku': f"PHA-{slugify(stem).upper()}",
            'name': name,
            'slug': slug,
            'category_slug': 'pet-bottles',
            'category_name': 'PET bottles',
            'subcategory': 'Pharmaceutical PET Bottles',
            'capacity': size if size else volume,
            'neck_finish': neck,
            'material': 'USP Class VI / EP Pharmaceutical-Grade PET',
            'weight': weight,
            'moq': '5,000 pcs',
            'short_desc': f"Pharmaceutical PET bottle ({size}, {weight}) with {neck} neck finish. Height: {height}, Dia: {dia}.",
            'description': f"Cleanroom manufactured {name} compliant with international pharmacopoeia standards (USP Class VI / EP). Suitable for oral syrups, antibiotics, pediatrics, suspensions, and vitamins. Excellent chemical stability and tamper resistance.",
            'seo_title': f"{name} ({size}) | Pharmaceutical PET Bottles",
            'seo_description': f"Buy {name} ({size}) for pharmaceutical packaging. Class 10,000 cleanroom production, amber & clear options from TrueNorth Group."
        })

    return items

if __name__ == '__main__':
    items = parse_all()
    print(f"Total items parsed: {len(items)}")
    # Check duplicate slugs or skus
    slugs = set()
    skus = set()
    for item in items:
        if item['slug'] in slugs:
            print(f"Duplicate slug: {item['slug']}")
        if item['sku'] in skus:
            print(f"Duplicate sku: {item['sku']}")
        slugs.add(item['slug'])
        skus.add(item['sku'])
    with open('scratch/manifest.json', 'w') as f:
        json.dump(items, f, indent=2)
    print("Manifest saved to scratch/manifest.json")
