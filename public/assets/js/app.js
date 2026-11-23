function initCinegoApp() {
  window.CINEGO_ROUTES = window.CINEGO_ROUTES || {
    api: '/api',
    chat: '/chat-ai',
    ticket: '/send-ticket'
  };

  const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      || window.CINEGO_CSRF_TOKEN
      || '';
  };
  const csrfToken = getCsrfToken();

  if (window.lucide) {
    window.lucide.createIcons();
  }

  const AppState = {
    currentHeroIndex: 0,
    heroTimer: null,
    selectedGenre: "all",
    selectedCinemaCity: "all",
    booking: {
      step: 1,
      movie: null,
      cinema: null,
      date: null,
      showtime: null,
      seats: [],
      occupiedSeats: [],
      combos: {},
      discount: 0,
      appliedCode: "",
      customer: {
        name: "",
        phone: "",
        email: ""
      },
      paymentMethod: "momo",
      bookingCode: ""
    }
  };
  window.AppState = AppState;

  // Khởi tạo danh sách 7 ngày chiếu khả dụng cho toàn bộ ứng dụng
  const weekdays = ["Chủ Nhật", "Thứ Hai", "Thứ Ba", "Thứ Tư", "Thứ Năm", "Thứ Sáu", "Thứ Bảy"];
  const dateOptions = [];
  const today = new Date();
  for (let i = 0; i < 7; i++) {
    const d = new Date();
    d.setDate(today.getDate() + i);
    const dayName = i === 0 ? "Hôm nay" : i === 1 ? "Ngày mai" : weekdays[d.getDay()];
    const dateFormatted = `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}`;
    dateOptions.push({
      label: `${dayName} (${dateFormatted})`,
      value: `${dateFormatted}/${today.getFullYear()}`
    });
  }
  window.dateOptions = dateOptions;

  window.showToast = function(message, type = "info") {
    const container = document.getElementById("toast-container");
    if (!container) return;

    const toast = document.createElement("div");
    toast.className = `toast-item flex items-center gap-3 px-5 py-3.5 rounded-xl text-sm font-medium shadow-2xl border transition-all ${
      type === "success"
        ? "bg-zinc-900/95 text-emerald-400 border-emerald-500/30 shadow-[0_0_20px_rgba(16,185,129,0.2)]"
        : type === "error"
        ? "bg-zinc-900/95 text-red-400 border-red-500/30 shadow-[0_0_20px_rgba(229,9,20,0.2)]"
        : "bg-zinc-900/95 text-white border-zinc-700/50 shadow-xl"
    }`;

    let iconHtml = "";
    if (type === "success") {
      iconHtml = `<svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
    } else if (type === "error") {
      iconHtml = `<svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
    } else {
      iconHtml = `<svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
    }

    toast.innerHTML = `${iconHtml}<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = "0";
      toast.style.transform = "translateY(20px)";
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  };

  // ==========================================
  // MODULE: NAVIGATION & MOBILE MENU
  // ==========================================
  try {
    const navHeader = document.getElementById("main-nav");
    const mobileMenuToggle = document.getElementById("mobile-menu-toggle");
    const mobileMenu = document.getElementById("mobile-menu");

    if (navHeader) {
      window.addEventListener("scroll", () => {
        if (window.scrollY > 40) {
          navHeader.classList.add("glass-nav", "py-3", "shadow-xl");
          navHeader.classList.remove("py-5");
        } else {
          navHeader.classList.remove("glass-nav", "shadow-xl");
          navHeader.classList.add("py-5");
        }
      });
    }

    if (mobileMenuToggle && mobileMenu) {
      mobileMenuToggle.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
      });

      mobileMenu.querySelectorAll("a").forEach(link => {
        link.addEventListener("click", () => {
          mobileMenu.classList.add("hidden");
        });
      });
    }
  } catch (err) {
    console.error("[Cinego Nav Init Error]:", err);
  }

  // Khai báo sẵn các stub toàn cục dự phòng cho Hero Banner
  window.sendHeroIframeCommand = function(cmd) {
    if (window.CinegoHero && typeof window.CinegoHero.sendCommand === "function") {
      window.CinegoHero.sendCommand(cmd);
    }
  };
  window.updateHeroMuteUI = function(muted) {
    if (window.CinegoHero && typeof window.CinegoHero.updateMuteUI === "function") {
      window.CinegoHero.updateMuteUI(muted);
    }
  };

  // ==========================================
  // MODULE: THANH ĐẶT VÉ NHANH (QUICK BOOKING)
  // ==========================================
  try {
    const quickCitySelect = document.getElementById("quick-city-select");
    const quickCinemaSelect = document.getElementById("quick-cinema-select");
    const quickDateSelect = document.getElementById("quick-date-select");
    const quickMovieSelect = document.getElementById("quick-movie-select");
    const quickSubmitBtn = document.getElementById("quick-submit-btn");

    if (quickDateSelect && Array.isArray(dateOptions)) {
      quickDateSelect.innerHTML = dateOptions.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join("");
    }

    if (quickMovieSelect && window.CINEGO_DATA && Array.isArray(CINEGO_DATA.nowShowing)) {
      quickMovieSelect.innerHTML = `<option value="">-- Chọn Phim --</option>` +
        CINEGO_DATA.nowShowing.map(m => `<option value="${m.title}">${m.title} (${m.ageRating || 'P'})</option>`).join("");
    }

    function updateQuickCinemas(selectedCity) {
      if (!quickCinemaSelect || !window.CINEGO_DATA || !Array.isArray(CINEGO_DATA.cinemas)) return;
      const filtered = selectedCity
        ? CINEGO_DATA.cinemas.filter(c => c.city === selectedCity)
        : CINEGO_DATA.cinemas;

      quickCinemaSelect.innerHTML = `<option value="">-- Chọn Rạp Chiếu --</option>` +
        filtered.map(c => `<option value="${c.name}">${c.name}</option>`).join("");
    }

    if (quickCitySelect) {
      quickCitySelect.addEventListener("change", (e) => {
        updateQuickCinemas(e.target.value);
      });
      updateQuickCinemas(quickCitySelect.value);
    }

    if (quickSubmitBtn) {
      quickSubmitBtn.addEventListener("click", () => {
        const cinema = quickCinemaSelect ? quickCinemaSelect.value : "";
        const date = quickDateSelect ? quickDateSelect.value : "";
        const movie = quickMovieSelect ? quickMovieSelect.value : "";

        if (!movie) {
          showToast("Vui lòng chọn bộ phim bạn muốn xem!", "error");
          if (quickMovieSelect) quickMovieSelect.focus();
          return;
        }

        openBookingModal({
          movieTitle: movie,
          cinemaName: cinema,
          date: date
        });
      });
    }
  } catch (err) {
    console.error("[Cinego Quick Booking Init Error]:", err);
  }

  // ==========================================
  // MODULE: PHIM ĐANG CHIẾU (NOW SHOWING)
  // ==========================================
  try {
    const moviesGrid = document.getElementById("now-showing-grid");
    const genreFiltersContainer = document.getElementById("genre-filters");
    const emptyState = document.getElementById("movies-empty-state");

    function renderMovies(genre = "all") {
      if (!moviesGrid || !window.CINEGO_DATA || !Array.isArray(CINEGO_DATA.nowShowing)) return;

      let filtered = CINEGO_DATA.nowShowing;
      if (genre !== "all") {
        filtered = filtered.filter(m => Array.isArray(m.genres) && m.genres.some(g => g.toLowerCase().includes(genre.toLowerCase())));
      }

      if (filtered.length === 0) {
        moviesGrid.innerHTML = "";
        if (emptyState) emptyState.classList.remove("hidden");
        return;
      }

      if (emptyState) emptyState.classList.add("hidden");

      moviesGrid.innerHTML = filtered.map(movie => {
        const formatBadges = Array.isArray(movie.formats) ? movie.formats.map(f =>
          `<span class="px-2 py-0.5 text-[10px] font-bold rounded bg-zinc-900/80 text-zinc-300 border border-zinc-700 backdrop-blur-md">${f}</span>`
        ).join(" ") : "";

        const genreBadges = Array.isArray(movie.genres) ? movie.genres.map(g =>
          `<span class="text-xs text-zinc-400">${g}</span>`
        ).join(" • ") : "";

        return `
          <div class="movie-card group bg-cine-card rounded-2xl overflow-hidden border border-white/5 flex flex-col justify-between hover:border-cine-red/40 transition-all duration-300">
            <div class="movie-poster-wrapper aspect-[2/3] w-full relative overflow-hidden bg-zinc-900">
              <img
                src="${movie.poster || ''}"
                alt="${movie.title || ''}"
                loading="lazy"
                class="movie-poster-img w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500&auto=format&fit=crop&q=80';"
              />

              <div class="absolute top-3 left-3 flex flex-wrap gap-1 z-10">
                <span class="px-2 py-0.5 text-[11px] font-black rounded bg-cine-red text-white shadow-md">${movie.ageRating || 'P'}</span>
                ${formatBadges}
              </div>

              <div class="absolute top-3 right-3 z-10 flex items-center gap-1 px-2.5 py-1 rounded-full bg-black/75 backdrop-blur-md border border-amber-500/30 text-amber-400 text-xs font-bold shadow-md">
                <svg class="w-3.5 h-3.5 fill-amber-400 text-amber-400" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span>${movie.rating || '9.0'}</span>
              </div>

              <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-5 z-20 gap-2.5">
                <button
                  onclick="window.openTrailerModal('${movie.trailerUrl || ''}', '${(movie.title || '').replace(/'/g, "\\'")}')"
                  class="w-full py-2.5 px-4 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-semibold backdrop-blur-md border border-white/20 flex items-center justify-center gap-2 transition-all">
                  <svg class="w-4 h-4 text-cine-red fill-cine-red" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                  Xem Trailer
                </button>
                <button
                  onclick="window.openBookingModal({ movieTitle: '${(movie.title || '').replace(/'/g, "\\'")}' })"
                  class="w-full py-2.5 px-4 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider shadow-[0_0_15px_rgba(229,9,20,0.5)] flex items-center justify-center gap-2 transition-all">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                  Đặt Vé Nhanh
                </button>
              </div>
            </div>

            <div class="p-4 flex flex-col justify-between flex-1">
              <div>
                <div class="text-xs text-zinc-400 mb-1 flex items-center gap-1.5 font-medium">
                  ${genreBadges}
                </div>
                <h3 class="font-bold text-white text-base leading-snug line-clamp-1 group-hover:text-cine-red transition-colors" title="${movie.title}">
                  ${movie.title}
                </h3>
              </div>

              <div class="pt-3 mt-3 border-t border-white/5 flex items-center justify-between text-xs text-zinc-400">
                <span class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  ${movie.duration || ''}
                </span>
                <button
                  onclick="window.openBookingModal({ movieTitle: '${(movie.title || '').replace(/'/g, "\\'")}' })"
                  class="text-xs font-bold text-cine-red hover:text-white transition-colors">
                  Đặt vé &rarr;
                </button>
              </div>
            </div>
          </div>
        `;
      }).join("");
    }
    window.renderMovies = renderMovies;

    if (genreFiltersContainer) {
      const filterButtons = genreFiltersContainer.querySelectorAll(".genre-filter-btn");
      filterButtons.forEach(btn => {
        btn.addEventListener("click", () => {
          filterButtons.forEach(b => {
            b.classList.remove("bg-cine-red", "text-white", "shadow-lg", "shadow-red-600/30");
            b.classList.add("bg-cine-card", "text-zinc-400", "hover:text-white");
          });
          btn.classList.add("bg-cine-red", "text-white", "shadow-lg", "shadow-red-600/30");
          btn.classList.remove("bg-cine-card", "text-zinc-400");

          const genre = btn.getAttribute("data-genre");
          AppState.selectedGenre = genre;
          renderMovies(genre);
        });
      });
    }

    renderMovies("all");
  } catch (err) {
    console.error("[Cinego Now Showing Init Error]:", err);
  }

  // ==========================================
  // MODULE: PHIM SẮP CHIẾU (COMING SOON)
  // ==========================================
  try {
    const comingSoonContainer = document.getElementById("coming-soon-grid");
    if (comingSoonContainer && window.CINEGO_DATA && Array.isArray(CINEGO_DATA.comingSoon)) {
      comingSoonContainer.innerHTML = CINEGO_DATA.comingSoon.map(movie => {
        const genreBadges = Array.isArray(movie.genres) ? movie.genres.join(" • ") : "";
        return `
          <div class="group relative bg-cine-card rounded-2xl overflow-hidden border border-white/5 hover:border-zinc-700 transition-all duration-300 flex flex-col">
            <div class="aspect-[16/10] sm:aspect-[2/3] w-full relative overflow-hidden bg-zinc-900">
              <img
                src="${movie.poster || ''}"
                alt="${movie.title || ''}"
                loading="lazy"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/40 to-transparent"></div>

              <div class="absolute top-3 left-3">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 backdrop-blur-md">
                  ${movie.releaseDate || ''}
                </span>
              </div>

              <div class="absolute bottom-3 left-3 right-3">
                <div class="flex items-center gap-1.5 text-xs text-zinc-400 mb-1">
                  ${genreBadges}
                </div>
                <h3 class="font-bold text-white text-base md:text-lg leading-snug line-clamp-1 group-hover:text-cine-red transition-colors">
                  ${movie.title}
                </h3>
              </div>
            </div>

            <div class="p-4 flex flex-col justify-between flex-1 bg-cine-card">
              <p class="text-xs text-zinc-400 line-clamp-2 mb-4 leading-relaxed">
                ${movie.description || ''}
              </p>
              <div class="flex items-center gap-2">
                <button
                  onclick="window.openTrailerModal('${movie.trailerUrl || ''}', '${(movie.title || '').replace(/'/g, "\\'")}')"
                  class="flex-1 py-2 px-3 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition-all">
                  <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                  Trailer
                </button>
                <button
                  onclick="window.toggleRemindMovie('${(movie.title || '').replace(/'/g, "\\'")}')"
                  class="remind-btn py-2 px-3 rounded-xl bg-zinc-800 hover:bg-cine-red text-zinc-300 hover:text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition-all">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                  Nhắc Tôi
                </button>
              </div>
            </div>
          </div>
        `;
      }).join("");
    }
  } catch (err) {
    console.error("[Cinego Coming Soon Init Error]:", err);
  }

  window.toggleRemindMovie = function(movieTitle) {
    showToast(`Đã bật nhắc nhở suất chiếu sớm cho phim "${movieTitle}"!`, "success");
  };

  // ==========================================
  // MODULE: CỤM RẠP CHIẾU (CINEMAS)
  // ==========================================
  try {
    const cinemasGrid = document.getElementById("cinemas-grid");
    const cityFilterBtns = document.querySelectorAll(".cinema-city-tab");

    function renderCinemas(city = "all") {
      if (!cinemasGrid || !window.CINEGO_DATA || !Array.isArray(CINEGO_DATA.cinemas)) return;

      let filtered = CINEGO_DATA.cinemas;
      if (city !== "all") {
        filtered = filtered.filter(c => c.city.toLowerCase() === city.toLowerCase());
      }

      cinemasGrid.innerHTML = filtered.map(cinema => {
        const amenitiesHtml = Array.isArray(cinema.amenities) ? cinema.amenities.map(a =>
          `<span class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-zinc-800/80 text-zinc-300 border border-zinc-700/50">${a}</span>`
        ).join("") : "";

        return `
          <div class="group bg-cine-card rounded-2xl overflow-hidden border border-white/5 hover:border-zinc-700 transition-all duration-300 flex flex-col">
            <div class="h-48 w-full relative overflow-hidden bg-zinc-900">
              <img
                src="${cinema.image || ''}"
                alt="${cinema.name || ''}"
                loading="lazy"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/30 to-transparent"></div>

              <div class="absolute top-3 left-3">
                <span class="px-2.5 py-1 rounded-md bg-cine-red text-white text-xs font-bold shadow-md">
                  ${cinema.city || ''}
                </span>
              </div>

              <div class="absolute top-3 right-3 flex items-center gap-1 px-2.5 py-1 rounded-full bg-black/70 backdrop-blur-md text-amber-400 text-xs font-bold border border-amber-500/20">
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span>${cinema.rating || '9.0'}</span>
              </div>

              <div class="absolute bottom-3 left-3 right-3">
                <span class="text-xs font-semibold text-emerald-400 flex items-center gap-1 mb-0.5">
                  <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                  Cách bạn ${cinema.distance || '1.2km'}
                </span>
                <h3 class="text-lg font-bold text-white leading-snug group-hover:text-cine-red transition-colors">
                  ${cinema.name}
                </h3>
              </div>
            </div>

            <div class="p-5 flex flex-col justify-between flex-1 gap-4">
              <div>
                <p class="text-xs text-zinc-400 flex items-start gap-1.5 mb-3 leading-relaxed">
                  <svg class="w-4 h-4 text-zinc-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  ${cinema.address || ''}
                </p>

                <div class="flex flex-wrap gap-1.5">
                  ${amenitiesHtml}
                </div>
              </div>

              <div class="pt-4 border-t border-white/5 flex items-center justify-between">
                <span class="text-xs text-zinc-400">Hotline: <strong class="text-white">${cinema.phone || '1900 8181'}</strong></span>
                <button
                  onclick="window.openBookingModal({ cinemaName: '${(cinema.name || '').replace(/'/g, "\\'")}' })"
                  class="px-4 py-2 rounded-xl bg-white/10 hover:bg-cine-red text-white text-xs font-bold transition-all shadow-md">
                  Xem Lịch Chiếu
                </button>
              </div>
            </div>
          </div>
        `;
      }).join("");
    }
    window.renderCinemas = renderCinemas;

    cityFilterBtns.forEach(btn => {
      btn.addEventListener("click", () => {
        cityFilterBtns.forEach(b => {
          b.classList.remove("bg-white", "text-black", "font-bold");
          b.classList.add("text-zinc-400", "hover:text-white");
        });
        btn.classList.add("bg-white", "text-black", "font-bold");
        btn.classList.remove("text-zinc-400");

        const city = btn.getAttribute("data-city");
        AppState.selectedCinemaCity = city;
        renderCinemas(city);
      });
    });

    renderCinemas("all");
  } catch (err) {
    console.error("[Cinego Cinemas Init Error]:", err);
  }

  // ==========================================
  // MODULE: ƯU ĐÃI & KHUYẾN MÃI (PROMOTIONS)
  // ==========================================
  try {
    const promotionsGrid = document.getElementById("promotions-grid");
    if (promotionsGrid && window.CINEGO_DATA && Array.isArray(CINEGO_DATA.promotions)) {
      promotionsGrid.innerHTML = CINEGO_DATA.promotions.map(promo => {
        return `
          <div class="group relative bg-cine-card rounded-2xl p-6 border border-white/5 hover:border-cine-red/30 transition-all duration-300 flex flex-col justify-between">
            <div class="absolute -top-3 left-6">
              <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-cine-red text-white uppercase tracking-wider shadow-lg">
                ${promo.tag}
              </span>
            </div>

            <div class="mt-2 mb-4">
              <h3 class="text-lg font-bold text-white group-hover:text-cine-red transition-colors mb-2">
                ${promo.title}
              </h3>
              <p class="text-xs text-zinc-400 leading-relaxed">
                ${promo.desc}
              </p>
            </div>

            <div class="pt-4 border-t border-white/5 flex items-center justify-between">
              <div class="flex flex-col">
                <span class="text-[10px] text-zinc-500 uppercase font-semibold">Mã ưu đãi demo</span>
                <span class="font-mono text-sm font-bold text-amber-400">${promo.code}</span>
              </div>
              <button
                onclick="window.copyVoucherCode('${promo.code}')"
                class="px-3.5 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Sao Chép
              </button>
            </div>
          </div>
        `;
      }).join("");
    }
  } catch (err) {
    console.error("[Cinego Promotions Init Error]:", err);
  }

  window.copyVoucherCode = function(code) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(code);
    }
    showToast(`Đã sao chép mã ưu đãi "${code}"!`, "success");
  };

  function escapeHtml(str) {
    if (!str) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // ==========================================
  // HỆ THỐNG ĐÁNH GIÁ PHIM THỰC TẾ (REVIEWS)
  // ==========================================
  window.openReviewModal = function(presetTicketCode = "") {
    const modal = document.getElementById("review-modal");
    if (!modal) return;
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.classList.add("modal-open");

    const codeInput = document.getElementById("review-ticket-code");
    if (codeInput) {
      if (presetTicketCode) {
        codeInput.value = presetTicketCode;
      }
      setTimeout(() => codeInput.focus(), 100);
    }
  };

  window.closeReviewModal = function() {
    const modal = document.getElementById("review-modal");
    if (!modal) return;
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    document.body.classList.remove("modal-open");

    const alertEl = document.getElementById("review-alert");
    if (alertEl) {
      alertEl.classList.add("hidden");
      alertEl.innerHTML = "";
    }
  };

  window.handleReviewSubmit = async function(event) {
    event.preventDefault();
    const form = document.getElementById("review-form");
    const submitBtn = document.getElementById("review-submit-btn");
    const alertEl = document.getElementById("review-alert");

    const ticketCode = (document.getElementById("review-ticket-code")?.value || "").trim();
    const rating     = document.getElementById("review-rating")?.value || "9.5";
    const comment    = (document.getElementById("review-comment")?.value || "").trim();

    if (!ticketCode) {
      showToast("Vui lòng nhập Mã vé điện tử của bạn!", "error");
      return;
    }
    if (!comment || comment.length < 5) {
      showToast("Nội dung nhận xét cần ít nhất 5 ký tự!", "error");
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span>Đang gửi...</span>
      `;
    }

    try {
      const response = await fetch(`${window.CINEGO_ROUTES.api}?action=submit_review`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrfToken || window.CINEGO_CSRF_TOKEN || ""
        },
        body: JSON.stringify({
          ticket_code: ticketCode,
          rating: rating,
          comment: comment
        })
      });

      const resData = await response.json();

      if (resData.success) {
        showToast(resData.message || "Gửi đánh giá thành công! Cảm ơn bạn.", "success");
        if (form) form.reset();
        closeReviewModal();
        if (typeof window.loadRealReviews === "function") {
          window.loadRealReviews();
        }
      } else {
        if (alertEl) {
          alertEl.className = "p-3 rounded-xl text-xs bg-red-950/60 border border-red-500/40 text-red-300 block";
          alertEl.textContent = resData.message || "Không thể gửi đánh giá. Vui lòng kiểm tra lại!";
        }
        showToast(resData.message || "Lỗi khi gửi đánh giá", "error");
      }
    } catch (err) {
      console.error("[CINEGO Review Error]:", err);
      if (alertEl) {
        alertEl.className = "p-3 rounded-xl text-xs bg-red-950/60 border border-red-500/40 text-red-300 block";
        alertEl.textContent = "Lỗi kết nối tới máy chủ. Vui lòng thử lại sau!";
      }
      showToast("Lỗi kết nối máy chủ", "error");
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<span>Gửi Đánh Giá</span>`;
      }
    }
  };

  window.loadRealReviews = async function() {
    const reviewsGrid = document.getElementById("reviews-grid");
    if (!reviewsGrid) return;

    try {
      const res = await fetch(`${window.CINEGO_ROUTES.api}?action=get_reviews`, {
        headers: { "X-CSRF-TOKEN": csrfToken }
      });
      const data = await res.json();

      if (data.success && Array.isArray(data.reviews) && data.reviews.length > 0) {
        reviewsGrid.innerHTML = data.reviews.slice(0, 8).map(rev => {
          const ratingVal = parseFloat(rev.rating) || 9.0;
          const starsCount = Math.min(5, Math.max(1, Math.round(ratingVal / 2)));
          const stars = "★".repeat(starsCount) + "☆".repeat(5 - starsCount);

          const firstChar = (rev.customer_name || "A").trim().charAt(0).toUpperCase();
          const cleanCode = rev.ticket_code ? rev.ticket_code.replace(/(.{4})(.*)(.{2})/, "$1***$3") : "CGO-***";

          return `
            <div class="bg-cine-card rounded-2xl p-6 border border-white/5 flex flex-col justify-between hover:border-red-500/30 transition-all duration-300 group shadow-lg">
              <div>
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-red-600 to-amber-500 flex items-center justify-center font-black text-white text-base shadow-md shrink-0">
                      ${firstChar}
                    </div>
                    <div>
                      <h4 class="text-sm font-bold text-white group-hover:text-red-400 transition-colors">${escapeHtml(rev.customer_name)}</h4>
                      <span class="text-[11px] text-emerald-400 flex items-center gap-1 font-medium">
                        <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Khách có vé (${escapeHtml(cleanCode)})
                      </span>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-amber-400 text-xs tracking-wider">${stars}</div>
                    <div class="text-amber-400 font-extrabold text-xs mt-0.5">${ratingVal.toFixed(1)}/10</div>
                  </div>
                </div>

                <p class="text-xs text-zinc-300 leading-relaxed mb-4 italic">
                  "${escapeHtml(rev.comment)}"
                </p>
              </div>

              <div class="pt-3 border-t border-white/5 flex items-center justify-between text-[11px] text-zinc-500">
                <span class="truncate max-w-[65%]" title="${escapeHtml(rev.movie_title)}">Phim: <strong class="text-zinc-300">${escapeHtml(rev.movie_title)}</strong></span>
                <span class="shrink-0">${escapeHtml(rev.date_formatted || rev.created_at || "")}</span>
              </div>
            </div>
          `;
        }).join("");
        return;
      }
    } catch (err) {
      console.warn("[CINEGO Reviews] Không thể nạp review từ MySQL, hiển thị dữ liệu mặc định:", err);
    }

    if (typeof CINEGO_DATA !== "undefined" && Array.isArray(CINEGO_DATA.reviews)) {
      reviewsGrid.innerHTML = CINEGO_DATA.reviews.map(rev => {
        const stars = "★".repeat(Math.floor(rev.rating || 5));
        return `
          <div class="bg-cine-card rounded-2xl p-6 border border-white/5 flex flex-col justify-between hover:border-red-500/20 transition-all duration-300">
            <div>
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                  <img src="${rev.avatar}" alt="${rev.name}" class="w-11 h-11 rounded-full object-cover border border-zinc-700" />
                  <div>
                    <h4 class="text-sm font-bold text-white">${rev.name}</h4>
                    <span class="text-[11px] text-emerald-400 flex items-center gap-1">
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                      Đã xem tại CINEGO
                    </span>
                  </div>
                </div>
                <div class="flex text-amber-400 text-sm tracking-wider">
                  ${stars}
                </div>
              </div>

              <p class="text-xs text-zinc-300 leading-relaxed mb-4 italic">
                "${rev.content}"
              </p>
            </div>

            <div class="pt-3 border-t border-white/5 flex items-center justify-between text-[11px] text-zinc-500">
              <span>Phim: <strong class="text-zinc-400">${rev.movie}</strong></span>
              <span>${rev.date}</span>
            </div>
          </div>
        `;
      }).join("");
    }
  };

  loadRealReviews();

  const trailerModal = document.getElementById("trailer-modal");
  const trailerFrame = document.getElementById("trailer-iframe");
  const trailerTitle = document.getElementById("trailer-title");
  const trailerCloseBtn = document.getElementById("trailer-close-btn");

  window.openTrailerModal = function(url, title = "Xem Trailer") {
    if (!trailerModal || !trailerFrame) return;
    if (typeof window.sendHeroIframeCommand === "function") {
      window.sendHeroIframeCommand("mute");
    }
    if (typeof window.updateHeroMuteUI === "function") {
      window.updateHeroMuteUI(true);
    }
    trailerTitle.innerText = title;

    let embedUrl = (url || "").trim();
    if (!embedUrl) {
      showToast("Chưa có trailer cho phim này", "info");
      return;
    }

    if (embedUrl.includes("watch?v=")) {
      embedUrl = embedUrl.replace("watch?v=", "embed/");
    } else if (embedUrl.includes("youtu.be/")) {
      embedUrl = embedUrl.replace("youtu.be/", "www.youtube.com/embed/");
    }

    const separator = embedUrl.includes("?") ? "&" : "?";
    if (!embedUrl.includes("autoplay=")) {
      embedUrl += `${separator}autoplay=1&rel=0`;
    }

    trailerFrame.src = embedUrl;
    trailerModal.classList.add("active");
    document.body.classList.add("modal-open");
  };

  function closeTrailerModal() {
    if (!trailerModal || !trailerFrame) return;
    trailerModal.classList.remove("active");
    document.body.classList.remove("modal-open");
    trailerFrame.src = "";
  }

  if (trailerCloseBtn) trailerCloseBtn.addEventListener("click", closeTrailerModal);
  if (trailerModal) {
    trailerModal.addEventListener("click", (e) => {
      if (e.target === trailerModal) closeTrailerModal();
    });
  }

  const searchModal = document.getElementById("search-modal");
  const searchInput = document.getElementById("search-input-field");
  const searchResultsContainer = document.getElementById("search-results-list");
  const searchCloseBtn = document.getElementById("search-close-btn");
  const navSearchBtn = document.getElementById("nav-search-btn");

  function openSearchModal() {
    if (!searchModal) return;
    searchModal.classList.add("active");
    document.body.classList.add("modal-open");
    if (searchInput) {
      searchInput.value = "";
      setTimeout(() => searchInput.focus(), 150);
      renderSearchResults("");
    }
  }

  function closeSearchModal() {
    if (!searchModal) return;
    searchModal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }

  if (navSearchBtn) navSearchBtn.addEventListener("click", openSearchModal);
  if (searchCloseBtn) searchCloseBtn.addEventListener("click", closeSearchModal);
  if (searchModal) {
    searchModal.addEventListener("click", (e) => {
      if (e.target === searchModal) closeSearchModal();
    });
  }

  function renderSearchResults(query) {
    if (!searchResultsContainer) return;
    const cleanQuery = query.trim().toLowerCase();

    const allMovies = [...CINEGO_DATA.nowShowing, ...CINEGO_DATA.comingSoon];
    const matches = cleanQuery === ""
      ? allMovies.slice(0, 5)
      : allMovies.filter(m =>
          m.title.toLowerCase().includes(cleanQuery) ||
          (m.vietnameseTitle && m.vietnameseTitle.toLowerCase().includes(cleanQuery)) ||
          m.genres.some(g => g.toLowerCase().includes(cleanQuery))
        );

    if (matches.length === 0) {
      searchResultsContainer.innerHTML = `
        <div class="py-12 text-center text-zinc-500">
          <svg class="w-12 h-12 mx-auto mb-3 text-zinc-600 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <p class="text-sm font-medium text-zinc-400">Không tìm thấy bộ phim nào phù hợp với từ khóa "${query}".</p>
          <p class="text-xs text-zinc-600 mt-1">Hãy thử tìm theo tên tiếng Việt hoặc thể loại (Hành động, Hoạt hình, v.v.)</p>
        </div>
      `;
      return;
    }

    searchResultsContainer.innerHTML = matches.map(movie => {
      const isNowShowing = !!movie.formats;
      return `
        <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-zinc-800/60 transition-all border border-transparent hover:border-zinc-700">
          <img src="${movie.poster}" alt="${movie.title}" class="w-14 h-20 rounded-lg object-cover bg-zinc-900 shrink-0" />
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="px-2 py-0.5 text-[10px] font-bold rounded ${isNowShowing ? 'bg-cine-red text-white' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30'}">
                ${isNowShowing ? 'Đang Chiếu' : 'Sắp Chiếu'}
              </span>
              <span class="text-xs text-zinc-400 truncate">${movie.genres.join(" • ")}</span>
            </div>
            <h4 class="text-sm font-bold text-white truncate">${movie.title}</h4>
            <p class="text-xs text-zinc-500 truncate mt-0.5">${movie.vietnameseTitle || movie.description}</p>
          </div>
          <div class="shrink-0 flex items-center gap-2">
            ${isNowShowing ? `
              <button
                onclick="closeSearchModal(); window.openBookingModal({ movieTitle: '${movie.title.replace(/'/g, "\\'")}' })"
                class="px-4 py-2 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold transition-all shadow-md">
                Đặt Vé
              </button>
            ` : `
              <button
                onclick="closeSearchModal(); window.openTrailerModal('${movie.trailerUrl}', '${movie.title.replace(/'/g, "\\'")}')"
                class="px-3.5 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-medium transition-all">
                Xem Trailer
              </button>
            `}
          </div>
        </div>
      `;
    }).join("");
  }

  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      renderSearchResults(e.target.value);
    });
  }

  const authModal = document.getElementById("auth-modal");
  const authLoginTab = document.getElementById("auth-tab-login");
  const authRegisterTab = document.getElementById("auth-tab-register");
  const authLoginForm = document.getElementById("auth-login-form");
  const authRegisterForm = document.getElementById("auth-register-form");
  const authCloseBtn = document.getElementById("auth-close-btn");

  const navLoginBtn = document.getElementById("nav-login-btn");
  const navUserMenu = document.getElementById("nav-user-menu");
  const navUserBtn = document.getElementById("nav-user-btn");
  const navUserChevron = document.getElementById("nav-user-chevron");
  const navUserDropdown = document.getElementById("nav-user-dropdown");
  const navUserEmail = document.getElementById("nav-user-email");
  const navUserInitial = document.getElementById("nav-user-initial");
  const dropdownUserEmail = document.getElementById("dropdown-user-email");
  const dropdownAccountBtn = document.getElementById("dropdown-account-btn");
  const dropdownHistoryBtn = document.getElementById("dropdown-history-btn");
  const dropdownLogoutBtn = document.getElementById("dropdown-logout-btn");

  const mobileLoginBtn = document.getElementById("mobile-login-btn");
  const mobileUserInfo = document.getElementById("mobile-user-info");
  const mobileUserEmail = document.getElementById("mobile-user-email");
  const mobileUserInitial = document.getElementById("mobile-user-initial");
  const mobileLogoutBtn = document.getElementById("mobile-logout-btn");
  const mobileAccountBtn = document.getElementById("mobile-account-btn");
  const mobileHistoryBtn = document.getElementById("mobile-history-btn");

  const userProfileModal = document.getElementById("user-profile-modal");
  const profileCloseBtn = document.getElementById("profile-close-btn");
  const profileLogoutBtn = document.getElementById("profile-logout-btn");
  const profileSaveBtn = document.getElementById("profile-save-btn");

  const ticketHistoryModal = document.getElementById("ticket-history-modal");
  const ticketHistoryList = document.getElementById("ticket-history-list");
  const historyCloseBtn = document.getElementById("history-close-btn");

  let userBookingHistory = [
    {
      code: "CGO-882319",
      movie: "Deadpool & Wolverine",
      cinema: "CINEGO Landmark 81 IMAX",
      hall: "Phòng Chiếu IMAX Laser",
      showtime: "19:20 • 20/10/2026",
      seats: "F06, F07",
      format: "IMAX 3D Laser",
      total: 350000,
      poster: "https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg",
      status: "Đã thanh toán (Sắp chiếu)"
    },
    {
      code: "CGO-612045",
      movie: "Inside Out 2",
      cinema: "CINEGO Vincom Center Đồng Khởi",
      hall: "Cinema 03 (Standard)",
      showtime: "14:00 • 18/10/2026",
      seats: "D04, D05",
      format: "2D Lồng tiếng",
      total: 190000,
      poster: "https://image.tmdb.org/t/p/w500/vpnVM9B6NMmQpWeZvzLvDESb2QY.jpg",
      status: "Đã xem"
    }
  ];

  const DEFAULT_ACCOUNTS = [
    {
      email: "dinhan33316@gmail.com",
      phone: "0901234567",
      password: "123456",
      name: "Nguyễn Đình An",
      points: 450,
      vip: true,
      joined: "15/01/2026"
    },
    {
      email: "admin@cinego.vn",
      phone: "0909999999",
      password: "admin123",
      name: "Quản Trị Viên",
      points: 9999,
      vip: true,
      joined: "01/01/2026"
    }
  ];

  function getRegisteredUsers() {
    try {
      const stored = localStorage.getItem("cinego_registered_users");
      if (stored) {
        const parsed = JSON.parse(stored);
        if (Array.isArray(parsed) && parsed.length > 0) {
          return parsed;
        }
      }
    } catch (e) {}
    try {
      localStorage.setItem("cinego_registered_users", JSON.stringify(DEFAULT_ACCOUNTS));
    } catch (e) {}
    return DEFAULT_ACCOUNTS;
  }

  function saveRegisteredUsers(users) {
    try {
      localStorage.setItem("cinego_registered_users", JSON.stringify(users));
    } catch (e) {}
  }

  try {
    const saved = localStorage.getItem("cinego_user");
    if (saved) {
      AppState.currentUser = JSON.parse(saved);
    }
  } catch (err) {
    AppState.currentUser = null;
  }

  function renderAuthUI() {
    const user = AppState.currentUser;

    if (user && user.email) {
      const email = user.email;
      const initial = email.charAt(0).toUpperCase();

      if (navLoginBtn) navLoginBtn.classList.add("hidden");
      if (navUserMenu) navUserMenu.classList.remove("hidden");
      if (navUserEmail) navUserEmail.innerText = email;
      if (dropdownUserEmail) dropdownUserEmail.innerText = email;
      if (navUserInitial) navUserInitial.innerText = initial;

      if (mobileLoginBtn) mobileLoginBtn.classList.add("hidden");
      if (mobileUserInfo) mobileUserInfo.classList.remove("hidden");
      if (mobileUserEmail) mobileUserEmail.innerText = email;
      if (mobileUserInitial) mobileUserInitial.innerText = initial;
    } else {
      if (navLoginBtn) navLoginBtn.classList.remove("hidden");
      if (navUserMenu) navUserMenu.classList.add("hidden");
      if (navUserDropdown) navUserDropdown.classList.add("hidden");
      if (navUserChevron) navUserChevron.classList.remove("rotate-180");

      if (mobileLoginBtn) mobileLoginBtn.classList.remove("hidden");
      if (mobileUserInfo) mobileUserInfo.classList.add("hidden");
    }
  }

  function setUserLoggedIn(email, name = "") {
    const users = getRegisteredUsers();
    const found = users.find(u => u.email.toLowerCase() === email.toLowerCase());
    const user = {
      email: email,
      name: name || (found ? found.name : email.split("@")[0]),
      initial: email.charAt(0).toUpperCase(),
      vip: true,
      points: found ? found.points : 450,
      joined: found ? found.joined : "15/01/2026"
    };

    AppState.currentUser = user;
    try {
      localStorage.setItem("cinego_user", JSON.stringify(user));
    } catch (e) {}

    renderAuthUI();
  }

  function setUserLoggedOut() {
    AppState.currentUser = null;
    try {
      localStorage.removeItem("cinego_user");
    } catch (e) {}

    renderAuthUI();
    closeUserProfileModal();
    closeTicketHistoryModal();
    showToast("Bạn đã đăng xuất thành công khỏi tài khoản CINEGO.", "info");
  }

  if (navUserBtn) {
    navUserBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      if (!navUserDropdown) return;
      const isClosed = navUserDropdown.classList.contains("hidden");
      if (isClosed) {
        navUserDropdown.classList.remove("hidden");
        if (navUserChevron) navUserChevron.classList.add("rotate-180");
      } else {
        navUserDropdown.classList.add("hidden");
        if (navUserChevron) navUserChevron.classList.remove("rotate-180");
      }
    });
  }

  document.addEventListener("click", (e) => {
    if (navUserDropdown && !navUserDropdown.classList.contains("hidden")) {
      if (!navUserMenu.contains(e.target)) {
        navUserDropdown.classList.add("hidden");
        if (navUserChevron) navUserChevron.classList.remove("rotate-180");
      }
    }
  });

  if (dropdownLogoutBtn) dropdownLogoutBtn.addEventListener("click", setUserLoggedOut);
  if (mobileLogoutBtn) mobileLogoutBtn.addEventListener("click", setUserLoggedOut);
  if (profileLogoutBtn) profileLogoutBtn.addEventListener("click", setUserLoggedOut);

  function openUserProfileModal() {
    if (!userProfileModal) return;
    if (navUserDropdown) navUserDropdown.classList.add("hidden");
    if (navUserChevron) navUserChevron.classList.remove("rotate-180");

    const user = AppState.currentUser || { email: "dinhan33316@gmail.com", name: "Nguyễn Đình An", points: 450 };
    const email = user.email || "dinhan33316@gmail.com";
    const initial = email.charAt(0).toUpperCase();

    const cardInitial = document.getElementById("profile-card-initial");
    const cardName = document.getElementById("profile-card-name");
    const cardEmail = document.getElementById("profile-card-email");
    const inputEmail = document.getElementById("profile-input-email");

    if (cardInitial) cardInitial.innerText = initial;
    if (cardName) cardName.innerText = user.name || "Nguyễn Đình An";
    if (cardEmail) cardEmail.innerText = email;
    if (inputEmail) inputEmail.value = email;

    userProfileModal.classList.add("active");
    document.body.classList.add("modal-open");
  }

  function closeUserProfileModal() {
    if (!userProfileModal) return;
    userProfileModal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }

  if (dropdownAccountBtn) dropdownAccountBtn.addEventListener("click", openUserProfileModal);
  if (mobileAccountBtn) mobileAccountBtn.addEventListener("click", openUserProfileModal);
  if (profileCloseBtn) profileCloseBtn.addEventListener("click", closeUserProfileModal);
  if (profileSaveBtn) {
    profileSaveBtn.addEventListener("click", () => {
      closeUserProfileModal();
      showToast("Đã lưu thông tin tài khoản thành công!", "success");
    });
  }
  if (userProfileModal) {
    userProfileModal.addEventListener("click", (e) => {
      if (e.target === userProfileModal) closeUserProfileModal();
    });
  }

  function renderTicketHistory() {
    if (!ticketHistoryList) return;

    if (userBookingHistory.length === 0) {
      ticketHistoryList.innerHTML = `
        <div class="py-12 text-center text-zinc-500">
          <svg class="w-12 h-12 mx-auto mb-2 text-zinc-600 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
          <p class="text-sm font-medium text-zinc-400">Bạn chưa có giao dịch đặt vé nào.</p>
        </div>
      `;
      return;
    }

    ticketHistoryList.innerHTML = userBookingHistory.map(ticket => {
      const isUpcoming = ticket.status.includes("Sắp") || ticket.status.includes("thanh toán");
      return `
        <div class="p-4 rounded-2xl bg-zinc-950/80 border border-zinc-800 hover:border-zinc-700 transition-all flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
          <div class="flex items-center gap-3.5 min-w-0">
            <img src="${ticket.poster}" alt="${ticket.movie}" class="w-14 h-20 rounded-xl object-cover shrink-0 bg-zinc-900 border border-zinc-800" />
            <div class="min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold ${isUpcoming ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-zinc-800 text-zinc-400'}">
                  ${ticket.status}
                </span>
                <span class="text-xs font-mono text-amber-400 font-bold">${ticket.code}</span>
              </div>
              <h4 class="text-sm font-bold text-white truncate">${ticket.movie}</h4>
              <p class="text-xs text-zinc-400 truncate mt-0.5">${ticket.cinema} • ${ticket.hall}</p>
              <div class="text-xs text-zinc-300 mt-1 flex items-center gap-3">
                <span>Suất: <strong class="text-white">${ticket.showtime}</strong></span>
                <span>Ghế: <strong class="text-cine-red">${ticket.seats}</strong></span>
              </div>
            </div>
          </div>

          <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-zinc-800 gap-2 shrink-0">
            <span class="text-sm font-extrabold text-white">${ticket.total.toLocaleString("vi-VN")} đ</span>
            <button
              type="button"
              onclick="showToast('Mã vé: ${ticket.code} - Hãy xuất trình mã này tại rạp!', 'info')"
              class="px-3 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold transition-colors">
              Xem Chi Tiết
            </button>
          </div>
        </div>
      `;
    }).join("");
  }

  function openTicketHistoryModal() {
    if (!ticketHistoryModal) return;
    if (navUserDropdown) navUserDropdown.classList.add("hidden");
    if (navUserChevron) navUserChevron.classList.remove("rotate-180");

    renderTicketHistory();
    ticketHistoryModal.classList.add("active");
    document.body.classList.add("modal-open");
  }

  function closeTicketHistoryModal() {
    if (!ticketHistoryModal) return;
    ticketHistoryModal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }

  window.closeTicketHistoryModal = closeTicketHistoryModal;
  if (dropdownHistoryBtn) dropdownHistoryBtn.addEventListener("click", openTicketHistoryModal);
  if (mobileHistoryBtn) mobileHistoryBtn.addEventListener("click", openTicketHistoryModal);
  if (historyCloseBtn) historyCloseBtn.addEventListener("click", closeTicketHistoryModal);
  if (ticketHistoryModal) {
    ticketHistoryModal.addEventListener("click", (e) => {
      if (e.target === ticketHistoryModal) closeTicketHistoryModal();
    });
  }

  window.openAuthModal = function() {
    if (!authModal) return;
    authModal.classList.add("active");
    document.body.classList.add("modal-open");
  };

  function closeAuthModal() {
    if (!authModal) return;
    authModal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }

  if (navLoginBtn) navLoginBtn.addEventListener("click", openAuthModal);
  if (mobileLoginBtn) mobileLoginBtn.addEventListener("click", openAuthModal);
  if (authCloseBtn) authCloseBtn.addEventListener("click", closeAuthModal);
  if (authModal) {
    authModal.addEventListener("click", (e) => {
      if (e.target === authModal) closeAuthModal();
    });
  }

  if (authLoginTab && authRegisterTab) {
    authLoginTab.addEventListener("click", () => {
      authLoginTab.className = "flex-1 py-3.5 text-sm font-bold text-white border-b-2 border-cine-red";
      authRegisterTab.className = "flex-1 py-3.5 text-sm font-medium text-zinc-400 border-b-2 border-transparent hover:text-white";
      authLoginForm.classList.remove("hidden");
      authRegisterForm.classList.add("hidden");
      if (document.getElementById("auth-forgot-container")) {
        document.getElementById("auth-forgot-container").classList.add("hidden");
      }
    });

    authRegisterTab.addEventListener("click", () => {
      authRegisterTab.className = "flex-1 py-3.5 text-sm font-bold text-white border-b-2 border-cine-red";
      authLoginTab.className = "flex-1 py-3.5 text-sm font-medium text-zinc-400 border-b-2 border-transparent hover:text-white";
      authRegisterForm.classList.remove("hidden");
      authLoginForm.classList.add("hidden");
      if (document.getElementById("auth-forgot-container")) {
        document.getElementById("auth-forgot-container").classList.add("hidden");
      }
    });
  }

  const authForgotLink = document.getElementById("auth-forgot-link");
  const authForgotContainer = document.getElementById("auth-forgot-container");
  const authBackToLoginBtn = document.getElementById("auth-back-to-login-btn");
  const authForgotStep1Form = document.getElementById("auth-forgot-step1-form");
  const authForgotStep2Form = document.getElementById("auth-forgot-step2-form");
  const authForgotEmail = document.getElementById("auth-forgot-email");
  const forgotTargetEmail = document.getElementById("forgot-target-email");
  const authForgotOtp = document.getElementById("auth-forgot-otp");
  const authForgotNewPass = document.getElementById("auth-forgot-newpass");
  const authForgotConfirmPass = document.getElementById("auth-forgot-confirmpass");
  const authForgotError = document.getElementById("auth-forgot-error");
  const authForgotErrorText = document.getElementById("auth-forgot-error-text");

  function showLoginFormView() {
    if (authLoginTab) authLoginTab.classList.remove("hidden");
    if (authRegisterTab) authRegisterTab.classList.remove("hidden");
    if (authLoginForm) authLoginForm.classList.remove("hidden");
    if (authRegisterForm) authRegisterForm.classList.add("hidden");
    if (authForgotContainer) authForgotContainer.classList.add("hidden");
  }

  if (authForgotLink) {
    authForgotLink.addEventListener("click", () => {
      if (authLoginForm) authLoginForm.classList.add("hidden");
      if (authRegisterForm) authRegisterForm.classList.add("hidden");
      if (authLoginTab) authLoginTab.classList.add("hidden");
      if (authRegisterTab) authRegisterTab.classList.add("hidden");

      if (authForgotContainer) authForgotContainer.classList.remove("hidden");
      if (authForgotStep1Form) authForgotStep1Form.classList.remove("hidden");
      if (authForgotStep2Form) authForgotStep2Form.classList.add("hidden");

      const currentLoginEmail = document.getElementById("auth-login-email");
      if (currentLoginEmail && currentLoginEmail.value.trim() && authForgotEmail) {
        authForgotEmail.value = currentLoginEmail.value.trim();
      }
    });
  }

  if (authBackToLoginBtn) {
    authBackToLoginBtn.addEventListener("click", showLoginFormView);
  }

  if (authForgotStep1Form) {
    authForgotStep1Form.addEventListener("submit", (e) => {
      e.preventDefault();
      const targetEmail = authForgotEmail ? authForgotEmail.value.trim() : "dinhan33316@gmail.com";
      if (!targetEmail) {
        showToast("Vui lòng nhập địa chỉ email hoặc số điện thoại!", "error");
        return;
      }

      if (forgotTargetEmail) forgotTargetEmail.innerText = targetEmail;

      authForgotStep1Form.classList.add("hidden");
      if (authForgotStep2Form) {
        authForgotStep2Form.classList.remove("hidden");
        if (authForgotOtp) authForgotOtp.value = "882319";
      }

      showToast(`Mã OTP xác thực 6 số (mã: 882319) đã được gửi đến email ${targetEmail}!`, "success");
    });
  }

  if (authForgotStep2Form) {
    authForgotStep2Form.addEventListener("submit", (e) => {
      e.preventDefault();
      const targetEmail = authForgotEmail ? authForgotEmail.value.trim() : "dinhan33316@gmail.com";
      const otp = authForgotOtp ? authForgotOtp.value.trim() : "";
      const newPass = authForgotNewPass ? authForgotNewPass.value : "";
      const confirmPass = authForgotConfirmPass ? authForgotConfirmPass.value : "";

      if (otp.length !== 6) {
        if (authForgotError && authForgotErrorText) {
          authForgotError.classList.remove("hidden");
          authForgotErrorText.innerText = "Mã OTP phải gồm 6 chữ số hợp lệ (Mã thử nghiệm: 882319).";
        }
        showToast("Mã OTP không hợp lệ!", "error");
        return;
      }

      if (newPass.length < 6) {
        if (authForgotError && authForgotErrorText) {
          authForgotError.classList.remove("hidden");
          authForgotErrorText.innerText = "Mật khẩu mới phải có độ dài ít nhất 6 ký tự.";
        }
        showToast("Mật khẩu mới quá ngắn!", "error");
        return;
      }

      if (newPass !== confirmPass) {
        if (authForgotError && authForgotErrorText) {
          authForgotError.classList.remove("hidden");
          authForgotErrorText.innerText = "Mật khẩu xác nhận không trùng khớp với mật khẩu mới.";
        }
        showToast("Mật khẩu xác nhận không khớp!", "error");
        return;
      }

      const users = getRegisteredUsers();
      const cleanEmail = targetEmail.toLowerCase();
      let user = users.find(u => u.email.toLowerCase() === cleanEmail || u.phone === targetEmail);
      if (user) {
        user.password = newPass;
      } else {
        user = {
          email: targetEmail,
          phone: "0901234567",
          password: newPass,
          name: targetEmail.split("@")[0],
          points: 100,
          vip: true,
          joined: "Hôm nay"
        };
        users.push(user);
      }
      saveRegisteredUsers(users);

      if (authForgotError) authForgotError.classList.add("hidden");

      showLoginFormView();
      const loginEmailField = document.getElementById("auth-login-email");
      const loginPassField = document.getElementById("auth-login-password");
      if (loginEmailField) loginEmailField.value = targetEmail;
      if (loginPassField) loginPassField.value = newPass;

      showToast("Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay với mật khẩu mới.", "success");
    });
  }

  if (authLoginForm) {
    authLoginForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const emailInput = document.getElementById("auth-login-email");
      const passwordInput = document.getElementById("auth-login-password");
      const errorBox = document.getElementById("auth-login-error");
      const errorText = document.getElementById("auth-login-error-text");

      const identifier = emailInput ? emailInput.value.trim() : "";
      const password = passwordInput ? passwordInput.value : "";

      if (!identifier || !password) {
        if (errorBox && errorText) {
          errorBox.classList.remove("hidden");
          errorText.innerText = "Vui lòng nhập đầy đủ email/số điện thoại và mật khẩu.";
        }
        return;
      }

      const users = getRegisteredUsers();
      const cleanInput = identifier.toLowerCase().replace(/[\s\-\.]/g, "");

      const matchedUser = users.find(u => {
        const uEmail = (u.email || "").toLowerCase().replace(/[\s\-\.]/g, "");
        const uPhone = (u.phone || "").replace(/[\s\-\.]/g, "");
        return uEmail === cleanInput || uPhone === cleanInput;
      });

      if (!matchedUser || matchedUser.password !== password) {
        if (errorBox && errorText) {
          errorBox.classList.remove("hidden");
          errorText.innerText = "Sai mật khẩu hoặc tài khoản không tồn tại.";
        }
        if (passwordInput) {
          passwordInput.classList.add("border-red-500", "focus:border-red-500");
          passwordInput.classList.remove("border-zinc-700");
          passwordInput.focus();
        }
        showToast("Sai mật khẩu hoặc tài khoản không tồn tại", "error");
        return;
      }

      if (errorBox) errorBox.classList.add("hidden");
      if (passwordInput) {
        passwordInput.classList.remove("border-red-500", "focus:border-red-500");
        passwordInput.classList.add("border-zinc-700");
      }

      closeAuthModal();
      setUserLoggedIn(matchedUser.email, matchedUser.name);
      showToast(`Đăng nhập thành công! Chào mừng ${matchedUser.name}`, "success");
    });
  }

  if (authRegisterForm) {
    authRegisterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const nameInput = document.getElementById("auth-reg-name");
      const phoneInput = document.getElementById("auth-reg-phone");
      const emailInput = document.getElementById("auth-reg-email");
      const passInput = document.getElementById("auth-reg-password");
      const regErrorBox = document.getElementById("auth-reg-error");
      const regErrorText = document.getElementById("auth-reg-error-text");

      const name = nameInput ? nameInput.value.trim() : "Khách Hàng Mới";
      const phone = phoneInput ? phoneInput.value.trim() : "";
      const email = emailInput ? emailInput.value.trim() : "";
      const password = passInput ? passInput.value : "";

      if (password.length < 6) {
        if (regErrorBox && regErrorText) {
          regErrorBox.classList.remove("hidden");
          regErrorText.innerText = "Mật khẩu phải có độ dài tối thiểu từ 6 ký tự.";
        }
        showToast("Mật khẩu phải có tối thiểu 6 ký tự!", "error");
        return;
      }

      const users = getRegisteredUsers();
      const existing = users.find(u => u.email.toLowerCase() === email.toLowerCase());
      if (existing) {
        if (regErrorBox && regErrorText) {
          regErrorBox.classList.remove("hidden");
          regErrorText.innerText = "Email này đã được sử dụng. Vui lòng đăng nhập hoặc chọn email khác.";
        }
        showToast("Email này đã được đăng ký tài khoản!", "error");
        return;
      }

      const newUser = {
        email: email,
        phone: phone,
        password: password,
        name: name,
        points: 100,
        vip: true,
        joined: "Hôm nay"
      };
      users.push(newUser);
      saveRegisteredUsers(users);

      if (regErrorBox) regErrorBox.classList.add("hidden");

      closeAuthModal();
      setUserLoggedIn(email, name);
      showToast("Đăng ký thành công! Bạn nhận được voucher CINEGO50 giảm 50% vé đầu.", "success");
    });
  }

  renderAuthUI();

  const getBookingModal = () => document.getElementById("booking-modal");
  const getBookingCloseBtn = () => document.getElementById("booking-close-btn");

  const stepContainers = {
    1: document.getElementById("booking-step-1"),
    2: document.getElementById("booking-step-2"),
    3: document.getElementById("booking-step-3"),
    4: document.getElementById("booking-step-4"),
    5: document.getElementById("booking-step-5")
  };

  const stepIndicators = document.querySelectorAll(".booking-step-pill");

  function openBookingModal(initialData = {}) {
    const modal = getBookingModal();
    if (!modal) {
      console.warn("[Cinego] Không tìm thấy phần tử #booking-modal trong DOM!");
      return;
    }

    const targetMovieTitle = (initialData.movieTitle || "").trim().toLowerCase();
    let selectedMovie = null;
    const nowShowingList = (window.CINEGO_DATA && Array.isArray(CINEGO_DATA.nowShowing))
      ? CINEGO_DATA.nowShowing
      : [];

    if (targetMovieTitle && nowShowingList.length > 0) {
      selectedMovie = nowShowingList.find(m => {
        const t1 = (m.title || "").toLowerCase();
        const t2 = (m.vietnameseTitle || "").toLowerCase();
        const t3 = (m.vietnamese_title || "").toLowerCase();
        return t1 === targetMovieTitle || t2 === targetMovieTitle || t3 === targetMovieTitle ||
               t1.includes(targetMovieTitle) || targetMovieTitle.includes(t1);
      });
    }
    if (!selectedMovie && nowShowingList.length > 0) {
      selectedMovie = nowShowingList[0];
    }
    if (!selectedMovie) {
      selectedMovie = {
        id: "default-movie",
        title: initialData.movieTitle || "Deadpool & Wolverine",
        vietnameseTitle: initialData.movieTitle || "Deadpool & Wolverine",
        genres: ["Hành động", "Khoa học viễn tưởng"],
        duration: "128 phút",
        rating: 9.3,
        ageRating: "T18",
        formats: ["2D", "IMAX"],
        poster: "https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg"
      };
    }

    const cinemaList = (window.CINEGO_DATA && Array.isArray(CINEGO_DATA.cinemas)) ? CINEGO_DATA.cinemas : [];
    const defaultCinema = cinemaList[0] || { id: "cine-1", name: "CINEGO Landmark 81 IMAX", city: "Hồ Chí Minh" };
    const selectedCinema = initialData.cinemaName
      ? (cinemaList.find(c => c.name === initialData.cinemaName) || defaultCinema)
      : defaultCinema;

    const defaultDate = (dateOptions && dateOptions.length > 0 && dateOptions[0].value)
      ? dateOptions[0].value
      : (window.dateOptions && window.dateOptions[0] ? window.dateOptions[0].value : `${String(new Date().getDate()).padStart(2, "0")}/${String(new Date().getMonth() + 1).padStart(2, "0")}/2026`);
    const selectedDate = initialData.date || defaultDate;

    const showtimeList = (window.CINEGO_DATA && Array.isArray(CINEGO_DATA.showtimes)) ? CINEGO_DATA.showtimes : [];
    const defaultShowtime = showtimeList[2] || showtimeList[0] || { time: "14:15", format: "2D Phụ đề", room: "Cinema 01 (Laser)", price: 95000 };

    let custName = "Nguyễn Đình An";
    let custPhone = "0901 234 567";
    let custEmail = "dinhan33316@gmail.com";

    if (AppState.currentUser) {
      if (AppState.currentUser.name) custName = AppState.currentUser.name;
      if (AppState.currentUser.phone) custPhone = AppState.currentUser.phone;
      if (AppState.currentUser.email) custEmail = AppState.currentUser.email;
    } else if (window.CINEGO_COOKIES) {
      if (window.CINEGO_COOKIES.name) custName = window.CINEGO_COOKIES.name;
      if (window.CINEGO_COOKIES.phone) custPhone = window.CINEGO_COOKIES.phone;
      if (window.CINEGO_COOKIES.email) custEmail = window.CINEGO_COOKIES.email;
    }

    AppState.booking = {
      step: 1,
      movie: selectedMovie,
      cinema: selectedCinema,
      date: selectedDate,
      showtime: defaultShowtime,
      seats: [],
      combos: {},
      discount: 0,
      appliedCode: "",
      customer: {
        name: custName,
        phone: custPhone,
        email: custEmail
      },
      paymentMethod: "momo",
      bookingCode: "CGO-" + Math.floor(100000 + Math.random() * 900000)
    };

    updateBookingUI();
    modal.classList.add("active");
    document.body.classList.add("modal-open");
  }
  window.openBookingModal = openBookingModal;

  function closeBookingModal() {
    const modal = getBookingModal();
    if (!modal) return;
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
  }
  window.closeBookingModal = closeBookingModal;

  const initialCloseBtn = getBookingCloseBtn();
  if (initialCloseBtn) initialCloseBtn.addEventListener("click", closeBookingModal);
  const initialModalEl = getBookingModal();
  if (initialModalEl) {
    initialModalEl.addEventListener("click", (e) => {
      if (e.target === initialModalEl) closeBookingModal();
    });
  }

  function goToBookingStep(step) {
    AppState.booking.step = step;
    updateBookingUI();
  }

  function updateBookingUI() {
    const step = AppState.booking ? AppState.booking.step : 1;

    const currentStepContainers = {
      1: document.getElementById("booking-step-1"),
      2: document.getElementById("booking-step-2"),
      3: document.getElementById("booking-step-3"),
      4: document.getElementById("booking-step-4"),
      5: document.getElementById("booking-step-5")
    };

    Object.keys(currentStepContainers).forEach(k => {
      const container = currentStepContainers[k];
      if (!container) return;
      if (parseInt(k, 10) === step) {
        container.classList.remove("hidden");
      } else {
        container.classList.add("hidden");
      }
    });

    const pills = document.querySelectorAll(".booking-step-pill");
    pills.forEach((pill, idx) => {
      const pillStep = idx + 1;
      if (pillStep === step) {
        pill.className = "booking-step-pill flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-cine-red text-white shadow-md shadow-red-600/30";
      } else if (pillStep < step) {
        pill.className = "booking-step-pill flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30";
      } else {
        pill.className = "booking-step-pill flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-medium bg-zinc-800 text-zinc-500";
      }
    });

    try {
      if (step === 1) renderBookingStep1();
      else if (step === 2) renderBookingStep2();
      else if (step === 3) renderBookingStep3();
      else if (step === 4) renderBookingStep4();
      else if (step === 5) renderBookingStep5();
    } catch (e) {
      console.error("[Cinego Booking Step Render Error]:", e);
    }
  }

  function renderBookingStep1() {
    if (!AppState.booking || !AppState.booking.movie) return;

    const movieSelect = document.getElementById("bstep1-movie");
    const cinemaSelect = document.getElementById("bstep1-cinema");
    const datesContainer = document.getElementById("bstep1-dates");
    const showtimesContainer = document.getElementById("bstep1-showtimes");
    const moviePoster = document.getElementById("bstep1-poster");
    const movieTitle = document.getElementById("bstep1-title");
    const movieMeta = document.getElementById("bstep1-meta");

    const currentMovie = AppState.booking.movie;
    const currentCinema = AppState.booking.cinema || (CINEGO_DATA.cinemas && CINEGO_DATA.cinemas[0]);

    if (movieSelect && Array.isArray(CINEGO_DATA.nowShowing)) {
      movieSelect.innerHTML = CINEGO_DATA.nowShowing.map(m =>
        `<option value="${m.id}" ${m.id === currentMovie.id ? "selected" : ""}>${m.title} (${m.ageRating || m.age_limit || 'P'})</option>`
      ).join("");

      movieSelect.onchange = (e) => {
        const found = CINEGO_DATA.nowShowing.find(m => m.id === e.target.value);
        if (found) {
          AppState.booking.movie = found;
          renderBookingStep1();
        }
      };
    }

    if (cinemaSelect && Array.isArray(CINEGO_DATA.cinemas)) {
      const currentCinemaId = currentCinema ? currentCinema.id : "";
      cinemaSelect.innerHTML = CINEGO_DATA.cinemas.map(c =>
        `<option value="${c.id}" ${c.id === currentCinemaId ? "selected" : ""}>${c.name} - ${c.city}</option>`
      ).join("");

      cinemaSelect.onchange = (e) => {
        const found = CINEGO_DATA.cinemas.find(c => c.id === e.target.value);
        if (found) {
          AppState.booking.cinema = found;
          renderBookingStep1();
        }
      };
    }

    if (moviePoster) moviePoster.src = currentMovie.poster || currentMovie.movie_poster || "";
    if (movieTitle) movieTitle.innerText = currentMovie.title || "";
    if (movieMeta) {
      const genresList = Array.isArray(currentMovie.genres)
        ? currentMovie.genres
        : (currentMovie.genres ? [currentMovie.genres] : (currentMovie.genre ? currentMovie.genre.split(",") : ["Hành động"]));
      const duration = currentMovie.duration || "120 phút";
      const rating = currentMovie.rating ? `${currentMovie.rating}` : "9.0";
      movieMeta.innerText = `${genresList.map(g => g.trim()).join(", ")} • ${duration} • Điểm ${rating}/10`;
    }

    const availableDates = (Array.isArray(dateOptions) && dateOptions.length > 0)
      ? dateOptions
      : (window.dateOptions || []);

    if (datesContainer && availableDates.length > 0) {
      datesContainer.innerHTML = availableDates.map((d, idx) => {
        const isSelected = d.value === AppState.booking.date;
        return `
          <button
            type="button"
            class="bstep1-date-btn shrink-0 px-4 py-2.5 rounded-xl text-xs font-semibold border transition-all ${
              isSelected
                ? "bg-cine-red text-white border-cine-red shadow-lg shadow-red-600/30"
                : "bg-zinc-800/80 text-zinc-300 border-zinc-700 hover:border-zinc-500"
            }"
            data-date="${d.value}">
            ${d.label}
          </button>
        `;
      }).join("");

      datesContainer.querySelectorAll(".bstep1-date-btn").forEach(btn => {
        btn.onclick = () => {
          AppState.booking.date = btn.getAttribute("data-date");
          renderBookingStep1();
        };
      });
    }

    if (showtimesContainer && Array.isArray(CINEGO_DATA.showtimes)) {
      showtimesContainer.innerHTML = CINEGO_DATA.showtimes.map((st, idx) => {
        const isSelected = AppState.booking.showtime && AppState.booking.showtime.time === st.time;
        const formatText = st.format || "2D";
        const isImax = formatText.includes("IMAX");
        return `
          <button
            type="button"
            class="bstep1-time-btn p-3.5 rounded-2xl border text-left transition-all ${
              isSelected
                ? "bg-cine-red/20 border-cine-red text-white shadow-[0_0_15px_rgba(229,9,20,0.3)]"
                : "bg-zinc-800/60 border-zinc-700/60 text-zinc-300 hover:border-zinc-500 hover:bg-zinc-800"
            }"
            data-index="${idx}">
            <div class="flex items-center justify-between mb-1">
              <span class="text-base font-extrabold text-white">${st.time}</span>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded ${isImax ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-zinc-700 text-zinc-300'}">${formatText}</span>
            </div>
            <div class="text-[11px] text-zinc-400">${st.room}</div>
            <div class="text-xs font-bold text-cine-red mt-2">${(st.price || 95000).toLocaleString("vi-VN")} đ</div>
          </button>
        `;
      }).join("");

      showtimesContainer.querySelectorAll(".bstep1-time-btn").forEach(btn => {
        btn.onclick = () => {
          const idx = parseInt(btn.getAttribute("data-index"), 10);
          if (CINEGO_DATA.showtimes[idx]) {
            AppState.booking.showtime = CINEGO_DATA.showtimes[idx];
            renderBookingStep1();
          }
        };
      });
    }

    const nextBtn = document.getElementById("bstep1-next-btn");
    if (nextBtn) {
      nextBtn.onclick = () => {
        if (!AppState.booking.showtime) {
          showToast("Vui lòng chọn khung giờ chiếu!", "error");
          return;
        }
        fetchOccupiedSeats(() => {
          goToBookingStep(2);
        });
      };
    }
  }

  function fetchOccupiedSeats(callback) {
    if (!AppState.booking || !AppState.booking.movie) {
      if (callback) callback();
      return;
    }
    const movieTitle = AppState.booking.movie.title;
    const showtime = AppState.booking.showtime ? `${AppState.booking.showtime.time} • ${AppState.booking.date}` : "";
    const apiUrl = (window.CINEGO_ROUTES && window.CINEGO_ROUTES.api) ? window.CINEGO_ROUTES.api : "/api";

    fetch(`${apiUrl}?action=get_occupied_seats&movie=${encodeURIComponent(movieTitle)}&showtime=${encodeURIComponent(showtime)}`, {
      headers: {
        "Accept": "application/json",
        "X-CSRF-TOKEN": csrfToken || window.CINEGO_CSRF_TOKEN || ""
      }
    })
      .then(res => res.json())
      .then(data => {
        if (data.success && Array.isArray(data.occupiedSeats)) {
          AppState.booking.occupiedSeats = data.occupiedSeats;
          console.log(`%c[CINEGO Real-time] Đã nạp ${data.occupiedSeats.length} vị trí ghế đã bán từ MySQL:`, "color: #E50914; font-weight: bold;", data.occupiedSeats);
        } else {
          AppState.booking.occupiedSeats = [];
        }
        if (callback) callback();
      })
      .catch(err => {
        console.warn("[CINEGO Real-time] Lỗi truy vấn API khóa ghế:", err);
        if (callback) callback();
      });
  }

  function renderBookingStep2() {
    const seatMapGrid = document.getElementById("seat-map-grid");
    const summarySelectedSeats = document.getElementById("bstep2-selected-seats");
    const summarySubtotal = document.getElementById("bstep2-subtotal");
    const nextBtn = document.getElementById("bstep2-next-btn");
    const backBtn = document.getElementById("bstep2-back-btn");

    if (backBtn) backBtn.onclick = () => goToBookingStep(1);

    const rows = ["A", "B", "C", "D", "E", "F", "G", "H"];
    const basePrice = AppState.booking.showtime ? AppState.booking.showtime.price : 95000;

    const seed = AppState.booking.showtime ? AppState.booking.showtime.time.charCodeAt(1) : 5;
    const isOccupiedSeat = (r, c) => {
      const seatId1 = `${r}${c}`;
      const seatId2 = `${r}${c < 10 ? '0' + c : c}`;
      const dbOccupied = AppState.booking.occupiedSeats || [];
      if (dbOccupied.includes(seatId1) || dbOccupied.includes(seatId2)) {
        return true;
      }
      return ((r.charCodeAt(0) * 7 + c * 13 + seed) % 7) === 0;
    };

    if (AppState.booking.seats && AppState.booking.seats.length > 0) {
      AppState.booking.seats = AppState.booking.seats.filter(s => {
        const r = s.row;
        const c = s.col;
        return !isOccupiedSeat(r, c);
      });
    }

    if (seatMapGrid) {
      let gridHtml = "";
      rows.forEach(row => {
        gridHtml += `<div class="flex items-center justify-center gap-1.5 sm:gap-2 mb-1.5">`;
        gridHtml += `<span class="w-5 text-center text-xs font-bold text-zinc-500">${row}</span>`;

        for (let col = 1; col <= 12; col++) {
          const seatId = `${row}${col}`;
          let seatType = "Standard";
          let seatPrice = basePrice;

          if (row === "D" || row === "E" || row === "F" || row === "G") {
            seatType = "VIP";
            seatPrice = basePrice + 25000;
          } else if (row === "H") {
            seatType = "Sweetbox";
            seatPrice = (basePrice * 2) + 30000;
          }

          const isOccupied = isOccupiedSeat(row, col);
          const isSelected = AppState.booking.seats.some(s => s.id === seatId);

          let seatClass = "seat-btn w-6 h-6 sm:w-8 sm:h-8 rounded-t-lg rounded-b text-[10px] font-bold flex items-center justify-center transition-all cursor-pointer ";
          if (isOccupied) {
            seatClass += "occupied bg-red-950/40 text-red-500/70 border border-red-900/60 cursor-not-allowed opacity-80 shadow-inner";
          } else if (isSelected) {
            seatClass += "selected bg-cine-red text-white border border-cine-red";
          } else {
            if (seatType === "Sweetbox") {
              seatClass += "bg-pink-950/40 border border-pink-500/50 text-pink-300 hover:bg-pink-900/60";
            } else if (seatType === "VIP") {
              seatClass += "bg-amber-950/40 border border-amber-500/50 text-amber-300 hover:bg-amber-900/60";
            } else {
              seatClass += "bg-zinc-800 border border-zinc-700 text-zinc-300 hover:bg-zinc-700";
            }
          }

          gridHtml += `
            <button
              type="button"
              class="${seatClass}"
              data-id="${seatId}"
              data-row="${row}"
              data-col="${col}"
              data-type="${seatType}"
              data-price="${seatPrice}"
              ${isOccupied ? "disabled" : ""}
              title="${isOccupied ? `Ghế ${seatId} (${seatType}) - ĐÃ BÁN (Không thể chọn)` : `Ghế ${seatId} (${seatType}) - ${seatPrice.toLocaleString('vi-VN')}đ`}">
              ${col}
            </button>
          `;
        }

        gridHtml += `<span class="w-5 text-center text-xs font-bold text-zinc-500">${row}</span>`;
        gridHtml += `</div>`;
      });

      seatMapGrid.innerHTML = gridHtml;

      seatMapGrid.querySelectorAll(".seat-btn:not(.occupied)").forEach(btn => {
        btn.onclick = () => {
          const seatId = btn.getAttribute("data-id");
          const row = btn.getAttribute("data-row");
          const col = parseInt(btn.getAttribute("data-col"), 10);
          const type = btn.getAttribute("data-type");
          const price = parseInt(btn.getAttribute("data-price"), 10);

          const existingIndex = AppState.booking.seats.findIndex(s => s.id === seatId);
          if (existingIndex >= 0) {
            AppState.booking.seats.splice(existingIndex, 1);
          } else {
            if (AppState.booking.seats.length >= 8) {
              showToast("Bạn chỉ có thể chọn tối đa 8 ghế trong một giao dịch!", "error");
              return;
            }
            AppState.booking.seats.push({ id: seatId, row, col, type, price });
          }

          renderBookingStep2();
        };
      });
    }

    const seatSubtotal = AppState.booking.seats.reduce((sum, s) => sum + s.price, 0);

    if (summarySelectedSeats) {
      if (AppState.booking.seats.length === 0) {
        summarySelectedSeats.innerHTML = `<span class="text-zinc-500 italic">Chưa chọn ghế nào</span>`;
      } else {
        summarySelectedSeats.innerHTML = AppState.booking.seats.map(s =>
          `<span class="px-2.5 py-1 rounded bg-zinc-800 text-white text-xs font-bold border border-zinc-700">${s.id}</span>`
        ).join(" ");
      }
    }

    if (summarySubtotal) {
      summarySubtotal.innerText = `${seatSubtotal.toLocaleString("vi-VN")} đ`;
    }

    if (nextBtn) {
      nextBtn.onclick = () => {
        if (AppState.booking.seats.length === 0) {
          showToast("Vui lòng chọn ít nhất một vị trí ghế ngồi!", "error");
          return;
        }
        goToBookingStep(3);
      };
    }
  }

  window.refreshOccupiedSeats = function(cb) {
    fetchOccupiedSeats(() => {
      const seatMapGrid = document.getElementById("seat-map-grid");
      if (seatMapGrid) {
        renderBookingStep2();
      }
      if (cb) cb();
    });
  };

  function renderBookingStep3() {
    const combosContainer = document.getElementById("bstep3-combos-list");
    const backBtn = document.getElementById("bstep3-back-btn");
    const nextBtn = document.getElementById("bstep3-next-btn");
    const skipBtn = document.getElementById("bstep3-skip-btn");
    const summaryTotal = document.getElementById("bstep3-subtotal");

    if (backBtn) backBtn.onclick = () => goToBookingStep(2);
    if (skipBtn) skipBtn.onclick = () => goToBookingStep(4);
    if (nextBtn) nextBtn.onclick = () => goToBookingStep(4);

    if (combosContainer) {
      combosContainer.innerHTML = CINEGO_DATA.combos.map(combo => {
        const qty = AppState.booking.combos[combo.id] || 0;
        return `
          <div class="flex items-center justify-between p-4 rounded-2xl bg-zinc-800/50 border border-zinc-700/60 hover:border-zinc-500 transition-all gap-4">
            <img src="${combo.image}" alt="${combo.name}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl object-cover shrink-0 bg-zinc-900 border border-zinc-700" />
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-amber-500/20 text-amber-300 border border-amber-500/30">${combo.badge}</span>
              </div>
              <h4 class="text-sm font-bold text-white truncate">${combo.name}</h4>
              <p class="text-xs text-zinc-400 line-clamp-1 mt-0.5">${combo.desc}</p>
              <div class="text-sm font-extrabold text-cine-red mt-1">${combo.price.toLocaleString("vi-VN")} đ</div>
            </div>

            <div class="flex items-center gap-2 shrink-0 bg-zinc-900/80 p-1.5 rounded-xl border border-zinc-700">
              <button
                type="button"
                class="combo-minus-btn w-7 h-7 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white flex items-center justify-center font-bold text-sm transition-colors"
                data-id="${combo.id}">
                -
              </button>
              <span class="w-6 text-center text-sm font-bold text-white">${qty}</span>
              <button
                type="button"
                class="combo-plus-btn w-7 h-7 rounded-lg bg-cine-red hover:bg-cine-red-hover text-white flex items-center justify-center font-bold text-sm transition-colors"
                data-id="${combo.id}">
                +
              </button>
            </div>
          </div>
        `;
      }).join("");

      combosContainer.querySelectorAll(".combo-minus-btn").forEach(btn => {
        btn.onclick = () => {
          const id = btn.getAttribute("data-id");
          if (AppState.booking.combos[id] && AppState.booking.combos[id] > 0) {
            AppState.booking.combos[id]--;
            if (AppState.booking.combos[id] === 0) delete AppState.booking.combos[id];
            renderBookingStep3();
          }
        };
      });

      combosContainer.querySelectorAll(".combo-plus-btn").forEach(btn => {
        btn.onclick = () => {
          const id = btn.getAttribute("data-id");
          AppState.booking.combos[id] = (AppState.booking.combos[id] || 0) + 1;
          renderBookingStep3();
        };
      });
    }

    const seatTotal = AppState.booking.seats.reduce((sum, s) => sum + s.price, 0);
    const comboTotal = Object.keys(AppState.booking.combos).reduce((sum, id) => {
      const c = CINEGO_DATA.combos.find(item => item.id === id);
      return sum + (c ? c.price * AppState.booking.combos[id] : 0);
    }, 0);

    if (summaryTotal) {
      summaryTotal.innerText = `${(seatTotal + comboTotal).toLocaleString("vi-VN")} đ`;
    }
  }

  function renderBookingStep4() {
    const summaryDetails = document.getElementById("bstep4-summary-details");
    const discountInput = document.getElementById("bstep4-discount-input");
    const applyDiscountBtn = document.getElementById("bstep4-apply-discount-btn");
    const totalAmount = document.getElementById("bstep4-total-amount");
    const backBtn = document.getElementById("bstep4-back-btn");
    const checkoutBtn = document.getElementById("bstep4-checkout-btn");

    if (backBtn) backBtn.onclick = () => goToBookingStep(3);

    const seatTotal = AppState.booking.seats.reduce((sum, s) => sum + s.price, 0);
    const comboTotal = Object.keys(AppState.booking.combos).reduce((sum, id) => {
      const c = CINEGO_DATA.combos.find(item => item.id === id);
      return sum + (c ? c.price * AppState.booking.combos[id] : 0);
    }, 0);

    const subtotal = seatTotal + comboTotal;
    const convenienceFee = 5000;
    const finalTotal = Math.max(0, subtotal + convenienceFee - AppState.booking.discount);

    if (summaryDetails) {
      const seatNames = AppState.booking.seats.map(s => s.id).join(", ");
      const comboSummary = Object.keys(AppState.booking.combos).map(id => {
        const c = CINEGO_DATA.combos.find(item => item.id === id);
        return `<div>${c.name} x ${AppState.booking.combos[id]} (${(c.price * AppState.booking.combos[id]).toLocaleString('vi-VN')} đ)</div>`;
      }).join("");

      summaryDetails.innerHTML = `
        <div class="space-y-3 text-xs">
          <div class="flex justify-between text-zinc-300">
            <span>Phim:</span>
            <strong class="text-white">${AppState.booking.movie.title}</strong>
          </div>
          <div class="flex justify-between text-zinc-300">
            <span>Rạp chiếu:</span>
            <strong class="text-white">${AppState.booking.cinema.name}</strong>
          </div>
          <div class="flex justify-between text-zinc-300">
            <span>Suất chiếu:</span>
            <strong class="text-white">${AppState.booking.showtime.time} - ${AppState.booking.date} (${AppState.booking.showtime.format})</strong>
          </div>
          <div class="flex justify-between text-zinc-300">
            <span>Ghế đã chọn (${AppState.booking.seats.length}):</span>
            <strong class="text-cine-red font-bold">${seatNames}</strong>
          </div>
          <div class="border-t border-zinc-700/60 pt-2 flex justify-between text-zinc-300">
            <span>Tiền vé:</span>
            <span>${seatTotal.toLocaleString('vi-VN')} đ</span>
          </div>
          ${comboTotal > 0 ? `
            <div class="flex justify-between text-zinc-300">
              <span>Bắp nước:</span>
              <span>${comboTotal.toLocaleString('vi-VN')} đ</span>
            </div>
            <div class="text-[11px] text-zinc-400 pl-2 border-l-2 border-zinc-700">
              ${comboSummary}
            </div>
          ` : ""}
          <div class="flex justify-between text-zinc-300">
            <span>Phí tiện ích dịch vụ:</span>
            <span>${convenienceFee.toLocaleString('vi-VN')} đ</span>
          </div>
          ${AppState.booking.discount > 0 ? `
            <div class="flex justify-between text-emerald-400 font-semibold">
              <span>Giảm giá voucher (${AppState.booking.appliedCode}):</span>
              <span>-${AppState.booking.discount.toLocaleString('vi-VN')} đ</span>
            </div>
          ` : ""}
        </div>
      `;
    }

    if (totalAmount) {
      totalAmount.innerText = `${finalTotal.toLocaleString("vi-VN")} đ`;
    }

    if (applyDiscountBtn && discountInput) {
      applyDiscountBtn.onclick = () => {
        const code = discountInput.value.trim().toUpperCase();
        if (code === "CINEGO50") {
          AppState.booking.discount = Math.floor(subtotal * 0.5);
          AppState.booking.appliedCode = code;
          showToast("Áp dụng mã CINEGO50 thành công! Giảm 50% tổng đơn.", "success");
          renderBookingStep4();
        } else if (code === "HAPPYWED" || code === "STUDENT45") {
          AppState.booking.discount = 30000;
          AppState.booking.appliedCode = code;
          showToast(`Áp dụng mã ${code} thành công! Giảm 30.000đ.`, "success");
          renderBookingStep4();
        } else {
          showToast("Mã ưu đãi không hợp lệ hoặc đã hết hạn!", "error");
        }
      };
    }

    const paymentMethods = document.querySelectorAll(".payment-method-radio");
    paymentMethods.forEach(rad => {
      rad.onchange = () => {
        AppState.booking.paymentMethod = rad.value;
      };
    });

    if (checkoutBtn) {
      checkoutBtn.onclick = () => {
        const nameField = document.getElementById("bstep4-name");
        const phoneField = document.getElementById("bstep4-phone");
        const emailField = document.getElementById("bstep4-email");

        if (nameField && nameField.value.trim()) AppState.booking.customer.name = nameField.value.trim();
        if (phoneField && phoneField.value.trim()) AppState.booking.customer.phone = phoneField.value.trim();
        if (emailField && emailField.value.trim()) AppState.booking.customer.email = emailField.value.trim();

        showToast("Đang xác nhận giao dịch & gửi vé điện tử đến email...", "info");

        setTimeout(() => {
          goToBookingStep(5);
        }, 600);
      };
    }
  }

  function sendTicketEmail(bookingData, finalTotal) {
    const paymentNames = {
      momo: "Ví MoMo (Đã thanh toán)",
      vnpay: "VNPAY QR (Đã thanh toán)",
      card: "Thẻ Quốc Tế Visa / Mastercard (Đã thanh toán)"
    };

    const comboNames = Object.keys(bookingData.combos).map(id => {
      const c = CINEGO_DATA.combos.find(item => item.id === id);
      return `${c ? c.name : id} x ${bookingData.combos[id]}`;
    }).join(", ") || "Không có";

    const payload = {
      ticketCode: bookingData.bookingCode,
      customerName: bookingData.customer.name || "Nguyễn Đình An",
      customerEmail: bookingData.customer.email || "dinhan33316@gmail.com",
      customerPhone: bookingData.customer.phone || "0901 234 567",
      movieTitle: bookingData.movie.title,
      moviePoster: bookingData.movie.poster,
      cinemaName: bookingData.cinema.name,
      hallName: bookingData.showtime.room,
      showtime: `${bookingData.showtime.time} • ${bookingData.date}`,
      seats: bookingData.seats.map(s => s.id).join(", "),
      format: bookingData.showtime.format,
      combos: comboNames,
      totalAmount: `${finalTotal.toLocaleString("vi-VN")} đ`,
      paymentMethod: paymentNames[bookingData.paymentMethod] || "Ví điện tử"
    };

    const ticketRoute = (window.CINEGO_ROUTES && window.CINEGO_ROUTES.ticket) ? window.CINEGO_ROUTES.ticket : "/send-ticket";

    fetch(ticketRoute, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": csrfToken || window.CINEGO_CSRF_TOKEN || ""
      },
      body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(resData => {
      console.log("CINEGO Ticket & Email Response:", resData);

      if (resData.conflict) {
        showToast(resData.message || "Ghế bạn chọn vừa có khách đặt trước! Vui lòng chọn ghế khác.", "error");
        fetchOccupiedSeats(() => {
          goToBookingStep(2);
        });
        return;
      }

      if (resData.dbSaved) {
        console.log(`%c[MySQL CINEGO] Đã lưu vé thành công vào DB (Ticket ID: ${resData.ticketId})`, "color: #10B981; font-weight: bold;");
      } else if (resData.dbError) {
        console.warn("[MySQL CINEGO Warning]:", resData.dbError);
      }
      if (resData.dbSaved) {
        const message = resData.emailSent
          ? `Vé điện tử và mã QR đã được gửi thành công đến email ${payload.customerEmail}`
          : "Đặt vé thành công. Gửi email chưa được cấu hình trong ứng dụng.";
        showToast(message, "success");
      } else {
        showToast(resData.dbError || "Không thể lưu vé. Vui lòng thử lại.", "error");
      }
    })
    .catch(err => {
      console.warn("Send ticket warning:", err);
      showToast("Không thể xác nhận đặt vé với máy chủ. Vui lòng tra cứu vé trước khi thử lại.", "error");
    });
  }

  function renderBookingStep5() {
    const ticketCode = document.getElementById("bstep5-ticket-code");
    const ticketMovie = document.getElementById("bstep5-movie");
    const ticketCinema = document.getElementById("bstep5-cinema");
    const ticketTime = document.getElementById("bstep5-time");
    const ticketSeats = document.getElementById("bstep5-seats");
    const ticketRoom = document.getElementById("bstep5-room");
    const ticketCustomer = document.getElementById("bstep5-customer");
    const ticketTotal = document.getElementById("bstep5-total");
    const ticketPoster = document.getElementById("bstep5-poster");
    const sentEmailEl = document.getElementById("bstep5-sent-email");
    const printBtn = document.getElementById("bstep5-print-btn");
    const finishBtn = document.getElementById("bstep5-finish-btn");

    const email = AppState.booking.customer.email || "dinhan33316@gmail.com";
    if (sentEmailEl) sentEmailEl.innerText = email;

    if (ticketCode) ticketCode.innerText = AppState.booking.bookingCode;
    if (ticketMovie) ticketMovie.innerText = AppState.booking.movie.title;
    if (ticketCinema) ticketCinema.innerText = AppState.booking.cinema.name;
    if (ticketTime) ticketTime.innerText = `${AppState.booking.showtime.time} • ${AppState.booking.date}`;
    if (ticketSeats) ticketSeats.innerText = AppState.booking.seats.map(s => s.id).join(", ");
    if (ticketRoom) ticketRoom.innerText = AppState.booking.showtime.room;
    if (ticketCustomer) ticketCustomer.innerText = `${AppState.booking.customer.name} - ${AppState.booking.customer.phone}`;
    if (ticketPoster) ticketPoster.src = AppState.booking.movie.poster;

    const seatTotal = AppState.booking.seats.reduce((sum, s) => sum + s.price, 0);
    const comboTotal = Object.keys(AppState.booking.combos).reduce((sum, id) => {
      const c = CINEGO_DATA.combos.find(item => item.id === id);
      return sum + (c ? c.price * AppState.booking.combos[id] : 0);
    }, 0);
    const finalTotal = Math.max(0, seatTotal + comboTotal + 5000 - AppState.booking.discount);
    if (ticketTotal) ticketTotal.innerText = `${finalTotal.toLocaleString("vi-VN")} đ`;

    sendTicketEmail(AppState.booking, finalTotal);

    const existingTicketIdx = userBookingHistory.findIndex(t => t.code === AppState.booking.bookingCode);
    if (existingTicketIdx === -1) {
      userBookingHistory.unshift({
        code: AppState.booking.bookingCode,
        movie: AppState.booking.movie.title,
        cinema: AppState.booking.cinema.name,
        hall: AppState.booking.showtime.room,
        showtime: `${AppState.booking.showtime.time} • ${AppState.booking.date}`,
        seats: AppState.booking.seats.map(s => s.id).join(", "),
        format: AppState.booking.showtime.format,
        total: finalTotal,
        poster: AppState.booking.movie.poster,
        status: "Đã thanh toán (Sắp chiếu)"
      });
    }

    if (printBtn) {
      printBtn.onclick = () => {
        window.print();
      };
    }

    if (finishBtn) {
      finishBtn.onclick = () => {
        closeBookingModal();
        showToast("Cảm ơn bạn! Vé điện tử và mã QR đã được gửi về email của bạn.", "success");
      };
    }
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeTrailerModal();
      closeSearchModal();
      closeAuthModal();
      closeBookingModal();
      closeUserProfileModal();
      closeTicketHistoryModal();
      if (typeof window.closeTicketLookupModal === "function") {
        window.closeTicketLookupModal();
      }
      if (typeof closeCinegoChatbox === "function") {
        closeCinegoChatbox();
      }
    }
  });

  const chatToggleBtn = document.getElementById("cinego-chat-toggle-btn");
  const chatBox = document.getElementById("cinego-chatbox");
  const chatCloseBtn = document.getElementById("chat-close-btn");
  const chatClearBtn = document.getElementById("chat-clear-btn");
  const chatMessagesContainer = document.getElementById("chat-messages-container");
  const chatForm = document.getElementById("chat-input-form");
  const chatInputField = document.getElementById("chat-input-field");
  const chatSendBtn = document.getElementById("chat-send-btn");

  let chatHistory = [];
  let isChatbotBusy = false;

  window.handleChatBookMovie = function(movieTitle) {
    if (typeof closeCinegoChatbox === "function") {
      closeCinegoChatbox();
    }

    let cleanTitle = (movieTitle || "")
      .replace(/&amp;/g, "&")
      .replace(/&lt;/g, "<")
      .replace(/&gt;/g, ">")
      .replace(/&quot;/g, '"')
      .replace(/&#039;/g, "'")
      .trim();

    if (window.CINEGO_DATA && Array.isArray(window.CINEGO_DATA.nowShowing)) {
      const found = window.CINEGO_DATA.nowShowing.find(m =>
        m.title.toLowerCase() === cleanTitle.toLowerCase() ||
        m.title.toLowerCase().includes(cleanTitle.toLowerCase()) ||
        cleanTitle.toLowerCase().includes(m.title.toLowerCase())
      );
      if (found) {
        cleanTitle = found.title;
      }
    }

    if (typeof window.openBookingModal === "function") {
      window.openBookingModal({ movieTitle: cleanTitle });
      showToast(`Đang mở phòng vé cho phim: ${cleanTitle}`, "info");
    }
  };

  function openCinegoChatbox() {
    if (!chatBox) return;
    chatBox.classList.remove("chatbox-closed");
    chatBox.classList.add("chatbox-open");
    if (chatInputField) {
      setTimeout(() => chatInputField.focus(), 150);
    }
    scrollChatToBottom();
  }

  function closeCinegoChatbox() {
    if (!chatBox) return;
    chatBox.classList.remove("chatbox-open");
    chatBox.classList.add("chatbox-closed");
  }

  window.closeCinegoChatbox = closeCinegoChatbox;

  function scrollChatToBottom() {
    if (!chatMessagesContainer) return;
    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
  }

  function formatAiMessage(text) {
    if (!text) return "";

    const bookButtons = [];
    const processedText = text.replace(/\[(?:Đặt vé|Dat ve|Đặt vé ngay|Dat ve ngay|book|Book):\s*([^\]\n]+)\]/gi, (match, rawTitle) => {
      const cleanTitle = rawTitle.replace(/[*_`]/g, "").trim();
      const placeholder = `__CINEGO_BOOK_BTN_${bookButtons.length}__`;
      bookButtons.push(cleanTitle);
      return placeholder;
    });

    let safe = processedText
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");

    safe = safe.replace(/\*\*(.*?)\*\*/g, "<strong class='text-white font-bold'>$1</strong>");
    safe = safe.replace(/\*(.*?)\*/g, "<em class='text-zinc-300'>$1</em>");
    safe = safe.replace(/`(.*?)`/g, "<code class='px-1.5 py-0.5 rounded bg-zinc-800 text-amber-400 font-mono text-[11px]'>$1</code>");
    safe = safe.replace(/^(\s*[-•*]\s+)(.*)$/gm, "<div class='flex items-start gap-1.5 my-1'><span class='text-cine-red font-bold'>•</span><span>$2</span></div>");

    safe = safe.replace(/\n\n/g, "<br><br>").replace(/\n/g, "<br>");

    bookButtons.forEach((title, idx) => {
      const placeholder = `__CINEGO_BOOK_BTN_${idx}__`;
      const safeTitleForJs = title.replace(/\\/g, "\\\\").replace(/'/g, "\\'").replace(/"/g, "&quot;");
      const safeTitleForHtml = title.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

      const btnHtml = `<div class="my-2.5"><button type="button" onclick="window.handleChatBookMovie('${safeTitleForJs}')" class="chat-book-btn group inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#E50914] to-red-700 hover:from-red-600 hover:to-red-800 text-white font-bold text-xs shadow-lg shadow-red-900/35 transition-all duration-200 cursor-pointer border border-white/20 transform hover:-translate-y-0.5 active:translate-y-0"><svg class="w-4 h-4 fill-current shrink-0 text-white group-hover:rotate-12 transition-transform duration-200" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c-1.1 0-2 .9-2 2s.9 2 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c1.1 0 2-.9 2-2s-.9-2-2-2V6c0-1.1.9-2 2-2zm4 4v8h2V8H8zm6 0v8h2V8h-2z"/></svg><span>Đặt vé: ${safeTitleForHtml}</span><svg class="w-3.5 h-3.5 text-white/80 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></button></div>`;

      safe = safe.replace(placeholder, btnHtml);
    });

    return safe;
  }

  function appendUserBubble(message) {
    if (!chatMessagesContainer) return;
    const msgDiv = document.createElement("div");
    msgDiv.className = "chat-msg chat-msg-user flex items-start justify-end gap-2.5";
    msgDiv.innerHTML = `
      <div class="max-w-[85%] bg-gradient-to-r from-[#E50914] to-red-700 text-white rounded-2xl rounded-tr-sm p-3.5 shadow-lg shadow-red-900/25 leading-relaxed font-normal">
        ${message.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")}
      </div>
      <div class="w-8 h-8 rounded-xl bg-zinc-800 border border-zinc-700 flex items-center justify-center shrink-0 text-white font-black text-xs shadow-md">
        Bạn
      </div>
    `;
    chatMessagesContainer.appendChild(msgDiv);
    scrollChatToBottom();
  }

  function showTypingIndicator() {
    if (!chatMessagesContainer) return;
    const typingDiv = document.createElement("div");
    typingDiv.id = "chat-typing-indicator";
    typingDiv.className = "chat-msg flex items-start gap-2.5";
    typingDiv.innerHTML = `
      <div class="w-8 h-8 rounded-xl bg-[#E50914]/20 border border-[#E50914]/40 flex items-center justify-center shrink-0 text-[#E50914]">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
        </svg>
      </div>
      <div class="bg-[#18181F] border border-white/10 text-zinc-300 rounded-2xl rounded-tl-sm px-4 py-3 flex items-center gap-2 shadow-md">
        <div class="flex items-center gap-1">
          <span class="w-2 h-2 rounded-full bg-[#E50914] typing-dot"></span>
          <span class="w-2 h-2 rounded-full bg-amber-400 typing-dot"></span>
          <span class="w-2 h-2 rounded-full bg-white typing-dot"></span>
        </div>
        <span class="text-xs text-zinc-400 font-medium ml-1">Đang suy nghĩ...</span>
      </div>
    `;
    chatMessagesContainer.appendChild(typingDiv);
    scrollChatToBottom();
  }

  function hideTypingIndicator() {
    const typingDiv = document.getElementById("chat-typing-indicator");
    if (typingDiv) {
      typingDiv.remove();
    }
  }

  function appendAiBubble(replyText, source = "gemini") {
    if (!chatMessagesContainer) return;
    const msgDiv = document.createElement("div");
    msgDiv.className = "chat-msg chat-msg-bot flex items-start gap-2.5";

    const badgeLabel = source === "gemini" ? "Gemini 2.5 Flash" : "CINEGO AI Bot";
    const badgeColor = source === "gemini"
      ? "bg-red-500/20 text-red-400 border-red-500/30"
      : "bg-amber-500/20 text-amber-400 border-amber-500/30";

    msgDiv.innerHTML = `
      <div class="w-8 h-8 rounded-xl bg-[#E50914]/20 border border-[#E50914]/40 flex items-center justify-center shrink-0 text-[#E50914]">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
        </svg>
      </div>
      <div class="max-w-[85%] bg-[#18181F] border border-white/10 text-zinc-200 rounded-2xl rounded-tl-sm p-3.5 shadow-md leading-relaxed space-y-2">
        <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-white/5">
          <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400">CINEGO Assistant</span>
          <span class="px-1.5 py-0.5 rounded text-[9px] font-bold border ${badgeColor}">${badgeLabel}</span>
        </div>
        <div class="text-xs text-zinc-200 leading-relaxed">
          ${formatAiMessage(replyText)}
        </div>
      </div>
    `;
    chatMessagesContainer.appendChild(msgDiv);
    scrollChatToBottom();
  }

  async function sendChatMessage(customText) {
    if (isChatbotBusy) return;
    const text = customText || (chatInputField ? chatInputField.value.trim() : "");
    if (!text) return;

    if (chatInputField) chatInputField.value = "";
    isChatbotBusy = true;
    if (chatSendBtn) chatSendBtn.disabled = true;

    appendUserBubble(text);
    chatHistory.push({ role: "user", content: text });

    showTypingIndicator();

    const movieContext = (CINEGO_DATA.nowShowing || []).map(m => {
      return {
        title: m.title,
        genres: m.genres ? m.genres.join(", ") : "",
        duration: m.duration,
        rating: m.rating,
        format: m.formats ? m.formats.join(", ") : "2D, IMAX",
        ageTag: m.ageTag || "P",
        description: m.description || ""
      };
    });

    try {
      const chatRoute = (window.CINEGO_ROUTES && window.CINEGO_ROUTES.chat) ? window.CINEGO_ROUTES.chat : "/chat-ai";
      const response = await fetch(chatRoute, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrfToken || window.CINEGO_CSRF_TOKEN || ""
        },
        body: JSON.stringify({
          message: text,
          history: chatHistory.slice(-8),
          movieContext: movieContext
        })
      });

      hideTypingIndicator();

      if (!response.ok) {
        const errorText = await response.text().catch(() => "");
        console.error(`[CINEGO Chatbot AI] Server trả về mã lỗi HTTP ${response.status}:`, errorText);
        throw new Error(`HTTP Error ${response.status}: ${errorText || response.statusText}`);
      }

      let data;
      try {
        data = await response.json();
      } catch (jsonErr) {
        console.error("[CINEGO Chatbot AI] Không thể phân tích phản hồi JSON từ /chat-ai:", jsonErr);
        throw jsonErr;
      }

      const botReply = data.reply || "Xin lỗi, tôi chưa thể trả lời câu hỏi lúc này. Bạn có thể chọn các gợi ý bên trên nhé!";
      const botSource = data.source || "smart_bot";

      appendAiBubble(botReply, botSource);
      chatHistory.push({ role: "model", content: botReply });

    } catch (err) {
      console.error("[CINEGO Chatbot AI] Chi tiết lỗi khi kết nối /chat-ai:", err);
      hideTypingIndicator();

      const fallbackReply = "Hệ thống AI đang tạm thời bận. Bạn có thể chọn trực tiếp các gợi ý nhanh hoặc bấm nút Đặt Vé ở thanh điều hướng để xem lịch chiếu hôm nay nhé!";
      appendAiBubble(fallbackReply, "smart_bot");
      chatHistory.push({ role: "model", content: fallbackReply });
    } finally {
      isChatbotBusy = false;
      if (chatSendBtn) chatSendBtn.disabled = false;
      if (chatInputField) chatInputField.focus();
    }
  }

  if (chatToggleBtn) {
    chatToggleBtn.addEventListener("click", () => {
      if (chatBox && chatBox.classList.contains("chatbox-open")) {
        closeCinegoChatbox();
      } else {
        openCinegoChatbox();
      }
    });
  }

  if (chatCloseBtn) {
    chatCloseBtn.addEventListener("click", closeCinegoChatbox);
  }

  if (chatClearBtn) {
    chatClearBtn.addEventListener("click", () => {
      chatHistory = [];
      if (chatMessagesContainer) {
        chatMessagesContainer.innerHTML = `

          <div class="chat-msg chat-msg-bot flex items-start gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-[#E50914]/20 border border-[#E50914]/40 flex items-center justify-center shrink-0 text-[#E50914]">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
              </svg>
            </div>
            <div class="max-w-[85%] bg-[#18181F] border border-white/10 text-zinc-200 rounded-2xl rounded-tl-sm p-3.5 shadow-md leading-relaxed space-y-2">
              <p class="font-bold text-white text-sm">
                Đã làm mới đoạn chat! 👋 Tôi là <span class="text-[#E50914]">CINEGO AI Assistant</span>.
              </p>
              <p class="text-zinc-300">
                Tôi sẵn sàng tư vấn phim bom tấn đang chiếu, gợi ý suất chiếu IMAX Laser, giá vé và tặng mã ưu đãi <span class="text-amber-400 font-mono font-bold">CINEGO50</span> giảm 50% cho bạn!
              </p>
            </div>
          </div>
        `;
      }
      showToast("Đoạn chat đã được làm mới.", "info");
    });
  }

  if (chatForm) {
    chatForm.addEventListener("submit", (e) => {
      e.preventDefault();
      sendChatMessage();
    });
  }

  function bindQuickSuggestionChips() {
    const chips = document.querySelectorAll(".chat-quick-chip");
    chips.forEach(chip => {
      chip.onclick = () => {
        const rawText = chip.innerText.replace(/^[^\w\s\u00C0-\u1EF9]+/, "").trim();
        openCinegoChatbox();
        sendChatMessage(rawText);
      };
    });
  }
  bindQuickSuggestionChips();

  setTimeout(() => {
    if (window.lucide) {
      window.lucide.createIcons();
    }
  }, 200);

  const getLookupModal = () => document.getElementById("ticketLookupModal") || document.getElementById("ticket-lookup-modal");
  const getLookupInput = () => document.getElementById("ticket-lookup-input") || document.getElementById("ticketLookupInput");
  const getLookupResults = () => document.getElementById("ticket-lookup-results") || document.getElementById("ticketLookupResults");

  window.openTicketLookupModal = function() {
    const modal = getLookupModal();
    if (!modal) return;
    modal.style.display = "flex";
    modal.classList.add("active");
    document.body.classList.add("modal-open");

    const input = getLookupInput();
    let autoKeyword = "";

    try {
      const userStr = localStorage.getItem("cinego_user");
      if (userStr) {
        const u = JSON.parse(userStr);
        if (u && u.phone) autoKeyword = u.phone;
        else if (u && u.email) autoKeyword = u.email;
      }
    } catch (e) {}

    if (!autoKeyword && window.CINEGO_COOKIES) {
      if (window.CINEGO_COOKIES.phone) autoKeyword = window.CINEGO_COOKIES.phone;
      else if (window.CINEGO_COOKIES.email) autoKeyword = window.CINEGO_COOKIES.email;
    }

    if (!autoKeyword && input && input.value.trim()) {
      autoKeyword = input.value.trim();
    }

    if (input) {
      if (autoKeyword) input.value = autoKeyword;
      setTimeout(() => input.focus(), 100);
    }

    if (autoKeyword) {
      window.performTicketLookup(autoKeyword);
    }
  };

  window.closeTicketLookupModal = function() {
    const modal = getLookupModal();
    if (!modal) return;
    modal.style.display = "none";
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
  };

  const currentLookupModal = getLookupModal();
  if (currentLookupModal) {
    currentLookupModal.addEventListener("click", (e) => {
      if (e.target === currentLookupModal) {
        window.closeTicketLookupModal();
      }
    });
  }

  window.performTicketLookup = function(customKw) {
    const input = getLookupInput();
    const resultsContainer = getLookupResults();
    if (!resultsContainer) return;

    let keyword = typeof customKw === "string" ? customKw.trim() : "";
    if (!keyword && input) {
      keyword = input.value.trim();
    }

    if (!keyword) {
      if (typeof window.showToast === "function") {
        window.showToast("Vui lòng nhập Mã vé, Số điện thoại hoặc Email để tra cứu!", "error");
      }
      if (input) input.focus();
      return;
    }

    if (input) input.value = keyword;

    if (!keyword) {
      showToast("Vui lòng nhập Mã vé, Số điện thoại hoặc Email để tra cứu!", "error");
      ticketLookupInput.focus();
      return;
    }

    ticketLookupResults.innerHTML = `
      <div class="text-center py-12">
        <div class="inline-block w-8 h-8 border-4 border-cine-red border-t-transparent rounded-full animate-spin mb-3"></div>
        <p class="text-xs text-zinc-400">Đang tra cứu dữ liệu vé từ cơ sở dữ liệu MySQL...</p>
      </div>
    `;

    const apiUrl = (window.CINEGO_ROUTES && window.CINEGO_ROUTES.api) ? window.CINEGO_ROUTES.api : "/api";

    fetch(`${apiUrl}?action=lookup_tickets&keyword=${encodeURIComponent(keyword)}`, {
      headers: {
        "Accept": "application/json",
        "X-CSRF-TOKEN": csrfToken || window.CINEGO_CSRF_TOKEN || ""
      }
    })
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.tickets) || data.tickets.length === 0) {
          ticketLookupResults.innerHTML = `
            <div class="text-center py-10 bg-white/[0.02] border border-white/5 rounded-2xl p-6">
              <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-zinc-800 flex items-center justify-center text-2xl">
                🔍
              </div>
              <h4 class="text-sm font-bold text-white">Không tìm thấy vé xem phim phù hợp</h4>
              <p class="text-xs text-zinc-400 mt-1 max-w-sm mx-auto">
                Không tìm thấy vé nào khớp với từ khóa <strong class="text-amber-400">"${keyword}"</strong>. Vui lòng kiểm tra lại Số điện thoại, Email hoặc Mã vé (vd: CGO-...).
              </p>
              <div class="mt-4">
                <button type="button" onclick="document.getElementById('ticket-lookup-input').value=''; document.getElementById('ticket-lookup-input').focus();" class="px-4 py-2 rounded-xl bg-zinc-800 text-xs font-semibold text-zinc-300 hover:text-white transition-colors">
                  Thử tìm kiếm lại
                </button>
              </div>
            </div>
          `;
          return;
        }

        ticketLookupResults.innerHTML = `
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-zinc-300">
              Tìm thấy <span class="text-cine-red font-black">${data.tickets.length}</span> vé cho từ khóa "<span class="text-white">${keyword}</span>":
            </span>
            <span class="text-[11px] text-zinc-500">Cơ sở dữ liệu CINEGO</span>
          </div>
          <div class="space-y-3.5">
            ${data.tickets.map(t => {
              const fileLink = t.ticket_file ? t.ticket_file : `sent-emails/ticket_${t.ticket_code}.html`;
              return `
                <div class="p-4 rounded-2xl bg-[#181822] border border-white/10 hover:border-zinc-700 transition-all shadow-lg flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">

                  <div class="flex items-center gap-3.5">
                    <img src="${t.movie_poster || 'assets/img/poster-default.jpg'}" alt="${t.movie_title}" class="w-14 h-20 rounded-xl object-cover shrink-0 bg-zinc-900 border border-white/10 shadow-md" onerror="this.src='https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg'" />
                    <div class="space-y-1">
                      <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded bg-cine-red text-white text-[10px] font-black uppercase tracking-wider">${t.ticket_code}</span>
                        <span class="text-[10px] font-bold text-emerald-400 bg-emerald-950/60 border border-emerald-500/30 px-2 py-0.5 rounded">Đã thanh toán</span>
                        ${t.format ? `<span class="text-[10px] text-zinc-400 bg-zinc-800 px-1.5 py-0.5 rounded">${t.format}</span>` : ''}
                      </div>
                      <h4 class="text-sm font-extrabold text-white leading-tight">${t.movie_title}</h4>
                      <p class="text-xs text-zinc-400">${t.cinema_name} ${t.hall_name ? `• ${t.hall_name}` : ''}</p>
                      <div class="flex items-center gap-3 text-xs text-zinc-300 flex-wrap pt-0.5">
                        <span>Suất: <strong class="text-white">${t.showtime}</strong></span>
                        <span>Ghế: <strong class="text-cine-red font-bold">${t.seats}</strong></span>
                        <span>Tổng tiền: <strong class="text-amber-400 font-extrabold">${t.total_amount}</strong></span>
                      </div>
                    </div>
                  </div>

                  <div class="w-full sm:w-auto flex flex-row sm:flex-col gap-2 shrink-0 border-t sm:border-t-0 border-white/5 pt-3 sm:pt-0">
                    <a
                      href="${fileLink}"
                      target="_blank"
                      title="Mở xem lại file vé điện tử HTML với đầy đủ mã QR"
                      class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 text-white font-bold text-xs transition-all shadow-md flex items-center justify-center gap-1.5 text-center cursor-pointer">
                      <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      <span>Xem Vé Điện Tử</span>
                    </a>
                    <button
                      type="button"
                      onclick="cancelCustomerTicket('${t.ticket_code}', ${t.id || 0})"
                      title="Hủy vé xem phim này và giải phóng ghế đã đặt"
                      class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-red-800 hover:from-red-500 hover:to-red-700 text-white font-bold text-xs transition-all shadow-md shadow-red-950/40 flex items-center justify-center gap-1.5 text-center cursor-pointer">
                      <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      <span>Hủy Vé</span>
                    </button>
                    <button
                      type="button"
                      onclick="navigator.clipboard.writeText('${t.ticket_code}'); showToast('Đã sao chép mã vé: ${t.ticket_code}', 'success');"
                      class="px-3 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white text-xs font-semibold transition-colors flex items-center justify-center gap-1 cursor-pointer">
                      <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                      <span>Sao chép mã</span>
                    </button>
                  </div>
                </div>
              `;
            }).join("")}
          </div>
        `;
      })
      .catch(err => {
        console.error("Lỗi tra cứu vé:", err);
        ticketLookupResults.innerHTML = `
          <div class="text-center py-8 text-red-400">
            <p class="text-xs">Có lỗi xảy ra trong quá trình tra cứu. Vui lòng thử lại sau!</p>
          </div>
        `;
      });
  };

  window.cancelCustomerTicket = function(ticketCode, ticketId) {
    if (!ticketCode) return;
    const confirmMsg = `Bạn có chắc chắn muốn hủy mã vé ${ticketCode} không? Ghế đã đặt sẽ được giải phóng.`;
    if (!confirm(confirmMsg)) {
      return;
    }

    const formData = new FormData();
    formData.append("action", "cancel_ticket");
    formData.append("ticket_code", ticketCode);
    if (ticketId) formData.append("id", ticketId);

    const apiUrl = (window.CINEGO_ROUTES && window.CINEGO_ROUTES.api) ? window.CINEGO_ROUTES.api : "/api";

    fetch(`${apiUrl}?action=cancel_ticket`, {
      method: "POST",
      headers: {
        "Accept": "application/json",
        "X-CSRF-TOKEN": csrfToken || window.CINEGO_CSRF_TOKEN || ""
      },
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const successMsg = data.message || `Đã hủy vé ${ticketCode} thành công! Ghế đã được giải phóng.`;
        showToast(successMsg, "success");

        const seatsStr = data.seats || "";
        const releasedSeats = seatsStr.split(/[,;\s]+/).map(s => s.trim().toUpperCase()).filter(Boolean);

        if (AppState.booking && Array.isArray(AppState.booking.occupiedSeats)) {
          AppState.booking.occupiedSeats = AppState.booking.occupiedSeats.filter(s => !releasedSeats.includes(s.toUpperCase()));
        }

        releasedSeats.forEach(seatCode => {
          document.querySelectorAll(`.seat-btn[data-id="${seatCode}"]`).forEach(btn => {
            btn.classList.remove("occupied", "bg-red-950/40", "text-red-500/70", "border-red-900/60", "cursor-not-allowed", "opacity-80", "shadow-inner");
            btn.removeAttribute("disabled");
            const stype = btn.getAttribute("data-type") || "Standard";
            const sprice = parseInt(btn.getAttribute("data-price"), 10) || 95000;
            btn.title = `Ghế ${seatCode} (${stype}) - ${sprice.toLocaleString("vi-VN")}đ`;
            if (stype === "VIP") {
              btn.className = "seat-btn w-6 h-6 sm:w-8 sm:h-8 rounded-t-lg rounded-b text-[10px] font-bold flex items-center justify-center transition-all cursor-pointer bg-amber-950/40 border border-amber-500/50 text-amber-300 hover:bg-amber-900/60";
            } else if (stype === "Sweetbox") {
              btn.className = "seat-btn w-6 h-6 sm:w-8 sm:h-8 rounded-t-lg rounded-b text-[10px] font-bold flex items-center justify-center transition-all cursor-pointer bg-pink-950/40 border border-pink-500/50 text-pink-300 hover:bg-pink-900/60";
            } else {
              btn.className = "seat-btn w-6 h-6 sm:w-8 sm:h-8 rounded-t-lg rounded-b text-[10px] font-bold flex items-center justify-center transition-all cursor-pointer bg-zinc-800 border border-zinc-700 text-zinc-300 hover:bg-zinc-700";
            }
          });
        });

        if (typeof window.refreshOccupiedSeats === "function") {
          window.refreshOccupiedSeats();
        }

        try {
          const userRaw = localStorage.getItem("cinego_user");
          if (userRaw) {
            const userObj = JSON.parse(userRaw);
            if (Array.isArray(userObj.bookingHistory)) {
              userObj.bookingHistory = userObj.bookingHistory.filter(b => b.code !== ticketCode);
              localStorage.setItem("cinego_user", JSON.stringify(userObj));
            }
          }
        } catch (e) {}

        const input = document.getElementById("ticket-lookup-input") || document.getElementById("ticketLookupInput");
        const currentKw = input ? input.value.trim() : "";
        if (typeof window.performTicketLookup === "function") {
          window.performTicketLookup(currentKw);
        }
      } else {
        showToast(data.message || "Không thể hủy vé. Vui lòng thử lại!", "error");
      }
    })
    .catch(err => {
      console.error("Lỗi khi hủy vé:", err);
      showToast("Lỗi kết nối máy chủ khi hủy vé!", "error");
    });
  };
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initCinegoApp);
} else {
  initCinegoApp();
}

// ==========================================
// CỤM ĐIỀU KHIỂN HERO BANNER ĐỘC LẬP (IIFE)
// ==========================================
(function initCinegoHero() {
  function startHero() {
    const dataObj = (typeof window.CINEGO_DATA !== "undefined" && window.CINEGO_DATA)
      ? window.CINEGO_DATA
      : ((typeof CINEGO_DATA !== "undefined" && CINEGO_DATA) ? CINEGO_DATA : null);

    const slides = (dataObj && Array.isArray(dataObj.heroSlides) && dataObj.heroSlides.length > 0)
      ? dataObj.heroSlides
      : ((dataObj && Array.isArray(dataObj.nowShowing)) ? dataObj.nowShowing : []);

    if (!slides || slides.length === 0) return;

    let currentIdx = 0;
    let fadeTimer = null;
    let autoTimer = null;
    let audioSyncTimers = [];
    let isMuted = true;
    let isCinemaMode = false;
    let ytReadyForCurrentSlide = false;
    let hasHandledVideoEnd = false;
    let lastHeroSwitchAt = Date.now();
    const AUTO_SLIDE_DELAY = 19000;

    function extractHeroVideoId(url) {
      if (!url) return "";
      if (typeof url !== "string") return "";
      const trimmed = url.trim();
      if (/^[a-zA-Z0-9_-]{11}$/.test(trimmed)) {
        return trimmed;
      }
      const match = trimmed.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
      return match ? match[1] : "";
    }

    function clearAudioAndListenerTimers() {
      audioSyncTimers.forEach(t => clearTimeout(t));
      audioSyncTimers = [];
    }

    function registerHeroYoutubeListener() {
      const iframe = document.getElementById('hero-bg-video');
      if (iframe && iframe.contentWindow) {
        try {
          iframe.contentWindow.postMessage(
            JSON.stringify({ event: 'listening', id: 'hero-bg-video', channel: 'widget' }),
            '*'
          );
        } catch (e) {}
      }
    }

    function syncHeroAudio() {
      const iframe = document.getElementById('hero-bg-video');
      const iconMuted = document.getElementById('hero-mute-icon-muted');
      const iconUnmuted = document.getElementById('hero-mute-icon-unmuted');

      if (iconMuted && iconUnmuted) {
        if (isMuted) {
          iconMuted.classList.remove('hidden');
          iconUnmuted.classList.add('hidden');
        } else {
          iconMuted.classList.add('hidden');
          iconUnmuted.classList.remove('hidden');
        }
      }

      if (iframe && iframe.contentWindow) {
        const cmd = isMuted ? 'mute' : 'unMute';
        try {
          iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: cmd, args: [] }), '*');
          if (!isMuted) {
            iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'setVolume', args: [100] }), '*');
          }
          // Luôn gửi playVideo để đảm bảo video tiếp tục phát
          iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'playVideo', args: [] }), '*');
        } catch (e) {}
      }
    }

    function resetHeroAutoSlide() {
      if (autoTimer) {
        clearInterval(autoTimer);
        autoTimer = null;
      }
      if (!isCinemaMode && isMuted) {
        autoTimer = setInterval(() => {
          renderHero(currentIdx + 1);
        }, AUTO_SLIDE_DELAY);
      }
    }

    function handleHeroYoutubeMessage(event) {
      const origin = event.origin || "";
      const isYoutubeOrigin = origin.includes("youtube.com") || origin.includes("youtube-nocookie.com");
      const isSameOrigin = Boolean(window.location && origin === window.location.origin);
      if (!isYoutubeOrigin && !isSameOrigin) return;

      if (!event.data || typeof event.data !== "string") return;

      let data;
      try {
        data = JSON.parse(event.data);
      } catch (e) {
        return;
      }

      if (!data || typeof data !== "object") return;
      if (data.id && data.id !== "hero-bg-video") return;

      if (data.event === "onReady" || data.event === "initialDelivery") {
        ytReadyForCurrentSlide = true;
        const iframe = document.getElementById("hero-bg-video");
        if (iframe && iframe.contentWindow) {
          try {
            iframe.contentWindow.postMessage(JSON.stringify({ event: "command", func: "addEventListener", args: ["onStateChange"] }), "*");
            if (isMuted) {
              iframe.contentWindow.postMessage(JSON.stringify({ event: "command", func: "mute", args: [] }), "*");
            }
            iframe.contentWindow.postMessage(JSON.stringify({ event: "command", func: "playVideo", args: [] }), "*");
          } catch (e) {}
        }
        syncHeroAudio();
      }

      let playerState = null;
      if ((data.event === "infoDelivery" || data.event === "initialDelivery") && data.info && typeof data.info.playerState === "number") {
        ytReadyForCurrentSlide = true;
        playerState = data.info.playerState;
      } else if (data.event === "onStateChange" && typeof data.info === "number") {
        ytReadyForCurrentSlide = true;
        playerState = data.info;
      }

      // Khi video thực sự phát (playerState === 1), hạ ngay màn chắn ảnh nền để hiển thị trailer
      if (playerState === 1) {
        hasHandledVideoEnd = false;
        const img = document.getElementById("hero-backdrop-img");
        if (img) {
          img.style.transition = "opacity 0.8s ease-in-out";
          img.style.opacity = "0";
        }
      }

      // Nếu gặp lỗi onError (video bị chặn nhúng hoặc lỗi ID), giữ lại ảnh backdrop để không bị đen màn hình
      if (data.event === "onError") {
        const img = document.getElementById("hero-backdrop-img");
        if (img) {
          img.style.transition = "opacity 0.5s ease-in-out";
          img.style.opacity = "0.99";
        }
      }

      if (playerState === 0) {
        const elapsedSinceSwitch = Date.now() - lastHeroSwitchAt;
        if (hasHandledVideoEnd || elapsedSinceSwitch < 2000) {
          return;
        }
        hasHandledVideoEnd = true;
        renderHero(currentIdx + 1);
        resetHeroAutoSlide();
      }
    }

    function renderHero(index) {
      currentIdx = (index + slides.length) % slides.length;
      const slide = slides[currentIdx];
      if (!slide) return;

      if (window.AppState) window.AppState.currentHeroIndex = currentIdx;

      // 1. Cập nhật các trường text/info
      const taglineEl = document.getElementById("hero-tagline");
      const titleEl = document.getElementById("hero-title");
      const ageEl = document.getElementById("hero-age");
      const ratingEl = document.getElementById("hero-rating");
      const durationEl = document.getElementById("hero-duration");
      const genreEl = document.getElementById("hero-genre");
      const descEl = document.getElementById("hero-desc");
      const formatsEl = document.getElementById("hero-formats");

      if (taglineEl) taglineEl.innerText = slide.tagline || "TRẢI NGHIỆM ĐIỆN ẢNH ĐỈNH CAO";
      if (titleEl) {
        titleEl.innerText = slide.vietnameseTitle || slide.title || "";
        titleEl.style.opacity = "1";
      }
      if (ageEl) ageEl.innerText = slide.ageRating || "P";
      if (ratingEl) ratingEl.innerText = slide.rating ? slide.rating : "9.0";
      if (durationEl) durationEl.innerText = slide.duration || "";
      if (genreEl) genreEl.innerText = slide.genre || (Array.isArray(slide.genres) ? slide.genres.join(", ") : "");
      if (descEl) {
        descEl.innerText = slide.description || "";
        descEl.style.opacity = "1";
      }

      // 2. Render lại badge định dạng phim #hero-formats
      if (formatsEl) {
        const formats = slide.format || slide.formats || ["2D"];
        formatsEl.innerHTML = formats.map(fmt =>
          `<span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-white/10 text-zinc-200 border border-white/15 backdrop-blur-sm">${fmt}</span>`
        ).join("");
      }

      // 3. Render lại #hero-indicators và gắn sự kiện click
      const indicatorContainer = document.getElementById("hero-indicators");
      if (indicatorContainer) {
        indicatorContainer.innerHTML = slides.map((_, idx) => {
          if (idx === currentIdx) {
            return `<button type="button" aria-label="Slide ${idx + 1}" data-index="${idx}" class="hero-dot h-2.5 rounded-full bg-red-600 w-8 transition-all duration-300 shadow-[0_0_12px_rgba(229,9,20,0.8)] cursor-pointer"></button>`;
          } else {
            return `<button type="button" aria-label="Slide ${idx + 1}" data-index="${idx}" class="hero-dot h-2.5 rounded-full bg-white/40 w-2.5 hover:bg-white/70 transition-all duration-300 cursor-pointer"></button>`;
          }
        }).join("");

        indicatorContainer.querySelectorAll(".hero-dot").forEach(btn => {
          btn.onclick = () => {
            const idx = parseInt(btn.getAttribute("data-index"), 10);
            renderHero(idx);
            resetHeroAutoSlide();
          };
        });
      }

      // 4. Lấy videoId của trailer (hỗ trợ cả trailerUrl lẫn trailer_url)
      let trailerUrl = slide.trailerUrl || slide.trailer_url || "";
      if (!trailerUrl && dataObj && Array.isArray(dataObj.nowShowing)) {
        const match = dataObj.nowShowing.find(m =>
          m.title && (m.title.toLowerCase() === (slide.title || "").toLowerCase() ||
          (slide.vietnameseTitle && m.title.toLowerCase() === slide.vietnameseTitle.toLowerCase()))
        );
        if (match) trailerUrl = match.trailerUrl || match.trailer_url || "";
      }
      let videoId = extractHeroVideoId(trailerUrl);
      if (!videoId) {
        videoId = "73_1biulkYk";
      }

      // 5. Cập nhật màn chắn #hero-backdrop-img (đóng kín 0ms)
      const img = document.getElementById("hero-backdrop-img");
      if (img) {
        img.style.transition = "none";
        img.style.opacity = "1";
        img.src = slide.backdrop || (videoId ? `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg` : (slide.poster || slide.movie_poster || ""));
        img.classList.remove("hero-ken-burns");
        void img.offsetWidth; // kích hoạt reflow
        img.classList.add("hero-ken-burns");
      }

      // 6. Cập nhật #hero-bg-video.src
      const iframe = document.getElementById("hero-bg-video");
      if (iframe && videoId) {
        clearAudioAndListenerTimers();
        ytReadyForCurrentSlide = false;
        hasHandledVideoEnd = false;
        lastHeroSwitchAt = Date.now();

        // Xử lý tham số origin: Tuyệt đối KHÔNG truyền origin trên localhost/127.0.0.1 hoặc có port
        // để tránh vi phạm chính sách của YouTube API gây ra lỗi màn hình đen.
        let originParam = "";
        try {
          const host = window.location.hostname || "";
          const isLocal = host === "127.0.0.1" || host === "localhost" || host === "::1" || host === "" || Boolean(window.location.port);
          if (!isLocal && window.location.origin && window.location.origin.startsWith("http")) {
            originParam = `&origin=${encodeURIComponent(window.location.origin)}`;
          }
        } catch (e) {}

        // Luôn khởi tạo ở trạng thái tắt tiếng (mute=1) trên URL để tuân thủ Autoplay Policy của trình duyệt
        const embedSrc = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&controls=0&showinfo=0&rel=0&modestbranding=1&iv_load_policy=3&playsinline=1&enablejsapi=1${originParam}`;

        iframe.onload = () => {
          registerHeroYoutubeListener();
          // Ngay khi nạp xong, gửi lệnh mute và playVideo để bắt đầu phát mượt mà
          try {
            iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'mute', args: [] }), '*');
            iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'playVideo', args: [] }), '*');
          } catch (e) {}
          syncHeroAudio();
          audioSyncTimers.push(setTimeout(() => {
            if (!ytReadyForCurrentSlide) {
              registerHeroYoutubeListener();
            }
            syncHeroAudio();
          }, 500));
        };

        if (iframe.src !== embedSrc) {
          iframe.src = embedSrc;
        } else if (slides.length === 1) {
          // Trường hợp chỉ có 1 slide: phát lại từ đầu khi hết video
          try {
            iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'seekTo', args: [0, true] }), '*');
            iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'playVideo', args: [] }), '*');
          } catch (e) {}
        }

        // Đồng bộ âm thanh & kích hoạt kênh lắng nghe ngay lập tức và khi video nạp xong
        registerHeroYoutubeListener();
        syncHeroAudio();
        [400, 1000, 2000].forEach(delay => {
          audioSyncTimers.push(setTimeout(() => {
            if (!ytReadyForCurrentSlide) {
              registerHeroYoutubeListener();
            }
            syncHeroAudio();
          }, delay));
        });
      }

      // 7. Mở màn chắn sau 3600ms (3.6 giây) nếu video đã sẵn sàng hoặc giữ ảnh nền an toàn nếu xảy ra sự cố
      clearTimeout(fadeTimer);
      fadeTimer = setTimeout(() => {
        if (img && ytReadyForCurrentSlide) {
          img.style.transition = "opacity 1s ease-in-out";
          img.style.opacity = "0";
        }
      }, 3600);
    }

    // Gắn sự kiện nút Prev / Next
    const prevBtn = document.getElementById("hero-prev-btn");
    const nextBtn = document.getElementById("hero-next-btn");
    if (prevBtn) {
      prevBtn.onclick = () => {
        renderHero(currentIdx - 1);
        resetHeroAutoSlide();
      };
    }
    if (nextBtn) {
      nextBtn.onclick = () => {
        renderHero(currentIdx + 1);
        resetHeroAutoSlide();
      };
    }

    // Gắn sự kiện nút âm thanh
    const muteBtn = document.getElementById("hero-mute-btn");
    if (muteBtn) {
      muteBtn.onclick = () => {
        isMuted = !isMuted;
        syncHeroAudio();
        setTimeout(syncHeroAudio, 150);

        if (!isMuted) {
          if (autoTimer) {
            clearInterval(autoTimer);
            autoTimer = null;
          }
        } else {
          resetHeroAutoSlide();
        }
      };
    }

    // Gắn sự kiện nút Xem Trailer (Cinema Mode) & Thoát
    const trailerBtn = document.getElementById("hero-trailer-btn");
    const exitCinemaBtn = document.getElementById("hero-exit-cinema-btn");
    const contentWrapper = document.getElementById("hero-content-wrapper");

    if (trailerBtn) {
      trailerBtn.onclick = () => {
        isCinemaMode = true;
        if (autoTimer) {
          clearInterval(autoTimer);
          autoTimer = null;
        }

        // Bật tiếng trailer
        isMuted = false;
        syncHeroAudio();
        setTimeout(syncHeroAudio, 250);

        // Mở ngay màn chắn ảnh nền
        const img = document.getElementById("hero-backdrop-img");
        if (img) img.style.opacity = "0";

        // Ẩn nội dung chữ, hiện nút thoát rạp
        if (contentWrapper) {
          contentWrapper.classList.add("opacity-0", "pointer-events-none");
          contentWrapper.classList.remove("opacity-100", "pointer-events-auto");
        }
        if (exitCinemaBtn) {
          exitCinemaBtn.classList.remove("hidden");
          exitCinemaBtn.classList.add("inline-flex");
        }
      };
    }

    if (exitCinemaBtn) {
      exitCinemaBtn.onclick = () => {
        isCinemaMode = false;
        if (contentWrapper) {
          contentWrapper.classList.remove("opacity-0", "pointer-events-none");
          contentWrapper.classList.add("opacity-100", "pointer-events-auto");
        }
        exitCinemaBtn.classList.add("hidden");
        exitCinemaBtn.classList.remove("inline-flex");
        resetHeroAutoSlide();
      };
    }

    // Gắn sự kiện nút Đặt vé
    const bookBtn = document.getElementById("hero-book-btn");
    if (bookBtn) {
      bookBtn.onclick = () => {
        const slide = slides[currentIdx];
        if (typeof window.openBookingModal === "function") {
          window.openBookingModal({ movieTitle: slide ? slide.title : "" });
        }
      };
    }

    // Expose API ra window
    window.CinegoHero = {
      renderHero,
      next: () => { renderHero(currentIdx + 1); resetHeroAutoSlide(); },
      prev: () => { renderHero(currentIdx - 1); resetHeroAutoSlide(); },
      mute: () => {
        isMuted = true;
        syncHeroAudio();
      },
      unMute: () => {
        isMuted = false;
        syncHeroAudio();
        if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
      },
      syncHeroAudio: syncHeroAudio,
      sendCommand: (cmd) => {
        const iframe = document.getElementById("hero-bg-video");
        if (iframe && iframe.contentWindow) {
          try {
            iframe.contentWindow.postMessage(JSON.stringify({ event: "command", func: cmd, args: [] }), "*");
          } catch (e) {}
        }
      },
      updateMuteUI: syncHeroAudio
    };
    window.sendHeroIframeCommand = (cmd) => {
      if (cmd === 'mute') { isMuted = true; syncHeroAudio(); }
      else if (cmd === 'unMute') { isMuted = false; syncHeroAudio(); }
    };
    window.updateHeroMuteUI = syncHeroAudio;

    // Lắng nghe sự kiện postMessage từ YouTube iframe & duy trì nhịp bắt tay listening mỗi 800ms
    window.addEventListener("message", handleHeroYoutubeMessage);
    setInterval(() => {
      const vidFrame = document.getElementById('hero-bg-video');
      if (vidFrame && vidFrame.contentWindow) {
        try {
          vidFrame.contentWindow.postMessage(
            JSON.stringify({ event: 'listening', id: 'hero-bg-video', channel: 'widget' }),
            '*'
          );
        } catch (e) {}
      }
    }, 800);

    // Khởi chạy slide 0 và kích hoạt bộ đếm 19000ms
    renderHero(0);
    resetHeroAutoSlide();
    syncHeroAudio();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", startHero);
  } else {
    startHero();
  }
})();
