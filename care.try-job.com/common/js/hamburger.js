document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector(".l-header");
  const hamburger = document.querySelector(".l-header__content__hamburger");
  const nav = document.querySelector(".l-global-nav");
  const navClose = document.querySelector(".l-global-nav__close");
  const body = document.body;
  const html = document.documentElement;
  let scrollPosition = 0;

  // overlay要素を生成
  const overlay = document.createElement("div");
  overlay.className = "l-overlay";
  overlay.style.cssText = `
    position: fixed;
    z-index: 999;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(255,255,255,0.6);
    display: none;
    transition: opacity 0.3s;
    opacity: 0;
  `;
  document.body.appendChild(overlay);

  function openNav() {
    // 開くアニメーション
    nav.classList.remove("is-closing");
    nav.classList.add("is-opening");
    nav.style.display = "flex";
    hamburger.classList.add("is-open");
    scrollPosition = window.scrollY;
    body.classList.add("no-scroll");
    html.classList.add("no-scroll");
    body.style.top = `-${scrollPosition}px`;
    overlay.style.display = "block";
    setTimeout(() => {
      overlay.style.opacity = "1";
    }, 10); // for fade-in
  }
  function closeNav() {
    // 閉じるアニメーション
    nav.classList.remove("is-opening");
    nav.classList.add("is-closing");
    hamburger.classList.remove("is-open");
    body.classList.remove("no-scroll");
    html.classList.remove("no-scroll");
    window.scrollTo(0, scrollPosition);
    body.style.top = "";
    overlay.style.opacity = "0";
    setTimeout(() => {
      overlay.style.display = "none";
    }, 300); // fade-out
  }
  // navのアニメーション完了後にdisplay:none
  nav.addEventListener("animationend", (e) => {
    if (nav.classList.contains("is-closing")) {
      nav.classList.remove("is-closing");
      nav.style.display = "none";
    }
    if (nav.classList.contains("is-opening")) {
      nav.style.display = "flex";
    }
  });

  hamburger.addEventListener("click", openNav);
  navClose.addEventListener("click", closeNav);
  overlay.addEventListener("click", closeNav); // overlayクリックで閉じる
  // ESCキー対応
  window.addEventListener("keydown", (e) => {
    if ((nav.classList.contains("is-opening") || nav.classList.contains("is-open")) && e.key === "Escape") {
      closeNav();
    }
  });
});
