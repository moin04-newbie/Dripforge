// script.js
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const totalSlides = slides.length;

function showSlide(index) {
  const slideWidth = slides[0].clientWidth;
  document.querySelector('.slides').style.transform = `translateX(${-index * slideWidth}px)`;
}

function nextSlide() {
  currentSlide = (currentSlide + 1) % totalSlides;
  showSlide(currentSlide);
}

function prevSlide() {
  currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
  showSlide(currentSlide);
}

// Optional: Auto-play the slider
setInterval(nextSlide, 5000); // Change slide every 5 seconds