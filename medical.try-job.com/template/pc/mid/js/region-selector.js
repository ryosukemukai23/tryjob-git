import { prefectureSubs, prefectures } from "https://medical.try-job.com/template/pc/mid/js/regions.js";

const subList = document.getElementById("sub-list");

function renderSubList(prefectureCode) {
  subList.innerHTML = "";

  // h3 要素をそれぞれ取得（.fourth タブ内も明示的に）
  const subTitleH3 = document.querySelector("h3.is-sub_title");
  const searchTitleH3 = document.querySelector(".is-search__tab__content.fourth h3.is-search_title");

  // 都道府県名の取得
  const prefecture = prefectures.find((p) => p.code === prefectureCode);
  const prefectureName = prefecture ? prefecture.name : "地域";

  // h3 の文言を更新
  if (subTitleH3) {
    subTitleH3.textContent = `${prefectureName}の市区町村`;
  }

  if (searchTitleH3) {
    searchTitleH3.textContent = `${prefectureName}-市区町村の沿線を選択`;
  }

  // 該当する市区町村（sub）を表示
  const subs = prefectureSubs[prefectureCode] || [];

  subs.forEach((sub, index) => {
    const li = document.createElement("li");
    const id = `sub_${prefectureCode}_${index}`;
    li.innerHTML = `
      <input type="radio" id="${id}" name="work_place_add_sub" value="${sub.value}">
      <label for="${id}">${sub.label}</label>
    `;
    subList.appendChild(li);
  });
}

document.querySelectorAll('input[name="work_place_adds"]').forEach((radio) => {
  radio.addEventListener("change", () => {
    renderSubList(radio.value);
  });
});

window.addEventListener("DOMContentLoaded", () => {
  const selected = document.querySelector('input[name="work_place_adds"]:checked');
  if (selected) renderSubList(selected.value);
});
