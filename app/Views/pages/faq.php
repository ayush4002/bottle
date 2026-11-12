<?php
// ==============================================================================
// FAQ PAGE — curated, schema-marked (FAQPage) answers for packaging buyers.
// Content lives in the admin FAQ repeater (SeoModel::getFaqs('page','faq'));
// these defaults seed the JSON fallback so the page is never empty.
// ==============================================================================
$tnFaqs = SeoModel::getFaqs('page', 'faq');
if (empty($tnFaqs)) {
    $tnFaqs = [
        ['question' => 'What is your minimum order quantity (MOQ)?',
         'answer' => 'Standard catalogue bottles, jars, caps and pumps carry MOQs from 5,000 to 30,000 units depending on the item. Custom moulds and fully bespoke containers start from agreed project quantities. Contact our sales team for a per-item MOQ sheet.'],
        ['question' => 'Do you provide free samples before placing an order?',
         'answer' => 'Yes. We dispatch sample batches from stock catalogue items free of charge so you can evaluate finish, dimensions and closure compatibility before committing to a production run.'],
        ['question' => 'Can you build a custom mould to our exact container design?',
         'answer' => 'Yes. Our cleanroom engineering team takes CAD drawings or reference samples and develops injection-blow moulds in-house. Typical mould tooling lead time is 8-12 weeks including first-article trials.'],
        ['question' => 'Are your containers food-grade and pharma-compliant?',
         'answer' => 'We produce in a Class 10,000 cleanroom under TUV NORD ISO 9001 protocols. Materials are suitable for food, cosmetic, pharmaceutical and nutraceutical contact, and we support FDA-compliant documentation on request.'],
        ['question' => 'What neck finishes and closure systems do you support?',
         'answer' => 'The range covers 18/410 through 28/410 neck finishes with compatible caps, flip-tops, lotion pumps, fine-mist sprayers and continuous sprays. Closure compatibility is verified during sample trials.'],
        ['question' => 'Do you manufacture rPET (recycled PET) packaging?',
         'answer' => 'Yes. We offer bottles and jars in 100% post-consumer rPET for sustainability targets, with MOQs from 30,000 units. Conventional virgin PET remains available for immediate stock requirements.'],
        ['question' => 'How do you pack and ship orders?',
         'answer' => 'Products ship in export-grade carton and pallet packaging sized to your lane. We handle global seaport logistics and provide per-container packing lists, plus leak and drop test reports for fragile items.'],
        ['question' => 'What payment and delivery terms do you work with?',
         'answer' => 'We support standard international terms including LC and TT with milestone-based production updates. Delivery is quoted lane by lane; urgent programmes can be prioritised on stock-eligible items.'],
    ];
}
$activePage = 'faq';
$pageTitle = 'Packaging Buying FAQs — MOQ, Moulding, rPET & Compliance | TrueNorth Group';
$pageDesc = 'Answers to the questions procurement teams ask most about PET bottles, jars and closures: MOQs, free samples, custom moulding, pharma compliance, rPET, packing, and payment terms.';
?>
  <div id="main-content" tabindex="-1" aria-hidden="true" style="position:absolute; left:0; top:0; width:0; height:0;"></div>
<section class="page-view" id="view-faq">
    <!-- FAQ Hero Banner -->
    <div class="products-page-title-banner about-hero-banner" style="padding: 40px 0;">
      <div class="tn-container">
        <div class="about-hero-badge">BUYER GUIDANCE</div>
        <h1 class="about-hero-title">Frequently Asked Questions About Packaging</h1>
        <p class="about-hero-subtitle">Direct answers on minimum orders, custom moulding, compliance and rPET — from our cleanroom engineering team.</p>
      </div>
    </div>

    <section class="tn-section" style="padding: 40px 0;">
      <div class="tn-container" style="max-width: 860px;">
        <?php foreach ($tnFaqs as $tnI => $tnF): ?>
          <details class="tn-faq-item" <?= $tnI === 0 ? 'open' : '' ?> style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 12px; background: #fff; overflow: hidden;">
            <summary style="cursor: pointer; padding: 18px 20px; font-weight: 700; color: #16273f; font-size: 1.02rem; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 12px;">
              <span><?= htmlspecialchars($tnF['question']) ?></span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5" style="flex-shrink:0;"><polyline points="6 9 12 15 18 9"/></svg>
            </summary>
            <div style="padding: 0 20px 18px; color: #475569; line-height: 1.65; font-size: 0.95rem;">
              <?= nl2br(htmlspecialchars($tnF['answer'])) ?>
            </div>
          </details>
        <?php endforeach; ?>

        <!-- CTA under FAQ -->
        <div style="text-align:center; margin: 40px 0 8px;">
          <p style="color:#475569; margin-bottom:14px;">Still have a specification question? Our technical desk replies within one business day.</p>
          <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <button type="button" class="tn-btn-gold" onclick="window.openInquiry('FAQ Page &mdash; Technical Question')">SEND AN INQUIRY</button>
            <a href="/contact" class="tn-btn-outline tn-btn-outline-dark">CONTACT US</a>
          </div>
        </div>
      </div>
    </section>
  </section>
