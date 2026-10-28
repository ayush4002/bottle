<?php
/**
 * Generic Packaging Searches Taxonomy & Synonym Dictionary
 * Maps generic search keywords to product categories, series, materials, and packaging specifications.
 */
class SearchDictionary {

    /**
     * Curated Popular Generic Searches for instant search suggestions
     */
    public static $popularSearches = [
        ['label' => 'Caps & Closures', 'query' => 'caps'],
        ['label' => 'Bottles (All)', 'query' => 'bottles'],
        ['label' => 'Jars', 'query' => 'jars'],
        ['label' => 'Lotion Pumps', 'query' => 'lotion pump'],
        ['label' => 'Foam Pumps', 'query' => 'foam pump'],
        ['label' => 'Trigger Sprayers', 'query' => 'trigger sprayer'],
        ['label' => 'Mist Sprayers', 'query' => 'mist sprayer'],
        ['label' => 'Agro Bottles', 'query' => 'agro bottles'],
        ['label' => 'Cosmetics Bottles', 'query' => 'cosmetics bottles'],
        ['label' => 'Pharma Bottles', 'query' => 'pharma bottles'],
        ['label' => 'HDPE Packaging', 'query' => 'hdpe'],
        ['label' => 'PET Packaging', 'query' => 'pet'],
        ['label' => '500ml Containers', 'query' => '500ml'],
        ['label' => 'Liquor & Spirits', 'query' => 'liquor'],
        ['label' => 'Nutraceutical Jars', 'query' => 'nutra'],
        ['label' => '24/410 Neck', 'query' => '24/410'],
        ['label' => '28/410 Neck', 'query' => '28/410']
    ];

    /**
     * Master Generic Packaging Taxonomy
     */
    public static $taxonomy = [
        'caps' => [
            'name' => 'Caps & Closures',
            'type' => 'category',
            'categorySlugs' => ['caps'],
            'terms' => [
                'cap', 'caps', 'closure', 'closures', 'lid', 'lids', 'cover', 'covers',
                'top', 'tops', 'screw cap', 'screw caps', 'screw top', 'screw tops',
                'flip top', 'fliptop', 'flip-top', 'disc top', 'disctop', 'disc-top',
                'crc', 'child resistant', 'tamper evident', 'push pull', 'twist top',
                'spout cap', 'plug', 'plugs', 'stopper', 'stoppers', 'seals', 'seal cap',
                'dropper cap', 'applicator cap', 'bottle cap', 'jar lid', 'container lid'
            ]
        ],
        'bottles' => [
            'name' => 'Bottles & Containers',
            'type' => 'category_group',
            'categorySlugs' => ['agro-bottles', 'cosmetics-bottles', 'pharma-bottles'],
            'terms' => [
                'bottle', 'bottles', 'container', 'containers', 'vial', 'vials',
                'flask', 'flasks', 'canister', 'canisters', 'plastic bottle', 'pet bottle',
                'hdpe bottle', 'liquid container', 'packaging bottle'
            ]
        ],
        'agro-bottles' => [
            'name' => 'Agro & Industrial Bottles',
            'type' => 'category',
            'categorySlugs' => ['agro-bottles'],
            'terms' => [
                'agro', 'agro bottle', 'agro bottles', 'agriculture', 'agricultural',
                'pesticide', 'pesticides', 'fertilizer', 'fertilizers', 'chemical',
                'chemicals', 'chemical bottle', 'chemical bottles', 'agro chemical',
                'crop protection', 'bio fit', 'weedicide', 'fungicide'
            ]
        ],
        'cosmetics-bottles' => [
            'name' => 'Cosmetic & Personal Care Bottles',
            'type' => 'category',
            'categorySlugs' => ['cosmetics-bottles'],
            'terms' => [
                'cosmetic', 'cosmetics', 'cosmetic bottle', 'cosmetic bottles', 'lotion bottle',
                'lotion bottles', 'shampoo', 'shampoo bottle', 'conditioner', 'conditioner bottle',
                'serum', 'serum bottle', 'skincare', 'skin care', 'hair oil', 'body wash',
                'cleanser', 'toner', 'beauty packaging', 'vasa'
            ]
        ],
        'pharma-bottles' => [
            'name' => 'Pharmaceutical & Medical Bottles',
            'type' => 'category',
            'categorySlugs' => ['pharma-bottles'],
            'terms' => [
                'pharma', 'pharmaceutical', 'pharmaceutical bottle', 'pharmaceutical bottles',
                'medicine', 'medicines', 'medical', 'medical bottle', 'medical bottles',
                'syrup', 'syrups', 'cough syrup', 'tablet', 'tablets', 'tablet bottle',
                'pill', 'pills', 'pill bottle', 'capsule bottle', 'oral liquid',
                'amber bottle', 'amber bottles', 'dropper bottle', 'liquid medicine',
                'usp class vi'
            ]
        ],
        'jars' => [
            'name' => 'Jars & Wide-Mouth Tubs',
            'type' => 'category',
            'categorySlugs' => ['jars'],
            'terms' => [
                'jar', 'jars', 'pot', 'pots', 'tub', 'tubs', 'cream jar', 'cream jars',
                'cosmetic jar', 'cosmetic jars', 'pet jar', 'pet jars', 'ointment',
                'balm', 'body butter', 'pomade', 'wide mouth', 'wide mouth jar',
                'nutraceutical jar', 'powder jar'
            ]
        ],
        'pumps' => [
            'name' => 'Dispensing Pumps',
            'type' => 'category',
            'categorySlugs' => ['pumps'],
            'terms' => [
                'pump', 'pumps', 'dispenser', 'dispensers', 'lotion pump', 'lotion pumps',
                'foam pump', 'foam pumps', 'foamer', 'foamers', 'treatment pump', 'treatment pumps',
                'soap pump', 'soap dispenser', 'cream pump', 'hand wash pump', 'clip pump',
                'switch pump', 'big output pump', 'liquid dispenser', 'gel pump',
                'sanitizer pump', 'essential oil pump'
            ]
        ],
        'sprays' => [
            'name' => 'Sprayers & Atomizers',
            'type' => 'category',
            'categorySlugs' => ['sprays'],
            'terms' => [
                'spray', 'sprays', 'sprayer', 'sprayers', 'trigger', 'trigger spray',
                'trigger sprays', 'trigger sprayer', 'trigger sprayers', 'mist', 'fine mist',
                'fine-mist', 'mist sprayer', 'mist sprayers', 'continuous mist',
                'continuous spray', 'atomizer', 'atomizers', 'perfume spray', 'perfume sprayer',
                'powder spray', 'powder sprayer', 'crimp spray', 'nasal spray', 'spray bottle',
                'spray bottles', 'mini sprayer', 'card sprayer', 'pocket sprayer'
            ]
        ],
        'pet' => [
            'name' => 'PET Plastic Products',
            'type' => 'material',
            'pattern' => '/\b(pet|rpet|polyethylene terephthalate)\b/i',
            'terms' => ['pet', 'rpet', 'recycled pet', 'polyethylene terephthalate']
        ],
        'hdpe' => [
            'name' => 'HDPE Packaging',
            'type' => 'material',
            'pattern' => '/\b(hdpe|high density polyethylene)\b/i',
            'terms' => ['hdpe', 'high density polyethylene', 'high-density']
        ],
        'pp' => [
            'name' => 'Polypropylene (PP)',
            'type' => 'material',
            'pattern' => '/\b(pp|polypropylene)\b/i',
            'terms' => ['pp', 'polypropylene']
        ],
        'liquor' => [
            'name' => 'Liquor & Spirits Containers',
            'type' => 'industry',
            'categorySlugs' => ['pharma-bottles', 'cosmetics-bottles'],
            'terms' => [
                'liquor', 'spirit', 'spirits', 'alcohol', 'beverage', 'beverages',
                'flask', 'flasks', 'pocket flask', 'whisky', 'rum', 'vodka', 'gin',
                'miniature', 'flat bottle', 'flat bottles'
            ]
        ],
        'nutraceuticals' => [
            'name' => 'Nutraceutical & Supplement Packaging',
            'type' => 'industry',
            'categorySlugs' => ['jars'],
            'terms' => [
                'nutra', 'nutraceutical', 'nutraceuticals', 'supplement', 'supplements',
                'vitamin', 'vitamins', 'capsule', 'capsules', 'tablet', 'tablets',
                'powder', 'powders', 'packer', 'packers', 'protein jar', 'nutra jar'
            ]
        ]
    ];

