<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CinegoController extends Controller
{
    /**
     * Hiển thị giao diện trang chủ Cinego
     */
    public function home(Request $request)
    {
        $showingMovies = DB::table('movies')
            ->whereIn('status', ['showing', 'now_showing'])
            ->orderBy('id')
            ->get();

        $upcomingMovies = DB::table('movies')
            ->whereIn('status', ['upcoming', 'coming_soon'])
            ->orderBy('id')
            ->get();

        $toClientMovie = static function ($movie, $upcoming = false) {
            $genres = array_values(array_filter(array_map('trim', explode(',', $movie->genre ?? ''))));
            $formats = array_values(array_filter(array_map('trim', explode(',', $movie->formats ?? ''))));

            $trailerUrl = $movie->trailer_url ?: ($upcoming
                ? 'https://www.youtube.com/embed/cqGjhVJWtEg'
                : 'https://www.youtube.com/embed/73_1biulkYk');

            preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $trailerUrl, $ytMatch);
            $videoId = $ytMatch[1] ?? '';
            $backdrop = $videoId ? "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg" : ($movie->poster ?: '');

            $genreList = $genres ?: [$upcoming ? 'Khoa học viễn tưởng' : 'Hành động'];
            $formatList = $formats ?: ($upcoming ? ['IMAX', '2D'] : ['2D', 'IMAX']);

            $data = [
                'id'              => ($upcoming ? 'db-cs-' : 'db-m-') . $movie->id,
                'db_id'           => $movie->id,
                'title'           => $movie->title,
                'vietnameseTitle' => $movie->vietnamese_title ?: $movie->title,
                'vietnamese_title'=> $movie->vietnamese_title ?: $movie->title,
                'genres'          => $genreList,
                'genre'           => $movie->genre ?: implode(', ', $genreList),
                'formats'         => $formatList,
                'format'          => $formatList,
                'poster'          => $movie->poster,
                'movie_poster'    => $movie->poster,
                'backdrop'        => $backdrop,
                'trailerUrl'      => $trailerUrl,
                'trailer_url'     => $trailerUrl,
                'description'     => strip_tags($movie->description ?? ''),
            ];

            if ($upcoming) {
                $release = $movie->release_date ?: 'Khởi chiếu: Sắp tới';
                $data['releaseDate']  = $release;
                $data['release_date'] = $release;
            } else {
                $data['duration']     = $movie->duration ?: '120 phút';
                $data['rating']       = (float) ($movie->rating ?? 9.0);
                $data['ageRating']    = $movie->age_limit ?: 'P';
                $data['age_limit']    = $movie->age_limit ?: 'P';
                $release = $movie->release_date ?: date('d/m/Y');
                $data['releaseDate']  = $release;
                $data['release_date'] = $release;
            }

            return $data;
        };

        return view('home', [
            'jsShowingMovies' => $showingMovies->map(fn ($movie) => $toClientMovie($movie))->values()->all(),
            'jsUpcomingMovies' => $upcomingMovies->map(fn ($movie) => $toClientMovie($movie, true))->values()->all(),
            'cookieCustName' => $request->cookie('cinego_customer_name', ''),
            'cookieCustEmail' => $request->cookie('cinego_customer_email', ''),
            'cookieCustPhone' => $request->cookie('cinego_customer_phone', ''),
        ]);
    }

    /**
     * Quản trị hệ thống Admin CINEGO
     */
    public function admin(Request $request)
    {
        // Đăng xuất Admin
        if ($request->query('action') === 'logout') {
            $request->session()->forget('admin_user');
            $request->session()->regenerate();

            return redirect('/admin');
        }

        // Xử lý Đăng nhập Admin
        $loginError = null;
        if ($request->isMethod('post') && $request->has('login_btn')) {
            $username = trim((string) $request->input('username'));
            $password = (string) $request->input('password');

            $user = DB::table('users')->where('username', $username)->first();

            $passwordOk = $user && (
                Hash::check($password, $user->password) ||
                password_verify($password, $user->password) ||
                $password === $user->password
            );

            if ($user && $user->role === 'admin' && $passwordOk) {
                $request->session()->regenerate();
                $request->session()->put('admin_user', [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'role' => $user->role,
                ]);

                return redirect('/admin');
            }

            $loginError = 'Tài khoản hoặc mật khẩu không chính xác! (Mặc định: admin / 123456)';
        }

        $adminUser = $request->session()->get('admin_user');
        if (!$adminUser || ($adminUser['role'] ?? null) !== 'admin') {
            return response()
                ->view('admin', ['adminUser' => null, 'loginError' => $loginError])
                ->withHeaders(['Cache-Control' => 'no-store, private']);
        }

        $action = $request->query('action');

        // Đồng bộ phim mặc định
        if ($action === 'seed_movies') {
            $this->seedDefaultMovies();

            return redirect('/admin?tab=movies')->with(
                'flash_alert',
                ['message' => 'Đã đồng bộ đủ 100% (17 bộ phim) vào bảng movies thành công!', 'type' => 'success']
            );
        }

        // Xóa phim
        if ($request->isMethod('post') && $action === 'delete_movie') {
            $deleted = DB::table('movies')->where('id', $request->integer('id'))->delete();

            return redirect('/admin?tab=movies')->with(
                'flash_alert',
                ['message' => $deleted ? 'Đã xóa phim thành công!' : 'Không thể xóa phim.', 'type' => $deleted ? 'success' : 'error']
            );
        }

        // Hủy và xóa vé
        if ($request->isMethod('post') && $action === 'delete_ticket') {
            $ticket = DB::table('tickets')->where('id', $request->integer('id'))->first();
            $deleted = false;
            if ($ticket) {
                $cleanCode = preg_replace('/[^a-zA-Z0-9_\-]/', '', $ticket->ticket_code);
                $filePath = public_path('sent-emails/ticket_' . $cleanCode . '.html');
                if (File::exists($filePath)) {
                    @File::delete($filePath);
                }
                $deleted = (bool) DB::table('tickets')->where('id', $ticket->id)->delete();
            }

            return redirect('/admin?tab=tickets')->with(
                'flash_alert',
                ['message' => $deleted ? 'Đã hủy và xóa vé thành công khỏi hệ thống!' : 'Không thể xóa vé.', 'type' => $deleted ? 'success' : 'error']
            );
        }

        // Thêm / Cập nhật phim
        if ($request->isMethod('post') && $request->has('save_movie_btn')) {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'vietnamese_title' => ['nullable', 'string', 'max:255'],
                'genre' => ['required', 'string', 'max:150'],
                'duration' => ['required', 'string', 'max:50'],
                'rating' => ['required', 'numeric', 'between:0,10'],
                'age_limit' => ['nullable', 'string', 'max:20'],
                'formats' => ['nullable', 'string', 'max:100'],
                'poster_url' => ['nullable', 'string'],
                'poster_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
                'trailer_url' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
                'release_date' => ['nullable', 'string', 'max:100'],
                'status' => ['required', 'in:showing,upcoming,now_showing,coming_soon'],
            ]);

            $poster = trim($validated['poster_url'] ?? '');
            if ($request->hasFile('poster_file')) {
                $file = $request->file('poster_file');
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = 'poster_' . time() . '_' . Str::random(8) . '.' . $extension;
                File::ensureDirectoryExists(public_path('assets/uploads'));
                $file->move(public_path('assets/uploads'), $fileName);
                $poster = 'assets/uploads/' . $fileName;
            }

            $movie = [
                'title' => trim($validated['title']),
                'vietnamese_title' => trim($validated['vietnamese_title'] ?? $validated['title']),
                'genre' => trim($validated['genre']),
                'duration' => trim($validated['duration']),
                'rating' => $validated['rating'],
                'age_limit' => trim($validated['age_limit'] ?? 'P'),
                'formats' => trim($validated['formats'] ?? '2D, 3D, IMAX'),
                'poster' => $poster,
                'trailer_url' => trim($validated['trailer_url'] ?? ''),
                'description' => $validated['description'] ?? '',
                'release_date' => trim($validated['release_date'] ?? date('d/m/Y')),
                'status' => match ($validated['status']) {
                    'now_showing' => 'showing',
                    'coming_soon' => 'upcoming',
                    default => $validated['status'],
                },
            ];

            $movieId = $request->integer('movie_id');
            if ($movieId > 0) {
                DB::table('movies')->where('id', $movieId)->update($movie);
                $message = "Cập nhật phim '{$movie['title']}' thành công!";
            } else {
                DB::table('movies')->insert($movie);
                $message = "Thêm phim mới '{$movie['title']}' thành công!";
            }

            return redirect('/admin?tab=movies')->with('flash_alert', ['message' => $message, 'type' => 'success']);
        }

        // Xuất file CSV danh sách vé
        if ($action === 'export_tickets_csv') {
            $tickets = DB::table('tickets')->orderByDesc('id')->get();

            return response()->streamDownload(function () use ($tickets) {
                $output = fopen('php://output', 'w');
                // UTF-8 BOM cho Excel
                fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($output, [
                    'Mã vé', 'Tên khách hàng', 'Số điện thoại', 'Email', 'Tên phim',
                    'Rạp chiếu', 'Phòng chiếu', 'Suất chiếu', 'Ghế', 'Định dạng',
                    'Combo', 'Tổng tiền', 'Phương thức thanh toán', 'Ngày đặt',
                ]);

                foreach ($tickets as $ticket) {
                    fputcsv($output, [
                        $ticket->ticket_code,
                        $ticket->customer_name,
                        $ticket->customer_phone,
                        $ticket->customer_email,
                        html_entity_decode($ticket->movie_title, ENT_QUOTES, 'UTF-8'),
                        $ticket->cinema_name,
                        $ticket->hall_name,
                        $ticket->showtime,
                        $ticket->seats,
                        $ticket->format,
                        $ticket->combos ?? 'Không có',
                        $ticket->total_amount,
                        $ticket->payment_method,
                        $ticket->created_at,
                    ]);
                }

                fclose($output);
            }, 'cinego_tickets_' . date('Ymd_His') . '.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
        }

        // Lọc và tìm kiếm
        $filterStatus = $request->query('filter_status');
        $movieKeyword = $request->query('movie_kw');
        $ticketKeyword = $request->query('ticket_kw');

        $movieQuery = DB::table('movies');
        if ($filterStatus === 'showing') {
            $movieQuery->whereIn('status', ['showing', 'now_showing']);
        } elseif ($filterStatus === 'upcoming') {
            $movieQuery->whereIn('status', ['upcoming', 'coming_soon']);
        } elseif ($filterStatus) {
            $movieQuery->where('status', $filterStatus);
        }

        if ($movieKeyword) {
            $movieQuery->where(function ($query) use ($movieKeyword) {
                $query->where('title', 'like', '%' . $movieKeyword . '%')
                    ->orWhere('genre', 'like', '%' . $movieKeyword . '%')
                    ->orWhere('vietnamese_title', 'like', '%' . $movieKeyword . '%');
            });
        }

        $ticketQuery = DB::table('tickets');
        if ($ticketKeyword) {
            $ticketQuery->where(function ($query) use ($ticketKeyword) {
                $query->where('ticket_code', 'like', '%' . $ticketKeyword . '%')
                    ->orWhere('customer_phone', 'like', '%' . $ticketKeyword . '%')
                    ->orWhere('customer_name', 'like', '%' . $ticketKeyword . '%')
                    ->orWhere('movie_title', 'like', '%' . $ticketKeyword . '%');
            });
        }

        $allTickets = DB::table('tickets')->get(['movie_title', 'total_amount', 'created_at']);
        $totalRevenue = $allTickets->sum(fn ($ticket) => (int) preg_replace('/[^0-9]/', '', $ticket->total_amount ?? ''));
        $dailyStats = $this->dailyTicketStats($allTickets);
        $movieRevenueStats = $this->movieRevenueStats($allTickets, 6);
        $topMovie = $allTickets->filter(fn ($ticket) => trim($ticket->movie_title ?? '') !== '')
            ->groupBy('movie_title')
            ->map(fn ($group, $title) => ['movie_title' => html_entity_decode($title, ENT_QUOTES, 'UTF-8'), 'ticket_count' => $group->count()])
            ->sortByDesc('ticket_count')
            ->first();

        return view('admin', [
            'adminUser' => $adminUser,
            'activeTab' => $request->query('tab', 'movies'),
            'alertMessage' => session('flash_alert.message'),
            'alertType' => session('flash_alert.type', 'success'),
            'editMovie' => $action === 'edit_movie'
                ? (array) DB::table('movies')->where('id', $request->integer('id'))->first()
                : null,
            'totalMoviesCount' => DB::table('movies')->count(),
            'totalMoviesShowing' => DB::table('movies')->whereIn('status', ['showing', 'now_showing'])->count(),
            'totalMoviesUpcoming' => DB::table('movies')->whereIn('status', ['upcoming', 'coming_soon'])->count(),
            'totalTickets' => DB::table('tickets')->count(),
            'totalRevenue' => $totalRevenue,
            'topBookedMovie' => $topMovie,
            'dailyStats' => $dailyStats,
            'movieRevenueStats' => $movieRevenueStats,
            'filterStatus' => $filterStatus,
            'movieKeyword' => $movieKeyword,
            'moviesList' => $movieQuery->orderBy('id')->get()->map(fn ($movie) => (array) $movie)->all(),
            'ticketKeyword' => $ticketKeyword,
            'ticketsList' => $ticketQuery->orderByDesc('id')->get()->map(fn ($ticket) => (array) $ticket)->all(),
        ]);
    }

    /**
     * API chung của hệ thống Cinego
     */
    public function api(Request $request)
    {
        $action = $request->input('action', $request->query('action', ''));

        switch ($action) {
            case 'get_occupied_seats':
                $movie = trim((string) ($request->input('movie') ?: $request->query('movie', '')));
                $showtime = trim((string) ($request->input('showtime') ?: $request->query('showtime', '')));
                if ($movie === '') {
                    return response()->json(['success' => false, 'message' => 'Thiếu thông tin tên phim (movie)', 'seats' => []]);
                }

                $rows = $this->occupiedSeatRows($movie, $showtime);
                $seats = $this->normalizeOccupiedSeats($rows);

                return response()->json([
                    'success' => true,
                    'movie' => $movie,
                    'showtime' => $showtime,
                    'occupiedSeats' => $seats,
                    'count' => count($seats),
                ]);

            case 'lookup_tickets':
                $keyword = trim((string) ($request->input('keyword') ?: $request->query('keyword', '')));
                if ($keyword === '') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vui lòng nhập Mã vé, Số điện thoại hoặc Email để tra cứu',
                        'tickets' => [],
                    ]);
                }

                $tickets = DB::table('tickets')
                    ->where(function ($query) use ($keyword) {
                        foreach (['ticket_code', 'customer_phone', 'customer_email', 'customer_name'] as $column) {
                            $query->orWhere($column, 'like', '%' . $keyword . '%');
                        }
                    })
                    ->orderByDesc('id')
                    ->limit(30)
                    ->get();

                $results = $tickets->map(function ($ticket) {
                    $cleanCode = preg_replace('/[^a-zA-Z0-9_\-]/', '', $ticket->ticket_code);
                    $fileName = 'ticket_' . $cleanCode . '.html';

                    return [
                        'id' => (int) $ticket->id,
                        'ticket_code' => $ticket->ticket_code,
                        'customer_name' => $ticket->customer_name,
                        'customer_email' => $ticket->customer_email,
                        'customer_phone' => $ticket->customer_phone,
                        'movie_title' => html_entity_decode($ticket->movie_title, ENT_QUOTES, 'UTF-8'),
                        'movie_poster' => $ticket->movie_poster,
                        'cinema_name' => $ticket->cinema_name,
                        'hall_name' => $ticket->hall_name,
                        'showtime' => $ticket->showtime,
                        'seats' => $ticket->seats,
                        'format' => $ticket->format,
                        'combos' => $ticket->combos,
                        'total_amount' => $ticket->total_amount,
                        'payment_method' => $ticket->payment_method,
                        'created_at' => $ticket->created_at,
                        'ticket_file' => File::exists(public_path('sent-emails/' . $fileName)) ? 'sent-emails/' . $fileName : null,
                    ];
                })->all();

                return response()->json(['success' => true, 'keyword' => $keyword, 'count' => count($results), 'tickets' => $results]);

            case 'cancel_ticket':
            case 'delete_ticket':
                $identifier = trim((string) ($request->input('ticket_code') ?: $request->input('id', '')));
                if ($identifier === '') {
                    return response()->json(['success' => false, 'message' => 'Thiếu thông tin mã vé (ticket_code) hoặc ID để hủy vé']);
                }

                $ticket = DB::table('tickets')
                    ->where('ticket_code', $identifier)
                    ->when(is_numeric($identifier), fn ($query) => $query->orWhere('id', (int) $identifier))
                    ->first();

                if (!$ticket || !DB::table('tickets')->where('id', $ticket->id)->delete()) {
                    return response()->json(['success' => false, 'message' => 'Không tìm thấy vé hoặc xảy ra lỗi trong quá trình hủy vé']);
                }

                // Xóa file vé HTML nếu có
                $cleanCode = preg_replace('/[^a-zA-Z0-9_\-]/', '', $ticket->ticket_code);
                $filePath = public_path('sent-emails/ticket_' . $cleanCode . '.html');
                if (File::exists($filePath)) {
                    @File::delete($filePath);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Hủy vé ' . $ticket->ticket_code . ' thành công! Ghế đã đặt được giải phóng.',
                    'ticket_code' => $ticket->ticket_code,
                    'seats' => $ticket->seats,
                    'movie_title' => $ticket->movie_title,
                    'showtime' => $ticket->showtime,
                ]);

            case 'get_reviews':
                $limit = max(1, (int) ($request->input('limit') ?: $request->query('limit', 20)));
                $reviews = DB::table('reviews')->orderByDesc('id')->limit($limit)->get([
                    'id', 'movie_title', 'ticket_code', 'customer_name', 'rating', 'comment', 'created_at',
                ])->map(function ($review) {
                    $review->movie_title = html_entity_decode($review->movie_title, ENT_QUOTES, 'UTF-8');
                    $review->customer_name = html_entity_decode($review->customer_name, ENT_QUOTES, 'UTF-8');
                    $review->rating = (float) $review->rating;
                    $review->date_formatted = $review->created_at
                        ? date('d/m/Y', strtotime($review->created_at))
                        : $review->created_at;

                    return $review;
                })->all();

                return response()->json(['success' => true, 'count' => count($reviews), 'reviews' => $reviews]);

            case 'submit_review':
                return $this->submitReview($request);

            case 'get_movies':
                $status = $request->input('status', $request->query('status'));
                $query = DB::table('movies');
                if ($status === 'showing' || $status === 'now_showing') {
                    $query->whereIn('status', ['showing', 'now_showing']);
                } elseif ($status === 'upcoming' || $status === 'coming_soon') {
                    $query->whereIn('status', ['upcoming', 'coming_soon']);
                } elseif (!empty($status)) {
                    $query->where('status', $status);
                }
                $movies = $query->orderBy('id')->get();

                $mapMovie = static function ($m) {
                    $isUpcoming = in_array($m->status, ['upcoming', 'coming_soon'], true);
                    $genres = array_values(array_filter(array_map('trim', explode(',', $m->genre ?? ''))));
                    $formats = !empty($m->formats)
                        ? array_values(array_filter(array_map('trim', explode(',', $m->formats))))
                        : ($isUpcoming ? ['IMAX', '2D'] : ['2D', 'IMAX']);

                    $trailerUrl = $m->trailer_url ?: ($isUpcoming
                        ? 'https://www.youtube.com/embed/cqGjhVJWtEg'
                        : 'https://www.youtube.com/embed/73_1biulkYk');

                    preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $trailerUrl, $ytMatch);
                    $videoId = $ytMatch[1] ?? '';
                    $backdrop = $videoId ? "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg" : ($m->poster ?: '');

                    $genreList = $genres ?: [$isUpcoming ? 'Khoa học viễn tưởng' : 'Hành động'];

                    return [
                        'id'               => $m->id,
                        'db_id'            => $m->id,
                        'title'            => $m->title,
                        'vietnameseTitle'  => $m->vietnamese_title ?: $m->title,
                        'vietnamese_title' => $m->vietnamese_title ?: $m->title,
                        'genres'           => $genreList,
                        'genre'            => $m->genre ?: implode(', ', $genreList),
                        'duration'         => $m->duration ?: '120 phút',
                        'rating'           => (float) ($m->rating ?? 9.0),
                        'ageRating'        => $m->age_limit ?: 'P',
                        'age_limit'        => $m->age_limit ?: 'P',
                        'formats'          => $formats,
                        'format'           => $formats,
                        'poster'           => $m->poster,
                        'movie_poster'     => $m->poster,
                        'backdrop'         => $backdrop,
                        'trailerUrl'       => $trailerUrl,
                        'trailer_url'      => $trailerUrl,
                        'description'      => strip_tags($m->description ?? ''),
                        'releaseDate'      => $m->release_date ?: date('d/m/Y'),
                        'release_date'     => $m->release_date ?: date('d/m/Y'),
                        'status'           => $m->status,
                    ];
                };

                return response()->json(['success' => true, 'count' => $movies->count(), 'movies' => $movies->map($mapMovie)->values()->all()]);

            case 'get_movie':
                $movieId = (int) ($request->input('id') ?: $request->query('id', 0));
                $movie = DB::table('movies')->where('id', $movieId)->first();
                if (!$movie) {
                    return response()->json(['success' => false, 'message' => 'Không tìm thấy phim'], 404);
                }

                $isUpcoming = in_array($movie->status, ['upcoming', 'coming_soon'], true);
                $genres = array_values(array_filter(array_map('trim', explode(',', $movie->genre ?? ''))));
                $formats = !empty($movie->formats)
                    ? array_values(array_filter(array_map('trim', explode(',', $movie->formats))))
                    : ($isUpcoming ? ['IMAX', '2D'] : ['2D', 'IMAX']);

                $trailerUrl = $movie->trailer_url ?: ($isUpcoming
                    ? 'https://www.youtube.com/embed/cqGjhVJWtEg'
                    : 'https://www.youtube.com/embed/73_1biulkYk');

                preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $trailerUrl, $ytMatch);
                $videoId = $ytMatch[1] ?? '';
                $backdrop = $videoId ? "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg" : ($movie->poster ?: '');
                $genreList = $genres ?: [$isUpcoming ? 'Khoa học viễn tưởng' : 'Hành động'];

                $mapped = [
                    'id'               => $movie->id,
                    'db_id'            => $movie->id,
                    'title'            => $movie->title,
                    'vietnameseTitle'  => $movie->vietnamese_title ?: $movie->title,
                    'vietnamese_title' => $movie->vietnamese_title ?: $movie->title,
                    'genres'           => $genreList,
                    'genre'            => $movie->genre ?: implode(', ', $genreList),
                    'duration'         => $movie->duration ?: '120 phút',
                    'rating'           => (float) ($movie->rating ?? 9.0),
                    'ageRating'        => $movie->age_limit ?: 'P',
                    'age_limit'        => $movie->age_limit ?: 'P',
                    'formats'          => $formats,
                    'format'           => $formats,
                    'poster'           => $movie->poster,
                    'movie_poster'     => $movie->poster,
                    'backdrop'         => $backdrop,
                    'trailerUrl'       => $trailerUrl,
                    'trailer_url'      => $trailerUrl,
                    'description'      => strip_tags($movie->description ?? ''),
                    'releaseDate'      => $movie->release_date ?: date('d/m/Y'),
                    'release_date'     => $movie->release_date ?: date('d/m/Y'),
                    'status'           => $movie->status,
                ];

                return response()->json(['success' => true, 'movie' => $mapped]);

            case 'login':
                $username = trim((string) $request->input('username', ''));
                $password = (string) $request->input('password', '');
                $user = DB::table('users')->where('username', $username)->first();

                if ($user && (Hash::check($password, $user->password) || password_verify($password, $user->password) || $password === $user->password)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Đăng nhập thành công',
                        'user' => [
                            'id' => $user->id,
                            'username' => $user->username,
                            'full_name' => $user->full_name,
                            'role' => $user->role,
                        ],
                    ]);
                }

                return response()->json(['success' => false, 'message' => 'Sai tài khoản hoặc mật khẩu'], 401);

            case 'register':
                $username = trim((string) $request->input('username', ''));
                $password = (string) $request->input('password', '');
                $fullName = trim((string) $request->input('full_name', 'Khách hàng CINEGO'));

                if (empty($username) || empty($password)) {
                    return response()->json(['success' => false, 'message' => 'Vui lòng điền đủ tên đăng nhập và mật khẩu'], 422);
                }

                if (DB::table('users')->where('username', $username)->exists()) {
                    return response()->json(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại'], 409);
                }

                $newId = DB::table('users')->insertGetId([
                    'username' => $username,
                    'password' => Hash::make($password),
                    'full_name' => $fullName,
                    'role' => 'user',
                    'created_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Đăng ký tài khoản thành công',
                    'user' => [
                        'id' => $newId,
                        'username' => $username,
                        'full_name' => $fullName,
                        'role' => 'user',
                    ],
                ]);

            default:
                return response()->json(['success' => false, 'message' => 'Hành động không hợp lệ hoặc thiếu tham số action']);
        }
    }

    /**
     * Đặt vé xem phim và lưu vào cơ sở dữ liệu MySQL
     */
    public function storeTicket(Request $request)
    {
        $rawTicketCode = trim((string) ($request->input('ticketCode') ?: $request->input('ticket_code') ?: ('CGO-' . rand(100000, 999999))));
        $rawCustomerName = trim((string) ($request->input('customerName') ?: $request->input('customer_name') ?: 'Quý Khách'));
        $rawCustomerEmail = trim((string) ($request->input('customerEmail') ?: $request->input('customer_email') ?: 'khachhang@cinego.vn'));
        $rawCustomerPhone = trim((string) ($request->input('customerPhone') ?: $request->input('customer_phone') ?: '0901 234 567'));
        $rawMovieTitle = trim((string) ($request->input('movieTitle') ?: $request->input('movie_title') ?: 'Phim Chiếu Rạp CINEGO'));
        $rawMoviePoster = trim((string) ($request->input('moviePoster') ?: $request->input('movie_poster') ?: 'https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg'));
        $rawCinemaName = trim((string) ($request->input('cinemaName') ?: $request->input('cinema_name') ?: 'CINEGO Landmark 81 IMAX'));
        $rawHallName = trim((string) ($request->input('hallName') ?: $request->input('hall_name') ?: 'Phòng Chiếu 01'));
        $rawShowtime = trim((string) ($request->input('showtime') ?: date('H:i • d/m/Y')));
        $rawSeats = trim((string) ($request->input('seats') ?: 'A01'));
        $rawFormat = trim((string) ($request->input('format') ?: '2D Phụ đề'));
        $rawCombos = trim((string) ($request->input('combos') ?: 'Không có'));
        $rawTotalAmount = trim((string) ($request->input('totalAmount') ?: $request->input('total_amount') ?: '0 đ'));
        $rawPaymentMethod = trim((string) ($request->input('paymentMethod') ?: $request->input('payment_method') ?: 'Ví điện tử'));

        // Kiểm tra xung đột ghế đã được đặt trước trong CSDL
        $existingSeats = $this->normalizeOccupiedSeats($this->occupiedSeatRows($rawMovieTitle, $rawShowtime));
        $requestedSeats = array_values(array_filter(array_map('trim', preg_split('/[,;\s]+/', $rawSeats))));
        $conflicts = [];
        foreach ($requestedSeats as $seat) {
            $variants = [$seat];
            if (preg_match('/^([A-Za-z]+)(\d+)$/', $seat, $matches)) {
                $row = strtoupper($matches[1]);
                $column = (int) $matches[2];
                $variants[] = $row . $column;
                $variants[] = $row . sprintf('%02d', $column);
            }
            if (array_intersect($variants, $existingSeats)) {
                $conflicts[] = $seat;
            }
        }

        if ($conflicts) {
            $conflicts = array_values(array_unique($conflicts));

            return response()->json([
                'success' => false,
                'conflict' => true,
                'message' => 'Ghế [' . implode(', ', $conflicts) . '] vừa được khách hàng khác đặt trước. Vui lòng quay lại bước chọn ghế!',
                'conflictSeats' => $conflicts,
            ]);
        }

        // Lưu vé vào bảng tickets
        $ticketId = DB::table('tickets')->insertGetId([
            'ticket_code' => $rawTicketCode,
            'customer_name' => $rawCustomerName,
            'customer_email' => $rawCustomerEmail,
            'customer_phone' => $rawCustomerPhone,
            'movie_title' => $rawMovieTitle,
            'movie_poster' => $rawMoviePoster,
            'cinema_name' => $rawCinemaName,
            'hall_name' => $rawHallName,
            'showtime' => $rawShowtime,
            'seats' => $rawSeats,
            'format' => $rawFormat,
            'combos' => $rawCombos,
            'total_amount' => $rawTotalAmount,
            'payment_method' => $rawPaymentMethod,
            'created_at' => now(),
        ]);

        // Tạo file HTML vé điện tử có mã QR để khách hàng có thể mở xem lại bất cứ lúc nào
        $cleanCode = preg_replace('/[^a-zA-Z0-9_\-]/', '', $rawTicketCode);
        $fileName = 'ticket_' . $cleanCode . '.html';
        $emailHtml = $this->generateTicketEmailHtml([
            'ticketCode' => $rawTicketCode,
            'customerName' => $rawCustomerName,
            'customerEmail' => $rawCustomerEmail,
            'customerPhone' => $rawCustomerPhone,
            'movieTitle' => $rawMovieTitle,
            'moviePoster' => $rawMoviePoster,
            'cinemaName' => $rawCinemaName,
            'hallName' => $rawHallName,
            'showtime' => $rawShowtime,
            'seats' => $rawSeats,
            'format' => $rawFormat,
            'combos' => $rawCombos,
            'totalAmount' => $rawTotalAmount,
            'paymentMethod' => $rawPaymentMethod,
        ]);

        File::ensureDirectoryExists(public_path('sent-emails'));
        File::put(public_path('sent-emails/' . $fileName), $emailHtml);

        $response = response()->json([
            'success' => true,
            'emailSent' => false,
            'simulated' => true,
            'recipient' => $rawCustomerEmail,
            'ticketCode' => $rawTicketCode,
            'ticketId' => $ticketId,
            'dbSaved' => true,
            'dbError' => null,
            'movieTitle' => $rawMovieTitle,
            'cinemaName' => $rawCinemaName,
            'showtime' => $rawShowtime,
            'seats' => $rawSeats,
            'totalAmount' => $rawTotalAmount,
            'message' => "Vé điện tử và mã QR đã được tạo thành công cho {$rawCustomerEmail}",
            'previewFile' => 'sent-emails/' . $fileName,
            'smtpStatus' => 'Môi trường máy chủ: Đã tạo vé điện tử HTML hoàn chỉnh và lưu tại sent-emails/.',
        ]);

        return $response
            ->cookie('cinego_customer_name', $rawCustomerName, 10080)
            ->cookie('cinego_customer_email', $rawCustomerEmail, 10080)
            ->cookie('cinego_customer_phone', $rawCustomerPhone, 10080);
    }

    /**
     * Trợ lý ảo Chatbot AI Gemini
     */
    public function chatAi(Request $request)
    {
        $userMessage = trim((string) $request->input('message', ''));
        $history = is_array($request->input('history')) ? array_slice($request->input('history'), -6) : [];
        $movieContext = is_array($request->input('movieContext')) ? $request->input('movieContext') : [];

        // Lấy API key từ config/services.php (tương ứng với GEMINI_API_KEY trong .env)
        $apiKey = trim((string) (config('services.gemini.key') ?: config('services.gemini.api_key') ?: env('GEMINI_API_KEY', '')));
        $apiKeyConfigured = $apiKey !== '' && $apiKey !== 'YOUR_GEMINI_API_KEY' && strlen($apiKey) > 15;

        if ($userMessage === '') {
            return response()->json([
                'success' => false,
                'reply' => 'Xin chào! Tôi là Trợ lý ảo CINEGO AI. Bạn đang muốn tìm phim gì hoặc cần tư vấn suất chiếu nào hôm nay?',
                'source' => 'system',
                'apiKeyConfigured' => $apiKeyConfigured,
            ]);
        }

        $movieSummary = $movieContext
            ? "\nDanh sách phim đang chiếu tại hệ thống rạp CINEGO:\n" . collect($movieContext)->map(function ($movie) {
                return '- ' . ($movie['title'] ?? '')
                    . ' | Thể loại: ' . ($movie['genres'] ?? '')
                    . ' | Thời lượng: ' . ($movie['duration'] ?? '')
                    . ' | Điểm: ' . ($movie['rating'] ?? '') . '/10'
                    . ' | Định dạng: ' . ($movie['format'] ?? '2D, IMAX');
            })->implode("\n")
            : "\nPhim nổi bật tại CINEGO:\n- Deadpool & Wolverine (Hành động, Hài hước, T18, IMAX 3D, 9.3/10)\n- Inside Out 2 (Hoạt hình, Gia đình, P, 2D/3D, 9.5/10)\n- Bạch Xà: Duyên Khởi (Hoạt hình 3D, Tiên hiệp, T13, IMAX Laser, 9.1/10)\n- Na Tra: Ma Đồng Giáng Thế (Hoạt hình 3D, Thần thoại, P, IMAX, 9.3/10)\n- Dune: Part Two (Khoa học viễn tưởng, T16, IMAX Laser, 9.4/10)\n- Godzilla x Kong: The New Empire (Hành động, Quái thú, T13, 8.9/10)";

        $systemInstruction = <<<'PROMPT'
Bạn là CINEGO AI - Trợ lý ảo chính thức của cụm rạp chiếu phim cao cấp CINEGO tại Việt Nam.
Nhiệm vụ của bạn:
1. Tư vấn phim đang chiếu, phim sắp chiếu, thể loại phù hợp với sở thích của khách (hành động, hoạt hình, tiên hiệp, tình cảm, viễn tưởng).
2. Cung cấp thông tin giá vé, công nghệ chiếu (IMAX Laser, Dolby Atmos 7.1, 2D/3D Laser, ghế đôi Sweetbox).
3. Hướng dẫn đặt vé trực tuyến 3 bước và nhắc khách áp dụng mã giảm giá 'CINEGO50' (giảm ngay 50% cho lần đặt đầu tiên).
4. Giới thiệu các combo bắp rang thơm bơ (Combo Solo 69k, Combo Couple Bestseller 99k, Combo Family 179k).
5. Phong cách trả lời: Thân thiện, nhiệt tình, lịch sự, ngắn gọn súc tích, ngắt dòng dễ đọc, dùng emoji điện ảnh sinh động.
6. ĐẶC BIỆT: Khi giới thiệu hoặc gợi ý một bộ phim cụ thể, hãy đặt cú pháp [Đặt vé: Tên phim] ở ngay sau phim đó để hệ thống tự động tạo nút Đặt Vé Ngay cho khách hàng (ví dụ: [Đặt vé: Deadpool & Wolverine] hoặc [Đặt vé: Bạch Xà: Duyên Khởi]).
PROMPT;

        $reply = null;
        $source = 'smart_bot';

        if ($apiKeyConfigured) {
            $contents = [];
            foreach ($history as $turn) {
                $content = trim((string) ($turn['content'] ?? ''));
                if ($content !== '') {
                    $contents[] = [
                        'role' => ($turn['role'] ?? '') === 'user' ? 'user' : 'model',
                        'parts' => [['text' => $content]],
                    ];
                }
            }
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $systemInstruction . $movieSummary . "\n\nKhách hàng hỏi: " . $userMessage]],
            ];

            $payload = [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 800,
                ],
            ];

            $isVertexKey = str_starts_with($apiKey, 'AQ.');
            $models = ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-1.5-flash'];

            foreach ($models as $model) {
                $baseUrl = $isVertexKey
                    ? 'https://aiplatform.googleapis.com/v1/publishers/google/models/'
                    : 'https://generativelanguage.googleapis.com/v1beta/models/';

                try {
                    $response = Http::withoutVerifying()
                        ->timeout(12)
                        ->post($baseUrl . $model . ':generateContent?key=' . urlencode($apiKey), $payload);

                    $candidate = $response->json('candidates.0.content.parts.0.text');
                    if ($response->successful() && is_string($candidate) && trim($candidate) !== '') {
                        $reply = trim($candidate);
                        $source = 'gemini';
                        break;
                    }
                } catch (\Throwable $exception) {
                    Log::warning('CINEGO Gemini request failed', ['model' => $model, 'error' => $exception->getMessage()]);
                    continue;
                }
            }
        }

        if ($reply === null) {
            $reply = $this->generateSmartResponse($userMessage);
        }

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'source' => $source,
            'apiKeyConfigured' => $apiKeyConfigured,
        ]);
    }

    /**
     * Gửi đánh giá phản hồi sau khi xem phim
     */
    private function submitReview(Request $request)
    {
        $ticketCode = trim((string) $request->input('ticket_code', ''));
        $rating = $request->input('rating', '');
        $comment = trim((string) $request->input('comment', ''));

        if ($ticketCode === '') {
            return response()->json(['success' => false, 'message' => 'Vui lòng cung cấp Mã vé (ticket_code).']);
        }
        if (!is_numeric($rating) || (float) $rating < 1 || (float) $rating > 10) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn điểm đánh giá từ 1 đến 10.']);
        }
        if (mb_strlen($comment, 'UTF-8') < 5) {
            return response()->json(['success' => false, 'message' => 'Nội dung nhận xét phải có ít nhất 5 ký tự.']);
        }

        $ticket = DB::table('tickets')->where('ticket_code', $ticketCode)->first();
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => "Mã vé '{$ticketCode}' không tồn tại trên hệ thống CINEGO. Vui lòng kiểm tra lại vé điện tử của bạn!"]);
        }
        if (DB::table('reviews')->where('ticket_code', $ticketCode)->exists()) {
            return response()->json(['success' => false, 'message' => "Mã vé '{$ticketCode}' đã được sử dụng để gửi đánh giá trước đó rồi. Mỗi vé chỉ được đánh giá 1 lần duy nhất!"]);
        }

        $customerName = $ticket->customer_name ?: 'Khán giả CINEGO';
        $reviewId = DB::table('reviews')->insertGetId([
            'movie_title' => $ticket->movie_title,
            'ticket_code' => $ticketCode,
            'customer_name' => $customerName,
            'rating' => (float) $rating,
            'comment' => $comment,
            'created_at' => now(),
        ]);

        $average = DB::table('reviews')->where('movie_title', $ticket->movie_title)->avg('rating');
        $count = DB::table('reviews')->where('movie_title', $ticket->movie_title)->count();
        $newRating = round((float) ($average ?: 9.0), 1);

        DB::table('movies')
            ->where('title', $ticket->movie_title)
            ->orWhere('vietnamese_title', $ticket->movie_title)
            ->update(['rating' => $newRating]);

        return response()->json([
            'success' => true,
            'message' => "Cảm ơn bạn {$customerName}! Đánh giá cho phim '{$ticket->movie_title}' đã được ghi nhận thành công.",
            'review_id' => $reviewId,
            'movie_title' => $ticket->movie_title,
            'customer_name' => $customerName,
            'new_rating' => $newRating,
            'total_reviews' => $count,
        ]);
    }

    /**
     * Đồng bộ danh sách 17 phim gốc vào cơ sở dữ liệu nếu chưa có
     */
    private function seedDefaultMovies(): void
    {
        $defaultMovies = [
            [
                'title' => 'Deadpool & Wolverine',
                'vietnamese_title' => 'Deadpool & Wolverine',
                'genre' => 'Hành động, Khoa học viễn tưởng',
                'duration' => '128 phút',
                'rating' => 9.3,
                'age_limit' => 'T18',
                'formats' => '2D, 3D, IMAX',
                'poster' => 'https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/73_1biulkYk',
                'description' => 'Cặp đôi bất hảo của vũ trụ Marvel tái xuất trong chuyến phiêu lưu nghẹt thở và hài hước nhất từ trước đến nay.',
                'release_date' => '26/07/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Inside Out 2',
                'vietnamese_title' => 'Những Mảnh Ghép Cảm Xúc 2',
                'genre' => 'Hoạt hình',
                'duration' => '96 phút',
                'rating' => 9.5,
                'age_limit' => 'P',
                'formats' => '2D, 3D',
                'poster' => 'https://image.tmdb.org/t/p/w500/vpnVM9B6NMmQpWeZvzLvDESb2QY.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/LEjhY15eCx0',
                'description' => 'Cuộc sống của cô bé Riley bùng nổ khi các cảm xúc mới xuất hiện và đảo lộn phòng chỉ huy tâm trí.',
                'release_date' => '14/06/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Godzilla x Kong: The New Empire',
                'vietnamese_title' => 'Godzilla x Kong: Đế Chế Mới',
                'genre' => 'Hành động, Khoa học viễn tưởng',
                'duration' => '115 phút',
                'rating' => 8.9,
                'age_limit' => 'T13',
                'formats' => '2D, IMAX',
                'poster' => 'https://image.tmdb.org/t/p/w500/tMefBSflR6PGQLv7WvFPpKLZkyk.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/qqrpMRDuPfc',
                'description' => 'Liên minh khổng lồ giữa Godzilla và Kong chống lại Skar King và đoàn quân quái thú cổ đại.',
                'release_date' => '29/03/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Oppenheimer',
                'vietnamese_title' => 'Oppenheimer',
                'genre' => 'Tâm lý, Lịch sử, Kịch tính',
                'duration' => '180 phút',
                'rating' => 9.6,
                'age_limit' => 'T18',
                'formats' => '2D, IMAX',
                'poster' => 'https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/uYPbbksJxIg',
                'description' => 'Kiệt tác tiểu sử kịch tính đoạt 7 giải Oscar của đạo diễn Christopher Nolan về vũ khí hạt nhân.',
                'release_date' => '11/08/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Alien: Romulus',
                'vietnamese_title' => 'Alien: Romulus',
                'genre' => 'Kinh dị, Khoa học viễn tưởng',
                'duration' => '119 phút',
                'rating' => 8.7,
                'age_limit' => 'T18',
                'formats' => '2D, IMAX',
                'poster' => 'https://image.tmdb.org/t/p/w500/b33nnKl1GSFbao4l3fZDDqsMx0F.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/GTNMt84KT0k',
                'description' => 'Nhóm bạn trẻ khám phá trạm không gian bỏ hoang và đối mặt với sinh vật săn mồi tàn bạo nhất vũ trụ.',
                'release_date' => '16/08/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Kung Fu Panda 4',
                'vietnamese_title' => 'Kung Fu Panda 4',
                'genre' => 'Hoạt hình, Hành động',
                'duration' => '94 phút',
                'rating' => 8.6,
                'age_limit' => 'P',
                'formats' => '2D, 3D',
                'poster' => 'https://image.tmdb.org/t/p/w500/kDp1vUBnMpe8ak4rjgl3cLELqjU.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/_inKs4eeHiI',
                'description' => 'Po được chọn trở thành Thủ lĩnh Tinh thần của Thung lũng Bình Yên và phải tìm kiếm một Thần Long Đại Hiệp mới.',
                'release_date' => '08/03/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Hành Tinh Khỉ: Vương Quốc Mới',
                'vietnamese_title' => 'Kingdom of the Planet of the Apes',
                'genre' => 'Hành động, Khoa học viễn tưởng',
                'duration' => '145 phút',
                'rating' => 8.5,
                'age_limit' => 'T13',
                'formats' => '2D, IMAX',
                'poster' => 'https://image.tmdb.org/t/p/w500/gKkl37BQuKTanygYQG1pyYgLVgf.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/XtFI7SNtVpY',
                'description' => 'Nhiều thế hệ sau triều đại của Caesar, một chú vượn trẻ dũng cảm dấn thân vào hành trình định đoạt tương lai nhân loại.',
                'release_date' => '10/05/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Bad Boys: Ride or Die',
                'vietnamese_title' => 'Những Gã Trai Hư: Chơi Tới Bến',
                'genre' => 'Hành động, Hài hước',
                'duration' => '115 phút',
                'rating' => 8.4,
                'age_limit' => 'T18',
                'formats' => '2D',
                'poster' => 'https://image.tmdb.org/t/p/w500/nP6RliHjxsz4irTKsxe8FRhKZYl.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/hRFY_Fesa9Q',
                'description' => 'Cặp bài trùng cảnh sát Mike Lowrey và Marcus Burnett trở thành những kẻ bị truy nã gắt gao nhất Miami.',
                'release_date' => '07/06/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Bạch Xà: Duyên Khởi',
                'vietnamese_title' => 'White Snake (Hoạt Hình 3D Tiên Hiệp)',
                'genre' => 'Hoạt hình, Tiên hiệp, Hành động',
                'duration' => '99 phút',
                'rating' => 9.1,
                'age_limit' => 'T13',
                'formats' => '2D, 3D, IMAX',
                'poster' => 'https://image.tmdb.org/t/p/w500/30SLnoKmYNyRPkKCYowsrGLRnJA.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/JeTy_t4WAfo',
                'description' => 'Siêu phẩm hoạt hình 3D huyền huyễn tiên hiệp mãn nhãn kể về tiền duyên ngàn năm của Bạch Tố Trinh và A Tuyên.',
                'release_date' => '18/10/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Na Tra: Ma Đồng Giáng Thế',
                'vietnamese_title' => 'Ne Zha (Hoạt Hình 3D Tiên Hiệp)',
                'genre' => 'Hoạt hình, Tiên hiệp, Hành động',
                'duration' => '110 phút',
                'rating' => 9.3,
                'age_limit' => 'P',
                'formats' => '2D, 3D, IMAX',
                'poster' => 'https://image.tmdb.org/t/p/w500/phM9bb6s9c60LA8qwsdk7U1N2cS.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/daoCb8oDf1k',
                'description' => 'Sinh ra mang danh Ma Hoàn nghịch thiên cải mệnh, Na Tra tự định đoạt số phận trong trận đại chiến bảo vệ muôn dân.',
                'release_date' => '20/10/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Dune: Part Two',
                'vietnamese_title' => 'Hành Tinh Cát: Phần Hai',
                'genre' => 'Hành động, Khoa học viễn tưởng',
                'duration' => '166 phút',
                'rating' => 9.4,
                'age_limit' => 'T16',
                'formats' => '2D, IMAX',
                'poster' => 'https://image.tmdb.org/t/p/w500/1pdfLvkbY9ohJlCjQH2CZjjYVvJ.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/Way9Dexny3w',
                'description' => 'Paul Atreides liên minh cùng Chani và tộc người Fremen để trả thù những kẻ hủy diệt gia tộc trên hành tinh Arrakis.',
                'release_date' => '01/03/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'One Piece Film Red',
                'vietnamese_title' => 'Đảo Hải Tặc: Phim Red',
                'genre' => 'Hoạt hình, Hành động',
                'duration' => '115 phút',
                'rating' => 8.9,
                'age_limit' => 'T13',
                'formats' => '2D, 3D, IMAX',
                'poster' => 'https://image.tmdb.org/t/p/w500/8ibfhe4P7rhmn3lrPhOZzIJHA2B.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/89JWRYEIG-s',
                'description' => 'Giọng hát của Uta - diva huyền thoại và con gái của Tứ Hoàng Shanks Tóc Đỏ làm rung chuyển toàn bộ Tân Thế Giới.',
                'release_date' => '25/08/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Suzume',
                'vietnamese_title' => 'Khóa Chặt Cửa Nào',
                'genre' => 'Hoạt hình, Tình cảm',
                'duration' => '122 phút',
                'rating' => 9.0,
                'age_limit' => 'T13',
                'formats' => '2D, IMAX',
                'poster' => 'https://image.tmdb.org/t/p/w500/yStW1TXF5s7Tbtu9KjIZEaWl6HL.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/9UuwS0mTBNM',
                'description' => 'Hành trình khắp đất nước Nhật Bản của nữ sinh Suzume và Souta để đóng lại những cánh cửa giải thoát hiểm họa.',
                'release_date' => '10/03/2026',
                'status' => 'showing',
            ],
            [
                'title' => 'Spider-Man: Beyond the Spider-Verse',
                'vietnamese_title' => 'Người Nhện: Du Hành Đa Vũ Trụ',
                'genre' => 'Hoạt hình, Hành động, Viễn tưởng',
                'duration' => '140 phút',
                'rating' => 9.5,
                'age_limit' => 'T13',
                'formats' => 'IMAX 3D, 2D',
                'poster' => 'https://image.tmdb.org/t/p/w500/8Vt6mWEReuy4Of61Lnj5Xj704m8.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/cqGjhVJWtEg',
                'description' => 'Chương cuối cùng đầy kịch tính của Miles Morales khi phải đối mặt với số phận của toàn bộ mạng lưới đa vũ trụ.',
                'release_date' => 'Khởi chiếu: 15/11/2026',
                'status' => 'upcoming',
            ],
            [
                'title' => 'The Batman: Part II',
                'vietnamese_title' => 'Hiệp Sĩ Bóng Đêm: Phần 2',
                'genre' => 'Hành động, Tâm lý, Kinh dị',
                'duration' => '165 phút',
                'rating' => 9.4,
                'age_limit' => 'T18',
                'formats' => 'IMAX, 2D',
                'poster' => 'https://image.tmdb.org/t/p/w500/74xTEgt7R36Fpooo50r9T25onhq.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/mqqft2x_Aa4',
                'description' => 'Bruce Wayne tiếp tục lột trần những góc khuất thối nát và đối đầu với kẻ thù mới nguy hiểm tại thành phố Gotham ngập lụt.',
                'release_date' => 'Khởi chiếu: 02/12/2026',
                'status' => 'upcoming',
            ],
            [
                'title' => 'Interstellar (Tái Chiếu IMAX Laser)',
                'vietnamese_title' => 'Hố Đen Tử Thần',
                'genre' => 'Khoa học viễn tưởng, Tâm lý',
                'duration' => '169 phút',
                'rating' => 9.8,
                'age_limit' => 'T13',
                'formats' => 'IMAX Laser',
                'poster' => 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/zSWdZVtXT7E',
                'description' => 'Trải nghiệm lại siêu phẩm điện ảnh không gian vĩ đại của Christopher Nolan trên màn hình khổng lồ IMAX Laser.',
                'release_date' => 'Khởi chiếu: 25/11/2026',
                'status' => 'upcoming',
            ],
            [
                'title' => 'Avatar: Fire and Ash',
                'vietnamese_title' => 'Avatar: Lửa & Tro Tàn',
                'genre' => 'Khoa học viễn tưởng, Phiêu lưu',
                'duration' => '190 phút',
                'rating' => 9.6,
                'age_limit' => 'T13',
                'formats' => 'IMAX 3D, HFR 3D',
                'poster' => 'https://image.tmdb.org/t/p/w500/t6HIqrRAclMCA60NsSmeqe9RmNV.jpg',
                'trailer_url' => 'https://www.youtube.com/embed/d9MyW72ELq0',
                'description' => 'James Cameron đưa khán giả khám phá một thị tộc Na\'vi tàn nhẫn gắn liền với núi lửa và bóng tối trên hành tinh Pandora.',
                'release_date' => 'Khởi chiếu: 18/12/2026',
                'status' => 'upcoming',
            ],
        ];

        foreach ($defaultMovies as $movie) {
            $exists = DB::table('movies')->where('title', $movie['title'])->exists();
            if (!$exists) {
                DB::table('movies')->insert(array_merge($movie, ['created_at' => now()]));
            }
        }
    }

    /**
     * Tạo nội dung mã HTML vé điện tử có mã QR Code
     */
    private function generateTicketEmailHtml(array $data): string
    {
        $rawTicketCode = $data['ticketCode'] ?? 'CGO-' . rand(100000, 999999);
        $rawCustomerName = $data['customerName'] ?? 'Quý Khách';
        $rawCustomerEmail = $data['customerEmail'] ?? 'khachhang@cinego.vn';
        $rawMovieTitle = $data['movieTitle'] ?? 'Phim Chiếu Rạp CINEGO';
        $rawMoviePoster = $data['moviePoster'] ?? 'https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg';
        $rawCinemaName = $data['cinemaName'] ?? 'CINEGO Landmark 81 IMAX';
        $rawHallName = $data['hallName'] ?? 'Phòng Chiếu 01';
        $rawShowtime = $data['showtime'] ?? date('H:i • d/m/Y');
        $rawSeats = $data['seats'] ?? 'A01';
        $rawFormat = $data['format'] ?? '2D Phụ đề';
        $rawCombos = $data['combos'] ?? 'Không có';
        $rawTotalAmount = $data['totalAmount'] ?? '0 đ';
        $rawPaymentMethod = $data['paymentMethod'] ?? 'Ví MoMo';

        $ticketCode = htmlspecialchars($rawTicketCode, ENT_QUOTES, 'UTF-8');
        $customerName = htmlspecialchars($rawCustomerName, ENT_QUOTES, 'UTF-8');
        $customerEmail = htmlspecialchars($rawCustomerEmail, ENT_QUOTES, 'UTF-8');
        $movieTitle = htmlspecialchars($rawMovieTitle, ENT_QUOTES, 'UTF-8');
        $moviePoster = htmlspecialchars($rawMoviePoster, ENT_QUOTES, 'UTF-8');
        $cinemaName = htmlspecialchars($rawCinemaName, ENT_QUOTES, 'UTF-8');
        $hallName = htmlspecialchars($rawHallName, ENT_QUOTES, 'UTF-8');
        $showtime = htmlspecialchars($rawShowtime, ENT_QUOTES, 'UTF-8');
        $seats = htmlspecialchars($rawSeats, ENT_QUOTES, 'UTF-8');
        $format = htmlspecialchars($rawFormat, ENT_QUOTES, 'UTF-8');
        $combos = htmlspecialchars($rawCombos, ENT_QUOTES, 'UTF-8');
        $totalAmount = htmlspecialchars($rawTotalAmount, ENT_QUOTES, 'UTF-8');
        $paymentMethod = htmlspecialchars($rawPaymentMethod, ENT_QUOTES, 'UTF-8');

        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=10&data=CINEGO-TICKET-' . urlencode($ticketCode);

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vé Điện Tử CINEGO</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0B0B0F; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #FFFFFF;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #0B0B0F; padding: 30px 10px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #14141B; border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 50px rgba(0,0,0,0.8);">
          <tr>
            <td align="center" style="padding: 28px 20px; background: linear-gradient(180deg, #1A1A24 0%, #14141B 100%); border-bottom: 1px solid rgba(255,255,255,0.08);">
              <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center">
                    <span style="display: inline-block; background-color: #E50914; color: #FFFFFF; font-weight: 900; font-size: 22px; padding: 6px 14px; border-radius: 10px; letter-spacing: 2px;">CINE<span style="color: #FFFFFF;">GO</span></span>
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding-top: 8px;">
                    <span style="color: #A1A1AA; font-size: 11px; letter-spacing: 3px; text-transform: uppercase;">PREMIUM CINEMA EXPERIENCE</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding: 24px 20px 10px 20px;">
              <span style="display: inline-block; background-color: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 11px; font-weight: bold; text-transform: uppercase; padding: 6px 16px; border-radius: 20px; letter-spacing: 1px;">
                ✓ XÁC NHẬN ĐẶT VÉ THÀNH CÔNG
              </span>
              <h1 style="color: #FFFFFF; font-size: 22px; font-weight: 800; margin: 14px 0 6px 0;">Cảm ơn bạn, {$customerName}!</h1>
              <p style="color: #A1A1AA; font-size: 13px; line-height: 1.5; margin: 0; max-width: 480px;">
                Vé xem phim điện tử của bạn đã được thanh toán thành công. Dưới đây là thông tin chi tiết và mã QR để vào phòng chiếu.
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding: 20px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #0E0E13; border-radius: 16px; border: 1px solid #272733; overflow: hidden;">
                <tr>
                  <td style="padding: 20px; border-bottom: 1px dashed #2E2E3E;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td width="90" valign="top">
                          <img src="{$moviePoster}" alt="{$movieTitle}" width="90" height="130" style="border-radius: 10px; display: block; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);" />
                        </td>
                        <td valign="top" style="padding-left: 18px;">
                          <span style="background-color: #E50914; color: #FFFFFF; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; display: inline-block; margin-bottom: 6px;">{$format}</span>
                          <h2 style="color: #FFFFFF; font-size: 18px; font-weight: 800; margin: 0 0 6px 0; line-height: 1.3;">{$movieTitle}</h2>
                          <p style="color: #E50914; font-size: 13px; font-weight: bold; margin: 0 0 4px 0;">{$cinemaName}</p>
                          <p style="color: #F59E0B; font-size: 12px; font-weight: 600; margin: 0;">{$hallName}</p>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td style="padding: 18px 20px; background-color: #12121A;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td width="50%" valign="top" style="padding-bottom: 12px;">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">MÃ ĐẶT VÉ</span>
                          <span style="color: #FFFFFF; font-size: 16px; font-family: monospace; font-weight: 900; letter-spacing: 1px;">{$ticketCode}</span>
                        </td>
                        <td width="50%" valign="top" style="padding-bottom: 12px;">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">GHẾ ĐÃ CHỌN</span>
                          <span style="color: #E50914; font-size: 16px; font-weight: 900;">{$seats}</span>
                        </td>
                      </tr>
                      <tr>
                        <td width="50%" valign="top" style="padding-bottom: 12px;">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">SUẤT CHIẾU</span>
                          <span style="color: #FFFFFF; font-size: 13px; font-weight: 700;">{$showtime}</span>
                        </td>
                        <td width="50%" valign="top" style="padding-bottom: 12px;">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">BẮP NƯỚC KÈM THEO</span>
                          <span style="color: #D4D4D8; font-size: 12px;">{$combos}</span>
                        </td>
                      </tr>
                      <tr>
                        <td width="50%" valign="top">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">PHƯƠNG THỨC</span>
                          <span style="color: #10B981; font-size: 12px; font-weight: bold;">{$paymentMethod}</span>
                        </td>
                        <td width="50%" valign="top">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">TỔNG THANH TOÁN</span>
                          <span style="color: #FFFFFF; font-size: 16px; font-weight: 900;">{$totalAmount}</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding: 24px 20px; background-color: #0E0E13; border-top: 1px dashed #2E2E3E;">
                    <span style="color: #A1A1AA; font-size: 11px; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; display: block; margin-bottom: 12px;">QUÉT MÃ NÀY TẠI CỔNG SOÁT VÉ</span>
                    <div style="display: inline-block; padding: 10px; background-color: #FFFFFF; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.5);">
                      <img src="{$qrCodeUrl}" alt="QR Code: {$ticketCode}" width="180" height="180" style="display: block;" />
                    </div>
                    <p style="color: #71717A; font-size: 11px; margin: 12px 0 0 0;">
                      Mã xác thực: <strong style="color: #D4D4D8; font-family: monospace;">{$ticketCode}</strong> • Khách: {$customerEmail}
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding: 0 20px 20px 20px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: rgba(229, 9, 20, 0.08); border-radius: 12px; border: 1px solid rgba(229, 9, 20, 0.2); padding: 14px;">
                <tr>
                  <td style="color: #E50914; font-size: 12px; font-weight: bold; padding-bottom: 4px;">
                    ℹ Lưu ý quan trọng khi vào rạp:
                  </td>
                </tr>
                <tr>
                  <td style="color: #A1A1AA; font-size: 11px; line-height: 1.6;">
                    • Vui lòng đến rạp trước giờ chiếu từ 10 - 15 phút để ổn định chỗ ngồi.<br>
                    • Bạn có thể quét trực tiếp mã QR trên điện thoại hoặc đưa cho nhân viên tại quầy.<br>
                    • Không chụp ảnh, quay phim hoặc mang thức ăn ngoài quy định vào phòng chiếu.
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding: 24px 20px; background-color: #0E0E13; border-top: 1px solid rgba(255,255,255,0.06); color: #71717A; font-size: 11px; line-height: 1.6;">
              <p style="margin: 0 0 6px 0;">Hotline Hỗ Trợ Khách Hàng: <strong style="color: #E50914;">1900 8181</strong> (8:00 - 23:00 hàng ngày)</p>
              <p style="margin: 0 0 10px 0;">Email: <a href="mailto:hotro@cinego.vn" style="color: #A1A1AA; text-decoration: none;">hotro@cinego.vn</a> | Website: <a href="/" style="color: #E50914; text-decoration: none;">cinego.vn</a></p>
              <p style="margin: 0; color: #52525B;">&copy; 2026 CINEGO Vietnam. Nền tảng đặt vé xem phim trực tuyến hàng đầu.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    /**
     * Lấy các dòng ghế đã đặt từ CSDL
     */
    private function occupiedSeatRows(string $movie, string $showtime = ''): array
    {
        $trimmed = trim($movie);
        $decoded = html_entity_decode($trimmed, ENT_QUOTES, 'UTF-8');
        $encoded = htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
        $query = DB::table('tickets')
            ->where(function ($query) use ($trimmed, $decoded, $encoded) {
                $query->where('movie_title', $trimmed)
                    ->orWhere('movie_title', $decoded)
                    ->orWhere('movie_title', $encoded)
                    ->orWhere('movie_title', 'like', '%' . $trimmed . '%');
            });

        if ($showtime !== '') {
            preg_match('/(\d{1,2}:\d{2})/', $showtime, $matches);
            $timeOnly = $matches[1] ?? null;
            $query->where(function ($query) use ($showtime, $timeOnly) {
                $query->where('showtime', 'like', '%' . $showtime . '%');
                if ($timeOnly) {
                    $query->orWhere('showtime', 'like', '%' . $timeOnly . '%');
                }
            });
        }

        return $query->pluck('seats')->all();
    }

    /**
     * Chuẩn hóa danh sách các ghế đã chiếm
     */
    private function normalizeOccupiedSeats(array $rows): array
    {
        $seats = [];
        foreach ($rows as $seatString) {
            foreach (preg_split('/[,;\s]+/', (string) $seatString) as $seat) {
                $seat = trim($seat);
                if ($seat === '') {
                    continue;
                }

                $seats[] = $seat;
                if (preg_match('/^([A-Za-z]+)(\d+)$/', $seat, $matches)) {
                    $row = strtoupper($matches[1]);
                    $column = (int) $matches[2];
                    $seats[] = $row . $column;
                    $seats[] = $row . sprintf('%02d', $column);
                }
            }
        }

        return array_values(array_unique($seats));
    }

    /**
     * Thống kê vé theo ngày
     */
    private function dailyTicketStats($tickets): array
    {
        $dates = [];
        for ($offset = 6; $offset >= 0; $offset--) {
            $date = date('Y-m-d', strtotime("-{$offset} days"));
            $dates[$date] = ['label' => date('d/m', strtotime($date)), 'revenue' => 0, 'tickets' => 0];
        }

        foreach ($tickets as $ticket) {
            if (empty($ticket->created_at)) {
                continue;
            }
            $date = substr($ticket->created_at, 0, 10);
            if (!isset($dates[$date])) {
                continue;
            }
            $dates[$date]['revenue'] += (int) preg_replace('/[^0-9]/', '', $ticket->total_amount ?? '');
            $dates[$date]['tickets']++;
        }

        return [
            'labels' => array_column($dates, 'label'),
            'revenues' => array_column($dates, 'revenue'),
            'tickets' => array_column($dates, 'tickets'),
        ];
    }

    /**
     * Thống kê doanh thu theo phim
     */
    private function movieRevenueStats($tickets, int $limit): array
    {
        $movies = [];
        foreach ($tickets as $ticket) {
            $title = trim(html_entity_decode($ticket->movie_title ?? '', ENT_QUOTES, 'UTF-8'));
            if ($title === '') {
                continue;
            }
            $movies[$title] ??= ['revenue' => 0, 'tickets' => 0];
            $movies[$title]['revenue'] += (int) preg_replace('/[^0-9]/', '', $ticket->total_amount ?? '');
            $movies[$title]['tickets']++;
        }

        uasort($movies, fn ($left, $right) => $right['revenue'] <=> $left['revenue']);
        $labels = $revenues = $counts = [];
        $otherRevenue = $otherCount = 0;
        foreach ($movies as $title => $movie) {
            if (count($labels) < $limit) {
                $labels[] = $title;
                $revenues[] = $movie['revenue'];
                $counts[] = $movie['tickets'];
            } else {
                $otherRevenue += $movie['revenue'];
                $otherCount += $movie['tickets'];
            }
        }
        if ($otherRevenue > 0 || $otherCount > 0) {
            $labels[] = 'Phim khác';
            $revenues[] = $otherRevenue;
            $counts[] = $otherCount;
        }

        return ['labels' => $labels, 'revenues' => $revenues, 'tickets' => $counts];
    }

    /**
     * Tạo câu trả lời thông minh khi Gemini API chưa kích hoạt hoặc ngoại lệ
     */
    private function generateSmartResponse(string $message): string
    {
        $text = mb_strtolower($this->removeVietnameseAccents($message), 'UTF-8');

        if (Str::contains($text, ['phim dang chieu hot', 'phim hot', 'bom tan', 'phim hay', 'dang chieu'])) {
            return <<<'RESPONSE'
🔥 **TOP PHIM ĐANG 'CHÁY VÉ' TẠI RẠP CINEGO HÔM NAY:**

1. **Deadpool & Wolverine** (T18)
• Điểm: ⭐ **9.3/10** | Hành động kịch tính & siêu hài hước từ Marvel Studios.
• Định dạng: **IMAX 3D Laser, 2D Phụ đề**.
[Đặt vé: Deadpool & Wolverine]

2. **Bạch Xà: Duyên Khởi** (T13)
• Điểm: ⭐ **9.1/10** | Siêu phẩm hoạt hình 3D tiên hiệp mãn nhãn với chuyện tình tiền duyên ngàn năm.
• Định dạng: **IMAX Laser, 3D, 2D**.
[Đặt vé: Bạch Xà: Duyên Khởi]

3. **Inside Out 2 (Những Mảnh Ghép Cảm Xúc 2)** (P)
• Điểm: ⭐ **9.5/10** | Phim hoạt hình ý nghĩa, cảm xúc cho cả gia đình và bạn trẻ.
[Đặt vé: Inside Out 2]

4. **Dune: Part Two (Hành Tinh Cát 2)** (T16)
• Điểm: ⭐ **9.4/10** | Kiệt tác điện ảnh viễn tưởng trên màn ảnh khổng lồ IMAX.
[Đặt vé: Dune: Part Two]

💡 *Bạn hãy bấm nút **[Đặt vé]** ngay dưới phim yêu thích để giữ ghế vị trí đẹp nhất nhé!*
RESPONSE;
        }

        if (Str::contains($text, ['tu van phim', 'so thich', 'the loai', 'nen xem phim gi'])) {
            return <<<'RESPONSE'
🎯 **GỢI Ý PHIM THEO GU XEM CỦA BẠN:**

⚔️ **Gu Hành động & Bom tấn mãn nhãn:**
• *Deadpool & Wolverine* (Cực hài hước, đánh đấm đỉnh cao) [Đặt vé: Deadpool & Wolverine]
• *Godzilla x Kong: Đế Chế Mới* (Đại chiến quái thú tiền sử) [Đặt vé: Godzilla x Kong: The New Empire]

🐉 **Gu Tiên hiệp, Thần thoại & Kỹ xảo 3D:**
• *Bạch Xà: Duyên Khởi* (Đồ họa 3D tuyệt mỹ) [Đặt vé: Bạch Xà: Duyên Khởi]
• *Na Tra: Ma Đồng Giáng Thế* (Nghịch thiên cải mệnh bùng nổ) [Đặt vé: Na Tra: Ma Đồng Giáng Thế]

👨‍👩‍👧 **Gu Gia đình, Hoạt hình vui nhộn & Cảm xúc:**
• *Inside Out 2* (Ấm áp, hài hước, tuổi dậy thì) [Đặt vé: Inside Out 2]
• *Kung Fu Panda 4* (Võ thuật gấu Po lém lỉnh) [Đặt vé: Kung Fu Panda 4]
• *Suzume* (Anime Nhật Bản cảm xúc) [Đặt vé: Suzume]

👉 *Bạn đang tìm suất chiếu cho mấy người và rạp ở khu vực nào (Hồ Chí Minh, Hà Nội hay Đà Nẵng)?*
RESPONSE;
        }

        if (Str::contains($text, ['gia ve', 'ma giam gia', 'khuyen mai', 'voucher', 'uu dai', 'cinego50'])) {
            return <<<'RESPONSE'
🎟️ **BẢNG GIÁ VÉ & ƯU ĐÃI ĐẶC QUYỀN TẠI CINEGO:**

💰 **Bảng giá vé tiêu chuẩn:**
• **Ghế Thường (2D):** 85.000đ – 95.000đ
• **Ghế VIP Trung tâm:** 110.000đ – 120.000đ (Góc nhìn hoàn mỹ)
• **Vé IMAX 3D Laser:** 160.000đ – 175.000đ (Màn hình siêu lớn, âm thanh Dolby Atmos)
• **Ghế Đôi Sweetbox:** 220.000đ/cặp (Hàng ghế cuối riêng tư cho cặp đôi)

🎁 **MÃ GIẢM GIÁ ĐỘC QUYỀN:**
Nhập mã **`CINEGO50`** tại Bước 4 (Thanh toán) để được **GIẢM NGAY 50%** tổng tiền vé cho lần đặt online đầu tiên!

🌟 **Ưu đãi cố định:**
• *Thứ Tư Vui Vẻ:* Đồng giá 55.000đ/vé 2D cả ngày.
• *Học sinh - Sinh viên (U22):* 45.000đ từ thứ 2 đến thứ 6.

[Đặt vé: Deadpool & Wolverine]
RESPONSE;
        }

        if (Str::contains($text, ['huong dan dat ve', 'cach dat ve', 'chon ghe', 'mua ve'])) {
            return <<<'RESPONSE'
📱 **HƯỚNG DẪN ĐẶT VÉ NHANH TRONG 3 BƯỚC TẠI CINEGO:**

1️⃣ **Bước 1 - Chọn Phim & Suất Chiếu:**
Bấm nút **Đặt Vé** tại trang chủ hoặc trên tin nhắn này. Chọn rạp gần bạn, ngày chiếu và khung giờ mong muốn.

2️⃣ **Bước 2 - Chọn Ghế & Bắp Nước:**
Chọn vị trí ghế yêu thích trên sơ đồ rạp (Ghế VIP, Thường hoặc Ghế đôi Sweetbox). Chọn thêm combo bắp rang bơ phô mai hoặc caramel thơm ngon.

3️⃣ **Bước 3 - Thanh Toán & Nhận Vé:**
Nhập mã giảm giá **`CINEGO50`**, điền email để nhận vé điện tử và mã QR. Quét mã QR trực tiếp tại cổng soát vé mà không cần xếp hàng đổi vé giấy!

[Đặt vé: Inside Out 2]
RESPONSE;
        }

        if (Str::contains($text, ['bach xa', 'na tra', 'tien hiep', 'white snake', 'ne zha'])) {
            return <<<'RESPONSE'
🐉 **SIÊU PHẨM HOẠT HÌNH 3D TIÊN HIỆP ĐANG CHIẾU:**

1. **Bạch Xà: Duyên Khởi** (*White Snake*)
• Đồ họa: Kỹ xảo 3D đỉnh cao của Light Chaser Animation.
• Đánh giá: ⭐ **9.1/10** | Thời lượng: **99 phút** (T13).
• Phòng chiếu: 2D, 3D & **IMAX Laser** sống động từng chi tiết phép thuật.
[Đặt vé: Bạch Xà: Duyên Khởi]

2. **Na Tra: Ma Đồng Giáng Thế** (*Ne Zha*)
• Bom tấn phá vỡ kỷ lục phòng vé với tinh thần bất khuất 'Mệnh do ta định chứ không do trời'.
• Đánh giá: ⭐ **9.3/10** | Phù hợp mọi lứa tuổi (P).
[Đặt vé: Na Tra: Ma Đồng Giáng Thế]
RESPONSE;
        }

        if (Str::contains($text, ['rap', 'o dau', 'dia chi', 'imax', 'landmark'])) {
            return <<<'RESPONSE'
📍 **CÁC CỤM RẠP CINEGO TIÊU CHUẨN QUỐC TẾ:**

🏙️ **TP. Hồ Chí Minh:**
• **CINEGO Landmark 81 IMAX:** Tầng B1, Tòa Landmark 81, Vinhomes Central Park, Bình Thạnh (Rạp IMAX Laser đỉnh nhất).
• **CINEGO Vincom Đồng Khởi:** Tầng 3, 72 Lê Thánh Tôn, Bến Nghé, Quận 1.
• **CINEGO Hùng Vương Plaza:** Tầng 7, 126 Hồng Bàng, Phường 12, Quận 5.

🏛️ **Hà Nội:**
• **CINEGO Metropolis Liễu Giai:** Tầng 4, Vincom Metropolis, 29 Liễu Giai, Ba Đình.
• **CINEGO Royal City Mega Mall:** Tầng B2, 72A Nguyễn Trãi, Thanh Xuân.

🌊 **Đà Nẵng:**
• **CINEGO Riverside:** Tầng 4, Vincom Plaza, 910A Ngô Quyền, Sơn Trà.

👉 *Bạn có thể chọn rạp ngay khi bấm nút Đặt Vé bên dưới!*
RESPONSE;
        }

        if (Str::contains($text, ['bap', 'nuoc', 'combo', 'do an', 'popcorn'])) {
            return <<<'RESPONSE'
🍿 **MENU BẮP NƯỚC CỰC HẤP DẪN TẠI CINEGO:**

• **Combo Solo (69.000đ):** 1 Bắp ngọt 60oz + 1 Nước ngọt lớn 32oz.
• **Combo Couple Bestseller (99.000đ):** 1 Bắp khổng lồ 85oz (Phô mai thơm nồng / Caramel ngọt dịu) + 2 Nước ngọt lớn 32oz.
• **Combo Family Party (179.000đ):** 2 Bắp lớn 85oz + 4 Nước ngọt + 1 Khoai tây lắc giòn rụm.

🥤 *Tất cả bắp rang đều được nổ nóng giòn liên tục tại quầy. Bạn có thể chọn kèm bắp nước ở Bước 3 khi đặt vé.*
RESPONSE;
        }

        if (Str::contains($text, ['chao', 'hi', 'hello', 'alo'])) {
            return <<<'RESPONSE'
👋 **Xin chào quý khách! Tôi là CINEGO AI - Trợ lý điện ảnh của bạn.**

Tôi có thể hỗ trợ bạn:
• 🎬 Gợi ý phim hot hôm nay (Deadpool, Bạch Xà, Inside Out 2, Na Tra...)
• 🎟️ Tra cứu giá vé & áp dụng mã giảm giá **`CINEGO50`** (giảm 50%)
• 📍 Tìm rạp chiếu CINEGO gần bạn nhất
• 🍿 Tư vấn các combo bắp nước giòn tan

Bạn muốn xem phim nào hay cần mình tư vấn phim theo sở thích?
RESPONSE;
        }

        return <<<'RESPONSE'
🎬 **Cảm ơn bạn đã nhắn tin cho CINEGO AI!**

Hôm nay rạp CINEGO đang chiếu rất nhiều bom tấn hấp dẫn:
• **Hành động & Siêu anh hùng:** *Deadpool & Wolverine* [Đặt vé: Deadpool & Wolverine]
• **Hoạt hình 3D & Tiên hiệp:** *Bạch Xà: Duyên Khởi*, *Na Tra: Ma Đồng Giáng Thế* [Đặt vé: Bạch Xà: Duyên Khởi]
• **Gia đình & Tâm lý:** *Inside Out 2*, *Kung Fu Panda 4* [Đặt vé: Inside Out 2]

💡 Bạn có thể bấm vào các nút gợi ý nhanh ở phía trên hoặc hỏi tôi về giá vé, suất chiếu và mã giảm giá nhé!
RESPONSE;
    }

    /**
     * Bỏ dấu tiếng Việt để so sánh tìm kiếm
     */
    private function removeVietnameseAccents(string $text): string
    {
        return strtr(mb_strtolower($text, 'UTF-8'), [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'đ' => 'd',
        ]);
    }
}
