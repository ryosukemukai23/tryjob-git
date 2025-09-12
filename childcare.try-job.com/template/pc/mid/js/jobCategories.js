const jobCategories = {
  IT001: "保育園",
  IT002: "幼稚園教諭",
  IT003: "保育補助員",
  IT004: "看護師",
  IT005: "地域限定保育士",
  IT006: "児童指導員",
  IT007: "管理栄養士",
  IT008: "園長・副園長・施設長",
  IT009: "事務員",
  IT010: "里親支援専門相談員",
  IT011: "家庭支援専門相談員",
  IT012: "心理士",
  IT013: "栄養士",
  IT014: "調理員",
  IT015: "社会福祉士・精神保健福祉士",
  IT016: "子育て支援員",
  IT017: "無資格",
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
