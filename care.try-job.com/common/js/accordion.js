document.addEventListener("DOMContentLoaded", function () {
  // アコーディオンのトリガー取得
  const triggers = document.querySelectorAll(".p-faq__content--trigger");

  triggers.forEach((trigger) => {
    trigger.addEventListener("click", function (e) {
      e.preventDefault();

      const contentId = this.getAttribute("aria-controls");
      const content = document.getElementById(contentId);
      const isOpen = content.classList.contains("is-open");

      // 他の開いているアコーディオンを閉じる
      document.querySelectorAll(".p-faq__content--content").forEach((el) => {
        el.classList.remove("is-open");
        el.previousElementSibling?.setAttribute("aria-expanded", "false");
      });

      // 今回クリックされたものを開く
      if (!isOpen) {
        content.classList.add("is-open");
        this.setAttribute("aria-expanded", "true");
      } else {
        this.setAttribute("aria-expanded", "false");
      }
    });
  });

  // 不要なセカンドアコーディオン処理は削除（上記で統合可能）
});
