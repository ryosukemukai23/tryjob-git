document.addEventListener("DOMContentLoaded", function () {
  const slides = document.querySelectorAll(".slide");
  const prevArrow = document.querySelector(".prev-arrow");
  const nextArrow = document.querySelector(".next-arrow");
  let currentIndex = 0;

  // 重要: DOMContentLoadedイベントで実行されるため、slider-containerにすでに存在するscriptタグは無視される

  // Function to check if slide contains an image
  function hasImage(slide) {
    const img = slide.querySelector("img");
    // 画像要素が存在し、src属性があり、空でないことを確認
    return img !== null && img.getAttribute("src") && img.getAttribute("src").trim() !== "";
  }

  // Initialize: Only set active class on slides with images
  function initializeSlider() {
    // まずすべてのスライドからactiveクラスを削除
    slides.forEach((slide) => {
      slide.classList.remove("active");
    });

    // 画像がある最初のスライドを見つけてアクティブにする
    let foundSlideWithImage = false;

    for (let i = 0; i < slides.length; i++) {
      if (hasImage(slides[i])) {
        slides[i].classList.add("active");
        currentIndex = i;
        foundSlideWithImage = true;
        break;
      }
    }

    // 画像があるスライドが見つからなかった場合はスライダーを無効化
    if (!foundSlideWithImage) {
      console.warn("No slides with images found. Slider disabled.");
    }
  }

  // Navigate to next slide with image
  function nextSlide() {
    if (slides.length <= 1) return;

    // 現在のスライドからactiveクラスを削除
    slides[currentIndex].classList.remove("active");

    let nextIndex = currentIndex;
    let checkedCount = 0;

    // 画像がある次のスライドを探す
    do {
      nextIndex = (nextIndex + 1) % slides.length;
      checkedCount++;

      // すべてのスライドをチェックしたが画像があるものが見つからない場合、元に戻る
      if (checkedCount >= slides.length) {
        slides[currentIndex].classList.add("active");
        return;
      }
    } while (!hasImage(slides[nextIndex]));

    // 画像があるスライドが見つかった場合
    slides[nextIndex].classList.add("active");
    currentIndex = nextIndex;
  }

  // Navigate to previous slide with image
  function prevSlide() {
    if (slides.length <= 1) return;

    // 現在のスライドからactiveクラスを削除
    slides[currentIndex].classList.remove("active");

    let prevIndex = currentIndex;
    let checkedCount = 0;

    // 画像がある前のスライドを探す
    do {
      prevIndex = (prevIndex - 1 + slides.length) % slides.length;
      checkedCount++;

      // すべてのスライドをチェックしたが画像があるものが見つからない場合、元に戻る
      if (checkedCount >= slides.length) {
        slides[currentIndex].classList.add("active");
        return;
      }
    } while (!hasImage(slides[prevIndex]));

    // 画像があるスライドが見つかった場合
    slides[prevIndex].classList.add("active");
    currentIndex = prevIndex;
  }

  // Add event listeners if arrows exist
  if (prevArrow) {
    prevArrow.addEventListener("click", prevSlide);
  }

  if (nextArrow) {
    nextArrow.addEventListener("click", nextSlide);
  }

  // スライダーを初期化
  initializeSlider();

  // デバッグ用ログ（必要に応じて）
  console.log("Slider initialized. Slides with images:");
  slides.forEach((slide, idx) => {
    if (hasImage(slide)) {
      console.log(`Slide ${idx} has image`);
    }
  });
});
