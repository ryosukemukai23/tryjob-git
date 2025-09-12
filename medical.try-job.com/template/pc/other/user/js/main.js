
	document.addEventListener("DOMContentLoaded", function () {
		const mediaQuery = window.matchMedia("(max-width: 768px)");

		function initAccordion() {
			const accordionHeaders = document.querySelectorAll(".accordion-header");

			accordionHeaders.forEach(header => {
				const content = header.nextElementSibling;

				// 初期状態を設定（閉じた状態）
				if (content) {
					content.style.display = "none";
				}

				// アコーディオンのクリックイベントを追加
				header.addEventListener("click", function () {
					// アクティブ状態の切り替え
					this.classList.toggle("active");

					// コンテンツの表示/非表示を切り替え
					if (content.style.display === "block") {
						content.classList.remove("open");
						setTimeout(() => {
							content.style.display = "none";
						}, 300);
					} else {
						content.style.display = "block";
						setTimeout(() => {
							content.classList.add("open");
						}, 10);
					}
				});
			});
		}

		function destroyAccordion() {
			const accordionHeaders = document.querySelectorAll(".accordion-header");

			accordionHeaders.forEach(header => {
				const content = header.nextElementSibling;

				// アコーディオンをリセット（すべて展開状態にする）
				header.classList.remove("active");
				if (content) {
					content.style.display = "block";
					content.classList.remove("open");
				}
			});
		}

		function handleMediaQueryChange(e) {
			if (e.matches) {
				// 768px以下の場合
				initAccordion();
			} else {
				// 768px以上の場合
				destroyAccordion();
			}
		}

		// 初期状態の設定
		handleMediaQueryChange(mediaQuery);

		// メディアクエリの変更を監視
		mediaQuery.addEventListener("change", handleMediaQueryChange);
	});
