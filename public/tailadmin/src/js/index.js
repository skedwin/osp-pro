import "jsvectormap/dist/jsvectormap.min.css";
import "flatpickr/dist/flatpickr.min.css";
import "dropzone/dist/dropzone.css";
import "../css/style.css";

import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import flatpickr from "flatpickr";
import Dropzone from "dropzone";

import chart01 from "./components/charts/chart-01";
import chart02 from "./components/charts/chart-02";
import chart03 from "./components/charts/chart-03";
import map01 from "./components/map-01";
import "./components/calendar-init.js";
import "./components/image-resize";


/* =====================================================
   Alpine.js Setup
===================================================== */
Alpine.plugin(persist);
window.Alpine = Alpine;
Alpine.start();


/* =====================================================
   Flatpickr Init
===================================================== */
flatpickr(".datepicker", {
  mode: "range",
  static: true,
  monthSelectorType: "static",
  dateFormat: "M j, Y",
  defaultDate: [new Date().setDate(new Date().getDate() - 6), new Date()],
  prevArrow:
    '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15.25 6L9 12.25L15.25 18.5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  nextArrow:
    '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M8.75 19L15 12.75L8.75 6.5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  onReady: (selectedDates, dateStr, instance) => {
    instance.element.value = dateStr.replace("to", "-");

    const customClass = instance.element.getAttribute("data-class");
    if (customClass) {
      instance.calendarContainer.classList.add(customClass);
    }
  },
  onChange: (selectedDates, dateStr, instance) => {
    instance.element.value = dateStr.replace("to", "-");
  },
});


/* =====================================================
   Dropzone Init
===================================================== */
const dropzoneArea = document.querySelector("#demo-upload");

if (dropzoneArea) {
  new Dropzone("#demo-upload", { url: "/file/post" });
}


/* =====================================================
   Charts + Map Initialization
===================================================== */
document.addEventListener("DOMContentLoaded", () => {
  if (typeof chart01 === "function") chart01();
  if (typeof chart02 === "function") chart02();
  if (typeof chart03 === "function") chart03();
  if (typeof map01 === "function") map01();
});


/* =====================================================
   Year Auto-update
===================================================== */
const year = document.getElementById("year");
if (year) {
  year.textContent = new Date().getFullYear();
}


/* =====================================================
   Copy-to-Clipboard Block (Guarded)
===================================================== */
document.addEventListener("DOMContentLoaded", () => {
  const copyInput = document.getElementById("copy-input");
  const copyButton = document.getElementById("copy-button");
  const copyText = document.getElementById("copy-text");
  const websiteInput = document.getElementById("website-input");

  // Only run if all required elements exist
  if (copyInput && copyButton && websiteInput && copyText) {
    copyButton.addEventListener("click", () => {
      navigator.clipboard.writeText(websiteInput.value).then(() => {
        copyText.textContent = "Copied";
        setTimeout(() => (copyText.textContent = "Copy"), 2000);
      });
    });
  }
});


/* =====================================================
   Search Shortcuts (Safe)
===================================================== */
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("search-input");
  const searchButton = document.getElementById("search-button");

  if (searchInput && searchButton) {
    // Focus function
    const focusSearchInput = () => searchInput.focus();

    // Click handler
    searchButton.addEventListener("click", focusSearchInput);

    // Cmd+K or Ctrl+K
    document.addEventListener("keydown", (event) => {
      if ((event.metaKey || event.ctrlKey) && event.key === "k") {
        event.preventDefault();
        focusSearchInput();
      }
    });

    // "/" key focus
    document.addEventListener("keydown", (event) => {
      if (event.key === "/" && document.activeElement !== searchInput) {
        event.preventDefault();
        focusSearchInput();
      }
    });
  }
});
