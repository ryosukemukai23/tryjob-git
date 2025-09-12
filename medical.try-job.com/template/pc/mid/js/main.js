$(function () {
  // --- 初期化処理 ---
  function initialize() {
    updateSelectedCount();
    initializeStepTabs();
    initializeTrafficHandlers();
    updateOptionalStepTabs();
    checkMediaQuery();
    initAccordion();
  }

  // --- ステップナビゲーション関連 ---
  function toggleNextButton(tabClass, inputName) {
    const isChecked = $(`.is-search__tab__content.${tabClass} input[name="${inputName}"]:checked`).length > 0;
    $(`.is-search__tab__content.${tabClass} .is-search__btn--next`)
      .toggleClass("disabled", !isChecked)
      .prop("disabled", !isChecked);
  }

  $(".is-search__btn--next").on("click", function (e) {
    e.preventDefault();
    const $currentTab = $(".is-search__tab__content.is-active");
    const steps = ["first", "second", "third", "fourth", "fifth"];
    const currentIdx = steps.findIndex((step) => $currentTab.hasClass(step));

    if ($currentTab.find("input:checked").length === 0 && currentIdx < 2) {
      alert(currentIdx === 0 ? "都道府県を選択してください" : "市区町村を選択してください");
      return;
    }

    const $nextTab = $(`.is-search__tab__content.${steps[currentIdx + 1]}`);
    if ($nextTab.length) {
      $currentTab.removeClass("is-active");
      $nextTab.addClass("is-active");

      $(".step-navigation .step").eq(currentIdx).removeClass("active").addClass("completed");
      $(".step-navigation .step")
        .eq(currentIdx + 1)
        .addClass("active");

      if (window.innerWidth <= 768) {
        $("html, body").animate({ scrollTop: $("h2.anchor").offset().top - 100 }, 300);
      }
    } else {
      alert("次のステップが見つかりません");
    }
  });

  $(".step-navigation .step").on("click", function () {
    const $clicked = $(this);
    const clickedIdx = $(".step-navigation .step").index($clicked);
    const currentIdx = $(".step-navigation .step").index($(".step.active"));

    const isFirstSelected = $('.is-search__tab__content.first input[name="work_place_adds"]:checked').length > 0;
    const isSecondSelected = $('#sub-list input[name="work_place_add_sub"]:checked').length > 0;
    const isThirdSelected = $('input[name="work_style[]"]:checked').length > 0;
    const allRequiredSelected = isFirstSelected && isSecondSelected && isThirdSelected;

    if (clickedIdx > 2 && !allRequiredSelected) {
      alert("必須項目をすべて選択してから次に進んでください");
      return;
    }

    if (clickedIdx <= currentIdx || allRequiredSelected) {
      $(".is-search__tab__content").removeClass("is-active").eq(clickedIdx).addClass("is-active");
      $(".step-navigation .step")
        .removeClass("active completed")
        .each(function (i) {
          if (i < clickedIdx) $(this).addClass("completed");
          else if (i === clickedIdx) $(this).addClass("active");
        });

      if (window.innerWidth <= 768) {
        $("html, body").animate({ scrollTop: $("h2.anchor").offset().top - 20 }, 50);
      }
    }
  });

  // --- フォーム送信・リセット処理 ---
  $("form").on("submit", function (e) {
    const isSortButtonSubmit = $('input[name="select_sort"]').val() !== "";
    if (isSortButtonSubmit) {
      console.log("ソートボタンからの送信: リセットをスキップします");
      return true;
    }

    console.log("通常の送信");
    // リセット処理を無効化
    // sessionStorage.setItem("form_submitted", "true");
  });

  $(window).on("pageshow", function (event) {
    // リセット処理を無効化
    /*
    if (event.originalEvent.persisted || sessionStorage.getItem("form_submitted") === "true") {
      resetAllFormValues();
      sessionStorage.removeItem("form_submitted");
    }
    */
  });

  // リセット処理を無効化
  /*
  function resetAllFormValues() {
    $('input[type="checkbox"], input[type="radio"]').prop("checked", false);
    $("select").prop("selectedIndex", 0);
    $('input[type="text"], input[type="number"], input[type="hidden"], textarea').val("");
    $(".is-search__btn span").text("0件選択中");
    $(".is-search__tab__content").removeClass("is-active");
    $(".is-search__tab__content.first").addClass("is-active");
    $(".step-navigation .step").removeClass("active completed allowed");
    $(".step-navigation .step:first").addClass("active");
    $(".is-search__btn, .is-search__btn--next").addClass("disabled").prop("disabled", true);
    updateOptionalStepTabs();
  }
  */

  // --- 選択肢の切り替え ---
  window.changeSalaryOptionDisp = function (selectElement) {
    const val = $(selectElement).val();
    const types = ["hour", "day", "month", "year"];
    const typeMap = { 時給: "hour", 日給: "day", 月給: "month", 年俸: "year" };

    types.forEach((type) => {
      $(`#salary_${type}_disp`).hide();
      $(`#salary_${type}`).prop("disabled", true);
    });

    const selected = typeMap[val];
    if (selected) {
      $(`#salary_${selected}_disp`).show();
      $(`#salary_${selected}`).prop("disabled", false);
    }
  };

  function updateSelectedCount() {
    const total = $('input[name="work_style[]"]:checked').length + $('input[name="addition[]"]:checked').length;
    $(".is-search__btn span").text(`${total}件選択中`);
  }

  // --- レスポンシブ対応 ---
function initAccordion() {
  // SP幅（768px以下）の場合のみアコーディオンを有効化
  if (window.innerWidth <= 768) {
    $(".fifth .is-search_title").each(function () {
      const $title = $(this); // タイトル要素を取得
      const $nextUl = $title.next("ul"); // タイトル直後の <ul> を取得

      // <ul> が存在しない場合の安全チェック
      if ($nextUl.length) {
        // 初期状態では非表示に設定
        $nextUl.hide();

        // クリックイベントを設定
        $title.off("click").on("click", function () {
          $nextUl.slideToggle(200); // アコーディオンの開閉
          $title.toggleClass("is-open"); // 開閉状態に応じたクラスを切り替え
        });
      }
    });
  } else {
    // PC幅（768px超）の場合はすべて表示
    $(".fifth .is-search__type ul").show();
    $(".fifth .is-search_title").removeClass("is-open");
  }
}

// ページロード時とリサイズ時に初期化処理を実行
$(document).ready(initAccordion);
$(window).on("resize", initAccordion);

  function checkMediaQuery() {
    // 必要に応じて追加
  }

  // --- イベントリスナー登録 ---
  $(".is-search__tab__content.first input[name='work_place_adds']").on("change", function () {
    toggleNextButton("first", "work_place_adds");
  });

  $("#sub-list").on("change", 'input[name="work_place_add_sub"]', function () {
    toggleNextButton("second", "work_place_add_sub");
  });

  $('input[name="work_style[]"]').on("change", function () {
    $(".is-search__btn, .is-search__btn--next")
      .toggleClass("disabled", !$('input[name="work_style[]"]:checked').length)
      .prop("disabled", !$('input[name="work_style[]"]:checked').length);
  });

  $('input[name="work_style[]"], input[name="addition[]"]').on("change", updateSelectedCount);

  $(
    ".is-search__tab__content.first input[name='work_place_adds'], #sub-list input[name='work_place_add_sub'], input[name='work_style[]']"
  ).on("change", updateOptionalStepTabs);

  $(window).on("resize", checkMediaQuery);

  // 初期化実行
  initialize();
});
