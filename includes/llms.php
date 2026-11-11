<?php
// ==============================================================================
// TRUENORTH GROUP — LLMs.TXT GENERATOR (AI-Search Readiness, checklist §11)
// A plain-markdown, LLM-friendly index of the site: entity info, key pages,
// category map, and the technical-guides corpus with their lead paragraphs.
// Served at /llms.txt (noindex, lightweight, dynamic).
// ==============================================================================

function tn_llms_txt() {
    $baseUrl = 'https://wetruenorthgroup.com';

    $lines = [];
    $lines[] = "# TrueNorth Group";
    $lines[] = "";
    $lines[] = "> Primary packaging manufacturer & partner: precision-injected PET bottles, HDPE containers, cosmetic jars, caps, lotion pumps and fine-mist sprays. ISO 9001 (TUV NORD), Class 10,000 cleanroom, 18/410-28/410 neck finishes, virgin & 100% post-consumer rPET.";
    $lines[] = "";
    $lines[] = "## Key Pages";
    $lines[] = "- [Home]($baseUrl/): Company overview, product categories and inquiry intake.";
    $lines[] = "- [Products]($baseUrl/products): Full catalogue of bottles, jars, caps, pumps and sprayers, filterable by category and industry.";
    $lines[] = "- [Custom Solutions]($baseUrl/custom): Bespoke moulding and dispensing-system engineering services.";
    $lines[] = "- [Sustainability]($baseUrl/sustainability): rPET and post-consumer recycled packaging programme.";
    $lines[] = "- [FAQ]($baseUrl/faq): Buying guidance - MOQs, sampling, moulding lead times, compliance, packing and payment terms.";
    $lines[] = "- [Contact]($baseUrl/contact): Head-office NAP, phone, WhatsApp, email and response SLAs.";
    $lines[] = "- [Blog]($baseUrl/blog): Technical packaging guides.";
    $lines[] = "";
    $lines[] = "## Contact & NAP";
    $lines[] = "- Name: TrueNorth Group (legal: TrueNorth Group Primary Packaging Limited)";
    $lines[] = "- Address: #201, Vardhaman Trade Towers, Rohit Kunj, LSC Pitampura, Delhi 110034, India";
    $lines[] = "- Phone: +91 98102 20761";
    $lines[] = "- Email: info@wetruenorthgroup.com";
    $lines[] = "- Hours: Monday-Friday 09:00-18:30 (IST)";
    $lines[] = "";
    $lines[] = "## Categories";

    // Product categories (DB/JSON)
    $cats = [];
    if (class_exists('ProductModel')) {
        $cats = ProductModel::getCategories() ?: [];
    }
    foreach ($cats as $c) {
        $slug = $c['slug'] ?? '';
        if (!$slug) continue;
        $lines[] = "- [{$c['name']}]" . "($baseUrl/products?category=" . urlencode($slug) . "): " . (mb_strimwidth($c['desc'] ?? '', 0, 140, '...'));
    }

    // Technical guides
    $posts = [];
    if (class_exists('BlogModel')) {
        $posts = BlogModel::getAllPosts(true) ?: [];
    }
    if ($posts) {
        $lines[] = "";
        $lines[] = "## Technical Guides (Blog)";
        foreach (array_slice($posts, 0, 25) as $p) {
            $lead = mb_strimwidth(strip_tags($p['excerpt'] ?? ($p['content'] ?? '')), 0, 220, '...');
            $lines[] = "- [" . $p['title'] . "]($baseUrl/blog/" . rawurlencode($p['slug']) . "): $lead";
        }
    }

    $lines[] = "";
    $lines[] = "Sources: " . $baseUrl . "/sitemap.xml (full URL index), robots.txt (crawl policy).";
    return implode("\n", $lines) . "\n";
}
