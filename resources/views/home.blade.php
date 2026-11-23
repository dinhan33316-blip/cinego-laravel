<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>CINEGO — Nền Tảng Đặt Vé Xem Phim Trực Tuyến Đẳng Cấp</title>
  <meta name="description" content="Khám phá phim chiếu rạp mới nhất, xem lịch chiếu IMAX, Dolby Atmos và đặt vé xem phim online nhanh chóng cùng CINEGO." />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            'cine-bg': '#0B0B0F',
            'cine-card': '#18181F',
            'cine-card-hover': '#22222B',
            'cine-surface': '#121217',
            'cine-red': '#E50914',
            'cine-red-hover': '#C10712',
            'cine-gold': '#F59E0B'
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          }
        }
      }
    }
  </script>

  <script src="https://unpkg.com/lucide@latest"></script>

  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
  <style>
    #ticketLookupModal, #ticket-lookup-modal {
      display: none;
      opacity: 0;
      visibility: hidden;
      z-index: 9999 !important;
    }
    #ticketLookupModal.active, #ticket-lookup-modal.active {
      display: flex !important;
      opacity: 1 !important;
      visibility: visible !important;
      z-index: 9999 !important;
    }
  </style>
</head>

<body class="bg-[#0B0B0F] text-white selection:bg-[#E50914] selection:text-white antialiased overflow-x-hidden min-h-screen flex flex-col justify-between">

  <header id="main-nav" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 py-5 bg-gradient-to-b from-[#0B0B0F]/95 via-[#0B0B0F]/75 to-transparent backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between gap-4">

        <a href="#hero" class="flex items-center gap-2.5 group whitespace-nowrap shrink-0">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cine-red to-red-600 flex items-center justify-center text-white shadow-[0_0_20px_rgba(229,9,20,0.6)] group-hover:scale-105 transition-transform shrink-0">
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
              <path d="M4 4h16c1.1 0 2 .9 2 2v3c-1.1 0-2 .9-2 2s.9 2 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c1.1 0 2-.9 2-2s-.9-2-2-2V6c0-1.1.9-2 2-2zm4 4v8h2V8H8zm6 0v8h2V8h-2z"/>
            </svg>
          </div>
          <div class="flex flex-col">
            <span class="text-2xl font-black tracking-wider text-white font-sans leading-none">CINE<span class="text-cine-red">GO</span></span>
            <span class="text-[9px] tracking-widest text-zinc-400 font-semibold uppercase mt-0.5">Cinema Experience</span>
          </div>
        </a>

        <nav class="hidden lg:flex items-center gap-7 text-sm font-medium text-zinc-300 whitespace-nowrap">
          <a href="#hero" class="hover:text-white transition-colors whitespace-nowrap">Trang chủ</a>
          <a href="#now-showing" class="hover:text-white transition-colors whitespace-nowrap">Phim đang chiếu</a>
          <a href="#coming-soon" class="hover:text-white transition-colors whitespace-nowrap">Phim sắp chiếu</a>
          <a href="#cinemas" class="hover:text-white transition-colors whitespace-nowrap">Rạp chiếu</a>
          <a href="#promotions" class="hover:text-white transition-colors whitespace-nowrap">Ưu đãi</a>
        </nav>

        <div class="hidden sm:flex items-center gap-2.5 whitespace-nowrap shrink-0">

          <button
            id="nav-search-btn"
            type="button"
            aria-label="Tìm kiếm phim"
            title="Tìm kiếm phim, rạp (Ctrl + K)"
            class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-zinc-300 hover:text-white text-xs font-medium border border-white/10 hover:border-white/20 transition-all whitespace-nowrap shrink-0 cursor-pointer">
            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <kbd class="px-1.5 py-0.5 text-[10px] font-mono bg-black/40 rounded text-zinc-400 border border-white/10">Ctrl K</kbd>
          </button>

          <button
            id="nav-ticket-lookup-btn"
            type="button"
            onclick="openTicketLookupModal()"
            title="Tra cứu vé của tôi bằng SĐT, Email hoặc Mã vé"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-red-950/60 to-zinc-900 hover:from-red-900/60 hover:to-zinc-800 border border-red-500/50 text-red-300 hover:text-white text-xs font-semibold transition-all shadow-[0_0_15px_rgba(229,9,20,0.2)] cursor-pointer whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 text-cine-red shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            <span>Tra cứu vé</span>
          </button>

          <div id="nav-auth-container" class="relative whitespace-nowrap shrink-0">

            <button
              id="nav-login-btn"
              type="button"
              onclick="window.openAuthModal()"
              class="px-3.5 py-2 rounded-xl text-xs font-semibold text-zinc-300 hover:text-white hover:bg-white/10 transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
              <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              <span>Đăng nhập</span>
            </button>

            <div id="nav-user-menu" class="hidden relative">
              <button
                id="nav-user-btn"
                type="button"
                aria-haspopup="true"
                aria-expanded="false"
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-900/90 hover:bg-zinc-800 border border-zinc-700/80 text-white text-xs font-medium transition-all shadow-md focus:outline-none whitespace-nowrap cursor-pointer">

                <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-cine-red to-amber-500 flex items-center justify-center text-white font-extrabold text-xs shadow-sm shrink-0">
                  <span id="nav-user-initial">D</span>
                </div>

                <div class="flex flex-col text-left max-w-[130px] truncate">
                  <span id="nav-user-email" class="text-xs font-bold text-white truncate">dinhan33316@gmail.com</span>
                  <span class="text-[10px] text-amber-400 font-semibold -mt-0.5">Thành viên VIP</span>
                </div>

                <svg id="nav-user-chevron" class="w-3.5 h-3.5 text-zinc-400 transition-transform duration-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </button>

              <div
                id="nav-user-dropdown"
                class="hidden absolute right-0 mt-2 w-60 rounded-2xl bg-zinc-900 border border-zinc-700/90 shadow-[0_20px_50px_rgba(0,0,0,0.8)] p-2 z-50 backdrop-blur-xl">

                <div class="px-3 py-2.5 border-b border-zinc-800 mb-1">
                  <div class="text-[11px] text-zinc-400">Đã đăng nhập với</div>
                  <div id="dropdown-user-email" class="text-xs font-bold text-white truncate">dinhan33316@gmail.com</div>
                </div>

                <button
                  type="button"
                  id="dropdown-account-btn"
                  class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-zinc-300 hover:text-white hover:bg-zinc-800/80 transition-colors text-left cursor-pointer">
                  <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                  <span>Tài khoản của tôi</span>
                </button>

                <button
                  type="button"
                  id="dropdown-history-btn"
                  class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-zinc-300 hover:text-white hover:bg-zinc-800/80 transition-colors text-left cursor-pointer">
                  <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                  <span>Lịch sử đặt vé</span>
                </button>

                <a
                  href="{{ route('admin') }}"
                  target="_blank"
                  class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-amber-400 hover:text-amber-300 hover:bg-amber-950/30 transition-colors text-left">
                  <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  <span>Quản trị hệ thống (Admin)</span>
                </a>

                <div class="border-t border-zinc-800 my-1"></div>

                <button
                  id="dropdown-logout-btn"
                  type="button"
                  class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-red-400 hover:text-red-300 hover:bg-red-950/40 transition-colors text-left cursor-pointer">
                  <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                  <span>Đăng xuất</span>
                </button>
              </div>
            </div>
          </div>

          <button
            type="button"
            onclick="window.openBookingModal()"
            class="relative inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider transition-all duration-300 shadow-[0_0_20px_rgba(229,9,20,0.5)] hover:shadow-[0_0_30px_rgba(229,9,20,0.8)] active:scale-95 whitespace-nowrap shrink-0">
            <span class="flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
              Đặt vé ngay
            </span>
          </button>
        </div>

        <button
          id="mobile-menu-toggle"
          type="button"
          aria-label="Mở menu"
          class="lg:hidden p-2 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-300 hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
        </button>

      </div>

      <div id="mobile-menu" class="hidden lg:hidden mt-4 p-5 rounded-2xl glass-panel border border-white/10 flex flex-col gap-4 shadow-2xl">
        <a href="#hero" class="text-sm font-medium text-zinc-200 hover:text-cine-red">Trang chủ</a>
        <a href="#now-showing" class="text-sm font-medium text-zinc-200 hover:text-cine-red">Phim đang chiếu</a>
        <a href="#coming-soon" class="text-sm font-medium text-zinc-200 hover:text-cine-red">Phim sắp chiếu</a>
        <a href="#cinemas" class="text-sm font-medium text-zinc-200 hover:text-cine-red">Rạp chiếu</a>
        <a href="#promotions" class="text-sm font-medium text-zinc-200 hover:text-cine-red">Ưu đãi & Khuyến mãi</a>
        <button
          type="button"
          onclick="window.openTicketLookupModal(); const mm = document.getElementById('mobile-menu'); if (mm) mm.classList.add('hidden');"
          class="text-sm font-bold text-cine-red hover:text-red-400 flex items-center gap-2 py-1 text-left cursor-pointer">
          <svg class="w-4 h-4 text-cine-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
          <span>🎟️ Tra Cứu Vé Của Tôi</span>
        </button>
        <a href="{{ route('admin') }}" target="_blank" class="text-sm font-bold text-red-400 hover:text-red-300 flex items-center gap-2">
          <span>⚙️</span>
          <span>Trang Quản Trị (Admin)</span>
        </a>

        <div id="mobile-auth-container" class="pt-3 border-t border-zinc-700 flex flex-col gap-2.5">

          <button
            id="mobile-login-btn"
            type="button"
            onclick="window.openAuthModal()"
            class="w-full py-2.5 rounded-xl bg-zinc-800 text-xs font-semibold text-white flex items-center justify-center gap-2">
            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Đăng nhập / Đăng ký</span>
          </button>

          <div id="mobile-user-info" class="hidden flex flex-col gap-2 p-3 rounded-xl bg-zinc-900 border border-zinc-800">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-cine-red to-amber-500 text-white flex items-center justify-center font-bold text-xs">
                  <span id="mobile-user-initial">D</span>
                </div>
                <div class="text-xs">
                  <div id="mobile-user-email" class="font-bold text-white truncate max-w-[160px]">dinhan33316@gmail.com</div>
                  <div class="text-[10px] text-amber-400 font-semibold">Thành viên VIP</div>
                </div>
              </div>
              <button
                id="mobile-logout-btn"
                type="button"
                class="px-3 py-1.5 text-xs text-red-400 hover:bg-red-950/50 rounded-lg font-bold">
                Đăng xuất
              </button>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-zinc-800 text-center">
              <button id="mobile-account-btn" type="button" class="py-1.5 px-2 rounded-lg bg-zinc-800 text-xs font-medium text-zinc-300 hover:text-white">
                Tài khoản
              </button>
              <button id="mobile-history-btn" type="button" class="py-1.5 px-2 rounded-lg bg-zinc-800 text-xs font-medium text-zinc-300 hover:text-white">
                Vé đã đặt
              </button>
            </div>
          </div>

          <button
            type="button"
            onclick="window.openBookingModal()"
            class="w-full py-3 rounded-xl bg-cine-red text-xs font-bold text-white uppercase tracking-wider shadow-lg shadow-red-600/30">
            Đặt vé ngay
          </button>
        </div>
      </div>

    </div>
  </header>

  <section id="hero" class="relative min-h-[92vh] flex items-center pt-24 pb-20 overflow-hidden">

    <!-- Lớp 1 (z-10): Video trailer YouTube tràn viền toàn màn hình -->
    <div id="hero-video-wrapper" class="absolute inset-0 overflow-hidden pointer-events-none" style="z-index: 10;">
      <iframe id="hero-bg-video" class="hero-bg-iframe" src="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen frameborder="0" title="Cinego Hero Background Trailer"></iframe>
    </div>

    <!-- Lớp 2 (z-20): Màn chắn ảnh nền (curtain) che kín video khi YouTube nạp/đổi slide -->
    <img
      id="hero-backdrop-img"
      src="https://img.youtube.com/vi/73_1biulkYk/maxresdefault.jpg"
      alt="Hero Trailer Background"
      class="absolute inset-0 w-full h-full object-cover object-center brightness-50 pointer-events-none select-none"
      style="z-index: 20; opacity: 0.99; transition: opacity 1s ease-in-out;"
    />

    <!-- Lớp 3 (z-30): Gradient tối hòa trộn viền mượt mà vào nền #0B0B0F -->
    <div class="absolute inset-0 bg-gradient-to-r from-[#0B0B0F] via-[#0B0B0F]/75 to-transparent pointer-events-none" style="z-index:30;"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#0B0B0F] via-transparent to-[#0B0B0F]/60 pointer-events-none" style="z-index:30;"></div>

    <!-- Lớp 4 (z-40): Nội dung chữ, nút bấm và cụm điều khiển slide -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full" style="z-index:40;">
      <div id="hero-content-wrapper" class="max-w-2xl transition-opacity duration-500">

        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-black/60 border border-cine-red/40 backdrop-blur-md mb-6">
          <span class="w-2 h-2 rounded-full bg-cine-red badge-pulse"></span>
          <span id="hero-tagline" class="text-xs font-bold uppercase tracking-widest text-zinc-200">
            TRẢI NGHIỆM ĐIỆN ẢNH ĐỈNH CAO
          </span>
        </div>

        <h1 id="hero-title" class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.1] mb-5 transition-opacity duration-300 drop-shadow-md">
          Deadpool & Wolverine
        </h1>

        <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm text-zinc-300 font-medium mb-5">
          <span id="hero-age" class="px-2.5 py-1 rounded-md bg-cine-red font-black text-white text-xs shadow-sm">T18</span>
          <div class="flex items-center gap-1.5 text-amber-400 font-bold bg-black/60 px-3 py-1 rounded-md border border-amber-500/25 shadow-sm">
            <svg class="w-4 h-4 fill-amber-400 text-amber-400" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span id="hero-rating">9.3</span>/10
          </div>
          <span id="hero-duration" class="text-zinc-400">128 phút</span>
          <span class="text-zinc-600">•</span>
          <span id="hero-genre" class="text-zinc-300 font-medium">Hành động, Hài hước, Viễn tưởng</span>
          <div id="hero-formats" class="flex items-center gap-1.5 ml-1">

          </div>
        </div>

        <p id="hero-desc" class="text-sm sm:text-base text-zinc-300/90 leading-relaxed max-w-2xl mb-8 line-clamp-3 transition-opacity duration-300">
          Khám phá những bom tấn mới nhất. Chọn ghế yêu thích và đặt vé chỉ trong vài thao tác cùng CINEGO. Hệ thống âm thanh vòm sống động và màn hình chiếu khổng lồ.
        </p>

        <div class="flex flex-wrap items-center gap-3.5 sm:gap-4">

          <button
            id="hero-book-btn"
            type="button"
            class="px-7 py-3.5 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-sm font-bold uppercase tracking-wider transition-all duration-300 shadow-[0_0_25px_rgba(229,9,20,0.6)] hover:shadow-[0_0_35px_rgba(229,9,20,0.9)] flex items-center gap-2.5 active:scale-95 whitespace-nowrap cursor-pointer">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            <span>Đặt vé ngay</span>
          </button>

          <button
            id="hero-trailer-btn"
            type="button"
            class="px-6 py-3.5 rounded-xl glass-panel hover:bg-white/15 text-white text-sm font-semibold transition-all flex items-center gap-2 border border-white/20 whitespace-nowrap cursor-pointer shadow-lg">
            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center shrink-0">
              <svg class="w-3.5 h-3.5 fill-current text-cine-red ml-0.5" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
            <span>Xem Trailer</span>
          </button>

          <a
            href="#now-showing"
            class="px-4 py-3.5 rounded-xl text-zinc-300 hover:text-white text-sm font-medium transition-colors flex items-center gap-1.5 whitespace-nowrap">
            <span>Xem lịch chiếu &darr;</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Nút thoát chế độ xem video nền toàn màn hình -->
    <div class="absolute bottom-10 left-4 sm:left-8 lg:left-12" style="z-index:40;">
      <button
        id="hero-exit-cinema-btn"
        type="button"
        class="hidden items-center gap-2 px-4 py-2.5 rounded-xl bg-black/75 hover:bg-zinc-800 text-white text-xs font-bold border border-white/20 backdrop-blur-md shadow-xl transition-all cursor-pointer">
        <svg class="w-4 h-4 text-cine-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>← Hiện thông tin phim</span>
      </button>
    </div>

    <!-- Cụm điều khiển góc dưới bên phải -->
    <div class="absolute bottom-10 right-4 sm:right-8 lg:right-12 flex items-center gap-3 sm:gap-4" style="z-index:40;">

      <!-- Nút Bật/Tắt tiếng video nền -->
      <button
        id="hero-mute-btn"
        type="button"
        aria-label="Bật/Tắt tiếng trailer"
        title="Bật/Tắt âm thanh trailer"
        class="w-10 h-10 rounded-xl bg-black/60 hover:bg-zinc-800 text-zinc-300 hover:text-white border border-zinc-700/60 backdrop-blur-md flex items-center justify-center transition-all cursor-pointer">
        <!-- Icon Loa Tắt (Muted) -->
        <svg id="hero-mute-icon-muted" class="w-5 h-5 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
        </svg>
        <!-- Icon Loa Bật (Unmuted) -->
        <svg id="hero-mute-icon-unmuted" class="w-5 h-5 text-cine-red hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
        </svg>
      </button>

      <div id="hero-indicators" class="flex items-center gap-1.5"></div>

      <div class="flex items-center gap-2">
        <button
          id="hero-prev-btn"
          aria-label="Phim trước"
          class="w-10 h-10 rounded-xl bg-black/60 hover:bg-zinc-800 text-zinc-300 hover:text-white border border-zinc-700/60 backdrop-blur-md flex items-center justify-center transition-all cursor-pointer">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button
          id="hero-next-btn"
          aria-label="Phim sau"
          class="w-10 h-10 rounded-xl bg-black/60 hover:bg-zinc-800 text-zinc-300 hover:text-white border border-zinc-700/60 backdrop-blur-md flex items-center justify-center transition-all cursor-pointer">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>

  </section>

  <section class="relative z-30 -mt-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
    <div class="glass-panel rounded-2xl p-4 sm:p-6 shadow-[0_20px_50px_rgba(0,0,0,0.8)] border border-white/10 cinema-glow">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">

        <div class="flex flex-col gap-1.5">
          <label class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-cine-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            1. Thành phố
          </label>
          <select id="quick-city-select" class="w-full bg-zinc-900/90 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-medium focus:border-cine-red focus:outline-none transition-colors">
            <option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option>
            <option value="Hà Nội">Hà Nội</option>
            <option value="Đà Nẵng">Đà Nẵng</option>
          </select>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-cine-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            2. Rạp CINEGO
          </label>
          <select id="quick-cinema-select" class="w-full bg-zinc-900/90 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-medium focus:border-cine-red focus:outline-none transition-colors">
            <option value="">-- Đang tải cụm rạp --</option>
          </select>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-cine-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            3. Ngày xem
          </label>
          <select id="quick-date-select" class="w-full bg-zinc-900/90 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-medium focus:border-cine-red focus:outline-none transition-colors">

          </select>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-cine-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            4. Chọn phim
          </label>
          <select id="quick-movie-select" class="w-full bg-zinc-900/90 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-medium focus:border-cine-red focus:outline-none transition-colors">

          </select>
        </div>

        <div>
          <button
            id="quick-submit-btn"
            type="button"
            class="w-full py-2.5 px-4 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider shadow-lg shadow-red-600/30 flex items-center justify-center gap-2 transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            Tìm Suất Chiếu
          </button>
        </div>

      </div>
    </div>
  </section>

  <section id="now-showing" class="py-24 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
        <div>
          <div class="flex items-center gap-2 text-cine-red font-bold text-xs uppercase tracking-widest mb-2">
            <span class="w-2 h-2 rounded-full bg-cine-red"></span>
            Hiện đang khởi chiếu
          </div>
          <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Phim Đang Chiếu</h2>
          <p class="text-zinc-400 text-sm mt-1">Những bộ phim đáng xem nhất tại rạp hôm nay.</p>
        </div>

        <div id="genre-filters" class="flex flex-wrap items-center gap-2">
          <button type="button" data-genre="all" class="genre-filter-btn px-4 py-2 rounded-xl text-xs font-bold bg-cine-red text-white shadow-lg shadow-red-600/30 transition-all">Tất cả</button>
          <button type="button" data-genre="Hành động" class="genre-filter-btn px-4 py-2 rounded-xl text-xs font-medium bg-cine-card text-zinc-400 hover:text-white transition-all">Hành động</button>
          <button type="button" data-genre="Hoạt hình" class="genre-filter-btn px-4 py-2 rounded-xl text-xs font-medium bg-cine-card text-zinc-400 hover:text-white transition-all">Hoạt hình 3D</button>
          <button type="button" data-genre="Tiên hiệp" class="genre-filter-btn px-4 py-2 rounded-xl text-xs font-medium bg-cine-card text-zinc-400 hover:text-white transition-all">Tiên hiệp</button>
          <button type="button" data-genre="Khoa học viễn tưởng" class="genre-filter-btn px-4 py-2 rounded-xl text-xs font-medium bg-cine-card text-zinc-400 hover:text-white transition-all">Khoa học viễn tưởng</button>
          <button type="button" data-genre="Kinh dị" class="genre-filter-btn px-4 py-2 rounded-xl text-xs font-medium bg-cine-card text-zinc-400 hover:text-white transition-all">Kinh dị</button>
          <button type="button" data-genre="Tình cảm" class="genre-filter-btn px-4 py-2 rounded-xl text-xs font-medium bg-cine-card text-zinc-400 hover:text-white transition-all">Tình cảm</button>
        </div>
      </div>

      <div id="now-showing-grid" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">

      </div>

      <div id="movies-empty-state" class="hidden py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-zinc-800 text-zinc-500 mx-auto mb-4 flex items-center justify-center">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
        </div>
        <h3 class="text-lg font-bold text-white mb-1">Chưa có phim trong thể loại này</h3>
        <p class="text-xs text-zinc-400 mb-4">Vui lòng chọn thể loại khác hoặc quay lại danh sách tất cả phim.</p>
        <button
          type="button"
          onclick="document.querySelector('[data-genre=all]').click()"
          class="px-4 py-2 rounded-xl bg-cine-red text-white text-xs font-bold">
          Xem Tất Cả Phim
        </button>
      </div>

    </div>
  </section>

  <section id="coming-soon" class="py-20 bg-[#121217]/50 relative border-y border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="flex items-end justify-between mb-10">
        <div>
          <div class="flex items-center gap-2 text-amber-400 font-bold text-xs uppercase tracking-widest mb-2">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            Sắp ra mắt tại rạp
          </div>
          <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Phim Sắp Chiếu</h2>
          <p class="text-zinc-400 text-sm mt-1">Đón chờ những bom tấn sắp đổ bộ màn ảnh rộng CINEGO.</p>
        </div>
      </div>

      <div id="coming-soon-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

      </div>

    </div>
  </section>

  <section id="cinemas" class="py-24 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
        <div>
          <div class="flex items-center gap-2 text-cine-red font-bold text-xs uppercase tracking-widest mb-2">
            <span class="w-2 h-2 rounded-full bg-cine-red"></span>
            Cụm rạp tiêu chuẩn quốc tế
          </div>
          <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Rạp Chiếu Nổi Bật</h2>
          <p class="text-zinc-400 text-sm mt-1">Trải nghiệm rạp chiếu IMAX Laser và âm thanh vòm Dolby Atmos đỉnh cao.</p>
        </div>

        <div class="flex items-center bg-zinc-900 p-1.5 rounded-2xl border border-zinc-800 text-xs">
          <button type="button" data-city="all" class="cinema-city-tab px-4 py-2 rounded-xl bg-white text-black font-bold transition-all">Tất cả</button>
          <button type="button" data-city="TP. Hồ Chí Minh" class="cinema-city-tab px-4 py-2 rounded-xl text-zinc-400 hover:text-white transition-all">TP. Hồ Chí Minh</button>
          <button type="button" data-city="Hà Nội" class="cinema-city-tab px-4 py-2 rounded-xl text-zinc-400 hover:text-white transition-all">Hà Nội</button>
          <button type="button" data-city="Đà Nẵng" class="cinema-city-tab px-4 py-2 rounded-xl text-zinc-400 hover:text-white transition-all">Đà Nẵng</button>
        </div>
      </div>

      <div id="cinemas-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      </div>

    </div>
  </section>

  <section class="py-20 bg-zinc-950 relative border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="text-cine-red font-bold text-xs uppercase tracking-widest">Tiện lợi & Nhanh chóng</span>
        <h2 class="text-3xl sm:text-4xl font-black text-white mt-2">Đặt Vé Dễ Dàng Trong 3 Bước</h2>
        <p class="text-zinc-400 text-sm mt-2">Không còn cảnh xếp hàng dài chờ đợi, chỉ cần 3 thao tác đơn giản trên điện thoại.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">

        <div class="relative bg-cine-card p-8 rounded-3xl border border-white/5 hover:border-cine-red/40 transition-all flex flex-col items-center text-center group">
          <div class="w-16 h-16 rounded-2xl bg-cine-red/10 border border-cine-red/30 flex items-center justify-center text-cine-red text-2xl font-black mb-6 group-hover:scale-110 transition-transform shadow-[0_0_20px_rgba(229,9,20,0.2)]">
            1
          </div>
          <h3 class="text-lg font-bold text-white mb-2">Chọn Phim & Suất Chiếu</h3>
          <p class="text-xs text-zinc-400 leading-relaxed">
            Dễ dàng lựa chọn phim yêu thích, rạp chiếu gần bạn và khung giờ lý tưởng với định dạng 2D, 3D hoặc IMAX Laser.
          </p>
        </div>

        <div class="relative bg-cine-card p-8 rounded-3xl border border-white/5 hover:border-cine-red/40 transition-all flex flex-col items-center text-center group">
          <div class="w-16 h-16 rounded-2xl bg-cine-red/10 border border-cine-red/30 flex items-center justify-center text-cine-red text-2xl font-black mb-6 group-hover:scale-110 transition-transform shadow-[0_0_20px_rgba(229,9,20,0.2)]">
            2
          </div>
          <h3 class="text-lg font-bold text-white mb-2">Chọn Ghế Yêu Thích</h3>
          <p class="text-xs text-zinc-400 leading-relaxed">
            Trải nghiệm sơ đồ rạp trực quan, phân biệt rõ ràng vị trí ghế Tiêu Chuẩn, hàng ghế VIP trung tâm và Sweetbox đôi.
          </p>
        </div>

        <div class="relative bg-cine-card p-8 rounded-3xl border border-white/5 hover:border-cine-red/40 transition-all flex flex-col items-center text-center group">
          <div class="w-16 h-16 rounded-2xl bg-cine-red/10 border border-cine-red/30 flex items-center justify-center text-cine-red text-2xl font-black mb-6 group-hover:scale-110 transition-transform shadow-[0_0_20px_rgba(229,9,20,0.2)]">
            3
          </div>
          <h3 class="text-lg font-bold text-white mb-2">Thanh Toán & Nhận Vé</h3>
          <p class="text-xs text-zinc-400 leading-relaxed">
            Thanh toán an toàn qua MoMo, VNPAY, Thẻ ngân hàng. Vé điện tử kèm mã QR quét ngay tại cửa vào rạp.
          </p>
        </div>

      </div>

    </div>
  </section>

  <section id="promotions" class="py-24 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
        <div>
          <div class="flex items-center gap-2 text-cine-red font-bold text-xs uppercase tracking-widest mb-2">
            <span class="w-2 h-2 rounded-full bg-cine-red"></span>
            Đặc quyền thành viên CINEGO
          </div>
          <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Ưu Đãi & Khuyến Mãi</h2>
          <p class="text-zinc-400 text-sm mt-1">Tận hưởng những chương trình giảm giá và combo hấp dẫn.</p>
        </div>
        <span class="text-xs text-zinc-500 italic">* Nội dung minh họa demo cho hệ thống CINEGO</span>
      </div>

      <div id="promotions-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

      </div>

    </div>
  </section>

  <section class="py-20 bg-[#121217]/40 relative border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-12 gap-4">
        <div>
          <span class="text-cine-red font-bold text-xs uppercase tracking-widest">Khán giả nói gì</span>
          <h2 class="text-3xl sm:text-4xl font-black text-white mt-1">Đánh Giá Từ Khách Hàng</h2>
          <p class="text-zinc-400 text-sm mt-1">Cảm nhận thực tế của người xem về trải nghiệm điện ảnh và dịch vụ tại CINEGO.</p>
        </div>
        <button
          type="button"
          onclick="openReviewModal()"
          class="px-5 py-3 rounded-xl bg-gradient-to-r from-[#E50914] to-red-700 hover:from-red-600 hover:to-red-800 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-red-900/40 flex items-center gap-2 cursor-pointer self-start sm:self-auto">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          <span>Viết Đánh Giá Của Bạn</span>
        </button>
      </div>

      <div id="reviews-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

      </div>

    </div>
  </section>

  <section class="py-24 relative overflow-hidden">

    <div class="absolute inset-0 spotlight-bottom"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
      <div class="glass-panel p-10 sm:p-16 rounded-3xl border border-white/10 cinema-glow-lg relative overflow-hidden">

        <div class="absolute inset-0 opacity-15 pointer-events-none">
          <img src="https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=1600&auto=format&fit=crop&q=80" alt="Cinema ambient" class="w-full h-full object-cover" />
        </div>

        <div class="relative z-10 max-w-2xl mx-auto">
          <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-cine-red/20 text-cine-red border border-cine-red/30 uppercase tracking-widest inline-block mb-4">
            Đắm chìm vào từng thước phim
          </span>
          <h2 class="text-3xl sm:text-5xl font-black text-white leading-tight mb-4">
            Sẵn Sàng Cho Một Trải Nghiệm Điện Ảnh Mới?
          </h2>
          <p class="text-zinc-300 text-sm sm:text-base leading-relaxed mb-8">
            Chọn bộ phim yêu thích, thưởng thức bắp rang bơ nóng hổi và đặt chỗ ngồi lý tưởng ngay hôm nay cùng CINEGO.
          </p>
          <button
            type="button"
            onclick="window.openBookingModal()"
            class="px-9 py-4 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-sm font-bold uppercase tracking-wider transition-all duration-300 shadow-[0_0_30px_rgba(229,9,20,0.7)] hover:shadow-[0_0_40px_rgba(229,9,20,1)] active:scale-95 inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            Đặt Vé Ngay
          </button>
        </div>

      </div>
    </div>
  </section>

  <footer class="bg-[#08080B] border-t border-white/5 pt-16 pb-12 text-zinc-400 text-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-12">

        <div class="lg:col-span-2">
          <div class="flex items-center gap-2.5 mb-4">
            <div class="w-9 h-9 rounded-xl bg-cine-red flex items-center justify-center text-white">
              <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M4 4h16c1.1 0 2 .9 2 2v3c-1.1 0-2 .9-2 2s.9 2 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c1.1 0 2-.9 2-2s-.9-2-2-2V6c0-1.1.9-2 2-2zm4 4v8h2V8H8zm6 0v8h2V8h-2z"/>
              </svg>
            </div>
            <span class="text-2xl font-black text-white">CINE<span class="text-cine-red">GO</span></span>
          </div>
          <p class="text-zinc-400 leading-relaxed max-w-sm mb-6">
            Nền tảng đặt vé xem phim trực tuyến hàng đầu Việt Nam. Mang đến trải nghiệm đặt vé nhanh chóng, tiện lợi với công nghệ rạp chiếu hiện đại nhất.
          </p>
          <div class="flex items-center gap-3">
            <a href="#" aria-label="Facebook" class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white flex items-center justify-center transition-colors">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.8c4.56-.93 8-4.96 8-9.8z"/></svg>
            </a>
            <a href="#" aria-label="YouTube" class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white flex items-center justify-center transition-colors">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.5 6.2c-.3-1-1-1.8-2-2.1C19.6 3.5 12 3.5 12 3.5s-7.6 0-9.5.6c-1 .3-1.7 1.1-2 2.1C0 8.1 0 12 0 12s0 3.9.5 5.8c.3 1 1 1.8 2 2.1 1.9.6 9.5.6 9.5.6s7.6 0 9.5-.6c1-.3 1.7-1.1 2-2.1.5-1.9.5-5.8.5-5.8s0-3.9-.5-5.8zM9.5 15.5v-7l6 3.5-6 3.5z"/></svg>
            </a>
            <a href="#" aria-label="TikTok" class="w-9 h-9 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white flex items-center justify-center transition-colors">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.5 0h2.5c.2 1.4 1 2.6 2.1 3.3.9.6 2 1 3.1 1.1v2.5c-1.3 0-2.6-.4-3.7-1.1-.3-.2-.6-.4-.8-.7v9.4c0 3.6-2.9 6.5-6.5 6.5S2.7 18.1 2.7 14.5s2.9-6.5 6.5-6.5c.5 0 1 .1 1.5.2v2.7c-.5-.2-1-.3-1.5-.3-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4V0z"/></svg>
            </a>
          </div>
        </div>

        <div>
          <h4 class="text-white font-bold text-sm mb-4">Khám Phá</h4>
          <ul class="space-y-2.5">
            <li><a href="#now-showing" class="hover:text-white transition-colors">Phim đang chiếu</a></li>
            <li><a href="#coming-soon" class="hover:text-white transition-colors">Phim sắp khởi chiếu</a></li>
            <li><a href="#cinemas" class="hover:text-white transition-colors">Danh sách rạp chiếu</a></li>
            <li><a href="#promotions" class="hover:text-white transition-colors">Ưu đãi & Khuyến mãi</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Phim IMAX Laser</a></li>
          </ul>
        </div>

        <div>
          <h4 class="text-white font-bold text-sm mb-4">Chăm Sóc Khách Hàng</h4>
          <ul class="space-y-2.5">
            <li><a href="#" class="hover:text-white transition-colors">Hướng dẫn đặt vé</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Chính sách hủy / đổi vé</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Câu hỏi thường gặp (FAQ)</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Quy định độ tuổi xem phim</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Liên hệ hợp tác & Sự kiện</a></li>
          </ul>
        </div>

        <div>
          <h4 class="text-white font-bold text-sm mb-4">Liên Hệ</h4>
          <ul class="space-y-2.5">
            <li class="flex items-start gap-2">
              <svg class="w-4 h-4 text-cine-red shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
              <span>Hotline: <strong class="text-white">1900 8181</strong> (8:00 - 23:00)</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-4 h-4 text-cine-red shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <span>Email: <strong class="text-white">hotro@cinego.vn</strong></span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-4 h-4 text-cine-red shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
              <span>Trụ sở: Tòa nhà Landmark 81, Vinhomes Central Park, Bình Thạnh, TP.HCM</span>
            </li>
          </ul>
        </div>

      </div>

      <div class="pt-8 border-t border-zinc-800/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-500">
        <p>&copy; 2026 CINEGO Vietnam. Tất cả quyền được bảo lưu. Dự án giao diện Landing Page rạp chiếu phim trực tuyến.</p>
        <div class="flex items-center gap-6">
          <a href="#" class="hover:text-zinc-300">Điều khoản sử dụng</a>
          <a href="#" class="hover:text-zinc-300">Chính sách bảo mật</a>
          <a href="#" class="hover:text-zinc-300">Quy chế hoạt động</a>
        </div>
      </div>
    </div>
  </footer>

  <div id="booking-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="modal-content bg-zinc-900 border border-zinc-700/80 rounded-3xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden shadow-2xl">

      <div class="p-5 border-b border-zinc-800 flex items-center justify-between shrink-0 bg-zinc-950/60">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-cine-red flex items-center justify-center text-white">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c-1.1 0-2 .9-2 2s.9 2 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c1.1 0 2-.9 2-2s-.9-2-2-2V6c0-1.1.9-2 2-2zm4 4v8h2V8H8zm6 0v8h2V8h-2z"/></svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-white">Đặt Vé CINEGO</h3>
            <p class="text-xs text-zinc-400">Trải nghiệm đặt vé trực tuyến nhanh chóng & tiện lợi</p>
          </div>
        </div>

        <div class="hidden sm:flex items-center gap-1.5">
          <span class="booking-step-pill">1. Suất chiếu</span>
          <span class="booking-step-pill">2. Chọn ghế</span>
          <span class="booking-step-pill">3. Bắp nước</span>
          <span class="booking-step-pill">4. Thanh toán</span>
          <span class="booking-step-pill">5. Vé điện tử</span>
        </div>

        <button id="booking-close-btn" type="button" onclick="window.closeBookingModal ? window.closeBookingModal() : null" class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="p-5 sm:p-6 overflow-y-auto flex-1">

        <div id="booking-step-1" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-zinc-950 p-4 rounded-2xl border border-zinc-800 flex flex-col items-center text-center">
              <img id="bstep1-poster" src="" alt="Poster" class="w-36 h-52 object-cover rounded-xl shadow-lg mb-3 bg-zinc-900" />
              <h4 id="bstep1-title" class="text-base font-bold text-white mb-1"></h4>
              <p id="bstep1-meta" class="text-xs text-zinc-400"></p>
            </div>

            <div class="md:col-span-2 space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Chọn Phim</label>
                  <select id="bstep1-movie" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white font-medium focus:border-cine-red focus:outline-none"></select>
                </div>
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Chọn Cụm Rạp</label>
                  <select id="bstep1-cinema" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white font-medium focus:border-cine-red focus:outline-none"></select>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Chọn Ngày Xem</label>
                <div id="bstep1-dates" class="flex gap-2 overflow-x-auto pb-2">

                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Chọn Khung Giờ Chiếu</label>
                <div id="bstep1-showtimes" class="grid grid-cols-2 sm:grid-cols-3 gap-3">

                </div>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-zinc-800 flex justify-end">
            <button id="bstep1-next-btn" type="button" class="px-6 py-2.5 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider transition-all">
              Tiếp tục: Chọn ghế &rarr;
            </button>
          </div>
        </div>

        <div id="booking-step-2" class="hidden space-y-6">
          <div class="text-center">

            <div class="curved-screen-container max-w-lg mx-auto mb-3">
              <div class="curved-screen"></div>
            </div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-zinc-400">MÀN HÌNH CHIẾU</span>
          </div>

          <div class="flex flex-wrap items-center justify-center gap-4 text-xs">
            <div class="flex items-center gap-1.5">
              <div class="w-4 h-4 rounded-t bg-zinc-800 border border-zinc-700"></div>
              <span class="text-zinc-400">Ghế Thường</span>
            </div>
            <div class="flex items-center gap-1.5">
              <div class="w-4 h-4 rounded-t bg-amber-950/40 border border-amber-500/50"></div>
              <span class="text-amber-300">Ghế VIP (+25k)</span>
            </div>
            <div class="flex items-center gap-1.5">
              <div class="w-4 h-4 rounded-t bg-pink-950/40 border border-pink-500/50"></div>
              <span class="text-pink-300">Sweetbox Đôi</span>
            </div>
            <div class="flex items-center gap-1.5">
              <div class="w-4 h-4 rounded-t bg-cine-red"></div>
              <span class="text-white">Đang chọn</span>
            </div>
            <div class="flex items-center gap-1.5">
              <div class="w-4 h-4 rounded-t bg-zinc-800/40 border border-zinc-800"></div>
              <span class="text-zinc-600">Đã bán</span>
            </div>
          </div>

          <div id="seat-map-grid" class="overflow-x-auto py-2">

          </div>

          <div class="bg-zinc-950 p-4 rounded-2xl border border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
              <div class="text-xs text-zinc-400">Ghế đã chọn:</div>
              <div id="bstep2-selected-seats" class="flex flex-wrap gap-1 mt-1"></div>
            </div>
            <div class="text-right">
              <div class="text-xs text-zinc-400">Tổng tạm tính:</div>
              <div id="bstep2-subtotal" class="text-xl font-black text-cine-red">0 đ</div>
            </div>
          </div>

          <div class="pt-4 border-t border-zinc-800 flex items-center justify-between">
            <button id="bstep2-back-btn" type="button" class="px-5 py-2.5 rounded-xl bg-zinc-800 text-white text-xs font-semibold hover:bg-zinc-700">
              &larr; Quay lại
            </button>
            <button id="bstep2-next-btn" type="button" class="px-6 py-2.5 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider transition-all">
              Tiếp tục: Bắp nước &rarr;
            </button>
          </div>
        </div>

        <div id="booking-step-3" class="hidden space-y-6">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-base font-bold text-white">Thưởng Thức Combo Bắp Nước</h4>
              <p class="text-xs text-zinc-400">Chọn thêm bắp nổ thơm lừng và nước ngọt mát lạnh để trọn vẹn buổi xem phim.</p>
            </div>
            <button id="bstep3-skip-btn" type="button" class="text-xs text-zinc-400 hover:text-white underline">
              Bỏ qua bước này &rarr;
            </button>
          </div>

          <div id="bstep3-combos-list" class="space-y-3">

          </div>

          <div class="bg-zinc-950 p-4 rounded-2xl border border-zinc-800 flex items-center justify-between">
            <span class="text-xs text-zinc-400">Tổng tiền vé & bắp nước:</span>
            <span id="bstep3-subtotal" class="text-lg font-black text-cine-red">0 đ</span>
          </div>

          <div class="pt-4 border-t border-zinc-800 flex items-center justify-between">
            <button id="bstep3-back-btn" type="button" class="px-5 py-2.5 rounded-xl bg-zinc-800 text-white text-xs font-semibold hover:bg-zinc-700">
              &larr; Chọn lại ghế
            </button>
            <button id="bstep3-next-btn" type="button" class="px-6 py-2.5 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider transition-all">
              Tiếp tục: Thanh toán &rarr;
            </button>
          </div>
        </div>

        <div id="booking-step-4" class="hidden space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <h4 class="text-sm font-bold text-white">Thông Tin Nhận Vé Điện Tử</h4>
                <?php if (!empty($cookieCustName)): ?>
                  <span class="text-[10px] text-emerald-400 bg-emerald-950/60 border border-emerald-500/40 px-2 py-0.5 rounded-full inline-flex items-center gap-1 font-medium">
                    <span>✓</span> Đã nạp từ Cookie (7 ngày)
                  </span>
                <?php endif; ?>
              </div>
              <div>
                <label class="block text-xs text-zinc-400 mb-1">Họ và tên</label>
                <input id="bstep4-name" type="text" value="<?= htmlspecialchars(!empty($cookieCustName) ? $cookieCustName : 'Nguyễn Đình An') ?>" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none" />
              </div>
              <div>
                <label class="block text-xs text-zinc-400 mb-1">Số điện thoại</label>
                <input id="bstep4-phone" type="text" value="<?= htmlspecialchars(!empty($cookieCustPhone) ? $cookieCustPhone : '0901 234 567') ?>" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none" />
              </div>
              <div>
                <label class="block text-xs text-zinc-400 mb-1">Email nhận vé</label>
                <input id="bstep4-email" type="email" value="<?= htmlspecialchars(!empty($cookieCustEmail) ? $cookieCustEmail : 'dinhan33316@gmail.com') ?>" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none font-medium" />
              </div>

              <h4 class="text-sm font-bold text-white pt-2">Phương Thức Thanh Toán Demo</h4>
              <div class="space-y-2">
                <label class="flex items-center gap-3 p-3 rounded-xl bg-zinc-800/80 border border-zinc-700 cursor-pointer hover:border-zinc-500">
                  <input type="radio" name="payment_method" value="momo" checked class="payment-method-radio text-cine-red focus:ring-0" />
                  <span class="text-xs font-semibold text-white">Ví MoMo (Khuyên dùng - Hoàn tiền 20K)</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl bg-zinc-800/80 border border-zinc-700 cursor-pointer hover:border-zinc-500">
                  <input type="radio" name="payment_method" value="vnpay" class="payment-method-radio text-cine-red focus:ring-0" />
                  <span class="text-xs font-semibold text-white">VNPAY QR (Quét mã mọi ứng dụng ngân hàng)</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl bg-zinc-800/80 border border-zinc-700 cursor-pointer hover:border-zinc-500">
                  <input type="radio" name="payment_method" value="card" class="payment-method-radio text-cine-red focus:ring-0" />
                  <span class="text-xs font-semibold text-white">Thẻ Quốc Tế Visa / Mastercard / JCB</span>
                </label>
              </div>
            </div>

            <div class="bg-zinc-950 p-5 rounded-2xl border border-zinc-800 flex flex-col justify-between">
              <div>
                <h4 class="text-sm font-bold text-white mb-3">Tóm Tắt Đơn Hàng</h4>
                <div id="bstep4-summary-details">

                </div>

                <div class="mt-4 pt-4 border-t border-zinc-800">
                  <label class="block text-xs text-zinc-400 mb-1.5">Mã Giảm Giá (Thử mã: CINEGO50)</label>
                  <div class="flex gap-2">
                    <input id="bstep4-discount-input" type="text" placeholder="Nhập mã ưu đãi..." class="flex-1 bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white uppercase focus:border-cine-red focus:outline-none" />
                    <button id="bstep4-apply-discount-btn" type="button" class="px-3.5 py-2 rounded-xl bg-zinc-700 hover:bg-zinc-600 text-white text-xs font-semibold transition-colors">
                      Áp dụng
                    </button>
                  </div>
                </div>
              </div>

              <div class="pt-4 mt-6 border-t border-zinc-800 flex items-center justify-between">
                <div>
                  <span class="text-xs text-zinc-400">Tổng thanh toán:</span>
                  <div id="bstep4-total-amount" class="text-2xl font-black text-cine-red">0 đ</div>
                </div>
                <div class="text-[11px] text-zinc-500 text-right">
                  (Đã bao gồm VAT & phí)
                </div>
              </div>
            </div>

          </div>

          <div class="pt-4 border-t border-zinc-800 flex items-center justify-between">
            <button id="bstep4-back-btn" type="button" class="px-5 py-2.5 rounded-xl bg-zinc-800 text-white text-xs font-semibold hover:bg-zinc-700">
              &larr; Bắp nước
            </button>
            <button id="bstep4-checkout-btn" type="button" class="px-7 py-3 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider shadow-lg shadow-red-600/40 transition-all">
              Xác Nhận & Thanh Toán Demo &rarr;
            </button>
          </div>
        </div>

        <div id="booking-step-5" class="hidden space-y-6">
          <div class="text-center max-w-lg mx-auto">
            <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 mx-auto flex items-center justify-center mb-2 border border-emerald-500/30 shadow-[0_0_20px_rgba(16,185,129,0.3)]">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h4 class="text-xl font-bold text-white">Đặt Vé Thành Công!</h4>

            <div id="bstep5-email-alert" class="mt-3 p-3.5 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-center gap-2.5 shadow-lg">
              <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <span>Vé điện tử và mã QR đã được gửi thành công đến email <strong id="bstep5-sent-email" class="text-white font-bold underline">dinhan33316@gmail.com</strong></span>
            </div>

            <p class="text-xs text-zinc-400 mt-2">Xuất trình mã vé điện tử bên dưới tại cổng soát vé hoặc quầy tự phục vụ.</p>
          </div>

          <div class="max-w-lg mx-auto bg-zinc-950 rounded-3xl border border-zinc-700 overflow-hidden relative shadow-2xl">

            <div class="ticket-edge-left"></div>
            <div class="ticket-edge-right"></div>

            <div class="p-6 border-b border-dashed border-zinc-800 flex gap-4">
              <img id="bstep5-poster" src="" alt="Poster" class="w-20 h-28 object-cover rounded-xl shrink-0 bg-zinc-900 border border-zinc-800" />
              <div class="flex-1 min-w-0">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cine-red text-white uppercase">Vé Điện Tử CINEGO</span>
                <h3 id="bstep5-movie" class="text-base font-bold text-white mt-1 leading-snug"></h3>
                <p id="bstep5-cinema" class="text-xs text-zinc-400 mt-1"></p>
                <div class="text-xs font-semibold text-amber-400 mt-2" id="bstep5-room"></div>
              </div>
            </div>

            <div class="p-6 bg-zinc-900/50 grid grid-cols-2 gap-4 text-xs">
              <div>
                <span class="text-zinc-500 text-[10px] uppercase font-bold">Thời gian chiếu</span>
                <div id="bstep5-time" class="font-bold text-white mt-0.5"></div>
              </div>
              <div>
                <span class="text-zinc-500 text-[10px] uppercase font-bold">Số ghế</span>
                <div id="bstep5-seats" class="font-extrabold text-cine-red mt-0.5 text-sm"></div>
              </div>
              <div>
                <span class="text-zinc-500 text-[10px] uppercase font-bold">Người nhận</span>
                <div id="bstep5-customer" class="font-medium text-zinc-300 mt-0.5"></div>
              </div>
              <div>
                <span class="text-zinc-500 text-[10px] uppercase font-bold">Tổng thanh toán</span>
                <div id="bstep5-total" class="font-bold text-white mt-0.5"></div>
              </div>
            </div>

            <div class="p-6 bg-zinc-950 border-t border-dashed border-zinc-800 flex items-center justify-between">
              <div>
                <span class="text-[10px] text-zinc-500 uppercase font-semibold">Mã Đặt Vé</span>
                <div id="bstep5-ticket-code" class="font-mono text-base font-black text-white tracking-widest"></div>
              </div>

              <div class="w-16 h-16 bg-white p-1 rounded-lg">
                <svg viewBox="0 0 100 100" class="w-full h-full fill-black">
                  <rect x="0" y="0" width="30" height="30"/>
                  <rect x="5" y="5" width="20" height="20" fill="white"/>
                  <rect x="10" y="10" width="10" height="10"/>
                  <rect x="70" y="0" width="30" height="30"/>
                  <rect x="75" y="5" width="20" height="20" fill="white"/>
                  <rect x="80" y="10" width="10" height="10"/>
                  <rect x="0" y="70" width="30" height="30"/>
                  <rect x="5" y="75" width="20" height="20" fill="white"/>
                  <rect x="10" y="80" width="10" height="10"/>
                  <rect x="40" y="10" width="20" height="10"/>
                  <rect x="40" y="40" width="20" height="20"/>
                  <rect x="70" y="50" width="20" height="30"/>
                  <rect x="30" y="70" width="30" height="10"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-zinc-800 flex items-center justify-center gap-3">
            <button id="bstep5-print-btn" type="button" class="px-5 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
              In Vé Điện Tử
            </button>
            <button id="bstep5-finish-btn" type="button" class="px-6 py-2.5 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider">
              Hoàn Tất & Đóng
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>

  <div id="trailer-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
    <div class="modal-content bg-zinc-950 border border-zinc-800 rounded-3xl w-full max-w-4xl overflow-hidden shadow-2xl">
      <div class="p-4 border-b border-zinc-800 flex items-center justify-between">
        <h3 id="trailer-title" class="text-sm font-bold text-white flex items-center gap-2">
          <svg class="w-4 h-4 text-cine-red fill-cine-red" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          Trailer Phim
        </h3>
        <button id="trailer-close-btn" class="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="aspect-video w-full bg-black">
        <iframe id="trailer-iframe" src="" class="w-full h-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      </div>
    </div>
  </div>

  <div id="search-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-start justify-center pt-20 p-4">
    <div class="modal-content bg-zinc-900 border border-zinc-700/80 rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl">
      <div class="p-4 border-b border-zinc-800 flex items-center gap-3">
        <svg class="w-5 h-5 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input
          id="search-input-field"
          type="text"
          placeholder="Nhập tên phim, diễn viên hoặc thể loại..."
          class="w-full bg-transparent text-sm text-white focus:outline-none placeholder:text-zinc-500"
        />
        <button id="search-close-btn" class="p-1.5 rounded-lg text-zinc-400 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div id="search-results-list" class="p-4 max-h-96 overflow-y-auto space-y-2">

      </div>
    </div>
  </div>

  <div id="auth-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
    <div class="modal-content bg-zinc-900 border border-zinc-700/80 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">

      <div class="flex border-b border-zinc-800 text-center">
        <button id="auth-tab-login" class="flex-1 py-3.5 text-sm font-bold text-white border-b-2 border-cine-red">
          Đăng Nhập
        </button>
        <button id="auth-tab-register" class="flex-1 py-3.5 text-sm font-medium text-zinc-400 border-b-2 border-transparent hover:text-white">
          Đăng Ký
        </button>
        <button id="auth-close-btn" class="px-4 text-zinc-400 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="p-6">

        <form id="auth-login-form" class="space-y-4">

          <div id="auth-login-error" class="hidden p-3 rounded-xl bg-red-950/60 border border-red-500/50 text-red-400 text-xs flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="auth-login-error-text">Sai mật khẩu hoặc tài khoản không tồn tại.</span>
          </div>

          <div>
            <label class="block text-xs text-zinc-400 mb-1">Email hoặc Số điện thoại</label>
            <input
              id="auth-login-email"
              type="text"
              required
              placeholder="Nhập email hoặc số điện thoại..."
              class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none transition-colors"
            />
          </div>
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="text-xs text-zinc-400">Mật khẩu</label>
              <button type="button" id="auth-forgot-link" class="text-[11px] text-cine-red hover:underline focus:outline-none font-medium">
                Quên mật khẩu?
              </button>
            </div>
            <input
              id="auth-login-password"
              type="password"
              required
              placeholder="Nhập mật khẩu..."
              class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none transition-colors"
            />
          </div>

          <button type="submit" class="w-full py-3 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider transition-all shadow-lg shadow-red-600/30 active:scale-95">
            Đăng Nhập
          </button>
        </form>

        <form id="auth-register-form" class="hidden space-y-4">
          <div id="auth-reg-error" class="hidden p-3 rounded-xl bg-red-950/60 border border-red-500/50 text-red-400 text-xs flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="auth-reg-error-text">Thông tin đăng ký không hợp lệ.</span>
          </div>

          <div>
            <label class="block text-xs text-zinc-400 mb-1">Họ và tên</label>
            <input id="auth-reg-name" type="text" required placeholder="Nguyễn Đình An" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none" />
          </div>
          <div>
            <label class="block text-xs text-zinc-400 mb-1">Số điện thoại</label>
            <input id="auth-reg-phone" type="tel" required placeholder="0901234567" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none" />
          </div>
          <div>
            <label class="block text-xs text-zinc-400 mb-1">Email</label>
            <input id="auth-reg-email" type="email" required placeholder="dinhan33316@gmail.com" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none" />
          </div>
          <div>
            <label class="block text-xs text-zinc-400 mb-1">Mật khẩu</label>
            <input id="auth-reg-password" type="password" required placeholder="Ít nhất 6 ký tự" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none" />
          </div>
          <button type="submit" class="w-full py-3 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider transition-all shadow-lg shadow-red-600/30">
            Đăng Ký Thành Viên & Nhận 50%
          </button>
        </form>

        <div id="auth-forgot-container" class="hidden space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
            <button type="button" id="auth-back-to-login-btn" class="text-xs text-zinc-400 hover:text-white flex items-center gap-1 font-semibold">
              &larr; Quay lại Đăng nhập
            </button>
            <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Khôi phục mật khẩu</span>
          </div>

          <form id="auth-forgot-step1-form" class="space-y-4">
            <p class="text-xs text-zinc-400 leading-relaxed">
              Nhập địa chỉ email đăng ký tài khoản CINEGO của bạn để nhận mã xác thực OTP gồm 6 chữ số.
            </p>

            <div>
              <label class="block text-xs text-zinc-400 mb-1">Email tài khoản</label>
              <input
                id="auth-forgot-email"
                type="text"
                required
                value="dinhan33316@gmail.com"
                placeholder="Nhập email của bạn..."
                class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none"
              />
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider transition-all shadow-lg shadow-red-600/30">
              Gửi Mã Xác Thực OTP &rarr;
            </button>
          </form>

          <form id="auth-forgot-step2-form" class="hidden space-y-3.5">

            <div class="p-3 rounded-xl bg-emerald-950/50 border border-emerald-500/40 text-emerald-300 text-xs flex items-start gap-2">
              <svg class="w-4 h-4 shrink-0 text-emerald-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <span>Mã OTP xác thực đã được gửi đến email <strong id="forgot-target-email" class="text-white">dinhan33316@gmail.com</strong> (Mã thử nghiệm: <strong class="text-amber-400 font-mono">882319</strong>).</span>
            </div>

            <div id="auth-forgot-error" class="hidden p-2.5 rounded-xl bg-red-950/60 border border-red-500/50 text-red-400 text-xs flex items-center gap-2">
              <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span id="auth-forgot-error-text">Mã OTP hoặc mật khẩu không hợp lệ.</span>
            </div>

            <div>
              <label class="block text-xs text-zinc-400 mb-1">Mã xác thực OTP (6 chữ số)</label>
              <input
                id="auth-forgot-otp"
                type="text"
                maxlength="6"
                required
                value="882319"
                placeholder="Nhập 6 số OTP..."
                class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono tracking-widest text-center focus:border-cine-red focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs text-zinc-400 mb-1">Mật khẩu mới</label>
              <input
                id="auth-forgot-newpass"
                type="password"
                required
                placeholder="Ít nhất 6 ký tự..."
                class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs text-zinc-400 mb-1">Xác nhận mật khẩu mới</label>
              <input
                id="auth-forgot-confirmpass"
                type="password"
                required
                placeholder="Nhập lại mật khẩu mới..."
                class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-cine-red focus:outline-none"
              />
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold uppercase tracking-wider transition-all shadow-lg shadow-red-600/30">
              Đặt Lại Mật Khẩu Mới
            </button>
          </form>
        </div>
      </div>

    </div>
  </div>

  <div id="user-profile-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
    <div class="modal-content bg-zinc-900 border border-zinc-700/80 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl">

      <div class="p-5 border-b border-zinc-800 flex items-center justify-between bg-zinc-950/60">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-cine-red flex items-center justify-center text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <h3 class="text-base font-bold text-white">Tài Khoản Của Tôi</h3>
        </div>
        <button id="profile-close-btn" class="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="p-6 space-y-5">

        <div class="relative rounded-2xl p-5 bg-gradient-to-tr from-zinc-950 via-zinc-900 to-zinc-800 border border-amber-500/30 overflow-hidden shadow-xl">
          <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full bg-amber-500/10 blur-2xl pointer-events-none"></div>

          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-black tracking-widest text-amber-400 uppercase">CINEGO DIAMOND VIP</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">Hạng Bạch Kim</span>
          </div>

          <div class="flex items-center gap-3.5 mb-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-cine-red to-amber-500 flex items-center justify-center text-white font-black text-lg shadow-md">
              <span id="profile-card-initial">D</span>
            </div>
            <div>
              <div id="profile-card-name" class="font-bold text-white text-base">Nguyễn Đình An</div>
              <div id="profile-card-email" class="text-xs text-zinc-400 font-mono">dinhan33316@gmail.com</div>
            </div>
          </div>

          <div class="pt-3 border-t border-zinc-700/60 flex items-center justify-between text-xs">
            <div>
              <div class="text-[10px] text-zinc-500 uppercase font-semibold">Điểm tích lũy</div>
              <div class="font-bold text-amber-400 text-sm mt-0.5">450 C-Points</div>
            </div>
            <div class="text-right">
              <div class="text-[10px] text-zinc-500 uppercase font-semibold">Ngày tham gia</div>
              <div class="font-medium text-zinc-300 mt-0.5">15/01/2026</div>
            </div>
          </div>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <label class="block text-zinc-400 mb-1">Email đăng nhập</label>
            <input id="profile-input-email" type="text" readonly class="w-full bg-zinc-800/80 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-zinc-300 font-mono focus:outline-none" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-zinc-400 mb-1">Số điện thoại</label>
              <input type="text" value="0901 234 567" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-white focus:border-cine-red focus:outline-none" />
            </div>
            <div>
              <label class="block text-zinc-400 mb-1">Thành phố chính</label>
              <input type="text" value="TP. Hồ Chí Minh" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl px-3.5 py-2.5 text-white focus:border-cine-red focus:outline-none" />
            </div>
          </div>
        </div>

        <div class="pt-2 flex items-center justify-between">
          <button
            type="button"
            id="profile-logout-btn"
            class="px-4 py-2.5 rounded-xl bg-red-950/40 hover:bg-red-900/60 text-red-400 border border-red-500/30 text-xs font-bold transition-all">
            Đăng Xuất
          </button>
          <button
            type="button"
            id="profile-save-btn"
            class="px-5 py-2.5 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold transition-all shadow-md">
            Lưu Thay Đổi
          </button>
        </div>
      </div>
    </div>
  </div>

  <div id="ticket-history-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
    <div class="modal-content bg-zinc-900 border border-zinc-700/80 rounded-3xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">

      <div class="p-5 border-b border-zinc-800 flex items-center justify-between bg-zinc-950/60 shrink-0">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-cine-red flex items-center justify-center text-white">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c-1.1 0-2 .9-2 2s.9 2 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c1.1 0 2-.9 2-2s-.9-2-2-2V6c0-1.1.9-2 2-2zm4 4v8h2V8H8zm6 0v8h2V8h-2z"/></svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-white">Lịch Sử Đặt Vé Của Bạn</h3>
            <p class="text-xs text-zinc-400">Danh sách các vé xem phim đã giao dịch thành công</p>
          </div>
        </div>
        <button id="history-close-btn" class="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div id="ticket-history-list" class="p-5 overflow-y-auto space-y-3.5 flex-1">

      </div>

      <div class="p-4 border-t border-zinc-800 flex items-center justify-between shrink-0 bg-zinc-950/40">
        <span class="text-xs text-zinc-500">Hiển thị vé 30 ngày gần nhất</span>
        <button
          type="button"
          onclick="closeTicketHistoryModal(); window.openBookingModal();"
          class="px-4 py-2 rounded-xl bg-cine-red hover:bg-cine-red-hover text-white text-xs font-bold transition-all shadow-md">
          + Đặt Vé Mới
        </button>
      </div>
    </div>
  </div>

  <div id="ticketLookupModal" data-alias="ticket-lookup-modal" class="modal-backdrop fixed inset-0 z-[9999] bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4">
    <div class="modal-content bg-[#14141B] border border-white/10 rounded-3xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden shadow-2xl relative">

      <div class="p-5 border-b border-white/10 flex items-center justify-between bg-[#181822]/90 shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#E50914] to-red-600 flex items-center justify-center text-white shadow-lg shadow-red-900/40">
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c-1.1 0-2 .9-2 2s.9 2 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c1.1 0 2-.9 2-2s-.9-2-2-2V6c0-1.1.9-2 2-2zm4 4v8h2V8H8zm6 0v8h2V8h-2z"/></svg>
          </div>
          <div>
            <h3 class="text-base sm:text-lg font-black text-white tracking-wide">Tra Cứu Vé Của Tôi</h3>
            <p class="text-xs text-zinc-400">Tìm kiếm vé xem phim bằng SĐT, Email hoặc Mã vé (CGO-...)</p>
          </div>
        </div>
        <button id="lookup-close-btn" type="button" onclick="closeTicketLookupModal()" class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="p-5 border-b border-white/5 bg-[#0E0E13] shrink-0 space-y-3">
        <form id="ticket-lookup-form" onsubmit="event.preventDefault(); performTicketLookup();" class="flex gap-2">
          <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-500">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            </span>
            <input
              id="ticket-lookup-input"
              type="text"
              required
              value="<?= htmlspecialchars(!empty($cookieCustPhone) ? $cookieCustPhone : (!empty($cookieCustEmail) ? $cookieCustEmail : '')) ?>"
              placeholder="Nhập Số điện thoại, Email hoặc Mã vé (vd: CGO-382235)..."
              class="w-full bg-[#18181F] border border-white/10 rounded-2xl pl-10 pr-4 py-3 text-xs sm:text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all"
            />
          </div>
          <button
            id="ticket-lookup-submit-btn"
            type="submit"
            class="px-5 py-3 rounded-2xl bg-gradient-to-r from-[#E50914] to-red-700 hover:from-red-600 hover:to-red-800 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-red-900/30 flex items-center gap-1.5 shrink-0 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
            <span class="hidden sm:inline">Tra Cứu</span>
          </button>
        </form>

        <?php if (!empty($cookieCustPhone) || !empty($cookieCustEmail)): ?>
          <div class="flex items-center flex-wrap gap-2 text-[11px] text-zinc-400">
            <span class="text-zinc-500">💡 Gợi ý từ Cookie của bạn:</span>
            <?php if (!empty($cookieCustPhone)): ?>
              <button
                type="button"
                onclick="performTicketLookup('<?= htmlspecialchars($cookieCustPhone) ?>');"
                class="px-2.5 py-1 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white font-medium border border-zinc-700 flex items-center gap-1 transition-colors cursor-pointer">
                <span>📱 <?= htmlspecialchars($cookieCustPhone) ?></span>
              </button>
            <?php endif; ?>
            <?php if (!empty($cookieCustEmail)): ?>
              <button
                type="button"
                onclick="performTicketLookup('<?= htmlspecialchars($cookieCustEmail) ?>');"
                class="px-2.5 py-1 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white font-medium border border-zinc-700 flex items-center gap-1 transition-colors cursor-pointer">
                <span>✉️ <?= htmlspecialchars($cookieCustEmail) ?></span>
              </button>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <div id="ticket-lookup-results" class="p-5 overflow-y-auto space-y-4 flex-1 min-h-[260px] max-h-[55vh]">

        <div class="text-center py-12 text-zinc-500">
          <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-white/5 border border-white/5 flex items-center justify-center text-2xl">
            🎟️
          </div>
          <h4 class="text-sm font-bold text-zinc-300">Nhập thông tin để tra cứu vé</h4>
          <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">
            Hệ thống sẽ tìm kiếm vé đã đặt trong cơ sở dữ liệu MySQL và cung cấp liên kết xem lại file vé điện tử gốc.
          </p>
        </div>
      </div>

      <div class="p-4 border-t border-white/10 flex items-center justify-between shrink-0 bg-[#181822]/70 text-xs">
        <span class="text-zinc-500">Hỗ trợ hotline: <strong class="text-zinc-300">1900 8181</strong></span>
        <button
          type="button"
          onclick="closeTicketLookupModal();"
          class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white font-semibold transition-colors cursor-pointer">
          Đóng cửa sổ
        </button>
      </div>

    </div>
  </div>

  <!-- Modal Đánh Giá Phim (Review Modal) -->
  <div id="review-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/85 backdrop-blur-md hidden items-center justify-center p-4">
    <div class="modal-content bg-[#14141B] border border-white/10 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl relative">

      <!-- Header Modal -->
      <div class="p-5 border-b border-white/10 flex items-center justify-between bg-[#181822]/90">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-600 to-[#E50914] flex items-center justify-center text-white font-bold text-lg shadow-md shadow-red-900/30">
            ✍️
          </div>
          <div>
            <h3 class="text-base font-bold text-white">Đánh Giá Phim & Dịch Vụ</h3>
            <p class="text-xs text-zinc-400">Chia sẻ trải nghiệm điện ảnh thực tế của bạn tại CINEGO</p>
          </div>
        </div>
        <button type="button" onclick="closeReviewModal()" class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Form Đánh Giá -->
      <form id="review-form" onsubmit="handleReviewSubmit(event)" class="p-6 space-y-4 text-xs">
        <div>
          <label class="block text-zinc-300 font-semibold mb-1.5 uppercase tracking-wider text-[11px]">
            Mã Vé Điện Tử <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input
              type="text"
              id="review-ticket-code"
              name="ticket_code"
              required
              placeholder="VD: CGO-123456"
              class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-4 py-3 text-sm text-white uppercase placeholder-zinc-500 focus:outline-none focus:border-[#E50914] font-mono tracking-wider"
            />
          </div>
          <p class="text-[11px] text-zinc-500 mt-1">💡 Nhập đúng mã vé trên vé điện tử hoặc email để hệ thống xác thực người xem thật.</p>
        </div>

        <div>
          <label class="block text-zinc-300 font-semibold mb-1.5 uppercase tracking-wider text-[11px]">
            Chấm Điểm Phim <span class="text-red-500">*</span>
          </label>
          <div class="flex items-center gap-3">
            <select
              id="review-rating"
              name="rating"
              required
              class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-4 py-3 text-sm text-amber-400 font-bold focus:outline-none focus:border-[#E50914]">
              <option value="10">⭐ 10/10 - Kiệt tác xuất sắc, nhất định phải xem!</option>
              <option value="9.5" selected>⭐ 9.5/10 - Quá đỉnh, hình âm mãn nhãn</option>
              <option value="9.0">⭐ 9.0/10 - Rất hay và ý nghĩa</option>
              <option value="8.5">⭐ 8.5/10 - Phim cuốn, kỹ xảo đẹp</option>
              <option value="8.0">⭐ 8.0/10 - Trải nghiệm điện ảnh tốt</option>
              <option value="7.5">⭐ 7.5/10 - Khá hay, xem giải trí tốt</option>
              <option value="7.0">⭐ 7.0/10 - Xem được, vừa phải</option>
              <option value="6.0">⭐ 6.0/10 - Bình thường</option>
              <option value="5.0">⭐ 5.0/10 - Tạm chấp nhận</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-zinc-300 font-semibold mb-1.5 uppercase tracking-wider text-[11px]">
            Nội Dung Nhận Xét Của Bạn <span class="text-red-500">*</span>
          </label>
          <textarea
            id="review-comment"
            name="comment"
            required
            rows="4"
            placeholder="Chia sẻ cảm xúc của bạn về nội dung phim, diễn xuất, kỹ xảo hoặc dịch vụ rạp chiếu CINEGO..."
            class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl p-3.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914] resize-none"
          ></textarea>
        </div>

        <div id="review-alert" class="hidden p-3 rounded-xl text-xs"></div>

        <div class="pt-3 border-t border-white/10 flex items-center justify-end gap-3">
          <button
            type="button"
            onclick="closeReviewModal()"
            class="px-5 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-semibold transition-all cursor-pointer">
            Hủy Bỏ
          </button>
          <button
            type="submit"
            id="review-submit-btn"
            class="px-6 py-2.5 rounded-xl bg-[#E50914] hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-red-900/30 flex items-center gap-2 cursor-pointer">
            <span>Gửi Đánh Giá</span>
          </button>
        </div>
      </form>

    </div>
  </div>

  <div id="toast-container" class="fixed top-20 right-6 flex flex-col gap-2 z-50 pointer-events-none"></div>

  <div id="cinego-chat-toggle-container" class="fixed bottom-6 right-6 z-40">
    <button
      id="cinego-chat-toggle-btn"
      type="button"
      title="Trò chuyện cùng CINEGO AI"
      class="relative w-14 h-14 rounded-full bg-[#E50914] text-white flex items-center justify-center shadow-[0_10px_30px_rgba(229,9,20,0.5)] hover:shadow-[0_15px_40px_rgba(229,9,20,0.7)] hover:scale-110 active:scale-95 transition-all duration-300 border-2 border-white/20 group">

      <span class="absolute -top-1 -right-1 flex h-4 w-4">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-[#0B0B0F]"></span>
      </span>

      <svg class="w-7 h-7 text-white transform group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
      </svg>
    </button>
  </div>

  <div
    id="cinego-chatbox"
    class="fixed bottom-24 right-4 sm:right-6 z-50 w-[92vw] sm:w-[420px] h-[600px] max-h-[86vh] bg-[#0B0B0F]/95 border border-white/10 rounded-3xl backdrop-blur-2xl shadow-[0_25px_60px_rgba(0,0,0,0.95)] flex flex-col overflow-hidden chatbox-closed">

    <div class="p-4 border-b border-white/10 bg-[#18181F]/90 flex items-center justify-between shrink-0">
      <div class="flex items-center gap-3">

        <div class="relative">
          <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#E50914] to-amber-500 flex items-center justify-center text-white shadow-lg shadow-red-900/40">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
            </svg>
          </div>
          <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-[#18181F] rounded-full" title="Đang trực tuyến"></span>
        </div>

        <div>
          <div class="flex items-center gap-1.5">
            <h4 class="text-sm font-black text-white tracking-wide">CINEGO AI Assistant</h4>
            <span class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-[#E50914]/20 text-[#E50914] border border-[#E50914]/30 uppercase tracking-wider">AI Studio</span>
          </div>
          <p class="text-[11px] text-zinc-400 flex items-center gap-1.5 mt-0.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Trợ lý ảo chính thức CINEGO Cinema
          </p>
        </div>
      </div>

      <div class="flex items-center gap-1">
        <button
          id="chat-clear-btn"
          type="button"
          title="Làm mới đoạn chat"
          class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-white/10 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
        <button
          id="chat-close-btn"
          type="button"
          title="Đóng chat"
          class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-white/10 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    <div class="px-3.5 py-2.5 bg-[#121217] border-b border-white/5 shrink-0">
      <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold mb-1.5 flex items-center gap-1">
        <span>⚡ Gợi ý câu hỏi nhanh:</span>
      </div>
      <div class="grid grid-cols-2 gap-1.5">
        <button type="button" class="chat-quick-chip px-2.5 py-1.5 rounded-xl text-[11px] text-zinc-300 font-medium text-left truncate flex items-center gap-1.5">
          <span>🎬</span> <span class="truncate">Phim đang chiếu hot</span>
        </button>
        <button type="button" class="chat-quick-chip px-2.5 py-1.5 rounded-xl text-[11px] text-zinc-300 font-medium text-left truncate flex items-center gap-1.5">
          <span>🎯</span> <span class="truncate">Tư vấn phim theo sở thích</span>
        </button>
        <button type="button" class="chat-quick-chip px-2.5 py-1.5 rounded-xl text-[11px] text-zinc-300 font-medium text-left truncate flex items-center gap-1.5">
          <span>🎟️</span> <span class="truncate">Giá vé & Mã giảm giá</span>
        </button>
        <button type="button" class="chat-quick-chip px-2.5 py-1.5 rounded-xl text-[11px] text-zinc-300 font-medium text-left truncate flex items-center gap-1.5">
          <span>📱</span> <span class="truncate">Hướng dẫn đặt vé</span>
        </button>
      </div>
    </div>

    <div id="chat-messages-container" class="flex-1 p-4 overflow-y-auto space-y-3.5 text-xs">

      <div class="chat-msg chat-msg-bot flex items-start gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-[#E50914]/20 border border-[#E50914]/40 flex items-center justify-center shrink-0 text-[#E50914]">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
          </svg>
        </div>
        <div class="max-w-[85%] bg-[#18181F] border border-white/10 text-zinc-200 rounded-2xl rounded-tl-sm p-3.5 shadow-md leading-relaxed space-y-2">
          <p class="font-bold text-white text-sm">
            Xin chào! 👋 Tôi là <span class="text-[#E50914]">CINEGO AI Assistant</span>.
          </p>
          <p class="text-zinc-300">
            Tôi là trợ lý ảo chính thức của rạp CINEGO, sẵn sàng tư vấn phim bom tấn đang chiếu, gợi ý suất chiếu IMAX Laser, giá vé và tặng mã ưu đãi <span class="text-amber-400 font-mono font-bold">CINEGO50</span> giảm 50% cho bạn!
          </p>
          <div class="pt-1">
            <p class="text-[11px] text-zinc-400">Bạn có thể chọn nhanh các gợi ý bên trên hoặc nhắn tin trực tiếp cho tôi nhé!</p>
          </div>
        </div>
      </div>

    </div>

    <form id="chat-input-form" class="p-3 border-t border-white/10 bg-[#18181F]/80 shrink-0">
      <div class="relative flex items-center">
        <input
          id="chat-input-field"
          type="text"
          autocomplete="off"
          placeholder="Hỏi về phim, suất chiếu, giá vé, bắp nước..."
          class="w-full bg-[#0B0B0F] border border-white/15 rounded-2xl pl-3.5 pr-12 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all"
        />
        <button
          id="chat-send-btn"
          type="submit"
          title="Gửi tin nhắn"
          class="absolute right-1.5 w-8 h-8 rounded-xl bg-[#E50914] hover:bg-red-700 text-white flex items-center justify-center transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-md shadow-red-900/30">
          <svg class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        </button>
      </div>
      <div class="mt-1.5 px-1 flex items-center justify-between text-[10px] text-zinc-500">
        <span>Google Gemini & CINEGO Engine</span>
        <span>Nhấn Enter để gửi</span>
      </div>
    </form>
  </div>

  <script src="{{ asset('assets/js/data.js') }}"></script>
  <script>
    window.CINEGO_ROUTES = {
      api: @json(route('cinego.api')),
      chat: @json(route('cinego.chat')),
      ticket: @json(route('cinego.tickets.store'))
    };
    window.CINEGO_CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";
    window.CINEGO_COOKIES = {
      name: @json($cookieCustName),
      phone: @json($cookieCustPhone),
      email: @json($cookieCustEmail)
    };

    (function() {
      try {
        const dbShowing = @json($jsShowingMovies);
        const dbUpcoming = @json($jsUpcomingMovies);

        if (Array.isArray(dbShowing) && dbShowing.length > 0) {
          CINEGO_DATA.nowShowing = dbShowing;
        }
        if (Array.isArray(dbUpcoming) && dbUpcoming.length > 0) {
          CINEGO_DATA.comingSoon = dbUpcoming;
        }
      } catch (err) {
        console.warn("Lỗi khi đồng bộ phim từ MySQL, dùng dữ liệu mặc định data.js:", err);
      }
    })();

    function getLookupModalEl() {
      return document.getElementById("ticketLookupModal") || document.getElementById("ticket-lookup-modal");
    }

    window.openTicketLookupModal = function() {
      const modal = getLookupModalEl();
      if (!modal) return;
      modal.style.display = "flex";
      modal.classList.add("active");
      document.body.classList.add("modal-open");

      const input = document.getElementById("ticket-lookup-input") || document.getElementById("ticketLookupInput");
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
    window.openTicketLookupModal = window.openTicketLookupModal;
    var openTicketLookupModal = window.openTicketLookupModal;

    window.closeTicketLookupModal = function() {
      const modal = getLookupModalEl();
      if (!modal) return;
      modal.style.display = "none";
      modal.classList.remove("active");
      document.body.classList.remove("modal-open");
    };
    var closeTicketLookupModal = window.closeTicketLookupModal;

    window.performTicketLookup = function(customKw) {
      const input = document.getElementById("ticket-lookup-input") || document.getElementById("ticketLookupInput");
      const resultsContainer = document.getElementById("ticket-lookup-results") || document.getElementById("ticketLookupResults");
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

      resultsContainer.innerHTML = `
        <div class="text-center py-12">
          <div class="inline-block w-8 h-8 border-4 border-[#E50914] border-t-transparent rounded-full animate-spin mb-3"></div>
          <p class="text-xs text-zinc-400">Đang truy vấn vé từ cơ sở dữ liệu MySQL...</p>
        </div>
      `;

      fetch(`${window.CINEGO_ROUTES.api}?action=lookup_tickets&keyword=${encodeURIComponent(keyword)}`, {
        headers: {
          "Accept": "application/json",
          "X-CSRF-TOKEN": window.CINEGO_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ""
        }
      })
        .then(res => res.json())
        .then(data => {
          if (!data.success || !Array.isArray(data.tickets) || data.tickets.length === 0) {
            resultsContainer.innerHTML = `
              <div class="text-center py-10 bg-white/[0.02] border border-white/5 rounded-2xl p-6">
                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-zinc-800 flex items-center justify-center text-2xl">
                  🔍
                </div>
                <h4 class="text-sm font-bold text-white">Không tìm thấy vé xem phim phù hợp</h4>
                <p class="text-xs text-zinc-400 mt-1 max-w-sm mx-auto">
                  Không tìm thấy vé nào khớp với thông tin <strong class="text-amber-400">"${keyword}"</strong>. Vui lòng kiểm tra lại Số điện thoại, Email hoặc Mã vé (vd: CGO-...).
                </p>
              </div>
            `;
            return;
          }

          resultsContainer.innerHTML = `
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-white/5">
              <span class="text-xs font-bold text-zinc-300">
                Tìm thấy <span class="text-red-500 font-black">${data.tickets.length}</span> vé cho "<span class="text-white">${keyword}</span>":
              </span>
              <span class="text-[10px] text-emerald-400 bg-emerald-950/60 border border-emerald-500/30 px-2 py-0.5 rounded-full font-semibold">
                ✓ Dữ liệu MySQL tickets
              </span>
            </div>
            <div class="space-y-3.5">
              ${data.tickets.map(t => {
                const fileLink = t.ticket_file ? t.ticket_file : `sent-emails/ticket_${t.ticket_code}.html`;
                return `
                  <div class="p-4 rounded-2xl bg-[#181822] border border-white/10 hover:border-zinc-700 transition-all shadow-lg flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                    <div class="flex items-center gap-3.5 flex-1 min-w-0">
                      <img src="${t.movie_poster || 'https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg'}" alt="${t.movie_title}" class="w-14 h-20 rounded-xl object-cover shrink-0 bg-zinc-900 border border-white/10 shadow-md" onerror="this.src='https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg'" />
                      <div class="space-y-1 min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                          <span class="px-2 py-0.5 rounded bg-[#E50914] text-white text-[10px] font-black uppercase tracking-wider">${t.ticket_code}</span>
                          <span class="text-[10px] font-bold text-emerald-400 bg-emerald-950/60 border border-emerald-500/30 px-2 py-0.5 rounded">Đã thanh toán</span>
                          ${t.format ? `<span class="text-[10px] text-zinc-400 bg-zinc-800 px-1.5 py-0.5 rounded">${t.format}</span>` : ''}
                        </div>
                        <h4 class="text-sm font-extrabold text-white truncate">${t.movie_title}</h4>
                        <p class="text-xs text-zinc-400 truncate">${t.cinema_name} ${t.hall_name ? `• ${t.hall_name}` : ''}</p>
                        <div class="flex items-center gap-3 text-xs text-zinc-300 flex-wrap pt-0.5">
                          <span>Suất: <strong class="text-white">${t.showtime}</strong></span>
                          <span>Ghế: <strong class="text-[#E50914] font-bold">${t.seats}</strong></span>
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
                        onclick="navigator.clipboard.writeText('${t.ticket_code}'); if(typeof window.showToast==='function') window.showToast('Đã sao chép mã vé: ${t.ticket_code}', 'success');"
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
          resultsContainer.innerHTML = `
            <div class="text-center py-8 text-red-400">
              <p class="text-xs">Có lỗi xảy ra trong quá trình tra cứu. Vui lòng thử lại sau!</p>
            </div>
          `;
        });
    };
    var performTicketLookup = window.performTicketLookup;

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

      fetch(`${window.CINEGO_ROUTES.api}?action=cancel_ticket`, {
        method: "POST",
        headers: {
          "Accept": "application/json",
          "X-CSRF-TOKEN": window.CINEGO_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ""
        },
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const successMsg = data.message || `Đã hủy vé ${ticketCode} thành công! Ghế đã được giải phóng.`;
          if (typeof window.showToast === "function") {
            window.showToast(successMsg, "success");
          } else {
            alert(successMsg);
          }

          const seatsStr = data.seats || "";
          const releasedSeats = seatsStr.split(/[,;\s]+/).map(s => s.trim().toUpperCase()).filter(Boolean);

          if (window.AppState && window.AppState.booking) {
            if (Array.isArray(window.AppState.booking.occupiedSeats)) {
              window.AppState.booking.occupiedSeats = window.AppState.booking.occupiedSeats.filter(s => !releasedSeats.includes(s.toUpperCase()));
            }
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
          const errMsg = data.message || "Không thể hủy vé. Vui lòng thử lại!";
          if (typeof window.showToast === "function") {
            window.showToast(errMsg, "error");
          } else {
            alert(errMsg);
          }
        }
      })
      .catch(err => {
        console.error("Lỗi khi hủy vé:", err);
        const errMsg = "Lỗi kết nối máy chủ khi hủy vé!";
        if (typeof window.showToast === "function") {
          window.showToast(errMsg, "error");
        } else {
          alert(errMsg);
        }
      });
    };
    var cancelCustomerTicket = window.cancelCustomerTicket;

    document.addEventListener("DOMContentLoaded", function() {
      const modal = getLookupModalEl();
      if (modal) {
        modal.addEventListener("click", function(e) {
          if (e.target === modal) {
            closeTicketLookupModal();
          }
        });
      }
    });

    document.addEventListener("keydown", function(e) {
      if (e.key === "Escape") {
        closeTicketLookupModal();
      }
    });
  </script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
