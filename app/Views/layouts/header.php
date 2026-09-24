<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TrueNorth Group — your packaging Experience</title>
  <meta name="description" content="TrueNorth Group, the hybrid packaging specialist with over three decades of excellence in manufacturing and distribution of PET bottles, PET jars, pumps, caps and sprays." />
  
  <link rel="icon" type="image/svg+xml" href="/logo_svg.svg" />
  <link rel="shortcut icon" href="/favicon.svg" />
  <link rel="stylesheet" href="/styles.css?v=1.0.7" />
</head>
<body>

  <!-- =========================================================================
       TOP UTILITY BAR (Exact Frapak Top Strip)
       ========================================================================= -->
  <div class="frapak-top-utility-bar">
    <div class="frapak-container top-bar-inner">
      
      <!-- Search Input with Live Autocomplete -->
      <div class="frapak-search-box">
        <input type="text" id="top-search-input" placeholder="Search your packaging..." />
        <button onclick="window.navigateToPage('products')" aria-label="Search">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </button>
        <div class="search-results-popover" id="top-search-popover"></div>
      </div>

      <!-- Certifications & Language Selector -->
      <div class="top-bar-right">
        <span>Audited Cleanroom Manufacturing • Food-Grade ISO Certified</span>
        <div class="lang-selector">
          <span style="display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            EN
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
          </span>
        </div>
      </div>

    </div>
  </div>

  <!-- =========================================================================
       MAIN NAVIGATION HEADER (White Sticky Header)
       ========================================================================= -->
  <header class="frapak-main-header">
    <div class="frapak-container header-nav-inner">
      
      <!-- Logo -->
      <a href="#home" class="frapak-logo" onclick="window.navigateToPage('home')">
        <img src="/logo_svg.svg" alt="TrueNorth Group Logo" class="frapak-logo-img" />
      </a>

      <!-- Exact Client Headers: Home - About - Products - Market - Custom Solutions - Contact -->
      <ul class="frapak-nav-list">
        <li><a href="#home" class="frapak-nav-link active" data-page="home" onclick="window.navigateToPage('home')">Home</a></li>
        <li><a href="#about" class="frapak-nav-link" data-page="about" onclick="window.navigateToPage('about')">About</a></li>
        <li><a href="#products" class="frapak-nav-link" data-page="products" onclick="window.navigateToPage('products')">Products</a></li>
        <li><a href="#market" class="frapak-nav-link" data-page="market" onclick="window.navigateToPage('market')">Market</a></li>
        <li><a href="#custom" class="frapak-nav-link" data-page="custom" onclick="window.navigateToPage('custom')">Custom Solutions</a></li>
        <li><a href="#contact" class="frapak-nav-link" data-page="contact" onclick="window.navigateToPage('contact')">Contact</a></li>
      </ul>

      <!-- Header Actions (Inquiry + Mobile Hamburger) -->
      <div class="header-actions-wrap">
        <button class="frapak-cart-inquiry-btn" onclick="window.openInquiry()" aria-label="Open Inquiry">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          <span>INQUIRY</span>
        </button>

        <!-- Mobile Hamburger Button -->
        <button class="frapak-mobile-menu-toggle" id="frapak-mobile-menu-btn" onclick="window.toggleMobileMenu()" aria-label="Toggle Navigation Menu" aria-expanded="false">
          <span class="hamburger-bar"></span>
          <span class="hamburger-bar"></span>
          <span class="hamburger-bar"></span>
        </button>
      </div>

    </div>
  </header>

  <!-- =========================================================================
       MOBILE NAVIGATION DRAWER & BACKDROP
       ========================================================================= -->
  <div class="frapak-mobile-nav-backdrop" id="mobile-nav-backdrop" onclick="window.closeMobileMenu()"></div>
  <aside class="frapak-mobile-nav-drawer" id="mobile-nav-drawer" aria-label="Mobile Navigation">
    <div class="mobile-nav-header">
      <a href="#home" class="mobile-nav-logo" onclick="window.navigateToPage('home'); window.closeMobileMenu();">
        <img src="/logo_svg.svg" alt="TrueNorth Group Logo" />
      </a>
      <button class="mobile-nav-close-btn" onclick="window.closeMobileMenu()" aria-label="Close navigation menu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
      </button>
    </div>

    <!-- Mobile Search Box -->
    <div class="mobile-nav-search">
      <div class="mobile-search-input-wrap">
        <input type="text" id="mobile-search-input" placeholder="Search packaging..." />
        <button onclick="window.executeMobileSearch()" aria-label="Search">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </button>
      </div>
      <div class="search-results-popover" id="mobile-search-popover"></div>
    </div>

    <!-- Mobile Navigation Links -->
    <nav class="mobile-nav-links">
      <a href="#home" class="mobile-nav-item active" data-page="home" onclick="window.navigateToPage('home'); window.closeMobileMenu();">
        <span>Home</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="#about" class="mobile-nav-item" data-page="about" onclick="window.navigateToPage('about'); window.closeMobileMenu();">
        <span>About</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="#products" class="mobile-nav-item" data-page="products" onclick="window.navigateToPage('products'); window.closeMobileMenu();">
        <span>Products Catalogue</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="#market" class="mobile-nav-item" data-page="market" onclick="window.navigateToPage('market'); window.closeMobileMenu();">
        <span>Markets</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="#custom" class="mobile-nav-item" data-page="custom" onclick="window.navigateToPage('custom'); window.closeMobileMenu();">
        <span>Custom Solutions</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="#contact" class="mobile-nav-item" data-page="contact" onclick="window.navigateToPage('contact'); window.closeMobileMenu();">
        <span>Contact & Locations</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
    </nav>

    <!-- Drawer Footer Actions -->
    <div class="mobile-nav-footer">
      <button class="frapak-btn-gold mobile-nav-inquiry-btn" onclick="window.closeMobileMenu(); window.openInquiry();">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        SEND AN INQUIRY
      </button>
      <div class="mobile-nav-cert-badge">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span>ISO 15593 Food-Grade • Class 10,000 Cleanroom</span>
      </div>
    </div>
  </aside>
