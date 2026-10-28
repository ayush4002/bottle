    </div><!-- /.cms-content -->

    <footer style="text-align: center; padding: 20px 16px; border-top: 1px solid #334155; color: #64748b; font-size: 0.82rem; background: #1e293b; margin-top: auto;">
      &copy; <?= date('Y') ?> TrueNorth Group. Enterprise CMS Suite. Protected by SSL &amp; Encryption.
    </footer>
  </div><!-- /.cms-main-wrapper -->

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const hamburger = document.getElementById('cmsHamburger');
      const sidebar = document.getElementById('cmsSidebar');
      const overlay = document.getElementById('cmsMobileOverlay');
      const closeBtn = document.getElementById('cmsSidebarClose');
      const navLinks = document.querySelectorAll('.cms-nav-link');

      function openDrawer() {
        if (sidebar) sidebar.classList.add('cms-drawer-open');
        if (overlay) overlay.classList.add('cms-overlay-open');
        document.body.style.overflow = 'hidden';
      }

      function closeDrawer() {
        if (sidebar) sidebar.classList.remove('cms-drawer-open');
        if (overlay) overlay.classList.remove('cms-overlay-open');
        document.body.style.overflow = '';
      }

      if (hamburger) hamburger.addEventListener('click', openDrawer);
      if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
      if (overlay) overlay.addEventListener('click', closeDrawer);

      navLinks.forEach(function(link) {
        link.addEventListener('click', closeDrawer);
      });

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDrawer();
      });
    });
  </script>
</body>
</html>

