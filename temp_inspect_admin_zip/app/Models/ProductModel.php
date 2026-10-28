<?php
// ==========================================================================
// TRUENORTH GROUP — PRODUCT MODEL & CENTRAL DATA REPOSITORY
// High-performance PHP data model with MySQL Database as SINGLE SOURCE OF TRUTH.
// Supports full CRUD, PDO transactions & standalone fallback.
// ==========================================================================

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once __DIR__ . '/SearchDictionary.php';

class ProductModel {
    private static $companyInfo = [
            "name" => "TrueNorth Group",
            "tagline" => "Primary Packaging Specialist — Engineered PET & Precision Closures",
            "heroHeadline" => "Primary Packaging Specialists & Manufacturers",
            "heroSubtitle" => "TrueNorth Group was established on the foundation of over 25 years of experience in the domestic and international primary-packaging industry. We specialise in precision PET bottles, jars, and closures for pharmaceutical, cosmetic, and personal-care applications.",
            "phone" => "+91 11 4752 8900",
            "email" => "info@wetruenorthgroup.com",
            "hq" => "#201, Vardhaman Trade Towers, Rohit Kunj, LSC Pitampura, Delhi-110034, India | Manufacturing: Daman, India | Closures: Ningbo, China",
            "experienceYears" => "25+ Years",
            "customerSatisfaction" => "99.8%",
            "stats" => [
                [
                    "label" => "Industry Heritage",
                    "value" => "25+ Years"
                ],
                [
                    "label" => "On-Time Dispatch",
                    "value" => "99.8%"
                ],
                [
                    "label" => "Controlled Cleanrooms",
                    "value" => "Class 10,000"
                ],
                [
                    "label" => "ISO 9001 Certification",
                    "value" => "TÜV NORD"
                ]
            ],
            "finishes" => [
                "Pearl finish",
                "Matte finish",
                "High-gloss finish",
                "UV-protective options",
                "Custom colours and finishes"
            ],
            "neckSizes" => [
                "15 mm",
                "19 mm",
                "20 mm",
                "22 mm",
                "25 mm",
                "28 mm",
                "38 mm"
            ]
        ];
    private static $categories = [
        [
            "name" => "Agro Bottles",
            "slug" => "agro-bottles",
            "image" => "/uploads/products/agro-bottles/agro-500ml-agro.webp",
            "coverImage" => "/uploads/products/agro-bottles/agro-500ml-agro.webp",
            "desc" => "Agro & pesticide PET bottles engineered with 46mm tamper-evident neck finishes from 100ml to 1,000ml."
        ],
        [
            "name" => "Caps",
            "slug" => "caps",
            "image" => "/uploads/products/caps/cap-nb108-a.webp",
            "coverImage" => "/uploads/products/caps/cap-nb108-a.webp",
            "desc" => "Engineered precision screw closures, push-pull caps, flip-top caps, disc-top caps, and CRC closures."
        ],
        [
            "name" => "Cosmetics Bottles",
            "slug" => "cosmetics-bottles",
            "image" => "/uploads/products/cosmetics-bottles/cosmetic-100ml-tulip.webp",
            "coverImage" => "/uploads/products/cosmetics-bottles/cosmetic-100ml-tulip.webp",
            "desc" => "Cosmetic & personal care PET containers in Veola, Tulip, Badami, Oval, and Boston configurations."
        ],
        [
            "name" => "Jars",
            "slug" => "jars",
            "image" => "/uploads/products/jars/jar-150cc-rd.webp",
            "coverImage" => "/uploads/products/jars/jar-150cc-rd.webp",
            "desc" => "Wide-mouth round PET jars from 75cc to 500cc for nutraceutical tablets, powders, and cosmetic creams."
        ],
        [
            "name" => "Pharma Bottles",
            "slug" => "pharma-bottles",
            "image" => "/uploads/products/pharma-bottles/pharma-100ml-dome-pet.webp",
            "coverImage" => "/uploads/products/pharma-bottles/pharma-100ml-dome-pet.webp",
            "desc" => "Cleanroom manufactured pharmaceutical PET bottles for syrups, suspensions, and oral formulations."
        ],
        [
            "name" => "Pumps",
            "slug" => "pumps",
            "image" => "/uploads/products/pumps/lotion-pump-nb101-a.webp",
            "coverImage" => "/uploads/products/pumps/lotion-pump-nb101-a.webp",
            "desc" => "High-output lotion pumps, treatment dispensers, switch pumps, and foaming dispensers with precision dosage."
        ],
        [
            "name" => "Sprays",
            "slug" => "sprays",
            "image" => "/uploads/products/sprays/medical-sprayer-nb107-a.webp",
            "coverImage" => "/uploads/products/sprays/medical-sprayer-nb107-a.webp",
            "desc" => "Medical sprayers, fine-mist atomizers, continuous-mist sprayers, and trigger spray systems."
        ],
            [
            "name" => "Liquor & Spirits Bottles",
            "slug" => "liquor-bottles",
            "image" => "/uploads/products/liquor-bottles/liq-180ml-kidney-10g.webp",
            "coverImage" => "/uploads/products/liquor-bottles/liq-180ml-kidney-10g.webp",
            "desc" => "Crystal-clear PET pocket flasks, flat liquor containers, round spirits bottles, and wine bottles from 90ml to 750ml."
        ],
];

