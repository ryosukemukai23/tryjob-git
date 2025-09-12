document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".toggle-text").forEach((button) => {
    button.addEventListener("click", function (event) {
      event.preventDefault(); // Prevent the default action

      const text = this.previousElementSibling;
      const icon = this.querySelector(".icon-down");

      if (text.style.display === "block") {
        text.style.display = "-webkit-box";
        text.style.overflow = "hidden";
        text.style.webkitLineClamp = "4";
        this.textContent = "すべて見る";
        this.appendChild(icon); // Re-append the icon to the button text
        icon.classList.remove("rotate");
      } else {
        text.style.display = "block";
        text.style.overflow = "visible";
        text.style.webkitLineClamp = "unset";
        this.textContent = "閉じる";
        this.appendChild(icon); // Re-append the icon to the button text
        icon.classList.add("rotate");
      }
    });
  });
});