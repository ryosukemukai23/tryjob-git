import { prefectureLines, lineStations } from "/gc/tryjob/template/pc/mid/js/stations.js";
import { prefectures } from "/gc/tryjob/template/pc/mid/js/regions.js";

$(document).ready(function () {
  // 要素の参照を取得
  const $trafficLineSelect = $('select[name="traffic_line"]');
  const $trafficStationList = $("#traffic_station");
  const $trafficLinePAL = $('input[name="traffic_line_PAL[]"]');
  const $trafficStationPAL = $('input[name="traffic_station_PAL[]"]');

  // 都道府県コードを取得する関数
  function getPrefectureCode() {
    // 1. ラジオボタンから取得
    const checkedValue = $('input[name="work_place_adds"]:checked').val();
    if (checkedValue) {
      // PF形式のコードを返す
      if (checkedValue.startsWith("PF") && checkedValue.length >= 4) {
        return checkedValue.substring(0, 4);
      }

      // 数値IDから都道府県コードへ変換
      const prefId = parseInt(checkedValue, 10);
      if (!isNaN(prefId) && prefId >= 1 && prefId <= 47) {
        return `PF${prefId.toString().padStart(2, "0")}`;
      }
    }

    // 2. hidden inputから取得
    const trafficAddsValue = $('input[name="traffic_adds"]').val();
    if (trafficAddsValue) {
      if (trafficAddsValue.startsWith("PF") && trafficAddsValue.length >= 4) {
        return trafficAddsValue.substring(0, 4);
      }

      const prefId = parseInt(trafficAddsValue, 10);
      if (!isNaN(prefId) && prefId >= 1 && prefId <= 47) {
        return `PF${prefId.toString().padStart(2, "0")}`;
      }
    }

    // 3. URL パラメータから取得
    const urlParams = new URLSearchParams(window.location.search);
    const prefParam = urlParams.get("pref") || urlParams.get("prefecture");
    if (prefParam) {
      if (prefParam.startsWith("PF") && prefParam.length >= 4) {
        return prefParam.substring(0, 4);
      }

      const prefId = parseInt(prefParam, 10);
      if (!isNaN(prefId) && prefId >= 1 && prefId <= 47) {
        return `PF${prefId.toString().padStart(2, "0")}`;
      }
    }

    // デフォルト値
    return "PF13"; // 東京都
  }

  // 選択された都道府県に基づいて路線セレクトを設定
  function populateTrafficLineOptions() {
    const prefectureCode = getPrefectureCode();

    // セレクトボックスをリセット
    $trafficLineSelect.html('<option value="">選択してください</option>');

    // 該当する都道府県の路線がない場合は終了
    if (!prefectureCode || !prefectureLines[prefectureCode]) return;

    // 該当する都道府県の路線を追加
    $.each(prefectureLines[prefectureCode], function (_, line) {
      $trafficLineSelect.append(
        $("<option>", {
          value: line.value,
          text: line.label,
        })
      );
    });
  }

  // 路線が選択されたとき、対応する駅を表示
  function handleLineChange() {
    const selectedValue = $trafficLineSelect.val();

    // 選択された値をhidden inputに設定
    $trafficLinePAL.val(selectedValue);

    // 駅リストをリセット
    $trafficStationList.empty();

    if (!selectedValue) return;

    const prefectureCode = getPrefectureCode();
    if (!prefectureCode) return;

    // 駅リストを取得
    const stations = lineStations[prefectureCode]?.[selectedValue] || [];
    if (stations.length === 0) return;

    // 駅リストを表示
    $.each(stations, function (index, station) {
      const id = `station_${selectedValue}_${index}`;
      $("<li>")
        .append(
          $("<input>", {
            type: "radio",
            id: id,
            name: "traffic_station",
            value: station.id,
          })
        )
        .append(
          $("<label>", {
            for: id,
            text: station.name,
          })
        )
        .appendTo($trafficStationList);
    });
  }

  // 初期化処理を実行
  function init() {
    // 都道府県選択の変更を監視
    $('input[name="work_place_adds"]').on("change", function () {
      // traffic_addsに値を設定
      $('input[name="traffic_adds"]').val($(this).val());

      // 路線リストを更新
      populateTrafficLineOptions();

      // 駅リストをリセット
      $trafficStationList.empty();
    });

    // 路線選択時のイベント
    $trafficLineSelect.on("change", handleLineChange);

    // 駅選択時のイベント
    $trafficStationList.on("change", 'input[name="traffic_station"]', function () {
      $trafficStationPAL.val($(this).val());
    });

    // 初期表示
    populateTrafficLineOptions();

    // 初期値があればhidden inputsに設定
    if ($trafficLineSelect.val()) {
      $trafficLinePAL.val($trafficLineSelect.val());
    }

    const initialStationValue = $('input[name="traffic_station"]:checked').val();
    if (initialStationValue) {
      $trafficStationPAL.val(initialStationValue);
    }
  }

  // 初期化実行
  init();
});