    /**
     * Find matching taxonomy definition for a query or stem
     */
    public static function matchTaxonomy($query) {
        $q = strtolower(trim($query));
        if ($q === '') return null;

        $qNorm = preg_replace('/[\s\-]+/', ' ', $q);

        // 1. Direct match on exact term
        foreach (self::$taxonomy as $key => $data) {
            foreach ($data['terms'] as $term) {
                if ($q === $term || $qNorm === $term) {
                    return array_merge(['key' => $key], $data);
                }
            }
        }

        // 2. Inflection / Stemming match
        $stems = [$q, $qNorm];
        if (substr($q, -1) === 's') {
            $stems[] = substr($q, 0, -1);
        }
        if (substr($q, -2) === 'es') {
            $stems[] = substr($q, 0, -2);
        }
        if (substr($q, -1) !== 's') {
            $stems[] = $q . 's';
        }
        $stems = array_unique(array_filter($stems));

        foreach (self::$taxonomy as $key => $data) {
            foreach ($data['terms'] as $term) {
                if (in_array($term, $stems)) {
                    return array_merge(['key' => $key], $data);
                }
            }
        }

        return null;
    }

    /**
     * Generate list of search tokens and stems
     */
    public static function getQueryStems($query) {
        $q = strtolower(trim($query));
        if ($q === '') return [];

        $qNorm = preg_replace('/[\s\-]+/', ' ', $q);
        $stems = [$q, $qNorm];

        if (substr($q, -1) === 's') {
            $stems[] = substr($q, 0, -1);
        }
        if (substr($q, -2) === 'es') {
            $stems[] = substr($q, 0, -2);
        }
        if (substr($q, -1) !== 's') {
            $stems[] = $q . 's';
        }

        return array_values(array_unique(array_filter($stems)));
    }
}
