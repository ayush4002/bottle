# TrueNorth Group — SEO Checklist Delivery Report
**Date:** 2026-11-10 · **Source repo:** `D:\wepitch\bottle (2)\bottle\bottle` · **Package:** `deploy_wetruenorthgroup.zip`

## Done in code (implemented + locally tested)

### Technical SEO
- **Canonical & clean URLs** — single-host canonical already in place (repo `.htaccess` redirects `www`→apex; **redeploy required** since the live server still serves `www` with 200). Trailing-slash / `.php` stripping, param-stripping canonicals: PASS.
- **404** — real 404 status, own title + `noindex` (verified live).
- **robots.txt** — now blocks `/admin/`, `/config/`, `/api/`, `*.json`, `*.sql`; `Allow: /faq` and `Allow: /llms.txt` added.
- **Sitemap** — `/faq` added to the dynamic sitemap (`SeoModel::generateSitemapXml`); 309 URLs, no 404s inside.
- **noindex rules** — search/faceted URLs auto-noindex (`?q`, `?filter`, `?sort`), admin noindex, 404 noindex (all in `includes/seo.php` — verified live).
- **Orphan links** — FAQ is 1-click from desktop nav, mobile drawer, homepage FAQ section, footer company column, and product/contact pages.

### Structured Data (JSON-LD) — all validated parse-clean in rendered HTML
| Page | Added/verified types |
|---|---|
| Home | Organization, LocalBusiness (geo), WebSite+SearchAction |
| About / Sustainability | **AboutPage (new)** |
| Contact | **ContactPage (new)** |
| Custom | **Service (new)** |
| Products / Category | BreadcrumbList, Product (quote-based Offer w/ dynamic `priceValidUntil`), **CollectionPage+ItemList (new)** |
| Blog | BlogPosting + BreadcrumbList |
| **/faq (new page)** | **FAQPage** (8 seeded Q&As in `config/seo_faqs.json` — editable in admin FAQ repeater) |
| Product detail | Product w/ AggregateOffer (no more `price: "0.00"`) |

### Speed / CWV
- Full-page cache (TTFB ~1.0s → ~50–100 ms warm) + LiteSpeed AJCC block in `.htaccess` (admin kept private).
- Images: hero preloaded; **all `<img>` now carry `width`/`height`** via `includes/dims.php` + `config/image_dims.json` (648 mapped); heavy WebPs re-compressed 52–80%.
- CSS minified; cache-buster fixed (`filemtime` instead of `time()`).
- Blog TOC + related-products blocks reduce bounce and add internal links.

### New pages & conversion features
- **`/faq` page** — buyer FAQs, FAQPage schema, CTA to inquiry.
- **Homepage**: "Latest From The Packaging Blog" (3 posts) + "Frequently Asked Questions" (4 Q&As) sections.
- **Product pages**: "You May Also Explore" related-products block (contextual internal links).
- **Contact page**: embedded Google Map (NAP + hours visible to Google).
- **Sticky mobile Call/WhatsApp bar** (mobile < 768 px).
- **GA4 `generate_lead` event** on every successful inquiry (drawer + contact form) + `fbq('Lead')` if a pixel is configured.
- **`/llms.txt`** — LLM-friendly entity/page/guide index (noindex, crawlable).
- **Site JS deferred + cached** — the ~27 KB render-blocking inline `<script>` in the footer (mobile drawer, inquiry forms, live search, footer accordions) extracted to `/js/site-main.js?v=<mtime>` and loaded with `defer`. Parsed once, cached for a year, no longer blocks initial render. Verified: `node --check` clean, all 28 `window.*` handlers still resolve, `/js/site-main.js` serves 200 through the router with the right MIME type.
- **Policy pages: "Last Updated" date** (checklist §14) — dynamic `filemtime` on `/terms` and `/privacy`.
- Footer: **LinkedIn** social link (driven by `seo_settings.org_social_linkedin`; add FB/IG/Twitter there and they'll flow through).
- **Testimonials & Client Logos system** (checklist §7/§12) — full CMS: `TestimonialModel` (MySQL + `config/testimonials.json` fallback), admin screens at `/admin/testimonials` (list/add/edit/delete, quote-vs-logo toggle, ratings, status), and a home-page "What Our Clients Say" section (logo wall + 3 starred quotes). Seeded with clearly-marked placeholder clients — **replace with genuine endorsements** before marketing relies on them. Review/AggregateRating schema intentionally NOT emitted for placeholder data (Google policy); save/edit/delete all purge the page cache.
- Skip-link + semantic heading order fixes; zero-emoji compliance maintained.

## Needs your input (Part D tracking sheet — fill the "Content" column)

| URL | Primary keyword | Title / Meta / H1 | Status |
|---|---|---|---|
| / | TrueNorth Group + "PET bottles manufacturer India" | auto (site default) | Done in code; **pick final title/meta in admin → Global SEO Settings** |
| /products | wholesale packaging / PET bottles wholesale | template-driven | Set templates in admin → Meta Templates |
| /products?category=… (×8) | e.g. "cosmetic bottles manufacturer" | one per category | **You map keywords → I/code set per-category meta in admin SEO Pages** |
| /product?id=… (×290) | product name | template | Auto; review via admin Page SEO Manager |
| /about | "about TrueNorth Group" | auto | Done; add team/cert photos (content) |
| /custom | "custom moulding PET" | auto | Done; add 2 case-study blocks (content) |
| /faq | "packaging FAQ" | done | Edit Q&A in admin FAQ repeater as needed |
| /blog/* | one per post | set in blog editor | 4 published; add 2–4 pillar guides (content) |

## Known issues surfaced (pre-existing, not introduced by this work)
- **`window.goToCategory` is undefined** — `sustainability.php:96,102` calls it on the rPET card `onclick` but no function defines it (in any layout or page). The two "rPET bottles / rPET jars" cards silently do nothing when clicked. **Fix:** wire them to `/products?category=cosmetics-bottles` / `/products?category=jars` (matches the home page's equivalent links) or add the missing handler. Flagging, not silently changing.
- Google Translate `element.js` loads site-wide and **is load-bearing** — the custom language dropdown drives it via the `googtrans` cookie (see `modals.php` `selectLanguage`). Do not remove it as "dead weight"; it's the multilingual engine.

## Not doable in code — external / manual
- **Google Business Profile** complete (categories, photos, Q&A) + NAP consistency with Justdial/Sulekha/IndiaMART/TradeIndia listings.
- **Backlinks / PR / social profiles** (only LinkedIn exists today — the `sameAs` in Organization schema picks up FB/IG/X URLs the moment you add them in Global SEO Settings).
- **Search Console / Bing / GA4 / Clarity**: GSC + Bing verification codes already emit when set in admin → Global SEO Settings; GA4 ID is wired (gtag) — **enter the ID there**. Heatmaps (Clarity) = add the snippet to *header_scripts* in the same panel.
- **Testimonials / team page / case studies with numbers** — content you need to supply; structure is ready.
- Re-submit sitemap in GSC after this deploy (URL already in robots.txt).

## Deploy
`deploy_wetruenorthgroup.zip` (rebuilt, 25.6 MB, exclusions verified: no backups, no cache, no `.orig`). Upload to cPanel `public_html`, extract over the install, then enable **LiteSpeed → App Level Cache**. The www→apex 301 and HSTS/CSP headers in the repo's `.htaccess` also go live with this deploy.
