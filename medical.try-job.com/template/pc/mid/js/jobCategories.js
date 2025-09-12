const jobCategories = {
  IT001: "医師",
  IT002: "薬剤師",
  IT003: "看護師/准看護師",
  IT004: "助産師",
  IT005: "保健師",
  IT006: "看護助手",
  IT007: "診療放射線技師",
  IT008: "臨床検査技師",
  IT009: "臨床工学技士",
  IT010: "管理栄養士/栄養士",
  IT011: "公認心理師/臨床心理士",
  IT012: "医療ソーシャルワーカー",
  IT013: "登録販売者",
  IT014: "医療事務/受付",
  IT015: "治験コーディネーター",
  IT016: "歯科医師",
  IT017: "歯科衛生士",
  IT018: "歯科技工士",
  IT019: "歯科助手",
  IT020: "理学療法士",
  IT022: "作業療法士",
  IT0b7: "視能訓練士",
  IT0bc: "柔道整復師",
  ITd2d: "あん摩マッサージ指圧師",
  IT44c: "鍼灸師",
  ITd77: "整体師",
  IT0e9: "薬剤師",
  IT72c: "調剤事務",
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
