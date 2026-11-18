<?php if (empty($adminUser)): ?>
<!DOCTYPE html>
    <html lang="vi">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="csrf-token" content="{{ csrf_token() }}" />
      <title>Đăng Nhập Quản Trị | CINEGO Cinema</title>
      <script src="https://cdn.tailwindcss.com"></script>
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
      <style>
        body { font-family: 'Inter', sans-serif; background-color: #0B0B0F; color: #FFFFFF; }
      </style>
    </head>
    <body class="min-h-screen flex items-center justify-center p-4 bg-[#0B0B0F] relative overflow-hidden">

      <div class="absolute -top-40 -left-40 w-96 h-96 bg-red-600/20 rounded-full blur-[120px] pointer-events-none"></div>
      <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-amber-500/15 rounded-full blur-[120px] pointer-events-none"></div>

      <div class="w-full max-w-md bg-[#18181F]/90 backdrop-blur-2xl border border-white/10 rounded-3xl p-8 shadow-2xl relative z-10">

        <div class="text-center mb-8">
          <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-gradient-to-r from-[#E50914] to-red-700 text-white font-black text-xl tracking-wider shadow-lg shadow-red-900/40 mb-3">
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v3c-1.1 0-2 .9-2 2s.9 2 2 2v3c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-3c1.1 0 2-.9 2-2s-.9-2-2-2V6c0-1.1.9-2 2-2zm4 4v8h2V8H8zm6 0v8h2V8h-2z"/></svg>
            <span>CINEGO ADMIN</span>
          </div>
          <h2 class="text-xl font-bold text-white tracking-tight">Hệ Thống Quản Trị Rạp Phim</h2>
          <p class="text-xs text-zinc-400 mt-1">Đăng nhập tài khoản quản trị viên để tiếp tục</p>
        </div>

        <?php if ($loginError): ?>
          <div class="mb-5 p-3.5 rounded-xl bg-red-950/60 border border-red-500/50 text-red-300 text-xs flex items-center gap-2.5">
            <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><?php echo htmlspecialchars($loginError); ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" action="{{ route('admin') }}" class="space-y-4">
          @csrf
          <div>
            <label class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-1.5">Tên Đăng Nhập</label>
            <div class="relative">
              <input
                type="text"
                name="username"
                required
                value="admin"
                placeholder="Nhập username..."
                class="w-full bg-[#0B0B0F] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-1.5">Mật Khẩu</label>
            <div class="relative">
              <input
                type="password"
                name="password"
                required
                placeholder="Nhập mật khẩu..."
                class="w-full bg-[#0B0B0F] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all"
              />
            </div>
          </div>

          <div class="p-3 rounded-xl bg-white/5 border border-white/5 text-[11px] text-zinc-400 flex items-center justify-between">
            <span>Tài khoản mẫu: <strong class="text-white">admin</strong></span>
            <span>Mật khẩu: <strong class="text-amber-400 font-mono">123456</strong></span>
          </div>

          <button
            type="submit"
            name="login_btn"
            class="w-full py-3.5 rounded-xl bg-gradient-to-r from-[#E50914] to-red-700 hover:from-red-600 hover:to-red-800 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-red-900/30 cursor-pointer">
            Đăng Nhập Quản Trị
          </button>
        </form>

        <div class="mt-6 text-center">
          <a href="{{ route('home') }}" class="text-xs text-zinc-400 hover:text-white transition-colors inline-flex items-center gap-1.5">
            &larr; Quay về trang chủ website CINEGO
          </a>
        </div>
      </div>
    </body>
    </html>

<?php else: ?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Bảng Điều Khiển Quản Trị CINEGO | Admin Dashboard</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #0B0B0F; color: #FFFFFF; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #0B0B0F; }
    ::-webkit-scrollbar-thumb { background: #272732; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #E50914; }
  </style>
</head>
<body class="min-h-screen bg-[#0B0B0F] text-zinc-100 flex flex-col">

  <header class="bg-[#121217] border-b border-white/10 sticky top-0 z-40 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

      <div class="flex items-center gap-3">
        <a href="{{ route('admin') }}" class="flex items-center gap-2">
          <span class="px-2.5 py-1 rounded-xl bg-[#E50914] text-white font-black text-base tracking-wider">CINEGO</span>
          <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest hidden sm:inline">Admin Portal</span>
        </a>
        <span class="text-zinc-600">|</span>
        <a href="{{ route('home') }}" target="_blank" class="text-xs text-zinc-400 hover:text-white transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          <span>Xem Trang Chủ</span>
        </a>
      </div>

      <div class="flex items-center gap-3.5">
        <div class="flex items-center gap-2 text-xs">
          <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#E50914] to-amber-500 flex items-center justify-center font-black text-white shadow-md">
            A
          </div>
          <div class="hidden sm:block text-left">
            <div class="font-bold text-white leading-tight"><?php echo htmlspecialchars($adminUser['full_name'] ?? 'Admin'); ?></div>
            <div class="text-[10px] text-zinc-400">@<?php echo htmlspecialchars($adminUser['username'] ?? 'admin'); ?> (Quản trị viên)</div>
          </div>
        </div>

        <a
          href="{{ route('admin') }}?action=logout"
          onclick="return confirm('Bạn có chắc chắn muốn đăng xuất khỏi trang Quản trị?');"
          class="px-3.5 py-2 rounded-xl bg-red-950/40 hover:bg-red-900/60 text-red-400 border border-red-500/30 text-xs font-bold transition-all flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          <span class="hidden sm:inline">Đăng Xuất</span>
        </a>
      </div>

    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-6">

    <?php if ($alertMessage): ?>
      <div class="p-4 rounded-2xl flex items-center gap-3 text-xs font-medium <?php echo $alertType === 'success' ? 'bg-emerald-950/60 border border-emerald-500/40 text-emerald-300' : 'bg-red-950/60 border border-red-500/40 text-red-300'; ?>">
        <?php if ($alertType === 'success'): ?>
          <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?php else: ?>
          <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?php endif; ?>
        <span><?php echo htmlspecialchars($alertMessage); ?></span>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

      <div class="bg-[#18181F] p-5 rounded-2xl border border-white/5 hover:border-red-500/30 transition-all flex items-center justify-between shadow-lg relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-red-600/10 rounded-full blur-xl group-hover:bg-red-600/20 transition-all"></div>
        <div>
          <span class="text-[11px] text-zinc-400 uppercase tracking-wider font-semibold block">Tổng Số Phim</span>
          <div class="text-2xl sm:text-3xl font-black text-white mt-1"><?php echo $totalMoviesCount; ?> <span class="text-xs font-semibold text-zinc-400">phim</span></div>
          <div class="text-[11px] text-zinc-400 mt-1 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span><?php echo $totalMoviesShowing; ?> Đang Chiếu • <?php echo $totalMoviesUpcoming; ?> Sắp Chiếu</span>
          </div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-red-600 to-[#E50914] text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-red-900/40 shrink-0">
          🎬
        </div>
      </div>

      <div class="bg-[#18181F] p-5 rounded-2xl border border-white/5 hover:border-emerald-500/30 transition-all flex items-center justify-between shadow-lg relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-600/10 rounded-full blur-xl group-hover:bg-emerald-600/20 transition-all"></div>
        <div>
          <span class="text-[11px] text-zinc-400 uppercase tracking-wider font-semibold block">Tổng Vé Đã Bán</span>
          <div class="text-2xl sm:text-3xl font-black text-emerald-400 mt-1"><?php echo number_format($totalTickets); ?> <span class="text-xs font-semibold text-emerald-300">vé</span></div>
          <div class="text-[11px] text-zinc-400 mt-1 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span>Từ bảng tickets (MySQL)</span>
          </div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-emerald-900/40 shrink-0">
          🎟️
        </div>
      </div>

      <div class="bg-[#18181F] p-5 rounded-2xl border border-white/5 hover:border-amber-500/30 transition-all flex items-center justify-between shadow-lg relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-amber-500/10 rounded-full blur-xl group-hover:bg-amber-500/20 transition-all"></div>
        <div>
          <span class="text-[11px] text-zinc-400 uppercase tracking-wider font-semibold block">Tổng Doanh Thu Ước Tính</span>
          <div class="text-2xl sm:text-3xl font-black text-amber-400 mt-1 tracking-tight">
            <?php echo number_format($totalRevenue, 0, ',', '.'); ?> <span class="text-xs font-bold text-amber-300">đ</span>
          </div>
          <div class="text-[11px] text-zinc-400 mt-1 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            <span>Tính tổng cột total_amount</span>
          </div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-amber-900/40 shrink-0">
          💰
        </div>
      </div>

      <div class="bg-[#18181F] p-5 rounded-2xl border border-white/5 hover:border-purple-500/30 transition-all flex items-center justify-between shadow-lg relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-purple-600/10 rounded-full blur-xl group-hover:bg-purple-600/20 transition-all"></div>
        <div class="max-w-[70%]">
          <span class="text-[11px] text-zinc-400 uppercase tracking-wider font-semibold block">Phim Đặt Nhiều Nhất</span>
          <div class="text-base sm:text-lg font-black text-white mt-1 truncate" title="<?php echo !empty($topBookedMovie['movie_title']) ? htmlspecialchars($topBookedMovie['movie_title']) : 'Chưa có dữ liệu'; ?>">
            <?php echo !empty($topBookedMovie['movie_title']) ? htmlspecialchars($topBookedMovie['movie_title']) : 'Chưa có vé nào'; ?>
          </div>
          <div class="text-[11px] text-purple-300 font-semibold mt-1 flex items-center gap-1.5">
            <span>🔥</span>
            <span><?php echo !empty($topBookedMovie['ticket_count']) ? ($topBookedMovie['ticket_count'] . ' vé đã giao dịch') : 'Chưa ghi nhận vé'; ?></span>
          </div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-purple-900/40 shrink-0">
          🏆
        </div>
      </div>

    </div>

    <!-- Khu vực Biểu Đồ Thống Kê Doanh Thu & Vé (Chart.js) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Biểu đồ cột/đường: Doanh thu & Số vé theo từng ngày -->
      <div class="lg:col-span-2 bg-[#18181F] p-6 rounded-3xl border border-white/10 shadow-xl flex flex-col justify-between">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-white/5 gap-2">
          <div>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-[#E50914]"></span>
              <span>Xu Hướng Doanh Thu & Lượng Vé (7 Ngày Gần Nhất)</span>
            </h3>
            <p class="text-xs text-zinc-400 mt-0.5">Biểu đồ kết hợp cột (Doanh thu VNĐ) và đường (Số lượng vé) tổng hợp theo ngày</p>
          </div>
          <div class="flex items-center gap-3 text-xs">
            <span class="inline-flex items-center gap-1.5 text-red-400 font-semibold">
              <span class="w-3 h-3 rounded bg-[#E50914]"></span> Doanh thu (VNĐ)
            </span>
            <span class="inline-flex items-center gap-1.5 text-emerald-400 font-semibold">
              <span class="w-3 h-1 bg-emerald-400"></span> Số vé
            </span>
          </div>
        </div>
        <div class="relative w-full h-72 sm:h-80">
          <canvas id="dailyRevenueChart"></canvas>
        </div>
      </div>

      <!-- Biểu đồ tròn: Tỷ lệ doanh thu theo từng phim -->
      <div class="bg-[#18181F] p-6 rounded-3xl border border-white/10 shadow-xl flex flex-col justify-between">
        <div class="pb-4 mb-4 border-b border-white/5">
          <h3 class="text-base font-bold text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
            <span>Tỷ Lệ Doanh Thu Theo Phim</span>
          </h3>
          <p class="text-xs text-zinc-400 mt-0.5">Thị phần đóng góp doanh số của các tựa phim ăn khách</p>
        </div>
        <div class="relative w-full h-72 sm:h-80 flex items-center justify-center">
          <canvas id="movieRevenueChart"></canvas>
        </div>
      </div>

    </div>

    <div class="flex items-center gap-2 border-b border-white/10 pb-3">
      <a
        href="{{ route('admin') }}?tab=movies"
        class="px-5 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 transition-all <?php echo $activeTab === 'movies' ? 'bg-[#E50914] text-white shadow-lg shadow-red-900/30' : 'bg-[#18181F] text-zinc-400 hover:text-white border border-white/5'; ?>">
        <span>🎬</span>
        <span>Tab 1: Quản Lý Phim (<?php echo ($totalMoviesShowing + $totalMoviesUpcoming); ?>)</span>
      </a>

      <a
        href="{{ route('admin') }}?tab=tickets"
        class="px-5 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 transition-all <?php echo $activeTab === 'tickets' ? 'bg-[#E50914] text-white shadow-lg shadow-red-900/30' : 'bg-[#18181F] text-zinc-400 hover:text-white border border-white/5'; ?>">
        <span>🎟️</span>
        <span>Tab 2: Quản Lý Vé Đã Đặt (<?php echo $totalTickets; ?>)</span>
      </a>
    </div>

    <?php if ($activeTab === 'movies'): ?>

      <div id="movie-form-section" class="bg-[#18181F] rounded-3xl border border-white/10 p-6 shadow-xl">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-white/10">
          <div>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-[#E50914]"></span>
              <span><?php echo $editMovie ? 'Chỉnh Sửa Bộ Phim: ' . htmlspecialchars($editMovie['title']) : 'Thêm Phim Mới Vào Rạp'; ?></span>
            </h3>
            <p class="text-xs text-zinc-400 mt-0.5">Nhập thông tin phim, chọn ảnh tải lên từ máy tính hoặc link ảnh và soạn thảo mô tả bằng CKEditor.</p>
          </div>

          <?php if ($editMovie): ?>
            <a href="{{ route('admin') }}?tab=movies" class="px-3.5 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-semibold transition-all">
              &times; Hủy Chỉnh Sửa
            </a>
          <?php endif; ?>
        </div>

        <form method="POST" action="{{ route('admin') }}?tab=movies" enctype="multipart/form-data" class="space-y-5">
            @csrf
          <input type="hidden" name="movie_id" value="<?php echo $editMovie ? $editMovie['id'] : ''; ?>">

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">

            <div>
              <label class="block text-zinc-300 font-semibold mb-1.5">Tên phim (Tiêu đề chính) <span class="text-red-500">*</span></label>
              <input
                type="text"
                name="title"
                required
                placeholder="VD: Deadpool & Wolverine"
                value="<?php echo htmlspecialchars($editMovie['title'] ?? ''); ?>"
                class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-3.5 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914]"
              />
            </div>

            <div>
              <label class="block text-zinc-300 font-semibold mb-1.5">Tên phim tiếng Việt / Phụ đề</label>
              <input
                type="text"
                name="vietnamese_title"
                placeholder="VD: Deadpool & Wolverine"
                value="<?php echo htmlspecialchars($editMovie['vietnamese_title'] ?? ''); ?>"
                class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-3.5 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914]"
              />
            </div>

            <div>
              <label class="block text-zinc-300 font-semibold mb-1.5">Thể loại phim <span class="text-red-500">*</span></label>
              <input
                type="text"
                name="genre"
                required
                placeholder="VD: Hành động, Khoa học viễn tưởng"
                value="<?php echo htmlspecialchars($editMovie['genre'] ?? ''); ?>"
                class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-3.5 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914]"
              />
            </div>

            <div>
              <label class="block text-zinc-300 font-semibold mb-1.5">Trạng thái phát hành <span class="text-red-500">*</span></label>
              <select
                name="status"
                class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-[#E50914]">
                <option value="showing" <?php echo ($editMovie && ($editMovie['status'] === 'showing' || $editMovie['status'] === 'now_showing')) ? 'selected' : ''; ?>>
                  🔴 Phim Đang Chiếu (Now Showing)
                </option>
                <option value="upcoming" <?php echo ($editMovie && ($editMovie['status'] === 'upcoming' || $editMovie['status'] === 'coming_soon')) ? 'selected' : ''; ?>>
                  ⏳ Phim Sắp Chiếu (Coming Soon)
                </option>
              </select>
            </div>

            <div>
              <label class="block text-zinc-300 font-semibold mb-1.5">Thời lượng phim</label>
              <input
                type="text"
                name="duration"
                placeholder="VD: 128 phút"
                value="<?php echo htmlspecialchars($editMovie['duration'] ?? '120 phút'); ?>"
                class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-3.5 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914]"
              />
            </div>

            <div>
              <label class="block text-zinc-300 font-semibold mb-1.5">Điểm đánh giá (0 - 10)</label>
              <input
                type="number"
                step="0.1"
                min="0"
                max="10"
                name="rating"
                value="<?php echo htmlspecialchars($editMovie['rating'] ?? '9.0'); ?>"
                class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-3.5 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914]"
              />
            </div>

            <div>
              <label class="block text-zinc-300 font-semibold mb-1.5">Độ tuổi khán giả</label>
              <select
                name="age_limit"
                class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-[#E50914]">
                <option value="P" <?php echo ($editMovie && $editMovie['age_limit'] === 'P') ? 'selected' : ''; ?>>P - Mọi lứa tuổi</option>
                <option value="T13" <?php echo ($editMovie && $editMovie['age_limit'] === 'T13') ? 'selected' : ''; ?>>T13 - Khán giả từ 13 tuổi</option>
                <option value="T16" <?php echo ($editMovie && $editMovie['age_limit'] === 'T16') ? 'selected' : ''; ?>>T16 - Khán giả từ 16 tuổi</option>
                <option value="T18" <?php echo ($editMovie && $editMovie['age_limit'] === 'T18') ? 'selected' : ''; ?>>T18 - Khán giả từ 18 tuổi</option>
              </select>
            </div>

            <div>
              <label class="block text-zinc-300 font-semibold mb-1.5">Định dạng chiếu</label>
              <input
                type="text"
                name="formats"
                placeholder="VD: 2D, 3D, IMAX Laser"
                value="<?php echo htmlspecialchars($editMovie['formats'] ?? '2D, 3D, IMAX'); ?>"
                class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-3.5 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914]"
              />
            </div>

            <div>
              <label class="block text-zinc-300 font-semibold mb-1.5">Ngày khởi chiếu</label>
              <input
                type="text"
                name="release_date"
                placeholder="VD: 26/07/2026 hoặc Khởi chiếu: 15/11/2026"
                value="<?php echo htmlspecialchars($editMovie['release_date'] ?? date('d/m/Y')); ?>"
                class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-3.5 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914]"
              />
            </div>

          </div>

          <div class="text-xs">
            <label class="block text-zinc-300 font-semibold mb-1.5">Đường dẫn Video Trailer (YouTube Embed hoặc Watch)</label>
            <input
              type="text"
              name="trailer_url"
              placeholder="VD: https://www.youtube.com/embed/73_1biulkYk"
              value="<?php echo htmlspecialchars($editMovie['trailer_url'] ?? ''); ?>"
              class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl px-3.5 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914]"
            />
          </div>

          <div class="p-4 rounded-2xl bg-[#0B0B0F] border border-white/10 space-y-3 text-xs">
            <span class="font-bold text-white uppercase tracking-wider text-[11px] block">Hình ảnh Poster Phim:</span>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
              <div>
                <label class="block text-zinc-400 mb-1">Cách 1: Chọn tải file ảnh từ máy tính (Lưu vào assets/uploads/)</label>
                <input
                  type="file"
                  name="poster_file"
                  accept="image/*"
                  class="w-full text-xs text-zinc-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#E50914] file:text-white hover:file:bg-red-700 cursor-pointer"
                />
              </div>

              <div>
                <label class="block text-zinc-400 mb-1">Cách 2: Hoặc nhập trực tiếp link URL ảnh trực tuyến</label>
                <input
                  type="text"
                  name="poster_url"
                  placeholder="https://image.tmdb.org/t/p/w500/..."
                  value="<?php echo htmlspecialchars($editMovie['poster'] ?? ''); ?>"
                  class="w-full bg-[#18181F] border border-white/15 rounded-xl px-3.5 py-2 text-white placeholder-zinc-600 focus:outline-none focus:border-[#E50914]"
                />
              </div>
            </div>

            <?php if ($editMovie && !empty($editMovie['poster'])): ?>
              <div class="pt-2 flex items-center gap-3">
                <span class="text-zinc-500">Ảnh poster hiện tại:</span>
                <img src="<?php echo htmlspecialchars(str_starts_with($editMovie['poster'] ?? '', 'http') ? $editMovie['poster'] : asset($editMovie['poster'])); ?>" alt="Poster" class="h-16 w-11 object-cover rounded-lg border border-white/20 shadow">
                <span class="text-[11px] text-zinc-400 font-mono truncate"><?php echo htmlspecialchars($editMovie['poster']); ?></span>
              </div>
            <?php endif; ?>
          </div>

          <div>
            <label class="block text-zinc-300 font-semibold text-xs mb-1.5">Mô tả tóm tắt nội dung phim (Trình soạn thảo CKEditor 4)</label>
            <textarea
              name="description"
              id="movie_description"
              rows="5"
              class="w-full bg-[#0B0B0F] border border-white/15 rounded-xl p-3 text-white focus:outline-none focus:border-[#E50914]">
              <?php echo htmlspecialchars($editMovie['description'] ?? ''); ?>
            </textarea>
          </div>

          <div class="pt-2 flex items-center justify-end gap-3">
            <button
              type="reset"
              class="px-5 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-semibold transition-all">
              Làm Mới Form
            </button>
            <button
              type="submit"
              name="save_movie_btn"
              class="px-6 py-2.5 rounded-xl bg-[#E50914] hover:bg-red-700 text-white text-xs font-bold uppercase tracking-wider shadow-lg shadow-red-900/30 transition-all flex items-center gap-2 cursor-pointer">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span><?php echo $editMovie ? 'Lưu Thay Đổi Phim' : 'Thêm Phim Vào CSDL'; ?></span>
            </button>
          </div>
        </form>
      </div>

      <div class="bg-[#18181F] rounded-3xl border border-white/10 overflow-hidden shadow-xl">
        <div class="p-5 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <span>Danh Sách Phim Trong CSDL MySQL</span>
              <span class="px-2 py-0.5 rounded-full bg-white/10 text-white text-xs font-bold"><?php echo count($moviesList); ?> phim</span>
            </h3>
            <p class="text-xs text-zinc-400 mt-0.5">Dữ liệu này sẽ tự động đồng bộ ra giao diện trang chủ website</p>
          </div>

          <div class="flex items-center flex-wrap gap-2">
            <a
              href="{{ route('admin') }}?tab=movies&action=seed_movies"
              onclick="return confirm('Bạn có chắc chắn muốn nạp và đồng bộ đủ 100% (17 bộ phim) từ data.js vào bảng movies không?');"
              class="px-3.5 py-2 rounded-xl bg-red-950/40 hover:bg-red-900/60 border border-red-500/40 text-red-300 hover:text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm"
              title="Đồng bộ 13 phim đang chiếu và 4 phim sắp chiếu vào MySQL">
              <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              <span>Đồng Bộ 17 Phim Gốc</span>
            </a>

            <form method="GET" action="{{ route('admin') }}" class="flex items-center gap-2">
              <input type="hidden" name="tab" value="movies">
              <select
                name="filter_status"
                onchange="this.form.submit()"
                class="bg-[#0B0B0F] border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none">
                <option value="">Tất cả trạng thái</option>
                <option value="showing" <?php echo $filterStatus === 'showing' ? 'selected' : ''; ?>>Phim Đang Chiếu</option>
                <option value="upcoming" <?php echo $filterStatus === 'upcoming' ? 'selected' : ''; ?>>Phim Sắp Chiếu</option>
              </select>

              <div class="relative">
                <input
                  type="text"
                  name="movie_kw"
                  placeholder="Tìm tên phim..."
                  value="<?php echo htmlspecialchars($movieKeyword ?? ''); ?>"
                  class="bg-[#0B0B0F] border border-white/15 rounded-xl pl-3 pr-8 py-2 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914]"
                />
                <button type="submit" class="absolute right-2.5 top-2 text-zinc-400 hover:text-white">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-[#121217] text-zinc-400 font-semibold uppercase tracking-wider border-b border-white/5">
              <tr>
                <th class="py-3.5 px-4 w-12 text-center">ID</th>
                <th class="py-3.5 px-4 w-20">Poster</th>
                <th class="py-3.5 px-4">Tên Phim & Thể Loại</th>
                <th class="py-3.5 px-4">Trạng Thái</th>
                <th class="py-3.5 px-4">Thời Lượng & Điểm</th>
                <th class="py-3.5 px-4">Định Dạng</th>
                <th class="py-3.5 px-4 text-right">Thao Tác</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-zinc-300">
              <?php if (empty($moviesList)): ?>
                <tr>
                  <td colspan="7" class="py-8 text-center text-zinc-500">
                    Không tìm thấy bộ phim nào phù hợp.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($moviesList as $m): ?>
                  <tr class="hover:bg-white/[0.02] transition-colors">
                    <td class="py-3.5 px-4 text-center font-mono text-zinc-500"><?php echo $m['id']; ?></td>
                    <td class="py-3.5 px-4">
                      <img
                        src="<?php echo htmlspecialchars(str_starts_with($m['poster'] ?? '', 'http') ? $m['poster'] : asset($m['poster'])); ?>"
                        alt="Poster"
                        class="w-12 h-16 object-cover rounded-lg border border-white/10 shadow-sm"
                        onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500&auto=format&fit=crop&q=80';"
                      />
                    </td>
                    <td class="py-3.5 px-4">
                      <div class="font-bold text-white text-sm leading-snug"><?php echo htmlspecialchars($m['title']); ?></div>
                      <div class="text-[11px] text-zinc-400 mt-0.5"><?php echo htmlspecialchars($m['vietnamese_title'] ?? $m['title']); ?></div>
                      <div class="text-[11px] text-zinc-500 mt-1 flex items-center gap-1">
                        <span>🏷️ <?php echo htmlspecialchars($m['genre']); ?></span>
                      </div>
                    </td>
                    <td class="py-3.5 px-4">
                      <?php if ($m['status'] === 'showing' || $m['status'] === 'now_showing'): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-500/20 text-red-400 border border-red-500/30">
                          <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                          Đang Chiếu
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                          <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                          Sắp Chiếu
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="py-3.5 px-4 whitespace-nowrap">
                      <div>⏱️ <?php echo htmlspecialchars($m['duration']); ?></div>
                      <div class="text-amber-400 font-bold mt-0.5">⭐ <?php echo htmlspecialchars($m['rating']); ?>/10</div>
                      <div class="text-[10px] text-zinc-500 mt-0.5">Độ tuổi: <strong class="text-zinc-300"><?php echo htmlspecialchars($m['age_limit']); ?></strong></div>
                    </td>
                    <td class="py-3.5 px-4">
                      <span class="px-2 py-0.5 rounded bg-zinc-800 text-[11px] text-zinc-300 border border-white/5 font-mono">
                        <?php echo htmlspecialchars($m['formats'] ?? '2D, IMAX'); ?>
                      </span>
                    </td>
                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                      <div class="flex items-center justify-end gap-1.5">
                        <a
                          href="{{ route('admin') }}?tab=movies&action=edit_movie&id=<?php echo $m['id']; ?>#movie-form-section"
                          class="px-3 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-semibold transition-all">
                          ✏️ Sửa
                        </a>
                        <form method="POST" action="{{ route('admin') }}?tab=movies&action=delete_movie&id=<?php echo $m['id']; ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phim \'<?php echo addslashes($m['title']); ?>\' khỏi cơ sở dữ liệu không?');">
                          @csrf
                          <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-950/50 hover:bg-red-900/60 text-red-400 border border-red-500/30 text-xs font-semibold transition-all">
                          🗑️ Xóa
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php else: ?>

      <div class="bg-[#18181F] rounded-3xl border border-white/10 overflow-hidden shadow-xl">
        <div class="p-5 border-b border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
              <span>Danh Sách Vé Xem Phim Đã Đặt Thành Công (Bảng tickets)</span>
            </h3>
            <p class="text-xs text-zinc-400 mt-0.5">Dữ liệu vé tự động được ghi nhận mỗi khi khách hàng hoàn tất đặt vé trên website</p>
          </div>

          <div class="flex items-center flex-wrap gap-2.5">
            <a
              href="{{ route('admin') }}?action=export_tickets_csv"
              class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-md shadow-emerald-900/30"
              title="Tải toàn bộ danh sách vé dạng file Excel CSV chuẩn tiếng Việt UTF-8 BOM">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              <span>Xuất Báo Cáo Excel (.csv)</span>
            </a>

            <form method="GET" action="{{ route('admin') }}" class="flex items-center gap-2">
              <input type="hidden" name="tab" value="tickets">
              <div class="relative">
                <input
                  type="text"
                  name="ticket_kw"
                  placeholder="Tìm mã vé, SĐT, tên khách..."
                  value="<?php echo htmlspecialchars($ticketKeyword ?? ''); ?>"
                  class="w-60 bg-[#0B0B0F] border border-white/15 rounded-xl pl-3.5 pr-8 py-2 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-[#E50914]"
                />
                <button type="submit" class="absolute right-2.5 top-2 text-zinc-400 hover:text-white">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
              </div>
              <?php if (!empty($ticketKeyword)): ?>
                <a href="{{ route('admin') }}?tab=tickets" class="px-3 py-2 rounded-xl bg-zinc-800 text-zinc-400 hover:text-white text-xs">
                  Xóa lọc
                </a>
              <?php endif; ?>
            </form>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-[#121217] text-zinc-400 font-semibold uppercase tracking-wider border-b border-white/5">
              <tr>
                <th class="py-3.5 px-4 w-12 text-center">ID</th>
                <th class="py-3.5 px-4">Mã Vé</th>
                <th class="py-3.5 px-4">Khách Hàng</th>
                <th class="py-3.5 px-4">Phim & Rạp Chiếu</th>
                <th class="py-3.5 px-4">Suất & Ghế Ngồi</th>
                <th class="py-3.5 px-4">Combo & Tổng Tiền</th>
                <th class="py-3.5 px-4">Ngày Đặt</th>
                <th class="py-3.5 px-4 text-right">Thao Tác</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-zinc-300">
              <?php if (empty($ticketsList)): ?>
                <tr>
                  <td colspan="8" class="py-10 text-center text-zinc-500">
                    Chưa có vé nào được đặt hoặc không tìm thấy vé phù hợp với từ khóa.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($ticketsList as $t): ?>
                  <tr class="hover:bg-white/[0.02] transition-colors">
                    <td class="py-3.5 px-4 text-center font-mono text-zinc-500"><?php echo $t['id']; ?></td>
                    <td class="py-3.5 px-4">
                      <span class="px-2.5 py-1 rounded-lg bg-zinc-800 text-amber-400 font-mono font-bold text-xs border border-amber-500/30">
                        <?php echo htmlspecialchars($t['ticket_code']); ?>
                      </span>
                    </td>
                    <td class="py-3.5 px-4">
                      <div class="font-bold text-white"><?php echo htmlspecialchars($t['customer_name']); ?></div>
                      <div class="text-[11px] text-zinc-400 font-mono mt-0.5">📞 <?php echo htmlspecialchars($t['customer_phone']); ?></div>
                      <div class="text-[11px] text-zinc-500 font-mono"><?php echo htmlspecialchars($t['customer_email']); ?></div>
                    </td>
                    <td class="py-3.5 px-4">
                      <div class="font-bold text-white text-sm"><?php echo htmlspecialchars($t['movie_title']); ?></div>
                      <div class="text-[11px] text-red-400 mt-0.5 font-medium">📍 <?php echo htmlspecialchars($t['cinema_name']); ?></div>
                      <div class="text-[10px] text-zinc-500 mt-0.5"><?php echo htmlspecialchars($t['hall_name'] ?? ''); ?></div>
                    </td>
                    <td class="py-3.5 px-4">
                      <div class="font-bold text-emerald-400">🎟️ Ghế: <?php echo htmlspecialchars($t['seats']); ?></div>
                      <div class="text-[11px] text-zinc-400 mt-0.5">🕒 <?php echo htmlspecialchars($t['showtime']); ?></div>
                      <div class="text-[10px] text-zinc-500">Định dạng: <?php echo htmlspecialchars($t['format'] ?? '2D'); ?></div>
                    </td>
                    <td class="py-3.5 px-4">
                      <div class="font-bold text-amber-400 text-sm"><?php echo htmlspecialchars($t['total_amount']); ?></div>
                      <div class="text-[11px] text-zinc-400 mt-0.5">🍿 <?php echo htmlspecialchars($t['combos'] ?? 'Không'); ?></div>
                      <div class="text-[10px] text-zinc-500 mt-0.5"><?php echo htmlspecialchars($t['payment_method'] ?? 'Ví MoMo'); ?></div>
                    </td>
                    <td class="py-3.5 px-4 font-mono text-zinc-400 text-[11px] whitespace-nowrap">
                      <?php echo htmlspecialchars($t['created_at']); ?>
                    </td>
                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                      <form method="POST" action="{{ route('admin') }}?tab=tickets&action=delete_ticket&id=<?php echo $t['id']; ?>" onsubmit="return confirm('Bạn có chắc chắn muốn hủy và xóa vé \'<?php echo addslashes($t['ticket_code']); ?>\' của khách \'<?php echo addslashes($t['customer_name']); ?>\' không?');">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-950/40 hover:bg-red-900/60 text-red-400 border border-red-500/30 text-xs font-semibold transition-all">
                        🗑️ Hủy Vé
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php endif; ?>

  </main>

  <footer class="bg-[#121217] border-t border-white/5 py-4 text-center text-xs text-zinc-500 mt-auto">
    <span>CINEGO Cinema Administration &bull; Phiên bản Dynamic PHP PDO &bull; Bảng điều khiển Quản trị viên</span>
  </footer>

  <script>
    if (document.getElementById('movie_description')) {
      CKEDITOR.replace('movie_description', {
        height: 200,
        removeButtons: 'About,Save,NewPage,ExportPdf,Preview,Print',
        toolbarGroups: [
          { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
          { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
          { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align' ] },
          { name: 'links' },
          { name: 'insert' },
          { name: 'styles' },
          { name: 'colors' }
        ]
      });
    }

    // ==========================================
    // KHỞI TẠO BIỂU ĐỒ CHART.JS (DARK MODE)
    // ==========================================
    const dailyStats = <?php echo json_encode($dailyStats, JSON_UNESCAPED_UNICODE); ?>;
    const movieStats = <?php echo json_encode($movieRevenueStats, JSON_UNESCAPED_UNICODE); ?>;

    // 1. Biểu đồ cột/đường: Doanh thu & Lượng vé theo ngày
    const dailyCtx = document.getElementById('dailyRevenueChart');
    if (dailyCtx && typeof Chart !== 'undefined') {
      new Chart(dailyCtx, {
        type: 'bar',
        data: {
          labels: dailyStats.labels || [],
          datasets: [
            {
              type: 'bar',
              label: 'Doanh thu (VNĐ)',
              data: dailyStats.revenues || [],
              backgroundColor: 'rgba(229, 9, 20, 0.8)',
              hoverBackgroundColor: '#E50914',
              borderRadius: 8,
              barThickness: 28,
              yAxisID: 'y'
            },
            {
              type: 'line',
              label: 'Số vé bán ra',
              data: dailyStats.tickets || [],
              borderColor: '#10B981',
              backgroundColor: 'rgba(16, 185, 129, 0.2)',
              borderWidth: 3,
              pointBackgroundColor: '#10B981',
              pointBorderColor: '#FFFFFF',
              pointBorderWidth: 1.5,
              pointRadius: 5,
              pointHoverRadius: 7,
              tension: 0.35,
              fill: false,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#18181F',
              borderColor: 'rgba(255,255,255,0.15)',
              borderWidth: 1,
              titleColor: '#FFFFFF',
              bodyColor: '#D4D4D8',
              padding: 12,
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) label += ': ';
                  if (context.dataset.yAxisID === 'y') {
                    label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.raw);
                  } else {
                    label += context.raw + ' vé';
                  }
                  return label;
                }
              }
            }
          },
          scales: {
            x: {
              grid: { color: 'rgba(255, 255, 255, 0.05)' },
              ticks: { color: '#A1A1AA', font: { size: 11, family: 'Inter' } }
            },
            y: {
              type: 'linear',
              display: true,
              position: 'left',
              grid: { color: 'rgba(255, 255, 255, 0.06)' },
              ticks: {
                color: '#A1A1AA',
                font: { size: 10, family: 'Inter' },
                callback: function(val) {
                  if (val >= 1000000) return (val / 1000000) + 'M';
                  if (val >= 1000) return (val / 1000) + 'k';
                  return val;
                }
              }
            },
            y1: {
              type: 'linear',
              display: true,
              position: 'right',
              grid: { drawOnChartArea: false },
              ticks: {
                color: '#10B981',
                stepSize: 1,
                font: { size: 10, family: 'Inter' },
                callback: function(val) {
                  return Number.isInteger(val) ? (val + ' vé') : '';
                }
              }
            }
          }
        }
      });
    }

    // 2. Biểu đồ tròn Doughnut: Tỷ lệ doanh thu theo phim
    const movieCtx = document.getElementById('movieRevenueChart');
    if (movieCtx && typeof Chart !== 'undefined') {
      const palette = [
        '#E50914', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#6366F1'
      ];
      const hasData = movieStats.revenues && movieStats.revenues.length > 0 && movieStats.revenues.some(v => v > 0);

      new Chart(movieCtx, {
        type: 'doughnut',
        data: {
          labels: hasData ? movieStats.labels : ['Chưa có vé'],
          datasets: [{
            data: hasData ? movieStats.revenues : [1],
            backgroundColor: hasData ? palette.slice(0, movieStats.labels.length) : ['#272732'],
            borderColor: '#18181F',
            borderWidth: 3,
            hoverOffset: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                color: '#D4D4D8',
                boxWidth: 12,
                font: { size: 11, family: 'Inter' },
                padding: 10
              }
            },
            tooltip: {
              backgroundColor: '#18181F',
              borderColor: 'rgba(255,255,255,0.15)',
              borderWidth: 1,
              titleColor: '#FFFFFF',
              bodyColor: '#D4D4D8',
              padding: 12,
              callbacks: {
                label: function(context) {
                  if (!hasData) return ' Chưa ghi nhận doanh thu vé';
                  const val = context.raw || 0;
                  const formatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
                  return ' ' + context.label + ': ' + formatted;
                }
              }
            }
          },
          cutout: '65%'
        }
      });
    }
  </script>
</body>
</html>
<?php endif; ?>
