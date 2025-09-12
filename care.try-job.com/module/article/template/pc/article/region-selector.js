import { hokkaidoSubs, tokyoSubs, prefectureSubs, prefectures } from './regions.js';

const subList = document.getElementById('sub-list');

function renderSubList(prefectureCode) {
    subList.innerHTML = '';
    const h3Element = document.querySelector('h3.is-sub_title');
    
    // prefectures 配列から都道府県名を取得
    const prefecture = prefectures.find(p => p.code === prefectureCode);
    h3Element.textContent = prefecture ? prefecture.name : '地域を選択';

    const subs = prefectureSubs[prefectureCode] || [];  // Dynamically fetch subregions from prefectureSubs

    subs.forEach((sub, index) => {
        const li = document.createElement('li');
        const id = `sub_${prefectureCode}_${index}`;
        li.innerHTML = `
            <input type="radio" id="${id}" name="work_place_add_sub" value="${sub.value}">
            <label for="${id}">${sub.label}</label>
        `;
        subList.appendChild(li);
    });
}

document.querySelectorAll('input[name="work_place_adds"]').forEach(radio => {
    radio.addEventListener('change', () => {
        renderSubList(radio.value);
    });
});

window.addEventListener('DOMContentLoaded', () => {
    const selected = document.querySelector('input[name="work_place_adds"]:checked');
    if (selected) renderSubList(selected.value);
});