    private static $industries = [
        'agro' => [
            'name' => 'Agrochemicals & Pesticides',
            'slug' => 'agro',
            'headline' => 'Agrochemicals & Pesticides Packaging',
            'desc' => 'High-barrier PET & HDPE containers, tamper-evident 46mm agro neck finishes, and chemical-resistant closures designed for agrochemicals and pesticides.',
            'image' => '/vikaas_inputs/AGRO_PESTICIDES.webp'
        ],
        'cosmetics' => [
            'name' => 'Cosmetics & Personal Care',
            'slug' => 'cosmetics',
            'headline' => 'Cosmetics & Personal Care Packaging',
            'desc' => 'Premium aesthetic PET bottles, luxury dispensing pumps, fine mist atomizers, and cosmetic jars crafted for skincare, haircare, and personal care brands.',
            'image' => '/vikaas_inputs/COSMETICS.webp'
        ],
        'pharma' => [
            'name' => 'Pharmaceuticals & Healthcare',
            'slug' => 'pharma',
            'headline' => 'Pharmaceuticals & Healthcare Packaging',
            'desc' => 'Cleanroom-manufactured USP Class VI oral syrup bottles, CRC child-resistant closures, amber UV-protective bottles, and medical dispensing solutions.',
            'image' => '/vikaas_inputs/PHARMA_BOTTLES.webp'
        ],
        'nutraceuticals' => [
            'name' => 'Nutraceuticals & Supplements',
            'slug' => 'nutraceuticals',
            'headline' => 'Nutraceuticals & Health Supplements Packaging',
            'desc' => 'Wide-mouth round PET jars and packer bottles from 75cc to 500cc engineered for tablets, capsules, nutritional powders, and wellness supplements.',
            'image' => '/vikaas_inputs/PET_nutraceuticle_jars.webp'
        ],
        'liquor' => [
            'name' => 'Liquor, Spirits & Beverages',
            'slug' => 'liquor',
            'headline' => 'Liquor, Spirits & Beverages Packaging',
            'desc' => 'Crystal-clear PET pocket flasks, flat liquor containers, round spirits bottles, and beverage packaging engineered for premium shelf appeal and shatter resistance.',
            'image' => '/vikaas_inputs/LIQUOR_Bottles.webp'
        ]
    ];
    private static $series = [
        [
            "name" => "Liquor & Spirits PET Bottles",
            "slug" => "liquor-spirits-pet-bottles",
            "categorySlug" => "liquor-bottles",
            "categoryName" => "Liquor & Spirits Bottles",
            "image" => "/uploads/products/liquor-bottles/liq-180ml-kidney-10g.webp",
            "material" => "PET",
            "shape" => "Kidney Flask / Fluted Round / Wine",
            "volumeRange" => "90ml - 750ml",
            "neckRange" => "25mm - 30mm",
            "desc" => "Crystal-clear PET pocket flasks, flat liquor bottles, fluted round spirits containers, and wine bottles engineered for high clarity, shatter resistance, and alcohol packaging."
        ],
            [
                "name" => "Agro & Pesticide PET Bottles",
                "slug" => "agro-bottles",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET / HDPE",
                "shape" => "Cylindrical",
                "volumeRange" => "100ml – 1000ml",
                "neckRange" => "46mm",
                "desc" => "Heavy-duty chemical-resistant PET and HDPE bottles engineered for agrochemicals, pesticides, bio-fertilizers, and liquid nutrients with 46mm tamper-evident neck finishes."
            ],
            [
                "name" => "Cosmetic & Personal Care PET",
                "slug" => "cosmetic-bottles",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET / RPET",
                "shape" => "Oval / Round / Boston / Tulip",
                "volumeRange" => "10ml – 1000ml",
                "neckRange" => "14mm – 28mm",
                "desc" => "Premium cosmetic PET bottles featuring Veola, Tulip, Badami, Oval, Canthradine, and Boston profiles for hair oils, lotions, serums, and body care."
            ],
            [
                "name" => "Nutraceutical & Round PET Jars",
                "slug" => "round-jars",
                "categorySlug" => "pet-jars",
                "categoryName" => "PET jars",
                "image" => "/logo_svg.svg",
                "material" => "PET",
                "shape" => "Round Wide-Mouth",
                "volumeRange" => "75cc – 500cc",
                "neckRange" => "38mm – 45mm",
                "desc" => "Wide-mouth round PET jars designed for pharmaceuticals, nutraceutical capsules, powders, protein supplements, and cosmetic creams."
            ],
            [
                "name" => "Sharp Cylindrical PET",
                "slug" => "sharp-cylindrical-pet",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET / RPET",
                "shape" => "Cylindrical",
                "volumeRange" => "100ml – 500ml",
                "neckRange" => "20-410, 24-410, 28-410",
                "desc" => "Classic straight-wall cylindrical silhouette ideal for body sprays, cosmetic lotions, hand sanitizers, and haircare."
            ],
            [
                "name" => "Boston Round PET",
                "slug" => "boston-round-pet",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET / RPET",
                "shape" => "Round",
                "volumeRange" => "50ml – 1,000ml",
                "neckRange" => "20-410, 24-410, 28-410",
                "desc" => "Timeless rounded shoulder design, universally recognised standard for pharmaceutical syrups, personal care, and high-end liquid soaps."
            ],
            [
                "name" => "Tall Boston Round PET",
                "slug" => "tall-boston-round-pet",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET / RPET",
                "shape" => "Round",
                "volumeRange" => "100ml – 500ml",
                "neckRange" => "20-410, 24-410",
                "desc" => "Elongated profile giving a slender, upscale shelf appeal for premium toners and luxury cosmetic lines."
            ],
            [
                "name" => "Flex Oval",
                "slug" => "flex-oval",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET",
                "shape" => "Oval",
                "volumeRange" => "100ml – 300ml",
                "neckRange" => "24-410",
                "desc" => "Sleek ergonomic oval cross-section with high grip stability, ideal for shampoo bottles and sunscreens."
            ],
            [
                "name" => "Cosmo Sirop",
                "slug" => "cosmo-sirop",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET",
                "shape" => "Round",
                "volumeRange" => "60ml – 500ml",
                "neckRange" => "22mm, 25mm, 28mm",
                "desc" => "Pharmaceutical-standard amber and transparent syrup bottles manufactured under Class 10,000 cleanroom conditions."
            ],
            [
                "name" => "Cosmo Veral",
                "slug" => "cosmo-veral",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET",
                "shape" => "Round",
                "volumeRange" => "100ml – 400ml",
                "neckRange" => "24-410, 28mm",
                "desc" => "Steep-shoulder bottle architecture designed for chemical stability and pharmaceutical suspensions."
            ],
            [
                "name" => "Short Round",
                "slug" => "short-round",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET",
                "shape" => "Round",
                "volumeRange" => "50ml – 200ml",
                "neckRange" => "19mm, 20-410",
                "desc" => "Compact profile engineered for travel packaging, hotel amenities, and compact serum solutions."
            ],
            [
                "name" => "Tri Oval",
                "slug" => "tri-oval",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET",
                "shape" => "Tri Oval",
                "volumeRange" => "100ml – 300ml",
                "neckRange" => "24-410, 26mm",
                "desc" => "Subtle triangular oval contour providing distinct shelf differentiation and comfortable hand ergonomics."
            ],
            [
                "name" => "Dome Shape PET",
                "slug" => "dome-shape-pet",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET",
                "shape" => "Dome Round",
                "volumeRange" => "100ml – 250ml",
                "neckRange" => "24-410, 28-410",
                "desc" => "Curved aesthetic dome-shaped PET bottles designed for luxury cosmetics with custom tinting options."
            ],
            [
                "name" => "Square PET",
                "slug" => "square-pet",
                "categorySlug" => "pet-bottles",
                "categoryName" => "PET bottles",
                "image" => "/logo_svg.svg",
                "material" => "PET",
                "shape" => "Square",
                "volumeRange" => "150ml – 500ml",
                "neckRange" => "24-410, 28-410",
                "desc" => "Clean contemporary square profile optimizing packing density and label display area."
            ],
            [
                "name" => "Pharma Brute HDPE",
                "slug" => "pharma-brute-hdpe",
                "categorySlug" => "hdpe-bottles",
                "categoryName" => "HDPE bottles",
                "image" => "/logo_svg.svg",
                "material" => "HDPE",
                "shape" => "Round",
                "volumeRange" => "60ml – 500ml",
                "neckRange" => "22mm, 25mm, 28mm",
                "desc" => "USP Class VI compliant oral medicine bottles with tamper-evident pilfer-proof neck finish."
            ],
            [
                "name" => "Flat Syrup HDPE",
                "slug" => "flat-syrup-hdpe",
                "categorySlug" => "hdpe-bottles",
                "categoryName" => "HDPE bottles",
                "image" => "/logo_svg.svg",
                "material" => "HDPE",
                "shape" => "Flat Oval",
                "volumeRange" => "60ml – 300ml",
                "neckRange" => "25mm, 28mm",
                "desc" => "Pocket-friendly flat oral suspension container with airtight torque seal closure compatibility."
            ],
            [
                "name" => "Boston White HDPE",
                "slug" => "boston-white-hdpe",
                "categorySlug" => "hdpe-bottles",
                "categoryName" => "HDPE bottles",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp",
                "material" => "HDPE",
                "shape" => "Round",
                "volumeRange" => "100ml – 1,000ml",
                "neckRange" => "24-410, 28-410",
                "desc" => "Opaque white barrier bottles for sensitive skincare emulsions and medical liquids."
            ],
            [
                "name" => "Screw & Dispensing Caps",
                "slug" => "screw-dispensing-caps",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_caps_and_closures.webp",
                "material" => "Polypropylene (PP)",
                "shape" => "Round",
                "volumeRange" => "Fitment 18mm – 38mm",
                "neckRange" => "18/410 to 38mm",
                "desc" => "Precision injection-moulded screw closures, flip tops, and disc top caps with guaranteed leak-tight seals."
            ],
            [
                "name" => "Child Resistant Closures (CRC)",
                "slug" => "crc-caps",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_caps_and_closures.webp",
                "material" => "PP / PE",
                "shape" => "Round",
                "volumeRange" => "Fitment 20mm – 28mm",
                "neckRange" => "20mm, 25mm, 28mm",
                "desc" => "Certified child-resistant closures compliant with ISO 8317 for pharmaceutical and chemical packaging."
            ],
            [
                "name" => "Cosmetic Lotion Pumps",
                "slug" => "cosmetic-lotion-pumps",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_pumps.webp",
                "material" => "PP with SS304 Spring",
                "shape" => "Dispenser",
                "volumeRange" => "Output: 1.2cc – 4.0cc",
                "neckRange" => "20/410, 24/410, 28/410",
                "desc" => "Smooth actuation lotion pumps with lock-up and lock-down mechanisms tested over 1,000 continuous cycles."
            ],
            [
                "name" => "Fine Mist Atomizers",
                "slug" => "fine-mist-atomizers",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_sprays.webp",
                "material" => "PP / PET Nozzle",
                "shape" => "Dispenser",
                "volumeRange" => "Output: 0.12ml – 0.18ml",
                "neckRange" => "18/410, 20/410, 24/410",
                "desc" => "Ultra-fine droplet dispersal sprayers with clear protective over-caps for perfumes and facial mists."
            ],
            [
                "name" => "Industrial Trigger Sprayers",
                "slug" => "industrial-trigger-sprayers",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "Heavy-Duty PP",
                "shape" => "Trigger",
                "volumeRange" => "Output: 0.8cc – 1.3cc",
                "neckRange" => "28/400, 28/410",
                "desc" => "Dual spray and jet stream industrial nozzles designed for agrochemical pesticides and household cleaners."
            ],
            [
                "name" => "Nutraceutical Tablet Jars",
                "slug" => "nutraceutical-tablet-jars",
                "categorySlug" => "pet-jars",
                "categoryName" => "PET jars",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_nutraceuticle_jars.webp",
                "material" => "Food-Grade PET",
                "shape" => "Wide-Mouth Round",
                "volumeRange" => "75cc – 500cc",
                "neckRange" => "38mm, 45mm, 53mm",
                "desc" => "Class 10,000 cleanroom produced tablet jars with induction heat-seal neck finishes for capsules and powders."
            ],
            [
                "name" => "Metal Unishell & Smooth Lids",
                "slug" => "metal-unishell-lids",
                "categorySlug" => "lids",
                "categoryName" => "Lids",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_caps_and_closures.webp",
                "material" => "Aluminum / PP",
                "shape" => "Round",
                "volumeRange" => "Fitment 38mm – 70mm",
                "neckRange" => "38mm, 58mm, 70mm",
                "desc" => "Luxury metal unishell covers and smooth PP closures giving a high-end cosmetic finish."
            ],
            [
                "name" => "Double-Wall Luxury PP Jars",
                "slug" => "double-wall-pp-jars",
                "categorySlug" => "pp-jars",
                "categoryName" => "PP jars",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_nutraceuticle_jars.webp",
                "material" => "PP Double-Wall",
                "shape" => "Round Jar",
                "volumeRange" => "50ml – 250ml",
                "neckRange" => "58mm, 70mm",
                "desc" => "Double-wall insulated cosmetic cream jars with high-gloss pearl finish, protective interior shive and gasket."
            ],
            [
                "name" => "Eco Mono-Material PP Jars",
                "slug" => "mono-pp-jars",
                "categorySlug" => "mono-material-jars",
                "categoryName" => "Mono material jar",
                "image" => "/vikaas_inputs/pet_nutraceuticle_jars.webp",
                "material" => "100% Recyclable PP",
                "shape" => "Round",
                "volumeRange" => "50ml – 200ml",
                "neckRange" => "53mm, 63mm",
                "desc" => "Single-polymer circular packaging ready for mainstream recycling streams without material separation."
            ],
            [
                "name" => "Micro-Foam Dispensing Bottles",
                "slug" => "micro-foamers",
                "categorySlug" => "foamer-pumps",
                "categoryName" => "Foamer pumps",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_pumps.webp",
                "material" => "PET Bottle & PP Foamer",
                "shape" => "Cylindrical",
                "volumeRange" => "50ml – 200ml",
                "neckRange" => "30mm, 43mm Foamer",
                "desc" => "Produces rich, instant micro-foam without propellants for dermatological washes and hand cleansers."
            ],
            [
                "name" => "Airless & Active Serum Dispensers",
                "slug" => "airless-dispensers",
                "categorySlug" => "airless",
                "categoryName" => "Airless",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_pumps.webp",
                "material" => "PET / PP Airless Piston",
                "shape" => "Bulb / Cylinder",
                "volumeRange" => "30ml – 100ml",
                "neckRange" => "Snap-On / 20mm",
                "desc" => "Hermetic airless pump systems shielding active cosmetic actives and vitamins from air exposure."
            ],
            [
                "name" => "Push-Pull Dispensing Closure (NB108-A)",
                "slug" => "push-pull-dispensing-nb108-a",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE",
                "shape" => "Round Cylindrical",
                "volumeRange" => "Neck 20/410 – 28/400",
                "neckRange" => "20/410, 24/410, 28/410, 28/400",
                "desc" => "Dishwash liquids, sports drinks, hair serums, personal care lotions"
            ],
            [
                "name" => "Classic Cylindrical Screw Closure (NB108-B)",
                "slug" => "classic-cylindrical-screw-nb108-b",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum",
                "shape" => "Cylindrical Flat Top",
                "volumeRange" => "Neck 18/410 – 43/410",
                "neckRange" => "18/410, 20/410, 24/410, 28/410, 24/415, 28/415, 33/410, 43/410",
                "desc" => "Pharmaceutical syrups, luxury cosmetics, skincare bottles, essential oils"
            ],
            [
                "name" => "Precision Tapered Nozzle Spout (NB108-C)",
                "slug" => "precision-tapered-nozzle-nb108-c",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / LDPE",
                "shape" => "Conical Nozzle",
                "volumeRange" => "Neck 18/410 – 28/410",
                "neckRange" => "18/410, 20/410, 24/410, 28/410",
                "desc" => "Hair oils, scalp treatments, glue & adhesives, technical liquids"
            ],
            [
                "name" => "Dual-Tone Applicator Spout Cap (NB108-D)",
                "slug" => "dual-tone-applicator-spout-nb108-d",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / HDPE",
                "shape" => "Conical Twist Spout",
                "volumeRange" => "Neck 18/410 – 28/410",
                "neckRange" => "18/410, 20/410, 24/410, 28/410",
                "desc" => "Automotive additives, hairdressing dyes, cosmetic lotions, precision chemical dosing"
            ],
            [
                "name" => "Wide-Flange Flip Top Dispensing Cap (NB108-E)",
                "slug" => "wide-flange-flip-top-nb108-e",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP",
                "shape" => "Round Wide Top",
                "volumeRange" => "Neck 24/410 – 33/410",
                "neckRange" => "24/410, 28/410, 28/415, 33/410",
                "desc" => "Body lotions, suncare, liquid soaps, shampoo & conditioners"
            ],
            [
                "name" => "Butterfly Hinge Ribbed Flip Top (NB108-F)",
                "slug" => "butterfly-hinge-ribbed-flip-top-nb108-f",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP",
                "shape" => "Cylindrical Ribbed",
                "volumeRange" => "Neck 24/410 – 28/410",
                "neckRange" => "24/410, 28/400, 28/410",
                "desc" => "Hand sanitizers, body lotions, face cleansers, liquid soaps"
            ],
            [
                "name" => "Dual-Wall Flip Top Snap Closure (NB108-G)",
                "slug" => "dual-wall-flip-top-snap-nb108-g",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP",
                "shape" => "Cylindrical Flat Shoulder",
                "volumeRange" => "Neck 20/410 – 28/400",
                "neckRange" => "20/410, 24/410, 28/410, 28/400",
                "desc" => "Food condiments, dish soap, household cleaners, cosmetic shampoos"
            ],
            [
                "name" => "Luxury Ribbed Skirt Flip Top Cap (NB108-H)",
                "slug" => "luxury-ribbed-skirt-flip-top-nb108-h",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Electroplated",
                "shape" => "Cylindrical Ribbed",
                "volumeRange" => "Neck 18/410 – 38/400",
                "neckRange" => "18/410, 18/415, 20/410, 20/415, 24/410, 24/415, 28/410, 28/415, 33/410, 38/400",
                "desc" => "High-end haircare, salon shampoos, luxury shower gels, prestige cosmetics"
            ],
            [
                "name" => "Prestige Disc Top Press Closure (NB108-I)",
                "slug" => "prestige-disc-top-press-nb108-i",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_caps_and_closures.webp",
                "material" => "PP / Aluminum Collar",
                "shape" => "Cylindrical Disc Top",
                "volumeRange" => "Neck 18/410 – 28/415",
                "neckRange" => "18/410, 18/415, 20/410, 20/415, 24/410, 24/415, 28/410, 28/415",
                "desc" => "Facial cleansers, body lotions, haircare conditioners, baby wash"
            ],
            [
                "name" => "Smooth Cylindrical Flip Top Cap (NB108-J)",
                "slug" => "smooth-cylindrical-flip-top-nb108-j",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP",
                "shape" => "Smooth Cylindrical",
                "volumeRange" => "Neck 20/410 – 28/410",
                "neckRange" => "20/410, 24/410, 28/410",
                "desc" => "Sun care, skincare serums, dermatological lotions, baby care"
            ],
            [
                "name" => "Continuous Thread Screw Closure (NB108-K)",
                "slug" => "continuous-thread-screw-nb108-k",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / HDPE",
                "shape" => "Ribbed Flat Top",
                "volumeRange" => "Neck 20mm – 90mm",
                "neckRange" => "20mm, 24mm, 28mm, 32mm, 38mm, 45mm, 54mm, 61mm, 68mm, 90mm",
                "desc" => "Pharma bottles, nutraceutical packers, chemical reagents, wide-mouth jars"
            ],
            [
                "name" => "Mushroom Dome Luxury Cosmetic Cap (NB108-L)",
                "slug" => "mushroom-dome-luxury-nb108-l",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / SAN",
                "shape" => "Mushroom Dome",
                "volumeRange" => "Neck 20/415 – 24/415",
                "neckRange" => "20/415, 24/415",
                "desc" => "High-prestige perfumes, luxury body lotions, hair tonics, vanity skincare"
            ],
            [
                "name" => "Contoured Ergonomic Flip Top Closure (NB108-M)",
                "slug" => "contoured-ergonomic-flip-top-nb108-m",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP",
                "shape" => "Asymmetric Ergonomic Top",
                "volumeRange" => "Neck 24/410 – 24/410",
                "neckRange" => "24/410",
                "desc" => "Premium facial care, hand creams, sun lotions, body moisturizer"
            ],
            [
                "name" => "Deep-Skirt Heavy-Wall Jar & Bottle Closure (NB108-N)",
                "slug" => "deep-skirt-heavy-wall-nb108-n",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Double-Wall",
                "shape" => "Straight Cylindrical",
                "volumeRange" => "Neck 18/410 – 24/410",
                "neckRange" => "18/410, 20/410, 24/410",
                "desc" => "Cosmetic jars, pharmaceutical tablets, specialty chemical concentrates"
            ],
            [
                "name" => "Decorative Spherical Ball Closure (NB108-O)",
                "slug" => "decorative-spherical-ball-nb108-o",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / ABS",
                "shape" => "Spherical / Fluted Ball",
                "volumeRange" => "Neck 24/410 – 24/410",
                "neckRange" => "24/410",
                "desc" => "Artisanal perfumes, youth cosmetics, luxury bath oils, novelty skincare"
            ],
            [
                "name" => "Tamper-Evident Sports Drink Push-Pull Cap (NB108-P)",
                "slug" => "tamper-evident-sports-push-pull-nb108-p",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / PET",
                "shape" => "Sports Push-Pull with Overcap",
                "volumeRange" => "Neck 28/410 – 28/415",
                "neckRange" => "28/410, 28/415",
                "desc" => "Energy drinks, functional beverages, syrup bottles, active sports nutrition"
            ],
            [
                "name" => "Ribbed Skirt Swivel Turret Dispenser Cap (NB108-Q)",
                "slug" => "ribbed-skirt-swivel-turret-nb108-q",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / LDPE",
                "shape" => "Turret Spout",
                "volumeRange" => "Neck 24/410 – 28/410",
                "neckRange" => "24/410, 28/410",
                "desc" => "Edible oils, hair oils, automotive lubricants, craft liquids"
            ],
            [
                "name" => "Domed Flip Top Closure in Chrome & PP (NB108-R)",
                "slug" => "domed-flip-top-closure-nb108-r",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Shell",
                "shape" => "Domed Cylinder",
                "volumeRange" => "Neck 20/410 – 28/410",
                "neckRange" => "20/410, 24/410, 28/410",
                "desc" => "Prestige cosmetics, salon hair oils, luxury bath gels, organic personal care"
            ],
            [
                "name" => "Organic Pebble-Form Smooth Closure (NB108-S)",
                "slug" => "organic-pebble-form-smooth-nb108-s",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "PP / High-Gloss Resin",
                "shape" => "Ergonomic Organic Pebble",
                "volumeRange" => "Neck 24mm – 24mm",
                "neckRange" => "24mm",
                "desc" => "Luxury botanical skincare, spa toners, high-end beauty elixirs"
            ],
            [
                "name" => "Translucent Polypropylene Snap-Hinge Cap (NB108-T)",
                "slug" => "translucent-pp-snap-hinge-nb108-t",
                "categorySlug" => "caps",
                "categoryName" => "Caps",
                "image" => "/logo_svg.svg",
                "material" => "100% Virgin PP Natural",
                "shape" => "Stepped Cylindrical",
                "volumeRange" => "Neck 18/410 – 24/410",
                "neckRange" => "18/410, 20/410, 24/410",
                "desc" => "Medical liquids, laboratory reagents, chemical dosing, cosmetic droppers"
            ],
            [
                "name" => "NB101-A Lotion Pump (2cc)",
                "slug" => "pump-nb101-a",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_pumps.webp",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-B Lotion Pump (2cc)",
                "slug" => "pump-nb101-b",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-D Lotion Pump (2cc)",
                "slug" => "pump-nb101-d",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-O Lotion Pump (2cc)",
                "slug" => "pump-nb101-o",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-E Lotion Pump (2cc)",
                "slug" => "pump-nb101-e",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-F Lotion Pump (2cc)",
                "slug" => "pump-nb101-f",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-G Lotion Pump (2cc)",
                "slug" => "pump-nb101-g",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-H Lotion Pump (2cc)",
                "slug" => "pump-nb101-h",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-I Lotion Pump (2cc)",
                "slug" => "pump-nb101-i",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-J Lotion Pump (2cc)",
                "slug" => "pump-nb101-j",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-K Lotion Pump (2cc)",
                "slug" => "pump-nb101-k",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-M Lotion Pump (2cc)",
                "slug" => "pump-nb101-m",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB101-N Lotion Pump (2cc)",
                "slug" => "pump-nb101-n",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2cc/T +/-",
                "neckRange" => "24/410, 28/410, 28/400, 24/415, 28/412, 28/415",
                "desc" => "NB101 Lotion Pump delivering 2cc/T +/- precision dosage. Available in 24/410, 28/410, 28/400, 24/415, 28/412, 28/415 neck finishes. Closures: Smooth 24/410,28/410; Ribbed 24/410,28/410; Ribbed 28/400; Ribbed 24/415,28/412,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB102-A Outside-Spring Switch Lotion Pump (2.5cc)",
                "slug" => "pump-nb102-a",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2.5cc/T +/-",
                "neckRange" => "28/400, 24/410, 28/410, 24/415, 28/415",
                "desc" => "NB102 Outside-Spring Switch Lotion Pump delivering 2.5cc/T +/- precision dosage. Available in 28/400, 24/410, 28/410, 24/415, 28/415 neck finishes. Closures: Smooth/Ribbed 28/400; Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB102-B Outside-Spring Switch Lotion Pump (2.5cc)",
                "slug" => "pump-nb102-b",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2.5cc/T +/-",
                "neckRange" => "28/400, 24/410, 28/410, 24/415, 28/415",
                "desc" => "NB102 Outside-Spring Switch Lotion Pump delivering 2.5cc/T +/- precision dosage. Available in 28/400, 24/410, 28/410, 24/415, 28/415 neck finishes. Closures: Smooth/Ribbed 28/400; Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB102-C Outside-Spring Switch Lotion Pump (2.5cc)",
                "slug" => "pump-nb102-c",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2.5cc/T +/-",
                "neckRange" => "28/400, 24/410, 28/410, 24/415, 28/415",
                "desc" => "NB102 Outside-Spring Switch Lotion Pump delivering 2.5cc/T +/- precision dosage. Available in 28/400, 24/410, 28/410, 24/415, 28/415 neck finishes. Closures: Smooth/Ribbed 28/400; Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB102-D Outside-Spring Switch Lotion Pump (2.5cc)",
                "slug" => "pump-nb102-d",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2.5cc/T +/-",
                "neckRange" => "28/400, 24/410, 28/410, 24/415, 28/415",
                "desc" => "NB102 Outside-Spring Switch Lotion Pump delivering 2.5cc/T +/- precision dosage. Available in 28/400, 24/410, 28/410, 24/415, 28/415 neck finishes. Closures: Smooth/Ribbed 28/400; Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB102-E Outside-Spring Switch Lotion Pump (2.5cc)",
                "slug" => "pump-nb102-e",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2.5cc/T +/-",
                "neckRange" => "28/400, 24/410, 28/410, 24/415, 28/415",
                "desc" => "NB102 Outside-Spring Switch Lotion Pump delivering 2.5cc/T +/- precision dosage. Available in 28/400, 24/410, 28/410, 24/415, 28/415 neck finishes. Closures: Smooth/Ribbed 28/400; Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB102-F Outside-Spring Switch Lotion Pump (2.5cc)",
                "slug" => "pump-nb102-f",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 2.5cc/T +/-",
                "neckRange" => "28/400, 24/410, 28/410, 24/415, 28/415",
                "desc" => "NB102 Outside-Spring Switch Lotion Pump delivering 2.5cc/T +/- precision dosage. Available in 28/400, 24/410, 28/410, 24/415, 28/415 neck finishes. Closures: Smooth/Ribbed 28/400; Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB103-A Inside-Spring Switch Lotion Pump (1.4cc)",
                "slug" => "pump-nb103-a",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 1.4cc/T +/-",
                "neckRange" => "24/410, 28/410, 24/415, 28/415, 28/400",
                "desc" => "NB103 Inside-Spring Switch Lotion Pump delivering 1.4cc/T +/- precision dosage. Available in 24/410, 28/410, 24/415, 28/415, 28/400 neck finishes. Closures: Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Smooth/Ribbed 28/400; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB103-C Inside-Spring Switch Lotion Pump (1.4cc)",
                "slug" => "pump-nb103-c",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 1.4cc/T +/-",
                "neckRange" => "24/410, 28/410, 24/415, 28/415, 28/400",
                "desc" => "NB103 Inside-Spring Switch Lotion Pump delivering 1.4cc/T +/- precision dosage. Available in 24/410, 28/410, 24/415, 28/415, 28/400 neck finishes. Closures: Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Smooth/Ribbed 28/400; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB103-D Inside-Spring Switch Lotion Pump (1.4cc)",
                "slug" => "pump-nb103-d",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 1.4cc/T +/-",
                "neckRange" => "24/410, 28/410, 24/415, 28/415, 28/400",
                "desc" => "NB103 Inside-Spring Switch Lotion Pump delivering 1.4cc/T +/- precision dosage. Available in 24/410, 28/410, 24/415, 28/415, 28/400 neck finishes. Closures: Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Smooth/Ribbed 28/400; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB103-E Inside-Spring Switch Lotion Pump (1.4cc)",
                "slug" => "pump-nb103-e",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 1.4cc/T +/-",
                "neckRange" => "24/410, 28/410, 24/415, 28/415, 28/400",
                "desc" => "NB103 Inside-Spring Switch Lotion Pump delivering 1.4cc/T +/- precision dosage. Available in 24/410, 28/410, 24/415, 28/415, 28/400 neck finishes. Closures: Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Smooth/Ribbed 28/400; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB103-F Inside-Spring Switch Lotion Pump (1.4cc)",
                "slug" => "pump-nb103-f",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 1.4cc/T +/-",
                "neckRange" => "24/410, 28/410, 24/415, 28/415, 28/400",
                "desc" => "NB103 Inside-Spring Switch Lotion Pump delivering 1.4cc/T +/- precision dosage. Available in 24/410, 28/410, 24/415, 28/415, 28/400 neck finishes. Closures: Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Smooth/Ribbed 28/400; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB103-G Inside-Spring Switch Lotion Pump (1.4cc)",
                "slug" => "pump-nb103-g",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 1.4cc/T +/-",
                "neckRange" => "24/410, 28/410, 24/415, 28/415, 28/400",
                "desc" => "NB103 Inside-Spring Switch Lotion Pump delivering 1.4cc/T +/- precision dosage. Available in 24/410, 28/410, 24/415, 28/415, 28/400 neck finishes. Closures: Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Smooth/Ribbed 28/400; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB103-H Inside-Spring Switch Lotion Pump (1.4cc)",
                "slug" => "pump-nb103-h",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 1.4cc/T +/-",
                "neckRange" => "24/410, 28/410, 24/415, 28/415, 28/400",
                "desc" => "NB103 Inside-Spring Switch Lotion Pump delivering 1.4cc/T +/- precision dosage. Available in 24/410, 28/410, 24/415, 28/415, 28/400 neck finishes. Closures: Smooth/Ribbed 24/410,28/410; Smooth/Ribbed 24/415,28/415; Smooth/Ribbed 28/400; Treatments: Aluminum, UV, Sandblasting, Bamboo, Water transfer printing."
            ],
            [
                "name" => "NB104-A Big-Output Lotion Pump (4cc)",
                "slug" => "pump-nb104-a",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 4cc/T +/-",
                "neckRange" => "28/410, 33/410, 38/400, 38/410",
                "desc" => "NB104 Big-Output Lotion Pump delivering 4cc/T +/- precision dosage. Available in 28/410, 33/410, 38/400, 38/410 neck finishes. Closure sizes: 28/410,33/410,38/400,38/410. Ribbed/Smooth 28/410,33/410,38/410; Double wall closure 33/410,28/410,38/400; Treatments: Ribbed, Smooth, UV, Sandblasting, Water transfer printing, Aluminum, Bamboo."
            ],
            [
                "name" => "NB104-B Big-Output Lotion Pump (4cc)",
                "slug" => "pump-nb104-b",
                "categorySlug" => "pumps",
                "categoryName" => "Pumps",
                "image" => "/logo_svg.svg",
                "material" => "PP / Aluminum Sheath / SS304",
                "shape" => "Switch Lotion Dispenser",
                "volumeRange" => "Output 4cc/T +/-",
                "neckRange" => "28/410, 33/410, 38/400, 38/410",
                "desc" => "NB104 Big-Output Lotion Pump delivering 4cc/T +/- precision dosage. Available in 28/410, 33/410, 38/400, 38/410 neck finishes. Closure sizes: 28/410,33/410,38/400,38/410. Ribbed/Smooth 28/410,33/410,38/410; Double wall closure 33/410,28/410,38/400; Treatments: Ribbed, Smooth, UV, Sandblasting, Water transfer printing, Aluminum, Bamboo."
            ],
            [
                "name" => "NB107-A Medical Sprayer (0.12cc/T +/- , 0.05cc/T +/-)",
                "slug" => "spray-nb107-a",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output 0.12cc/T +/- , 0.05cc/T +/-",
                "neckRange" => "18/410, 20/410, 24/410, 28/410",
                "desc" => "NB107 Medical Sprayer engineered for uniform dispensing. Available sizes: 18/410, 20/410, 24/410, 28/410. Actuator style options 1/2/3 shown within photo."
            ],
            [
                "name" => "NB107-C Medical Sprayer (0.12cc/T +/- , 0.05cc/T +/-)",
                "slug" => "spray-nb107-c",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output 0.12cc/T +/- , 0.05cc/T +/-",
                "neckRange" => "18/410, 20/410, 24/410",
                "desc" => "NB107 Medical Sprayer engineered for uniform dispensing. Available sizes: 18/410, 20/410, 24/410. Actuator style options 1/2/3 shown within photo."
            ],
            [
                "name" => "NB107-B Medical Sprayer (0.12cc/T +/- , 0.05cc/T +/-)",
                "slug" => "spray-nb107-b",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output 0.12cc/T +/- , 0.05cc/T +/-",
                "neckRange" => "18/410, 20/410, 24/410",
                "desc" => "NB107 Medical Sprayer engineered for uniform dispensing. Available sizes: 18/410, 20/410, 24/410. Actuator style options 1/2/3 shown within photo."
            ],
            [
                "name" => "NB107-D Medical Sprayer (0.12cc/T +/- , 0.05cc/T +/-)",
                "slug" => "spray-nb107-d",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output 0.12cc/T +/- , 0.05cc/T +/-",
                "neckRange" => "30/410",
                "desc" => "NB107 Medical Sprayer engineered for uniform dispensing. Available sizes: 30/410. Actuator style options 1/2/3 shown within photo."
            ],
            [
                "name" => "NB107-E Medical Sprayer (0.12cc/T +/- , 0.05cc/T +/-)",
                "slug" => "spray-nb107-e",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output 0.12cc/T +/- , 0.05cc/T +/-",
                "neckRange" => "18/410, 18/415, 20/410, 20/415",
                "desc" => "NB107 Medical Sprayer engineered for uniform dispensing. Available sizes: 18/410, 18/415, 20/410, 20/415. Actuator style options 1/2/3 shown within photo."
            ],
            [
                "name" => "NB201-A Trigger Sprayer (0.8cc/T +/-)",
                "slug" => "spray-nb201-a",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_sprays.webp",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.8cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB201 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Closure 28/400,28/410,28/415. Nozzle options: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam. Trigger options: Normal, Two Finger, Big Trigger."
            ],
            [
                "name" => "NB201-C Trigger Sprayer (0.8cc/T +/-)",
                "slug" => "spray-nb201-c",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.8cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB201 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Closure 28/400,28/410,28/415. Nozzle options: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam. Trigger options: Normal, Two Finger, Big Trigger."
            ],
            [
                "name" => "NB201-G Trigger Sprayer (0.8cc/T +/-)",
                "slug" => "spray-nb201-g",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.8cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB201 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Closure 28/400,28/410,28/415. Nozzle options: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam. Trigger options: Normal, Two Finger, Big Trigger."
            ],
            [
                "name" => "NB201-H Trigger Sprayer (0.8cc/T +/-)",
                "slug" => "spray-nb201-h",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.8cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB201 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Closure 28/400,28/410,28/415. Nozzle options: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam. Trigger options: Normal, Two Finger, Big Trigger."
            ],
            [
                "name" => "NB201-I Trigger Sprayer (0.8cc/T +/-)",
                "slug" => "spray-nb201-i",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.8cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB201 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Closure 28/400,28/410,28/415. Nozzle options: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam. Trigger options: Normal, Two Finger, Big Trigger."
            ],
            [
                "name" => "NB201-J Trigger Sprayer (0.8cc/T +/-)",
                "slug" => "spray-nb201-j",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.8cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB201 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Closure 28/400,28/410,28/415. Nozzle options: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam. Trigger options: Normal, Two Finger, Big Trigger."
            ],
            [
                "name" => "NB201-K Trigger Sprayer (0.8cc/T +/-)",
                "slug" => "spray-nb201-k",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.8cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB201 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Closure 28/400,28/410,28/415. Nozzle options: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam. Trigger options: Normal, Two Finger, Big Trigger."
            ],
            [
                "name" => "NB201-M Trigger Sprayer (0.8cc/T +/-)",
                "slug" => "spray-nb201-m",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.8cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB201 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Closure 28/400,28/410,28/415. Nozzle options: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam. Trigger options: Normal, Two Finger, Big Trigger."
            ],
            [
                "name" => "NB201-N Trigger Sprayer (0.8cc/T +/-)",
                "slug" => "spray-nb201-n",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.8cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB201 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Closure 28/400,28/410,28/415. Nozzle options: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam. Trigger options: Normal, Two Finger, Big Trigger."
            ],
            [
                "name" => "NB201-O Trigger Sprayer (0.8cc/T +/-)",
                "slug" => "spray-nb201-o",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.8cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB201 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Closure 28/400,28/410,28/415. Nozzle options: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam. Trigger options: Normal, Two Finger, Big Trigger."
            ],
            [
                "name" => "NB202-A Trigger Sprayer (1.0cc/T +/-)",
                "slug" => "spray-nb202-a",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 1.0cc/T +/-",
                "neckRange" => "24/410, 28/400, 28/410",
                "desc" => "NB202 Trigger Sprayer engineered for uniform dispensing. Available sizes: 24/410, 28/400, 28/410. ."
            ],
            [
                "name" => "NB202-B Trigger Sprayer (1.0cc/T +/-)",
                "slug" => "spray-nb202-b",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 1.0cc/T +/-",
                "neckRange" => "28/400, 28/410",
                "desc" => "NB202 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410. ."
            ],
            [
                "name" => "NB202-C Trigger Sprayer (1.0cc/T +/-)",
                "slug" => "spray-nb202-c",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 1.0cc/T +/-",
                "neckRange" => "28/400, 28/410",
                "desc" => "NB202 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410. ."
            ],
            [
                "name" => "NB203-A Trigger Sprayer (0.3cc/T +/- , 0.65cc/T +/-)",
                "slug" => "spray-nb203-a",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.3cc/T +/- , 0.65cc/T +/-",
                "neckRange" => "20/410, 24/410, 24/415, 28/410",
                "desc" => "NB203 Trigger Sprayer engineered for uniform dispensing. Available sizes: 20/410, 24/410, 24/415, 28/410. Closure: Smooth, Ribbed. Sizes 20/410,24/410,24/415,28/410."
            ],
            [
                "name" => "NB203-B-1 Trigger Sprayer (0.3cc/T +/- , 0.65cc/T +/-)",
                "slug" => "spray-nb203-b-1",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.3cc/T +/- , 0.65cc/T +/-",
                "neckRange" => "20/410, 24/410, 24/415, 28/410",
                "desc" => "NB203 Trigger Sprayer engineered for uniform dispensing. Available sizes: 20/410, 24/410, 24/415, 28/410. Closure: Smooth, Ribbed. Sizes 20/410,24/410,24/415,28/410."
            ],
            [
                "name" => "NB203-B-2 Trigger Sprayer (0.3cc/T +/- , 0.65cc/T +/-)",
                "slug" => "spray-nb203-b-2",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.3cc/T +/- , 0.65cc/T +/-",
                "neckRange" => "20/410, 24/410, 24/415, 28/410",
                "desc" => "NB203 Trigger Sprayer engineered for uniform dispensing. Available sizes: 20/410, 24/410, 24/415, 28/410. Closure: Smooth, Ribbed. Sizes 20/410,24/410,24/415,28/410."
            ],
            [
                "name" => "NB203-C Trigger Sprayer (0.3cc/T +/- , 0.65cc/T +/-)",
                "slug" => "spray-nb203-c",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.3cc/T +/- , 0.65cc/T +/-",
                "neckRange" => "20/410, 24/410, 24/415, 28/410",
                "desc" => "NB203 Trigger Sprayer engineered for uniform dispensing. Available sizes: 20/410, 24/410, 24/415, 28/410. Closure: Smooth, Ribbed. Sizes 20/410,24/410,24/415,28/410."
            ],
            [
                "name" => "NB203-D Trigger Sprayer (0.3cc/T +/- , 0.65cc/T +/-)",
                "slug" => "spray-nb203-d",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.3cc/T +/- , 0.65cc/T +/-",
                "neckRange" => "20/410, 24/410, 24/415, 28/410",
                "desc" => "NB203 Trigger Sprayer engineered for uniform dispensing. Available sizes: 20/410, 24/410, 24/415, 28/410. Closure: Smooth, Ribbed. Sizes 20/410,24/410,24/415,28/410."
            ],
            [
                "name" => "NB203-E Trigger Sprayer (0.3cc/T +/- , 0.65cc/T +/-)",
                "slug" => "spray-nb203-e",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.3cc/T +/- , 0.65cc/T +/-",
                "neckRange" => "20/410, 24/410, 24/415, 28/410",
                "desc" => "NB203 Trigger Sprayer engineered for uniform dispensing. Available sizes: 20/410, 24/410, 24/415, 28/410. Closure: Smooth, Ribbed. Sizes 20/410,24/410,24/415,28/410."
            ],
            [
                "name" => "NB203-F Trigger Sprayer (0.3cc/T +/- , 0.65cc/T +/-)",
                "slug" => "spray-nb203-f",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.3cc/T +/- , 0.65cc/T +/-",
                "neckRange" => "20/410, 24/410, 24/415, 28/410",
                "desc" => "NB203 Trigger Sprayer engineered for uniform dispensing. Available sizes: 20/410, 24/410, 24/415, 28/410. Closure: Smooth, Ribbed. Sizes 20/410,24/410,24/415,28/410."
            ],
            [
                "name" => "NB204-A Trigger Sprayer (1.2cc/T +/-)",
                "slug" => "spray-nb204-a",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 1.2cc/T +/-",
                "neckRange" => "28/410, 28/400",
                "desc" => "NB204 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/410, 28/400. Closure size 28/410, 28/400."
            ],
            [
                "name" => "NB204-B Trigger Sprayer (1.2cc/T +/-)",
                "slug" => "spray-nb204-b",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 1.2cc/T +/-",
                "neckRange" => "28/410, 28/400",
                "desc" => "NB204 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/410, 28/400. Closure size 28/410, 28/400."
            ],
            [
                "name" => "NB205-A Trigger Sprayer (1.2cc/T +/-)",
                "slug" => "spray-nb205-a",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 1.2cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB205 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Size 28/400,28/410,28/415. All-plastic trigger sprayer, no metal spring inside. Foam variant also available."
            ],
            [
                "name" => "NB205-B Trigger Sprayer (1.2cc/T +/-)",
                "slug" => "spray-nb205-b",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 1.2cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB205 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Size 28/400,28/410,28/415. All-plastic trigger sprayer, no metal spring inside. Foam variant also available."
            ],
            [
                "name" => "NB205-C Trigger Sprayer (1.2cc/T +/-)",
                "slug" => "spray-nb205-c",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 1.2cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB205 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Size 28/400,28/410,28/415. All-plastic trigger sprayer, no metal spring inside. Foam variant also available."
            ],
            [
                "name" => "NB205-D Trigger Sprayer (1.2cc/T +/-)",
                "slug" => "spray-nb205-d",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 1.2cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB205 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Size 28/400,28/410,28/415. All-plastic trigger sprayer, no metal spring inside. Foam variant also available."
            ],
            [
                "name" => "NB206-A Trigger Sprayer (0.9cc/T +/-)",
                "slug" => "spray-nb206-a",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.9cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB206 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Size 28/400,28/410,28/415. Safety Closure / Normal Closure options. Nozzle: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam."
            ],
            [
                "name" => "NB206-B Trigger Sprayer (0.9cc/T +/-)",
                "slug" => "spray-nb206-b",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.9cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB206 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Size 28/400,28/410,28/415. Safety Closure / Normal Closure options. Nozzle: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam."
            ],
            [
                "name" => "NB206-C Trigger Sprayer (0.9cc/T +/-)",
                "slug" => "spray-nb206-c",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.9cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB206 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Size 28/400,28/410,28/415. Safety Closure / Normal Closure options. Nozzle: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam."
            ],
            [
                "name" => "NB206-D Trigger Sprayer (0.9cc/T +/-)",
                "slug" => "spray-nb206-d",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.9cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB206 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Size 28/400,28/410,28/415. Safety Closure / Normal Closure options. Nozzle: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam."
            ],
            [
                "name" => "NB206-E Trigger Sprayer (0.9cc/T +/-)",
                "slug" => "spray-nb206-e",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.9cc/T +/-",
                "neckRange" => "28/400, 28/410, 28/415",
                "desc" => "NB206 Trigger Sprayer engineered for uniform dispensing. Available sizes: 28/400, 28/410, 28/415. Size 28/400,28/410,28/415. Safety Closure / Normal Closure options. Nozzle: Spray/Spray, Spray/Stream, Metal mesh/Foam, Plastic mesh/Foam."
            ],
            [
                "name" => "NB207-A Trigger Sprayer (0.26-0.3cc/T +/-)",
                "slug" => "spray-nb207-a",
                "categorySlug" => "trigger-sprayers",
                "categoryName" => "Trigger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Ergonomic Trigger",
                "volumeRange" => "Output 0.26-0.3cc/T +/-",
                "neckRange" => "24/410, 28/400, 28/410",
                "desc" => "NB207 Trigger Sprayer engineered for uniform dispensing. Available sizes: 24/410, 28/400, 28/410. Size 24/410, 28/400, 28/410."
            ],
            [
                "name" => "NB304-A Fine-Mist / Perfume Sprayer (0.14ml +/- , 0.25ml +/-)",
                "slug" => "spray-nb304-a",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/vikaas_inputs/thumbnails/thumbnail_sprays.webp",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output 0.14ml +/- , 0.25ml +/-",
                "neckRange" => "14/410, 18/410, 18/415, 20/410, 20/415, 22/410, 22/415, 24/410, 24/415, 28/410",
                "desc" => "NB304 Fine-Mist / Perfume Sprayer engineered for uniform dispensing. Available sizes: 14/410, 18/410, 18/415, 20/410, 20/415, 22/410, 22/415, 24/410, 24/415, 28/410. Design options: spring outside, spring inside, 360 degree. Closure options: Ribbed, Smooth, UV, Aluminum, Sandblasting, Water transfer printing, Bamboo."
            ],
            [
                "name" => "NB304-B Fine-Mist / Perfume Sprayer (0.14ml +/- , 0.25ml +/-)",
                "slug" => "spray-nb304-b",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output 0.14ml +/- , 0.25ml +/-",
                "neckRange" => "14/410, 18/410, 18/415, 20/410, 20/415, 22/410, 22/415, 24/410, 24/415, 28/410",
                "desc" => "NB304 Fine-Mist / Perfume Sprayer engineered for uniform dispensing. Available sizes: 14/410, 18/410, 18/415, 20/410, 20/415, 22/410, 22/415, 24/410, 24/415, 28/410. Design options: spring outside, spring inside, 360 degree. Closure options: Ribbed, Smooth, UV, Aluminum, Sandblasting, Water transfer printing, Bamboo."
            ],
            [
                "name" => "NB304-C Fine-Mist / Perfume Sprayer (0.14ml +/- , 0.25ml +/-)",
                "slug" => "spray-nb304-c",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output 0.14ml +/- , 0.25ml +/-",
                "neckRange" => "20/410, 24/410",
                "desc" => "NB304 Fine-Mist / Perfume Sprayer engineered for uniform dispensing. Available sizes: 20/410, 24/410. Design options: spring outside, spring inside, 360 degree. Closure options: Ribbed, Smooth, UV, Aluminum, Sandblasting, Water transfer printing, Bamboo."
            ],
            [
                "name" => "ContinuedSpray-A Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-a",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "200ml, 300ml, 500ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 200ml, 300ml, 500ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-B Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-b",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "350ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 350ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-C Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-c",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "350ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 350ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-D Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-d",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "380ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 380ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-E Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-e",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "440ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 440ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-F Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-f",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "300ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 300ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-G Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-g",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "320ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 320ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-H Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-h",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "500ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 500ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-I Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-i",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "330ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 330ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-J Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-j",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "330ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 330ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-K Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-k",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "350ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 350ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ],
            [
                "name" => "ContinuedSpray-L Continuous-Mist Spray Bottle",
                "slug" => "spray-continuedspray-l",
                "categorySlug" => "finger-sprayers",
                "categoryName" => "Finger sprayer",
                "image" => "/logo_svg.svg",
                "material" => "PP / PE / Precision Nozzle",
                "shape" => "Fine Mist Atomizer",
                "volumeRange" => "Output Fine Mist",
                "neckRange" => "200ml",
                "desc" => "Continued Spray Bottle Continuous-Mist Spray Bottle engineered for uniform dispensing. Available sizes: 200ml. No product code printed in catalog for this group; lettered A-L by photo position only."
            ]
        ];
    private static $skus = [];
    private static $countries = [
            "United States",
            "United Kingdom",
            "Germany",
            "United Arab Emirates",
            "Saudi Arabia",
            "France",
            "Netherlands",
            "Australia",
            "Canada",
            "Singapore",
            "India",
            "Afghanistan",
            "Albania",
            "Algeria",
            "Andorra",
            "Angola",
            "Antigua and Barbuda",
            "Argentina",
            "Armenia",
            "Austria",
            "Azerbaijan",
            "Bahamas",
            "Bahrain",
            "Bangladesh",
            "Barbados",
            "Belarus",
            "Belgium",
            "Belize",
            "Benin",
            "Bhutan",
            "Bolivia",
            "Bosnia and Herzegovina",
            "Botswana",
            "Brazil",
            "Brunei",
            "Bulgaria",
            "Burkina Faso",
            "Burundi",
            "Cabo Verde",
            "Cambodia",
            "Cameroon",
            "Central African Republic",
            "Chad",
            "Chile",
            "China",
            "Colombia",
            "Comoros",
            "Congo",
            "Costa Rica",
            "Croatia",
            "Cuba",
            "Cyprus",
            "Czech Republic",
            "Denmark",
            "Djibouti",
            "Dominica",
            "Dominican Republic",
            "Ecuador",
            "Egypt",
            "El Salvador",
            "Equatorial Guinea",
            "Eritrea",
            "Estonia",
            "Eswatini",
            "Ethiopia",
            "Fiji",
            "Finland",
            "Gabon",
            "Gambia",
            "Georgia",
            "Ghana",
            "Greece",
            "Grenada",
            "Guatemala",
            "Guinea",
            "Guinea-Bissau",
            "Guyana",
            "Haiti",
            "Honduras",
            "Hungary",
            "Iceland",
            "Indonesia",
            "Iran",
            "Iraq",
            "Ireland",
            "Israel",
            "Italy",
            "Ivory Coast",
            "Jamaica",
            "Japan",
            "Jordan",
            "Kazakhstan",
            "Kenya",
            "Kiribati",
            "Kuwait",
            "Kyrgyzstan",
            "Laos",
            "Latvia",
            "Lebanon",
            "Lesotho",
            "Liberia",
            "Libya",
            "Liechtenstein",
            "Lithuania",
            "Luxembourg",
            "Madagascar",
            "Malawi",
            "Malaysia",
            "Maldives",
            "Mali",
            "Malta",
            "Marshall Islands",
            "Mauritania",
            "Mauritius",
            "Mexico",
            "Micronesia",
            "Moldova",
            "Monaco",
            "Mongolia",
            "Montenegro",
            "Morocco",
            "Mozambique",
            "Myanmar",
            "Namibia",
            "Nauru",
            "Nepal",
            "New Zealand",
            "Nicaragua",
            "Niger",
            "Nigeria",
            "North Korea",
            "North Macedonia",
            "Norway",
            "Oman",
            "Pakistan",
            "Palau",
            "Palestine",
            "Panama",
            "Papua New Guinea",
            "Paraguay",
            "Peru",
            "Philippines",
            "Poland",
            "Portugal",
            "Qatar",
            "Romania",
            "Russia",
            "Rwanda",
            "Saint Kitts and Nevis",
            "Saint Lucia",
            "Saint Vincent and the Grenadines",
            "Samoa",
            "San Marino",
            "Sao Tome and Principe",
            "Senegal",
            "Serbia",
            "Seychelles",
            "Sierra Leone",
            "Slovakia",
            "Slovenia",
            "Solomon Islands",
            "Somalia",
            "South Africa",
            "South Korea",
            "South Sudan",
            "Spain",
            "Sri Lanka",
            "Sudan",
            "Suriname",
            "Sweden",
            "Switzerland",
            "Syria",
            "Taiwan",
            "Tajikistan",
            "Tanzania",
            "Thailand",
            "Timor-Leste",
            "Togo",
            "Tonga",
            "Trinidad and Tobago",
            "Tunisia",
            "Turkey",
            "Turkmenistan",
            "Tuvalu",
            "Uganda",
            "Ukraine",
            "Uruguay",
            "Uzbekistan",
            "Vanuatu",
            "Vatican City",
            "Venezuela",
            "Vietnam",
            "Yemen",
            "Zambia",
            "Zimbabwe",
            "Other Country"
        ];

    /**
     * Get categories (from MySQL if connected, or memory array)
     */
    public static function getCategories() {
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'published' ORDER BY sort_order ASC");
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (!empty($rows)) {
                        return array_map(function($r) {
                            return [
                                'id' => $r['id'],
                                'name' => $r['name'],
                                'slug' => $r['slug'],
                                'image' => $r['image'] ?? '/logo_svg.svg',
                                'coverImage' => $r['cover_image'] ?? '/vikaas_inputs/PHARMA_BOTTLES.webp',
                                'desc' => $r['description'] ?? ''
                            ];
                        }, $rows);
                    }
                }
            } catch (Exception $e) {
                // Fallback
            }
        }
        $cats = self::$categories;
        $optsFile = self::getOptionsFilePath();
        if (file_exists($optsFile)) {
            $opts = json_decode(file_get_contents($optsFile), true) ?: [];
            if (!empty($opts['categories']) && is_array($opts['categories'])) {
                foreach ($opts['categories'] as $c) {
                    $slug = $c['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $c['name'] ?? ''), '-'));
                    $found = false;
                    foreach ($cats as $idx => $orig) {
                        if (($orig['slug'] ?? '') === $slug) {
                            $cats[$idx] = array_merge($orig, $c);
                            $found = true;
                            break;
                        }
                    }
                    if (!$found && !empty($c['name'])) {
                        $c['slug'] = $slug;
                        $c['image'] = $c['image'] ?? '/logo_svg.svg';
                        $cats[] = $c;
                    }
                }
            }
        }
        return $cats;
    }

    public static function getCategoryBySlug($slug) {
        $cats = self::getCategories();
        foreach ($cats as $c) {
            if (($c['slug'] ?? '') === $slug) return $c;
        }
        return null;
    }

    public static function getSeries() {
        $series = self::$series;
        $optsFile = self::getOptionsFilePath();
        if (file_exists($optsFile)) {
            $opts = json_decode(file_get_contents($optsFile), true) ?: [];
            if (!empty($opts['subcategories']) && is_array($opts['subcategories'])) {
                foreach ($opts['subcategories'] as $s) {
                    $slug = $s['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s['name'] ?? ''), '-'));
                    $found = false;
                    foreach ($series as $idx => $orig) {
                        if (($orig['slug'] ?? '') === $slug) {
                            $series[$idx] = array_merge($orig, $s);
                            $found = true;
                            break;
                        }
                    }
                    if (!$found && !empty($s['name'])) {
                        $s['slug'] = $slug;
                        $s['image'] = $s['image'] ?? '/logo_svg.svg';
                        $series[] = $s;
                    }
                }
            }
        }
        return $series;
    }

    public static function getSeriesByCategory($categorySlug) {
        $allSeries = self::getSeries();
        return array_values(array_filter($allSeries, function($s) use ($categorySlug) {
            return ($s['categorySlug'] ?? '') === $categorySlug;
        }));
    }

    public static function getSeriesBySlug($slug) {
        $allSeries = self::getSeries();
        foreach ($allSeries as $s) {
            if (($s['slug'] ?? '') === $slug) return $s;
        }
        return null;
    }

    /**
     * Get edited products from JSON
     */
    public static function getEditedProducts() {
        $filePath = defined('EDITED_PRODUCTS_FILE') ? EDITED_PRODUCTS_FILE : __DIR__ . '/../../config/edited_products.json';
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $data = json_decode($content, true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    /**
     * Get custom products from JSON
     */
    public static function getCustomProducts() {
        $filePath = defined('CUSTOM_PRODUCTS_FILE') ? CUSTOM_PRODUCTS_FILE : __DIR__ . '/../../config/custom_products.json';
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $data = json_decode($content, true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    /**
     * Get all products with MySQL DB as Single Source of Truth
     */
    public static function getSkus($includeArchived = false) {
        // Primary: Query MySQL DB if connected
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $sql = "SELECT p.*, GROUP_CONCAT(pi.image_url ORDER BY pi.sort_order ASC SEPARATOR '|||') as image_list 
                            FROM products p 
                            LEFT JOIN product_images pi ON p.id = pi.product_id ";
                    if (!$includeArchived) {
                        $sql .= " WHERE p.status = 'published' ";
                    }
                    $sql .= " GROUP BY p.id ORDER BY p.id ASC";

                    $stmt = $pdo->query($sql);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($rows)) {
                        $list = [];
                        foreach ($rows as $r) {
                            $imgArr = !empty($r['image_list']) ? explode('|||', $r['image_list']) : [];
                            $primaryImg = !empty($imgArr[0]) ? $imgArr[0] : '/logo_svg.svg';

                            $list[] = [
                                'db_id' => (int)$r['id'],
                                'id' => $r['sku'],
                                'name' => $r['name'],
                                'slug' => $r['slug'] ?? '',
                                'fullTitle' => $r['name'],
                                'articleNo' => $r['sku'],
                                'websiteNo' => $r['sku'],
                                'categorySlug' => $r['category_slug'],
                                'categoryName' => $r['category_name'],
                                'seriesSlug' => self::getSeriesSlugBySubcategory($r['subcategory']),
                                'serie' => $r['subcategory'],
                                'gender' => $r['gender'] ?? 'Unisex',
                                'regularPrice' => (float)$r['regular_price'],
                                'salePrice' => $r['sale_price'] !== null ? (float)$r['sale_price'] : null,
                                'stockQty' => (int)$r['stock_qty'],
                                'stockStatus' => (int)$r['stock_qty'] <= 0 ? 'out_of_stock' : $r['stock_status'],
                                'status' => $r['status'],
                                'isFeatured' => (bool)$r['is_featured'],
                                'image' => $primaryImg,
                                'images' => !empty($imgArr) ? $imgArr : [$primaryImg],
                                'gallery' => !empty($imgArr) ? $imgArr : [$primaryImg],
                                'shortDescription' => $r['short_desc'] ?? '',
                                'description' => $r['description'] ?? '',
                                'seoTitle' => $r['seo_title'] ?? '',
                                'seoDescription' => $r['seo_description'] ?? '',
                                'specs' => [
                                    'capacity' => $r['capacity'],
                                    'neck' => $r['neck_finish'],
                                    'material' => $r['material'],
                                    'weight' => $r['weight'],
                                    'moq' => $r['moq']
                                ],
                                'updatedAt' => $r['updated_at'] ?? ''
                            ];
                        }
                        foreach ($list as &$item) {
                            self::assignProductIndustries($item);
                        }
                        unset($item);
                        return $list;
                    }
                }
            } catch (Exception $e) {
                // Fallback to in-memory JSON merge
            }
        }

        // Secondary Fallback: In-memory array + JSON override merge
        $allMap = [];
        if (empty(self::$skus)) {
            $defFile = dirname(__DIR__, 2) . '/config/default_products.json';
            if (file_exists($defFile)) {
                self::$skus = json_decode(file_get_contents($defFile), true) ?: [];
            }
        }
        foreach (self::$skus as $sku) {
            $id = $sku['id'] ?? ($sku['articleNo'] ?? '');
            if ($id) {
                if (!isset($sku['status'])) $sku['status'] = 'published';
                if (!isset($sku['regularPrice'])) $sku['regularPrice'] = $sku['price'] ?? 0;
                if (!isset($sku['stockQty'])) $sku['stockQty'] = isset($sku['isStock']) && $sku['isStock'] ? 1000 : 0;
                if (!isset($sku['images'])) $sku['images'] = !empty($sku['gallery']) ? $sku['gallery'] : [$sku['image'] ?? '/logo_svg.svg'];
                $allMap[$id] = $sku;
            }
        }

        $customs = self::getCustomProducts();
        foreach ($customs as $c) {
            $id = $c['id'] ?? '';
            if ($id) {
                if (!isset($c['status'])) $c['status'] = 'published';
                if (!isset($c['regularPrice'])) $c['regularPrice'] = $c['price'] ?? 0;
                if (!isset($c['stockQty'])) $c['stockQty'] = 1000;
                if (!isset($c['images'])) $c['images'] = [$c['image'] ?? '/logo_svg.svg'];
                $allMap[$id] = array_merge($allMap[$id] ?? [], $c);
            }
        }

        $edited = self::getEditedProducts();
        foreach ($edited as $id => $overrides) {
            if (isset($allMap[$id])) {
                $allMap[$id] = array_merge($allMap[$id], $overrides);
            } else {
                $allMap[$id] = $overrides;
            }
        }

        $list = array_values($allMap);

        $list = array_values(array_filter($list, function($p) use ($includeArchived) {
            $st = $p['status'] ?? 'published';
            if ($st === 'deleted') return false;
            if (!$includeArchived) return $st === 'published';
            return true;
        }));

        foreach ($list as &$item) {
            self::assignProductIndustries($item);
        }
        unset($item);

        return $list;
    }

    /**
     * Categorize each SKU into client industry markets:
     * - agro: Agrochemicals & Pesticides
     * - cosmetics: Cosmetics & Personal Care
     * - pharma: Pharmaceuticals & Healthcare
     * - nutraceuticals: Nutraceuticals & Supplements
     * - liquor: Liquor, Spirits & Beverages
     */
    public static function assignProductIndustries(&$sku) {
        $cat = strtolower(trim($sku['categorySlug'] ?? ''));
        $series = strtolower(trim($sku['seriesSlug'] ?? ($sku['serie'] ?? '')));
        $name = strtolower(trim($sku['name'] ?? ''));
        $skuId = strtolower(trim($sku['id'] ?? ''));

        $inds = [];

        // 1. Agrochemicals & Pesticides
        if ($cat === 'agro-bottles' || 
            strpos($series, 'agro') !== false || 
            strpos($series, 'trigger') !== false ||
            strpos($name, 'agro') !== false ||
            strpos($name, 'pesticide') !== false ||
            strpos($name, 'chemical') !== false ||
            strpos($name, 'trigger') !== false) {
            $inds[] = 'agro';
        }

        // 2. Cosmetics & Personal Care
        if ($cat === 'cosmetics-bottles' || 
            $cat === 'pumps' || 
            $cat === 'sprays' ||
            $cat === 'finger-sprayers' ||
            strpos($series, 'cosmetic') !== false ||
            strpos($series, 'lotion') !== false ||
            strpos($series, 'foam') !== false ||
            strpos($series, 'mist') !== false ||
            strpos($series, 'atomizer') !== false ||
            strpos($name, 'cosmetic') !== false ||
            strpos($name, 'perfume') !== false ||
            strpos($name, 'tulip') !== false ||
            strpos($name, 'veola') !== false ||
            strpos($name, 'badami') !== false ||
            strpos($name, 'lotion') !== false ||
            strpos($name, 'spray') !== false ||
            $cat === 'caps') {
            $inds[] = 'cosmetics';
        }

        // 3. Pharmaceuticals & Healthcare
        if ($cat === 'pharma-bottles' || 
            strpos($series, 'pharma') !== false || 
            strpos($series, 'medical') !== false ||
            strpos($series, 'crc') !== false ||
            strpos($name, 'medical') !== false ||
            strpos($name, 'syrup') !== false ||
            strpos($name, 'round pet') !== false ||
            strpos($name, 'dome pet') !== false ||
            strpos($name, 'crc') !== false ||
            strpos($name, 'child') !== false ||
            $cat === 'caps') {
            $inds[] = 'pharma';
        }

        // 4. Nutraceuticals & Supplements
        if ($cat === 'jars' || 
            strpos($series, 'jar') !== false || 
            strpos($series, 'nutra') !== false ||
            strpos($name, 'jar') !== false ||
            strpos($name, 'nutra') !== false ||
            strpos($name, 'capsule') !== false ||
            strpos($name, 'tablet') !== false ||
            strpos($name, 'packer') !== false ||
            strpos($name, 'powder') !== false) {
            $inds[] = 'nutraceuticals';
        }

        // 5. Liquor, Spirits & Beverages
        if ($cat === 'liquor-bottles' || strpos($series, 'liquor') !== false || strpos($name, 'flat') !== false || 
            strpos($name, 'flask') !== false || 
            strpos($series, 'flat') !== false ||
            strpos($name, 'liquor') !== false ||
            strpos($name, 'spirit') !== false ||
            strpos($name, 'beverage') !== false ||
            strpos($name, 'round pet') !== false ||
            ($cat === 'pharma-bottles' && (strpos($name, '200ml') !== false || strpos($name, '100ml') !== false))) {
            $inds[] = 'liquor';
        }

        // Fallback default if unmatched
        if (empty($inds)) {
            $inds[] = 'cosmetics';
        }

        $sku['industries'] = array_values(array_unique($inds));
        $sku['industrySlugs'] = implode(',', $sku['industries']);
        return $sku['industries'];
    }

    public static function getIndustries() {
        return self::$industries;
    }

    public static function getIndustryBySlug($slug) {
        $slug = strtolower(trim($slug));
        return self::$industries[$slug] ?? null;
    }

    public static function getSkusByIndustry($industrySlug) {
        $industrySlug = strtolower(trim($industrySlug));
        $all = self::getSkus();
        $filtered = array_values(array_filter($all, function($sku) use ($industrySlug) {
            return in_array($industrySlug, $sku['industries'] ?? []);
        }));
        usort($filtered, [__CLASS__, 'compareProductSizes']);
        return $filtered;
    }

    public static function getSeriesSlugBySubcategory($subcat) {
        if (empty($subcat)) return 'custom-series';
        foreach (self::$series as $s) {
            if (strcasecmp($s['name'], $subcat) === 0 || strcasecmp($s['slug'], $subcat) === 0) {
                return $s['slug'];
            }
        }
        $subLower = strtolower($subcat);
        if (strpos($subLower, 'agro') !== false) return 'agro-bottles';
        if (strpos($subLower, 'cosmetic') !== false) return 'cosmetic-bottles';
        if (strpos($subLower, 'sharp') !== false) return 'sharp-cylindrical-pet';
        if (strpos($subLower, 'boston') !== false) return 'boston-round-pet';
        if (strpos($subLower, 'jar') !== false) return 'round-jars';
        if (strpos($subLower, 'liquor') !== false || strpos($subLower, 'spirit') !== false) return 'liquor-spirits-pet-bottles';

        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $subcat), '-'));
    }

    public static function getSkusBySeries($seriesSlug) {
        $all = self::getSkus();
        $filtered = array_values(array_filter($all, function($sku) use ($seriesSlug) {
            return ($sku['seriesSlug'] ?? '') === $seriesSlug;
        }));
        usort($filtered, [__CLASS__, 'compareProductSizes']);
        return $filtered;
    }

    public static function extractNumericSize($sku) {
        $capStr = trim($sku['specs']['capacity'] ?? ($sku['capacity'] ?? ($sku['volume'] ?? '')));
        if (!$capStr) {
            if (preg_match('/(\d+(?:\.\d+)?)\s*(ml|cc|gm|g|l)\b/i', ($sku['name'] ?? '') . ' ' . ($sku['id'] ?? ''), $m)) {
                $capStr = $m[0];
            }
        }
        if (preg_match('/(\d+(?:\.\d+)?)\s*(ml|cc|gm|g|l)?/i', $capStr, $matches)) {
            $val = (float)$matches[1];
            $unit = strtolower($matches[2] ?? 'ml');
            if ($unit === 'l') {
                $val *= 1000;
            }
            return $val;
        }
        return 999999;
    }

    public static function compareProductSizes($a, $b) {
        $sizeA = self::extractNumericSize($a);
        $sizeB = self::extractNumericSize($b);
        if ($sizeA === $sizeB) {
            return strcmp($a['name'] ?? '', $b['name'] ?? '');
        }
        return ($sizeA < $sizeB) ? -1 : 1;
    }

    public static function getSkusByCategory($categorySlug) {
        $all = self::getSkus();
        $filtered = array_values(array_filter($all, function($sku) use ($categorySlug) {
            return ($sku['categorySlug'] ?? '') === $categorySlug;
        }));
        usort($filtered, [__CLASS__, 'compareProductSizes']);
        return $filtered;
    }

    public static function getSkuById($id) {
        if (empty($id)) return null;

        $idAliases = [
            'COS-225ML-ESKINOL' => 'COS-225ML-OVAL-SKINCARE',
            'PHA-200ML-ROUND-PET-CHARAK' => 'PHA-200ML-ROUND-PET-CLASSIC',
            'PHA-250ML-BRUTE-PET-SANTIVANI' => 'PHA-250ML-BRUTE-PET-HD',
            'PHA-60ML-ROUND-PET-MACLEODS' => 'PHA-60ML-ROUND-PET-STD',
        ];
        if (isset($idAliases[$id])) {
            $id = $idAliases[$id];
        }

        // 1. Direct MySQL single-row lookup if connected
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("SELECT p.*, GROUP_CONCAT(pi.image_url ORDER BY pi.sort_order ASC SEPARATOR '|||') as image_list 
                                            FROM products p 
                                            LEFT JOIN product_images pi ON p.id = pi.product_id 
                                            WHERE p.id = ? OR p.sku = ? OR p.slug = ?
                                            GROUP BY p.id LIMIT 1");
                    $numId = is_numeric($id) ? (int)$id : 0;
                    $stmt->execute([$numId, (string)$id, (string)$id]);
                    $r = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($r) {
                        $imgArr = !empty($r['image_list']) ? explode('|||', $r['image_list']) : [];
                        $primaryImg = !empty($imgArr[0]) ? $imgArr[0] : '/logo_svg.svg';

                        $itemData = [
                            'db_id' => (int)$r['id'],
                            'id' => $r['sku'],
                            'name' => $r['name'],
                            'slug' => $r['slug'] ?? '',
                            'fullTitle' => $r['name'],
                            'articleNo' => $r['sku'],
                            'websiteNo' => $r['sku'],
                            'categorySlug' => $r['category_slug'],
                            'categoryName' => $r['category_name'],
                            'seriesSlug' => self::getSeriesSlugBySubcategory($r['subcategory']),
                            'serie' => $r['subcategory'],
                            'gender' => $r['gender'] ?? 'Unisex',
                            'regularPrice' => (float)$r['regular_price'],
                            'salePrice' => $r['sale_price'] !== null ? (float)$r['sale_price'] : null,
                            'stockQty' => (int)$r['stock_qty'],
                            'stockStatus' => (int)$r['stock_qty'] <= 0 ? 'out_of_stock' : $r['stock_status'],
                            'status' => $r['status'],
                            'isFeatured' => (bool)$r['is_featured'],
                            'image' => $primaryImg,
                            'images' => !empty($imgArr) ? $imgArr : [$primaryImg],
                            'gallery' => !empty($imgArr) ? $imgArr : [$primaryImg],
                            'shortDescription' => $r['short_desc'] ?? '',
                            'description' => $r['description'] ?? '',
                            'seoTitle' => $r['seo_title'] ?? '',
                            'seoDescription' => $r['seo_description'] ?? '',
                            'specs' => [
                                'capacity' => $r['capacity'],
                                'neck' => $r['neck_finish'],
                                'material' => $r['material'],
                                'weight' => $r['weight'],
                                'moq' => $r['moq']
                            ],
                            'updatedAt' => $r['updated_at'] ?? ''
                        ];
                        self::assignProductIndustries($itemData);
                        return $itemData;
                    }
                }
            } catch (Exception $e) {
                // Fallback to memory search
            }
        }

        // 2. Fallback memory / JSON lookup
        $all = self::getSkus(true);
        foreach ($all as $sku) {
            if (($sku['id'] ?? '') === $id || 
                ($sku['articleNo'] ?? '') === $id || 
                (isset($sku['db_id']) && (string)$sku['db_id'] === (string)$id) ||
                ($sku['slug'] ?? '') === $id) {
                return $sku;
            }
        }
        return null;
    }

    /**
     * Get distinct subcategories / series from DB
     */
    public static function getSubcategoriesByCategory($catSlug = '') {
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    if (!empty($catSlug)) {
                        $stmt = $pdo->prepare("SELECT DISTINCT subcategory FROM products WHERE category_slug = ? AND subcategory IS NOT NULL AND subcategory != '' ORDER BY subcategory ASC");
                        $stmt->execute([$catSlug]);
                    } else {
                        $stmt = $pdo->query("SELECT DISTINCT subcategory FROM products WHERE subcategory IS NOT NULL AND subcategory != '' ORDER BY subcategory ASC");
                    }
                    $subs = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    if (!empty($subs)) {
                        return array_values(array_filter($subs));
                    }
                }
            } catch (Exception $e) {}
        }
        return [];
    }

    /**
     * Intelligent Generic Packaging Search Engine
     * Leverages SearchDictionary taxonomy, singular/plural stemming, category expansion,
     * specification matching (capacity, neck finish, material, shape), and relevance scoring.
     */
    public static function searchProducts($query, $includeArchived = false) {
        $rawQuery = trim($query ?? '');
        $all = self::getSkus($includeArchived);
        if ($rawQuery === '') return $all;

        $q = strtolower($rawQuery);
        $qNormalized = preg_replace('/[\s\-]+/', ' ', $q);

        // 1. Check if query matches generic taxonomy terms
        $taxMatch = SearchDictionary::matchTaxonomy($q);
        $targetCategories = $taxMatch['categorySlugs'] ?? [];
        $materialPattern = $taxMatch['pattern'] ?? null;

        // 2. Generate search stems (singular/plural variations)
        $stems = SearchDictionary::getQueryStems($q);

        $scored = [];

        foreach ($all as $sku) {
            $score = 0;
            $catSlug = strtolower($sku['categorySlug'] ?? '');
            $catName = strtolower($sku['categoryName'] ?? '');
            $name = strtolower($sku['name'] ?? '');
            $fullTitle = strtolower($sku['fullTitle'] ?? '');
            $series = strtolower($sku['series'] ?? ($sku['serie'] ?? ''));
            $article = strtolower($sku['articleNo'] ?? '');
            $id = strtolower($sku['id'] ?? '');
            $desc = strtolower($sku['description'] ?? '');
            $specs = is_array($sku['specs'] ?? null) ? $sku['specs'] : [];
            $mat = strtolower($specs['material'] ?? ($sku['material'] ?? ''));
            $cap = strtolower($specs['capacity'] ?? '');
            $neck = strtolower($specs['neck'] ?? '');
            $shape = strtolower($sku['shape'] ?? ($specs['shape'] ?? ''));

            // A. Generic Taxonomy Category Match (e.g., "cap" matches all products in 'caps')
            if (!empty($targetCategories) && in_array($catSlug, $targetCategories)) {
                $score += 100;
            }

            // B. Material search match (e.g. "hdpe", "pet", "pp")
            if ($materialPattern && preg_match($materialPattern, $mat)) {
                $score += 85;
            }

            // C. Direct SKU ID / Article No match
            if ($id === $q || $article === $q) {
                $score += 150;
            } elseif (strpos($id, $q) !== false || strpos($article, $q) !== false) {
                $score += 90;
            }

            // D. Name, FullTitle, Category and Series matches across all stems
            foreach ($stems as $st) {
                // Word boundary name match
                if (preg_match('/\b' . preg_quote($st, '/') . '\b/i', $name) || preg_match('/\b' . preg_quote($st, '/') . '\b/i', $fullTitle)) {
                    $score += 70;
                } elseif (strpos($name, $st) !== false || strpos($fullTitle, $st) !== false) {
                    $score += 40;
                }

                if (preg_match('/\b' . preg_quote($st, '/') . '\b/i', $series)) {
                    $score += 50;
                } elseif (strpos($series, $st) !== false) {
                    $score += 30;
                }

                if (strpos($catName, $st) !== false || strpos($catSlug, $st) !== false) {
                    $score += 45;
                }
            }

            // E. Specs match (Capacity e.g. 500ml/500 ml, Neck e.g. 24/410, Material e.g. PET, Shape e.g. Round)
            $normCap = str_replace(' ', '', $cap);
            $normQ = str_replace(' ', '', $q);
            if ($normCap !== '' && ($normCap === $normQ || strpos($normCap, $normQ) !== false)) {
                $score += 65;
            }
            if ($neck !== '' && strpos($neck, $q) !== false) {
                $score += 45;
            }
            if ($mat !== '' && strpos($mat, $q) !== false) {
                $score += 35;
            }
            if ($shape !== '' && strpos($shape, $q) !== false) {
                $score += 30;
            }

            // F. Description match (Whole word match only to avoid false substring positives like capacity -> cap)
            // If the search query mapped to a specific category taxonomy (e.g. pumps, caps, agro-bottles),
            // do NOT match descriptions of products in unrelated categories (avoids cosmetics bottles matching 'pump' via compatibility descriptions).
            if ($score === 0 && empty($targetCategories)) {
                foreach ($stems as $st) {
                    if (preg_match('/\b' . preg_quote($st, '/') . '\b/i', $desc)) {
                        $score += 15;
                        break;
                    }
                }
            } elseif ($score > 0 && !empty($targetCategories)) {
                foreach ($stems as $st) {
                    if (preg_match('/\b' . preg_quote($st, '/') . '\b/i', $desc)) {
                        $score += 10;
                        break;
                    }
                }
            }

            if ($score > 0) {
                $scored[] = [
                    'sku' => $sku,
                    'score' => $score
                ];
            }
        }

        // Sort by score descending, then alphabetical by name
        usort($scored, function($a, $b) {
            if ($a['score'] === $b['score']) {
                return strcmp($a['sku']['name'] ?? '', $b['sku']['name'] ?? '');
            }
            return ($a['score'] > $b['score']) ? -1 : 1;
        });

        return array_map(function($item) {
            return $item['sku'];
        }, $scored);
    }

    public static function getPopularSearches() {
        return SearchDictionary::$popularSearches;
    }

    public static function getSearchTaxonomyMatch($query) {
        return SearchDictionary::matchTaxonomy($query);
    }

    public static function getGenericSearchTaxonomy() {
        return SearchDictionary::$taxonomy;
    }

    /**
     * Save/Update ANY product (migrated or custom) persistently in MySQL DB
     */
    public static function saveProduct($data) {
        $originalSku = trim($data['original_sku'] ?? ($data['id'] ?? ''));
        $originalId = !empty($data['db_id']) ? (int)$data['db_id'] : 0;
        $articleNo = trim($data['articleNo'] ?? ($originalSku ?: ('PROD-' . time() . '-' . rand(100, 999))));
        $id = $articleNo;

        // Existing product lookup for fallback data
        $existing = !empty($originalSku) ? self::getSkuById($originalSku) : (!empty($articleNo) ? self::getSkuById($articleNo) : null);

        $name = trim($data['name'] ?? ($existing['name'] ?? 'Product Item'));
        $fullTitle = trim($data['fullTitle'] ?? ($data['name'] ?? ($existing['fullTitle'] ?? $name)));
        $categorySlug = trim($data['categorySlug'] ?? ($existing['categorySlug'] ?? 'pet-bottles'));
        $categoryName = trim($data['categoryName'] ?? ($existing['categoryName'] ?? 'PET bottles'));
        $serie = trim($data['serie'] ?? ($existing['serie'] ?? 'General Series'));
        $seriesSlug = trim($data['seriesSlug'] ?? ($existing['seriesSlug'] ?? self::getSeriesSlugBySubcategory($serie)));
        $gender = trim($data['gender'] ?? ($existing['gender'] ?? 'Unisex'));

        $regularPrice = isset($data['regularPrice']) && $data['regularPrice'] !== '' ? max(0, (float)$data['regularPrice']) : (float)($existing['regularPrice'] ?? 0);
        $salePrice = isset($data['salePrice']) && $data['salePrice'] !== '' ? max(0, (float)$data['salePrice']) : null;
        if ($salePrice !== null && $salePrice > $regularPrice && $regularPrice > 0) {
            $salePrice = $regularPrice;
        }

        $stockQty = isset($data['stockQty']) && $data['stockQty'] !== '' ? max(0, (int)$data['stockQty']) : (int)($existing['stockQty'] ?? 1000);
        $stockStatus = $stockQty <= 0 ? 'out_of_stock' : trim($data['stockStatus'] ?? ($existing['stockStatus'] ?? 'in_stock'));

        $status = trim($data['status'] ?? ($existing['status'] ?? 'published'));
        if (!in_array($status, ['published', 'draft', 'archived'])) {
            $status = 'published';
        }

        $isFeatured = isset($data['isFeatured']) ? (bool)$data['isFeatured'] : (bool)($existing['isFeatured'] ?? false);

        $images = $data['images'] ?? ($existing['images'] ?? [($data['image'] ?? '/logo_svg.svg')]);
        if (!is_array($images)) $images = [$images];
        $images = array_values(array_filter($images));
        if (empty($images)) $images = ['/logo_svg.svg'];
        $primaryImage = $data['image'] ?? ($images[0] ?? '/logo_svg.svg');

        $capacity = trim($data['capacity'] ?? ($existing['specs']['capacity'] ?? ($existing['volume'] ?? ($existing['capacity'] ?? 'N/A'))));
        $neck = trim($data['neck'] ?? ($existing['specs']['neck'] ?? ($existing['neckSize'] ?? ($existing['neck'] ?? 'N/A'))));
        $material = trim($data['material'] ?? ($existing['specs']['material'] ?? ($existing['material'] ?? 'PET')));
        $weight = trim($data['weight'] ?? ($existing['specs']['weight'] ?? ($existing['weight'] ?? 'N/A')));
        $moq = trim($data['moq'] ?? ($existing['specs']['moq'] ?? ($existing['moq'] ?? '5,000 pcs')));

        $shortDescription = trim($data['shortDescription'] ?? ($existing['shortDescription'] ?? ''));
        $description = trim($data['description'] ?? ($existing['description'] ?? ''));
        $seoTitle = trim($data['seoTitle'] ?? ($existing['seoTitle'] ?? ''));
        $seoDescription = trim($data['seoDescription'] ?? ($existing['seoDescription'] ?? ''));
        $video = trim($data['video'] ?? ($existing['video'] ?? ''));
        $sizes = $data['sizes'] ?? ($existing['sizes'] ?? []);

        $updatedRecord = [
            'id' => $id,
            'name' => $name,
            'fullTitle' => $fullTitle,
            'articleNo' => $articleNo,
            'websiteNo' => $articleNo,
            'categorySlug' => $categorySlug,
            'categoryName' => $categoryName,
            'seriesSlug' => $seriesSlug,
            'serie' => $serie,
            'gender' => $gender,
            'regularPrice' => $regularPrice,
            'salePrice' => $salePrice,
            'stockQty' => $stockQty,
            'stockStatus' => $stockStatus,
            'status' => $status,
            'isFeatured' => $isFeatured,
            'image' => $primaryImage,
            'images' => $images,
            'gallery' => $images,
            'video' => $video,
            'sizes' => $sizes,
            'shortDescription' => $shortDescription,
            'description' => $description,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'specs' => [
                'capacity' => $capacity,
                'neck' => $neck,
                'material' => $material,
                'weight' => $weight,
                'moq' => $moq
            ],
            'updatedAt' => date('Y-m-d H:i:s')
        ];

        // 1. MySQL DB Transaction
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $pdo->beginTransaction();

                    // Check if existing record exists in MySQL
                    $existingRow = null;
                    if ($originalId > 0) {
                        $fStmt = $pdo->prepare("SELECT id, sku, slug, name FROM products WHERE id = ? LIMIT 1");
                        $fStmt->execute([$originalId]);
                        $existingRow = $fStmt->fetch(PDO::FETCH_ASSOC);
                    }
                    if (!$existingRow && !empty($originalSku)) {
                        $fStmt = $pdo->prepare("SELECT id, sku, slug, name FROM products WHERE sku = ? LIMIT 1");
                        $fStmt->execute([$originalSku]);
                        $existingRow = $fStmt->fetch(PDO::FETCH_ASSOC);
                    }
                    if (!$existingRow && !empty($articleNo)) {
                        $fStmt = $pdo->prepare("SELECT id, sku, slug, name FROM products WHERE sku = ? LIMIT 1");
                        $fStmt->execute([$articleNo]);
                        $existingRow = $fStmt->fetch(PDO::FETCH_ASSOC);
                    }

                    if ($existingRow) {
                        // UPDATE EXISTING ROW
                        $targetDbId = (int)$existingRow['id'];
                        $slug = $existingRow['slug'];

                        // If name changed, compute a unique slug
                        if (strcasecmp($existingRow['name'], $name) !== 0 || empty($slug)) {
                            $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
                            if (empty($baseSlug)) $baseSlug = 'product-' . $targetDbId;

                            // Check collision
                            $chk = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ? LIMIT 1");
                            $chk->execute([$baseSlug, $targetDbId]);
                            if ($chk->fetch()) {
                                $slug = $baseSlug . '-' . strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $articleNo), '-'));
                                $chk2 = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ? LIMIT 1");
                                $chk2->execute([$slug, $targetDbId]);
                                if ($chk2->fetch()) {
                                    $slug .= '-' . $targetDbId;
                                }
                            } else {
                                $slug = $baseSlug;
                            }
                        }

                        $updSql = "UPDATE products SET 
                                    sku = ?, 
                                    name = ?, 
                                    slug = ?, 
                                    category_slug = ?, 
                                    category_name = ?, 
                                    subcategory = ?, 
                                    gender = ?, 
                                    short_desc = ?, 
                                    description = ?, 
                                    regular_price = ?, 
                                    sale_price = ?, 
                                    stock_qty = ?, 
                                    stock_status = ?, 
                                    status = ?, 
                                    is_featured = ?, 
                                    capacity = ?, 
                                    neck_finish = ?, 
                                    material = ?, 
                                    weight = ?, 
                                    moq = ?, 
                                    seo_title = ?, 
                                    seo_description = ?, 
                                    deleted_at = CASE WHEN ? = 'archived' THEN NOW() ELSE NULL END, 
                                    updated_at = NOW() 
                                   WHERE id = ?";

                        $updStmt = $pdo->prepare($updSql);
                        $updStmt->execute([
                            $articleNo,
                            $name,
                            $slug,
                            $categorySlug,
                            $categoryName,
                            $serie,
                            $gender,
                            $shortDescription,
                            $description,
                            $regularPrice,
                            $salePrice,
                            $stockQty,
                            $stockStatus,
                            $status,
                            $isFeatured ? 1 : 0,
                            $capacity,
                            $neck,
                            $material,
                            $weight,
                            $moq,
                            $seoTitle,
                            $seoDescription,
                            $status,
                            $targetDbId
                        ]);

                        // Update images
                        $delStmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
                        $delStmt->execute([$targetDbId]);

                        $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                        foreach ($images as $iIdx => $imgUrl) {
                            $isPrim = ($iIdx === 0) ? 1 : 0;
                            $imgStmt->execute([$targetDbId, $imgUrl, $isPrim, $iIdx + 1]);
                        }

                        $updatedRecord['db_id'] = $targetDbId;
                        $updatedRecord['slug'] = $slug;

                    } else {
                        // INSERT NEW PRODUCT
                        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
                        if (empty($baseSlug)) $baseSlug = 'product-' . time();

                        $chk = $pdo->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
                        $chk->execute([$baseSlug]);
                        if ($chk->fetch()) {
                            $slug = $baseSlug . '-' . strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $articleNo), '-'));
                            $chk2 = $pdo->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
                            $chk2->execute([$slug]);
                            if ($chk2->fetch()) {
                                $slug .= '-' . rand(100, 999);
                            }
                        } else {
                            $slug = $baseSlug;
                        }

                        $insSql = "INSERT INTO products (sku, name, slug, category_slug, category_name, subcategory, gender, short_desc, description, regular_price, sale_price, stock_qty, stock_status, status, is_featured, capacity, neck_finish, material, weight, moq, seo_title, seo_description, created_at, updated_at, deleted_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), CASE WHEN ? = 'archived' THEN NOW() ELSE NULL END)";

                        $insStmt = $pdo->prepare($insSql);
                        $insStmt->execute([
                            $articleNo,
                            $name,
                            $slug,
                            $categorySlug,
                            $categoryName,
                            $serie,
                            $gender,
                            $shortDescription,
                            $description,
                            $regularPrice,
                            $salePrice,
                            $stockQty,
                            $stockStatus,
                            $status,
                            $isFeatured ? 1 : 0,
                            $capacity,
                            $neck,
                            $material,
                            $weight,
                            $moq,
                            $seoTitle,
                            $seoDescription,
                            $status
                        ]);

                        $newDbId = (int)$pdo->lastInsertId();

                        $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                        foreach ($images as $iIdx => $imgUrl) {
                            $isPrim = ($iIdx === 0) ? 1 : 0;
                            $imgStmt->execute([$newDbId, $imgUrl, $isPrim, $iIdx + 1]);
                        }

                        $updatedRecord['db_id'] = $newDbId;
                        $updatedRecord['slug'] = $slug;
                    }

                    $pdo->commit();
                }
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("DB Product Save Transaction Error: " . $e->getMessage());
                return false;
            }
        }

        // 2. Dual JSON Persistence
        $editedPath = defined('EDITED_PRODUCTS_FILE') ? EDITED_PRODUCTS_FILE : __DIR__ . '/../../config/edited_products.json';
        $edited = self::getEditedProducts();
        $edited[$articleNo] = $updatedRecord;
        if (!empty($originalSku) && $originalSku !== $articleNo) {
            unset($edited[$originalSku]);
        }
        file_put_contents($editedPath, json_encode($edited, JSON_PRETTY_PRINT));

        return $updatedRecord;
    }

    public static function saveCustomProduct($data) {
        return self::saveProduct($data);
    }

    /**
     * Safely archive a product in MySQL DB & JSON store
     */
    public static function deleteProduct($id) {
        if (empty($id)) return false;

        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("UPDATE products SET status = 'archived', deleted_at = NOW() WHERE sku = ? OR id = ?");
                    $numId = is_numeric($id) ? (int)$id : 0;
                    $stmt->execute([(string)$id, $numId]);
                }
            } catch (Exception $e) {
                error_log("DB Product Delete Error: " . $e->getMessage());
            }
        }

        $editedPath = defined('EDITED_PRODUCTS_FILE') ? EDITED_PRODUCTS_FILE : __DIR__ . '/../../config/edited_products.json';
        $edited = self::getEditedProducts();
        if (isset($edited[$id])) {
            $edited[$id]['status'] = 'archived';
        } else {
            $product = self::getSkuById($id);
            if ($product) {
                $product['status'] = 'archived';
                $edited[$product['articleNo'] ?? $id] = $product;
            }
        }
        file_put_contents($editedPath, json_encode($edited, JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * Restore an archived product to published
     */
    public static function restoreProduct($id) {
        if (empty($id)) return false;

        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("UPDATE products SET status = 'published', deleted_at = NULL WHERE sku = ? OR id = ?");
                    $numId = is_numeric($id) ? (int)$id : 0;
                    $stmt->execute([(string)$id, $numId]);
                }
            } catch (Exception $e) {
                error_log("DB Product Restore Error: " . $e->getMessage());
            }
        }

        $editedPath = defined('EDITED_PRODUCTS_FILE') ? EDITED_PRODUCTS_FILE : __DIR__ . '/../../config/edited_products.json';
        $edited = self::getEditedProducts();
        if (isset($edited[$id])) {
            $edited[$id]['status'] = 'published';
        } else {
            $product = self::getSkuById($id);
            if ($product) {
                $product['status'] = 'published';
                $edited[$product['articleNo'] ?? $id] = $product;
            }
        }
        file_put_contents($editedPath, json_encode($edited, JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * Permanently delete a product from MySQL DB & JSON store
     */
    public static function permanentDeleteProduct($id) {
        if (empty($id)) return false;

        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $numId = is_numeric($id) ? (int)$id : 0;

                    // Delete product images first (if cascade is not enabled)
                    $delImgs = $pdo->prepare("DELETE FROM product_images WHERE product_id IN (SELECT id FROM products WHERE sku = ? OR id = ?)");
                    @$delImgs->execute([(string)$id, $numId]);

                    $stmt = $pdo->prepare("DELETE FROM products WHERE sku = ? OR id = ?");
                    $stmt->execute([(string)$id, $numId]);
                }
            } catch (Exception $e) {
                error_log("DB Product Permanent Delete Error: " . $e->getMessage());
            }
        }

        $editedPath = defined('EDITED_PRODUCTS_FILE') ? EDITED_PRODUCTS_FILE : __DIR__ . '/../../config/edited_products.json';
        $edited = self::getEditedProducts();
        unset($edited[$id]);
        foreach ($edited as $k => $v) {
            if (($v['id'] ?? '') === $id || ($v['articleNo'] ?? '') === $id || (isset($v['db_id']) && (string)$v['db_id'] === (string)$id)) {
                unset($edited[$k]);
            }
        }
        file_put_contents($editedPath, json_encode($edited, JSON_PRETTY_PRINT));

        // Also clean up custom products if applicable
        $customPath = defined('CUSTOM_PRODUCTS_FILE') ? CUSTOM_PRODUCTS_FILE : __DIR__ . '/../../config/custom_products.json';
        if (file_exists($customPath)) {
            $customs = json_decode(file_get_contents($customPath), true) ?: [];
            $filteredCustoms = array_values(array_filter($customs, function($c) use ($id) {
                return ($c['id'] ?? '') !== $id && ($c['articleNo'] ?? '') !== $id;
            }));
            file_put_contents($customPath, json_encode($filteredCustoms, JSON_PRETTY_PRINT));
        }

        return true;
    }

    public static function deleteCustomProduct($id) {
        return self::permanentDeleteProduct($id);
    }

    public static function getCompanyInfo() {
        $info = self::$companyInfo;
        $settingsFile = defined('ROOT_PATH') ? ROOT_PATH . '/config/site_settings.json' : dirname(__DIR__, 2) . '/config/site_settings.json';
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
            if (!empty($settings['site_name'])) $info['name'] = $settings['site_name'];
            if (!empty($settings['contact_email'])) $info['email'] = $settings['contact_email'];
            if (!empty($settings['contact_phone'])) $info['phone'] = $settings['contact_phone'];
            if (!empty($settings['currency'])) $info['currency'] = $settings['currency'];
            if (!empty($settings['tagline'])) $info['tagline'] = $settings['tagline'];
        }
        return $info;
    }

    public static function getCountries() {
        return self::$countries;
    }

    // =========================================================================
    // CATEGORY & PRODUCT OPTIONS MANAGEMENT SYSTEM
    // Single Source of Truth: MySQL Database + JSON Fallback
    // =========================================================================

    public static function getOptionsFilePath() {
        return defined('ROOT_PATH') ? ROOT_PATH . '/config/product_options.json' : dirname(__DIR__, 2) . '/config/product_options.json';
    }

    public static function getAuditLogFilePath() {
        return defined('ROOT_PATH') ? ROOT_PATH . '/config/option_audit_logs.json' : dirname(__DIR__, 2) . '/config/option_audit_logs.json';
    }

    public static function getAllOptions($onlyActive = false) {
        $file = self::getOptionsFilePath();
        $options = [];
        if (file_exists($file)) {
            $options = json_decode(file_get_contents($file), true) ?: [];
        }

        // Add Categories and Series to Options map
        $options['categories'] = self::getCategories();
        $options['subcategories'] = self::getSeries();

        if ($onlyActive) {
            foreach ($options as $group => $items) {
                if (is_array($items)) {
                    $options[$group] = array_values(array_filter($items, function($item) {
                        return !isset($item['status']) || $item['status'] === 'active' || $item['status'] === 'published';
                    }));
                }
            }
        }

        return $options;
    }

    public static function getOptionsGroup($groupKey, $onlyActive = false) {
        $all = self::getAllOptions($onlyActive);
        return $all[$groupKey] ?? [];
    }

    public static function saveOption($groupKey, $data) {
        $file = self::getOptionsFilePath();
        $all = json_decode(file_get_contents($file), true) ?: [];
        if (!isset($all[$groupKey])) {
            $all[$groupKey] = [];
        }

        $id = !empty($data['id']) ? (int)$data['id'] : 0;
        $isEdit = false;
        $savedItem = null;

        if ($id > 0) {
            foreach ($all[$groupKey] as $idx => $item) {
                if (($item['id'] ?? 0) === $id) {
                    $all[$groupKey][$idx] = array_merge($item, $data, ['id' => $id]);
                    $savedItem = $all[$groupKey][$idx];
                    $isEdit = true;
                    break;
                }
            }
        }

        if (!$isEdit) {
            $maxId = 0;
            foreach ($all[$groupKey] as $item) {
                if (($item['id'] ?? 0) > $maxId) $maxId = $item['id'];
            }
            $newId = $maxId + 1;
            $data['id'] = $newId;
            $data['status'] = $data['status'] ?? 'active';
            $data['display_order'] = $data['display_order'] ?? (count($all[$groupKey]) + 1);
            $all[$groupKey][] = $data;
            $savedItem = $data;
        }

        file_put_contents($file, json_encode($all, JSON_PRETTY_PRINT));
        
        $action = $isEdit ? 'Updated' : 'Added';
        $name = $savedItem['name'] ?? ($savedItem['display_label'] ?? "ID {$savedItem['id']}");
        self::logOptionAudit("{$action} Option", $groupKey, $name, "Saved option attributes");

        return $savedItem;
    }

    public static function toggleOptionStatus($groupKey, $id, $status) {
        $file = self::getOptionsFilePath();
        $all = json_decode(file_get_contents($file), true) ?: [];
        if (!isset($all[$groupKey])) return false;

        $targetName = '';
        foreach ($all[$groupKey] as $idx => $item) {
            if (($item['id'] ?? 0) == $id) {
                $all[$groupKey][$idx]['status'] = $status;
                $targetName = $item['name'] ?? ($item['display_label'] ?? "ID {$id}");
                break;
            }
        }

        file_put_contents($file, json_encode($all, JSON_PRETTY_PRINT));
        self::logOptionAudit("Toggled Status ({$status})", $groupKey, $targetName, "Status changed to {$status}");
        return true;
    }

    public static function checkOptionInUse($groupKey, $optionValue) {
        $skus = self::getSkus(true);
        $count = 0;
        $valLower = strtolower(trim($optionValue));

        foreach ($skus as $sku) {
            if ($groupKey === 'materials') {
                $m = strtolower($sku['specs']['material'] ?? ($sku['material'] ?? ''));
                if (strpos($m, $valLower) !== false) $count++;
            } elseif ($groupKey === 'volumes') {
                $v = strtolower($sku['specs']['capacity'] ?? ($sku['capacity'] ?? ($sku['volume'] ?? '')));
                if (strpos($v, $valLower) !== false) $count++;
            } elseif ($groupKey === 'necks') {
                $n = strtolower($sku['specs']['neck'] ?? ($sku['neck'] ?? ''));
                if (strpos($n, $valLower) !== false) $count++;
            }
        }
        return $count;
    }

    public static function deleteOption($groupKey, $id) {
        $file = self::getOptionsFilePath();
        $all = json_decode(file_get_contents($file), true) ?: [];
        if (!isset($all[$groupKey])) return ['success' => false, 'message' => 'Group not found'];

        $targetItem = null;
        $targetIdx = -1;
        foreach ($all[$groupKey] as $idx => $item) {
            if (($item['id'] ?? 0) == $id) {
                $targetItem = $item;
                $targetIdx = $idx;
                break;
            }
        }

        if (!$targetItem) return ['success' => false, 'message' => 'Option not found'];

        $name = $targetItem['name'] ?? ($targetItem['display_label'] ?? '');
        $inUseCount = self::checkOptionInUse($groupKey, $name);

        if ($inUseCount > 0) {
            return [
                'success' => false,
                'in_use' => true,
                'count' => $inUseCount,
                'message' => "This option '{$name}' is currently used by {$inUseCount} product(s). You cannot permanently delete it. Would you like to deactivate it instead?"
            ];
        }

        array_splice($all[$groupKey], $targetIdx, 1);
        file_put_contents($file, json_encode($all, JSON_PRETTY_PRINT));
        self::logOptionAudit("Deleted Option", $groupKey, $name, "Permanently deleted option");

        return ['success' => true, 'message' => "Option '{$name}' deleted successfully."];
    }

    public static function logOptionAudit($action, $groupKey, $itemName, $details) {
        $file = self::getAuditLogFilePath();
        $logs = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];

        $user = $_SESSION['admin_user'] ?? 'Admin';
        $newLog = [
            'id' => time() . rand(100, 999),
            'user' => $user,
            'action' => $action,
            'group' => $groupKey,
            'item' => $itemName,
            'details' => $details,
            'created_at' => date('Y-m-d H:i:s')
        ];

        array_unshift($logs, $newLog);
        $logs = array_slice($logs, 0, 100);
        file_put_contents($file, json_encode($logs, JSON_PRETTY_PRINT));
    }

    public static function getOptionAuditLogs($limit = 50) {
        $file = self::getAuditLogFilePath();
        if (!file_exists($file)) return [];
        $logs = json_decode(file_get_contents($file), true) ?: [];
        return array_slice($logs, 0, $limit);
    }
}
