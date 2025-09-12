const jobCategories = {
  IT001: "介護福祉士",
  IT002: "訪問介護士（ホームヘルパー）",
  IT003: "介護補助/助手",
  IT004: "生活相談員",
  IT005: "ケアマネージャー",
  IT006: "管理職",
  IT007: "サービス提供責任者",
  IT008: "生活支援員",
  IT009: "福祉用具専門相談員",
  IT010: "介護タクシー/ドライバー",
  IT011: "介護事務",
  IT012: "管理栄養士/栄養士",
  IT013: "看護師/准看護師",
  IT014: "理学療法士",
  IT015: "言語聴覚士",
  IT016: "作業療法士",
  IT017: "視能訓練士",
  IT018: "柔道整復師",
  IT019: "あん摩マッサージ指圧師",
  IT020: "鍼灸師",
  IT021: "整体師",
};

document.addEventListener("DOMContentLoaded", function () {
  const categoryId = new URLSearchParams(window.location.search).get("category");
  const jobName = jobCategories[categoryId];

  if (categoryId && jobName) {
    const jobTitleSpan = document.querySelector("h2 span.bold");
    if (jobTitleSpan) {
      jobTitleSpan.innerHTML = `${jobName}<span style="font-weight:500;">の</span>`;
    }
  }
});
