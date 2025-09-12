document.addEventListener("DOMContentLoaded", function () {
  const slides = document.querySelectorAll(".slide");
  const prevArrow = document.querySelector(".prev-arrow");
  const nextArrow = document.querySelector(".next-arrow");

  // 画像を持っているスライドのみ抽出
  const imageSlides = Array.from(slides).filter((slide) => slide.querySelector("img"));

  let currentSlide = 0;

  function showSlide(slideIndex) {
    slides.forEach((slide) => {
      slide.classList.remove("active");
    });

    // imageSlides配列から選ばれたスライドだけにactiveを付ける
    const targetSlide = imageSlides[slideIndex];
    if (targetSlide) {
      targetSlide.classList.add("active");
    }
  }

  function nextSlide() {
    currentSlide = (currentSlide + 1) % imageSlides.length;
    showSlide(currentSlide);
  }

  function prevSlide() {
    currentSlide = (currentSlide - 1 + imageSlides.length) % imageSlides.length;
    showSlide(currentSlide);
  }

  if (nextArrow && prevArrow) {
    nextArrow.addEventListener("click", nextSlide);
    prevArrow.addEventListener("click", prevSlide);
  }

  setInterval(nextSlide, 5000);

  showSlide(currentSlide);

  const favoriteBtn = document.querySelector(".btn-favorite");
  if (favoriteBtn) {
    favoriteBtn.addEventListener("click", function () {
      this.classList.toggle("active");
    });
  }
});
