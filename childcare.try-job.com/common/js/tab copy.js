document.addEventListener('DOMContentLoaded', function () {
  function setupTabs(containerSelector, tabSelector, contentSelector, activeClass) {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    const tabs = container.querySelectorAll(tabSelector);
    const tabContents = container.querySelectorAll(contentSelector);

    tabs.forEach((tab, index) => {
      tab.addEventListener('click', function () {
        // Remove active class from all tabs and contents
        tabs.forEach(t => t.classList.remove(activeClass));
        tabContents.forEach(content => content.classList.remove(activeClass));

        // Add active class to clicked tab
        this.classList.add(activeClass);

        // Show corresponding tab content
        if (contentSelector === '.tab-content') {
          const targetContent = document.getElementById(this.dataset.tab);
          if (targetContent) {
            targetContent.classList.add(activeClass);
          }
        } else {
          tabContents[index].classList.add(activeClass);
        }
      });
    });
  }

  // Setup tabs for different containers
  setupTabs('.is-search__tabs--top', '.is-search__tab__list button', '.is-search__tab__content', 'is-active');
  setupTabs('.is-search__tabs--bottom', '.is-search__tab__list button', '.is-search__tab__content', 'is-active');
  setupTabs('body', '.tab', '.tab-content', 'active'); // General tab setup
});