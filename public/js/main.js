/* Prime Cargo — interactions */
document.addEventListener("DOMContentLoaded", () => {
  // Mobile nav: left drawer
  const toggle = document.querySelector(".nav-toggle");
  const drawer = document.getElementById("siteDrawer");
  const overlay = document.getElementById("drawerOverlay");
  if (toggle && drawer && overlay) {
    const setOpen = (open) => {
      drawer.classList.toggle("open", open);
      overlay.hidden = !open;
      requestAnimationFrame(() => overlay.classList.toggle("show", open));
      toggle.classList.toggle("active", open);
      toggle.setAttribute("aria-expanded", open);
      drawer.setAttribute("aria-hidden", !open);
      document.body.classList.toggle("no-scroll", open);
      if (open) drawer.querySelector(".drawer-close").focus(); else toggle.focus({ preventScroll: true });
    };
    toggle.addEventListener("click", () => setOpen(!drawer.classList.contains("open")));
    overlay.addEventListener("click", () => setOpen(false));
    drawer.querySelector(".drawer-close").addEventListener("click", () => setOpen(false));
    drawer.querySelectorAll("a").forEach((a) => a.addEventListener("click", () => setOpen(false)));
    document.addEventListener("keydown", (e) => { if (e.key === "Escape" && drawer.classList.contains("open")) setOpen(false); });
    window.matchMedia("(min-width: 1181px)").addEventListener("change", (m) => { if (m.matches) setOpen(false); });
  }

  // FAQ accordion
  document.querySelectorAll(".faq-item").forEach((item) => {
    const q = item.querySelector(".faq-q");
    const a = item.querySelector(".faq-a");
    q.addEventListener("click", () => {
      const open = item.classList.contains("open");
      document.querySelectorAll(".faq-item.open").forEach((o) => {
        o.classList.remove("open");
        o.querySelector(".faq-a").style.maxHeight = null;
      });
      if (!open) {
        item.classList.add("open");
        a.style.maxHeight = a.scrollHeight + "px";
      }
    });
  });

  // Tracking demo
  const trackForms = document.querySelectorAll("[data-track-form]");
  trackForms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const input = form.querySelector("input");
      const code = (input.value || "").trim();
      const result = document.querySelector("[data-track-result]");
      const empty = document.querySelector("[data-track-empty]");
      if (!code) {
        input.focus();
        return;
      }
      const idEl = document.querySelector("[data-track-id]");
      if (idEl) idEl.textContent = code.toUpperCase();
      if (empty) empty.classList.add("hidden");
      if (result) {
        result.classList.remove("hidden");
        result.scrollIntoView({ behavior: "smooth", block: "center" });
      } else {
        // On other pages, redirect to tracking page with code
        window.location.href = "tracking.html?code=" + encodeURIComponent(code);
      }
    });
  });

  // Pre-fill tracking from URL
  const params = new URLSearchParams(window.location.search);
  const codeParam = params.get("code");
  if (codeParam) {
    const tInput = document.querySelector("[data-track-form] input");
    if (tInput) {
      tInput.value = codeParam;
      const f = tInput.closest("[data-track-form]");
      if (f) f.dispatchEvent(new Event("submit"));
    }
  }

  // Quote / contact form fake submit
  document.querySelectorAll("[data-fake-form]").forEach((form) => {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const msg = form.querySelector("[data-form-msg]");
      if (msg) {
        msg.style.display = "block";
        msg.scrollIntoView({ behavior: "smooth", block: "center" });
      }
      form.reset();
    });
  });

  // Live shipping cost estimator (GBP, £6/kg air)
  const estForm = document.querySelector("[data-estimator]");
  if (estForm) {
    const calc = () => {
      const weight = parseFloat(estForm.weight.value) || 0;
      const ratePerKg = 6; // £ / কেজি — এয়ার কার্গো
      const total = weight * ratePerKg;
      const out = document.querySelector("[data-est-out]");
      if (out) {
        out.innerHTML = weight
          ? `আনুমানিক খরচ: <b>£${total.toLocaleString("en-GB", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</b> &nbsp;·&nbsp; সম্ভাব্য সময়: <b>৫–৮ দিন</b>`
          : "ওজন দিন, খরচ দেখুন।";
      }
    };
    estForm.addEventListener("input", calc);
    estForm.addEventListener("change", calc);
    calc();
  }

  // Sticky header shadow on scroll
  const header = document.querySelector(".site-header");
  if (header) {
    const onScroll = () => header.classList.toggle("scrolled", window.scrollY > 8);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });

    // Expose the real header height so the sticky promo badge keeps the same gap below it
    const setHeaderH = () => document.documentElement.style.setProperty("--header-h", header.offsetHeight + "px");
    setHeaderH();
    window.addEventListener("resize", setHeaderH);
  }

  // Scroll reveal (progressive enhancement — no-JS shows everything)
  const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (!reduce && "IntersectionObserver" in window) {
    document.documentElement.classList.add("js-reveal");
    const revealSel = ".section-head, .card, .step, .price-card, .info-item, .stat, .cta-band, .faq-item, .split .visual, .split > div, .form-card, .track-strip, .rate-table, .timeline";
    const groups = new Map();
    document.querySelectorAll(revealSel).forEach((el) => {
      el.setAttribute("data-reveal", "");
      const parent = el.parentElement;
      const idx = groups.get(parent) || 0;
      el.style.transitionDelay = Math.min(idx * 80, 320) + "ms";
      groups.set(parent, idx + 1);
    });
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) {
            e.target.classList.add("in");
            io.unobserve(e.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -8% 0px" }
    );
    document.querySelectorAll("[data-reveal]").forEach((el) => io.observe(el));
  }

  // Footer year
  const yr = document.querySelector("[data-year]");
  if (yr) yr.textContent = new Date().getFullYear();
});
