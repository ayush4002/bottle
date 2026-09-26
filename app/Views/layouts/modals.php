  <!-- =========================================================================
       INQUIRY DRAWER & MODAL STYLES (Self-contained for immediate live rendering)
       ========================================================================= -->
  <style>
    .frapak-drawer-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 27, 44, 0.65);
      z-index: 9998;
      display: none;
      backdrop-filter: blur(3px);
      transition: opacity 0.25s ease;
    }
    .frapak-drawer-overlay.open {
      display: block !important;
    }
    .frapak-drawer {
      position: fixed;
      top: 0;
      right: 0;
      bottom: 0;
      width: 480px;
      max-width: 95vw;
      background: #ffffff;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      transform: translateX(100%);
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: -6px 0 30px rgba(0, 0, 0, 0.2);
      border-top-left-radius: 12px;
      border-bottom-left-radius: 12px;
    }
    .frapak-drawer.open {
      transform: translateX(0) !important;
    }
    .frapak-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 27, 44, 0.65);
      z-index: 10000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      backdrop-filter: blur(4px);
    }
    .frapak-modal-overlay.open {
      display: flex !important;
    }
    .frapak-modal-box {
      background: #ffffff;
      border-radius: 12px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
      animation: frapakModalIn 0.25s ease-out;
    }
    @keyframes frapakModalIn {
      from { opacity: 0; transform: translateY(16px) scale(0.98); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
  </style>

  <!-- =========================================================================
       INQUIRY DRAWER
       ========================================================================= -->
  <div class="frapak-drawer-overlay" id="inquiry-drawer-overlay" onclick="window.closeInquiry()"></div>
  <aside class="frapak-drawer" id="inquiry-drawer" onclick="event.stopPropagation()">
    <div class="drawer-header" style="background: var(--color-navy); color: #fff; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: #fff;">Wholesale Packaging Inquiry</h3>
        <p style="font-size: 0.8rem; color: #b8892e; margin: 3px 0 0; font-weight: 600;">Direct Factory RFQ &amp; Sample Request</p>
      </div>
      <button onclick="window.closeInquiry()" style="color: #fff; border: none; background: rgba(255,255,255,0.1); border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0;" aria-label="Close">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="drawer-body" style="padding: 22px; overflow-y: auto; flex: 1;">
      <div id="inquiry-product-badge" style="display: none;"></div>

      <form id="drawer-inquiry-form">
        <input type="hidden" name="product" id="inquiry-hidden-product" value="" />
        <div class="frapak-form-field">
          <label>1. Name *</label>
          <input type="text" name="name" required placeholder="Enter your full name" autocomplete="name" />
        </div>
        <div class="frapak-form-field">
          <label>2. Company Name *</label>
          <input type="text" name="company" required placeholder="Enter company name" autocomplete="organization" />
        </div>
        <div class="frapak-form-field">
          <label>3. Email ID *</label>
          <input type="email" name="email" required placeholder="Enter work email address" autocomplete="email" />
        </div>
        <div class="frapak-form-field">
          <label>4. Country *</label>
          <select name="country" class="country-select" required>
            <option value="" disabled selected>Select your country...</option>
            <option value="United States">United States</option>
            <option value="United Kingdom">United Kingdom</option>
            <option value="Germany">Germany</option>
            <option value="United Arab Emirates">United Arab Emirates</option>
            <option value="Saudi Arabia">Saudi Arabia</option>
            <option value="France">France</option>
            <option value="Netherlands">Netherlands</option>
            <option value="Australia">Australia</option>
            <option value="Canada">Canada</option>
            <option value="Singapore">Singapore</option>
            <option value="South Africa">South Africa</option>
            <option value="Malaysia">Malaysia</option>
            <option value="India">India</option>
            <option value="Italy">Italy</option>
            <option value="Spain">Spain</option>
            <option value="Switzerland">Switzerland</option>
            <option value="Japan">Japan</option>
            <option value="China">China</option>
            <option value="Brazil">Brazil</option>
            <option value="Mexico">Mexico</option>
            <option value="Poland">Poland</option>
            <option value="Belgium">Belgium</option>
            <option value="Sweden">Sweden</option>
            <option value="Austria">Austria</option>
            <option value="Denmark">Denmark</option>
            <option value="Norway">Norway</option>
            <option value="Ireland">Ireland</option>
            <option value="New Zealand">New Zealand</option>
            <option value="Other Country">Other Country</option>
          </select>
        </div>
        <div class="frapak-form-field">
          <label>5. Phone Number *</label>
          <input type="tel" name="phone" required placeholder="Country code + phone number" autocomplete="tel" />
        </div>
        <div class="frapak-form-field">
          <label>6. Inquiry Details *</label>
          <textarea id="inquiry-notes-field" name="inquiry" required placeholder="Tell us about your required volumes, neck sizes, caps, or target delivery timeline" style="min-height: 90px;"></textarea>
        </div>

        <button type="submit" class="frapak-btn-gold" style="margin-left: 0; width: 100%; margin-top: 10px; padding: 13px; font-weight: 700; font-size: 0.95rem; border-radius: 6px;">
          SEND INQUIRY
        </button>
      </form>
    </div>
  </aside>

  <!-- =========================================================================
       CONFIRMATION MODAL
       ========================================================================= -->
  <div class="frapak-modal-overlay" id="success-modal" onclick="window.closeModal('success-modal')">
    <div class="frapak-modal-box" style="max-width: 460px; padding: 24px;" onclick="event.stopPropagation()">
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
