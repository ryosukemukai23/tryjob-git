document.addEventListener('DOMContentLoaded', function() {
  const toggleButton = document.querySelector('.sidebar-toggle');
  const toggleArrow = document.querySelector('.toggle-arrow');
  const sidebarNav = document.querySelector('.sidebar-nav');
  const overlay = document.querySelector('.sidebar-overlay');
  
  if (toggleButton && sidebarNav) {
    toggleButton.addEventListener('click', function(e) {
      e.stopPropagation();
      sidebarNav.classList.toggle('open');
      toggleArrow.classList.toggle('open');
      overlay.classList.toggle('active');
      
      // Prevent body scrolling when menu is open
      if (sidebarNav.classList.contains('open')) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    });
  }
  
  // Close menu when clicking on overlay
  if (overlay) {
    overlay.addEventListener('click', function() {
      sidebarNav.classList.remove('open');
      toggleArrow.classList.remove('open');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    });
  }
  
  // Close menu when pressing Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && sidebarNav.classList.contains('open')) {
      sidebarNav.classList.remove('open');
      toggleArrow.classList.remove('open');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  });
  
  // Reset menu visibility on window resize
  window.addEventListener('resize', function() {
    if (window.innerWidth > 768 && sidebarNav) {
      sidebarNav.classList.remove('open');
      toggleArrow.classList.remove('open');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  });
});