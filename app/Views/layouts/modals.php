  <!-- =========================================================================
       INQUIRY DRAWER
       ========================================================================= -->
  <div class="frapak-drawer-overlay" id="inquiry-drawer-overlay" onclick="window.closeInquiry()"></div>
  <aside class="frapak-drawer" id="inquiry-drawer">
    <div class="drawer-header" style="background: var(--color-navy); color: #fff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.1rem; font-weight: 700;">Packaging Inquiry</h3>
      <button onclick="window.closeInquiry()" style="color: #fff; border: none; background: none; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px;" aria-label="Close">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="drawer-body" style="padding: 20px; overflow-y: auto;">
      <div id="inquiry-product-badge" style="display: none;"></div>

      <form id="drawer-inquiry-form">
        <div class="frapak-form-field">
          <label>1. Name *</label>
          <input type="text" name="name" required placeholder="Enter your name" />
        </div>
        <div class="frapak-form-field">
          <label>2. Company Name *</label>
          <input type="text" name="company" required placeholder="Enter company name" />
        </div>
        <div class="frapak-form-field">
          <label>3. Email ID *</label>
          <input type="email" name="email" required placeholder="Enter email address" />
        </div>
        <div class="frapak-form-field">
          <label>4. Country *</label>
          <select name="country" class="country-select" required></select>
        </div>
        <div class="frapak-form-field">
          <label>5. Phone Number *</label>
          <input type="tel" name="phone" required placeholder="Country code + phone number" />
        </div>
        <div class="frapak-form-field">
          <label>6. Inquiry *</label>
          <textarea id="inquiry-notes-field" name="inquiry" required placeholder="Tell us about your requirement"></textarea>
        </div>

        <button type="submit" class="frapak-btn-gold" style="margin-left: 0; width: 100%; margin-top: 6px;">
          SEND INQUIRY
        </button>
      </form>
    </div>
  </aside>

  <!-- =========================================================================
       CONFIRMATION MODAL
       ========================================================================= -->
  <div class="frapak-modal-overlay" id="success-modal">
    <div class="frapak-modal-box" style="max-width: 440px;">
      <div id="success-modal-content"></div>
    </div>
  </div>

  <!-- =========================================================================
       TERMS & CONDITIONS MODAL
       ========================================================================= -->
  <div class="frapak-modal-overlay" id="terms-modal" onclick="window.closeTermsModal()">
    <div class="frapak-modal-box" style="max-width: 640px; padding: 28px;" onclick="event.stopPropagation()">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--color-navy); margin: 0;">General Terms &amp; Conditions</h3>
        <button onclick="window.closeTermsModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-navy); line-height: 1;">&times;</button>
      </div>
      <div style="font-size: 0.84rem; color: #4B5563; line-height: 1.6; max-height: 60vh; overflow-y: auto; padding-right: 8px;">
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">1. Quality Assurance &amp; Manufacturing Standards</h4>
        <p style="margin-bottom: 12px;">All PET bottles, jars, closures, and pumps produced by TrueNorth Group comply strictly with TÜV NORD ISO 9001 quality management protocols and food-grade safety standards. Production is conducted under cleanroom protocols for medical, cosmetic, and food packaging applications.</p>
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">2. Minimum Order Quantities (MOQ) &amp; Tooling</h4>
        <p style="margin-bottom: 12px;">Standard catalogue items feature low MOQs starting from 20,000 units per batch. Custom pilot tooling and 3D prototype sampling terms are agreed upon project specifications prior to mold fabrication.</p>
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">3. Leak-Free Guarantee &amp; Inspection</h4>
        <p style="margin-bottom: 12px;">Every production batch undergoes 1,000+ cycle vacuum chamber leak tests and neck-finish torque calibration. Technical samples dispatched for customer testing must be verified before mass production sign-off.</p>
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">4. Export Shipping &amp; Delivery Terms</h4>
        <p style="margin-bottom: 0;">Shipments are packaged in export-grade corrugated cartons, stretch-wrapped pallets, and assigned full batch traceability for zero-damage transit across domestic and global logistics destinations.</p>
      </div>
      <button onclick="window.closeTermsModal()" class="frapak-btn-gold" style="width: 100%; margin-top: 20px; text-align: center; margin-left: 0;">CLOSE TERMS</button>
    </div>
  </div>

  <!-- =========================================================================
       PRIVACY STATEMENT MODAL
       ========================================================================= -->
  <div class="frapak-modal-overlay" id="privacy-modal" onclick="window.closePrivacyModal()">
    <div class="frapak-modal-box" style="max-width: 640px; padding: 28px;" onclick="event.stopPropagation()">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--color-navy); margin: 0;">Privacy Statement</h3>
        <button onclick="window.closePrivacyModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-navy); line-height: 1;">&times;</button>
      </div>
      <div style="font-size: 0.84rem; color: #4B5563; line-height: 1.6; max-height: 60vh; overflow-y: auto; padding-right: 8px;">
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">1. Client Data Protection</h4>
        <p style="margin-bottom: 12px;">TrueNorth Group values your business privacy. All technical drawings, CAD files, sample requests, and procurement inquiries submitted through our portal are stored securely under encrypted SSL protocols.</p>
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">2. Confidentiality &amp; Non-Disclosure (NDA)</h4>
        <p style="margin-bottom: 12px;">Proprietary bottle designs and custom mold specifications remain 100% confidential. We strictly execute Non-Disclosure Agreements (NDAs) for custom tooling and private-label packaging developments.</p>
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">3. Information Usage &amp; Communication</h4>
        <p style="margin-bottom: 0;">Contact details provided during RFQ submissions are exclusively utilized by our engineering team to provide quotations, technical specifications, and shipment status updates. We do not sell or share data with third parties.</p>
      </div>
      <button onclick="window.closePrivacyModal()" class="frapak-btn-gold" style="width: 100%; margin-top: 20px; text-align: center; margin-left: 0;">CLOSE PRIVACY STATEMENT</button>
    </div>
  </div>
