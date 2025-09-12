document.addEventListener("DOMContentLoaded", () => {
    const modal = document.querySelector(".is-modal01");
    const closeBtn = document.querySelector(".is-modal01 .flex a");

    // 要素が正しく取得されているか確認
    if (modal && closeBtn) {
        // バツボタンをクリックしたらモーダルを削除する
        closeBtn.addEventListener("click", (e) => {
            e.preventDefault();
            modal.remove(); // モーダルをDOMから削除する
        });
    } else {
        console.error("Modal or close button not found");
    }
});