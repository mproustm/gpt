<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Player;
use App\Models\ArchivedPlayer;
use App\Models\Owner;
use App\Models\ArchivedOwner;
use App\Models\Stadium;
use App\Models\Service;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Support;
use App\Models\PlayerReservation;           // NEW
use App\Services\WalletService;             // NEW
use App\Services\FcmService;                // NEW
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * عمولة المشرف الثابتة من كل حجز (10%)
     */
    private const ADMIN_COMMISSION = 0.10;

    /**
     * لوحة التحكم: إحصاءات ديناميكية + أعلى اللاعبين/الملاعب حجزًا
     */
    public function dashboard()
    {
        $startOfMonth  = now()->startOfMonth();
        $last30Days    = now()->subDays(30);

        // إجمالي اللاعبين + النشطون (آخر 30 يومًا)
        $totalPlayers       = Player::count();
        $activePlayersCount = DB::table('player_reservations')
            ->where('created_at', '>=', $last30Days)
            ->distinct('player_id')
            ->count('player_id');

        // الملاعب
        $totalStadiums     = Stadium::count();
        $availableStadiums = Stadium::where('status', 1)->count();

        // الحجوزات
        $totalReservations     = DB::table('player_reservations')->count();
        $reservationsThisMonth = DB::table('player_reservations')
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // مستخدمون جدد هذا الشهر (لاعبون + مُلاك)
        $newUsersThisMonth = Player::where('created_at', '>=', $startOfMonth)->count()
            + Owner::where('created_at', '>=', $startOfMonth)->count();

        $grossTotal = (float) DB::table('player_reservations')
            ->whereIn('status', ['finished', 'playing'])
            ->sum('res_price');

        $grossThisMonth = (float) DB::table('player_reservations')
            ->whereIn('status', ['finished', 'playing'])
            ->where('created_at', '>=', $startOfMonth)
            ->sum('res_price');

        // عمولة المشرف = 10%
        $adminRevenueTotal = round($grossTotal * self::ADMIN_COMMISSION, 2);
        $adminRevenueMonth = round($grossThisMonth * self::ADMIN_COMMISSION, 2);

        // أكثر اللاعبين حجزًا (يحسب فقط finished/playing)
        $topPlayersRaw = DB::table('player_reservations')
            ->select('player_id', DB::raw('COUNT(*) as bookings_count'))
            ->whereIn('status', ['finished', 'playing'])
            ->whereNotNull('player_id')
            ->groupBy('player_id')
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get();

        $playerNames = Player::whereIn('id', $topPlayersRaw->pluck('player_id'))
            ->pluck('name', 'id');

        $topPlayers = $topPlayersRaw->map(function ($row) use ($playerNames) {
            return [
                'name'  => $playerNames[$row->player_id] ?? ('لاعب #' . $row->player_id),
                'count' => (int) $row->bookings_count,
            ];
        });

        // أكثر الملاعب حجزًا (يحسب فقط finished/playing) — يستخدم s_id
        $topStadiumsRaw = DB::table('player_reservations')
            ->select('s_id', DB::raw('COUNT(*) as bookings_count'))
            ->whereIn('status', ['finished', 'playing'])
            ->whereNotNull('s_id')
            ->groupBy('s_id')
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get();

        $stadiumNames = Stadium::whereIn('id', $topStadiumsRaw->pluck('s_id'))
            ->pluck('name', 'id');

        $topStadiums = $topStadiumsRaw->map(function ($row) use ($stadiumNames) {
            return [
                'name'  => $stadiumNames[$row->s_id] ?? ('ملعب #' . $row->s_id),
                'count' => (int) $row->bookings_count,
            ];
        });

        return view('admin.dashboard', compact(
            'totalPlayers',
            'activePlayersCount',
            'totalStadiums',
            'availableStadiums',
            'totalReservations',
            'reservationsThisMonth',
            'newUsersThisMonth',
            'adminRevenueTotal',
            'adminRevenueMonth',
            'topPlayers',
            'topStadiums'
        ));
    }

    public function players(Request $request)
    {
        if ($request->ajax() && $request->has('name')) {
            $tab  = $request->get('tab', 'view');
            $name = $request->get('name');
            if ($tab === 'archived') {
                $list = ArchivedPlayer::when($name, fn($q) => $q->where('name','like',"%{$name}%"))
                    ->latest()
                    ->get()
                    ->map(fn($p) => [
                        'id'         => $p->id,
                        'name'       => $p->name,
                        'user_name'  => $p->user_name,
                        'phone'      => $p->phone,
                        'created_at' => $p->created_at->format('Y-m-d H:i'),
                    ]);
                return response()->json($list);
            }
            $list = Player::when($name, fn($q) => $q->where('name','like',"%{$name}%"))
                ->latest()
                ->get()
                ->map(fn($p) => [
                    'id'        => $p->id,
                    'name'      => $p->name,
                    'user_name' => $p->user_name,
                    'phone'     => $p->phone,
                ]);
            return response()->json($list);
        }
        if ($request->isMethod('post')
            && ! $request->has('_method')
            && ! $request->hasAny(['archive_id','restore_id','edit_id'])
        ) {
            $request->validate([
                'name'      => 'required|string|max:100',
                'user_name' => 'required|string|max:50|unique:players,user_name',
                'phone'     => 'required|string|max:15|unique:players,phone',
                'password'  => 'required|string|min:6',
            ]);
            Player::create([
                'name'      => $request->name,
                'user_name' => $request->user_name,
                'phone'     => $request->phone,
                'password'  => Hash::make($request->password),
                'admin_id'  => Auth::id(),
            ]);
            return redirect()->route('admin.players', ['tab' => 'view'])
                             ->with('success', 'تمت إضافة اللاعب بنجاح');
        }
        if ($request->isMethod('post') && $request->has('edit_id')) {
            $player = Player::findOrFail($request->edit_id);
            $request->validate([
                'name'      => 'required|string|max:100',
                'user_name' => 'required|string|max:50|unique:players,user_name,'.$player->id,
                'phone'     => 'required|string|max:15|unique:players,phone,'.$player->id,
                'password'  => 'nullable|string|min:6',
            ]);
            $player->name      = $request->name;
            $player->user_name = $request->user_name;
            $player->phone     = $request->phone;
            if ($request->filled('password')) {
                $player->password = Hash::make($request->password);
            }
            $player->admin_id = Auth::id();
            $player->save();
            return redirect()->route('admin.players', ['tab' => 'view'])
                             ->with('success', 'تم تحديث بيانات اللاعب');
        }
        if ($request->isMethod('delete') && $request->has('delete_id')) {
            Player::destroy($request->delete_id);
            return redirect()->route('admin.players', ['tab' => 'view'])
                             ->with('success', 'تم حذف اللاعب');
        }
        if ($request->isMethod('post') && $request->has('archive_id')) {
            $player = Player::findOrFail($request->archive_id);
            ArchivedPlayer::create([
                'name'      => $player->name,
                'user_name' => $player->user_name,
                'phone'     => $player->phone,
                'admin_id'  => $player->admin_id,
            ]);
            $player->delete();
            return redirect()->route('admin.players', ['tab' => 'view'])
                             ->with('success', 'تمت أرشفة اللاعب');
        }
        if ($request->isMethod('post') && $request->has('restore_id')) {
            $archived = ArchivedPlayer::findOrFail($request->restore_id);
            Player::create([
                'name'      => $archived->name,
                'user_name' => $archived->user_name,
                'phone'     => $archived->phone,
                'password'  => Hash::make('default123'),
                'admin_id'  => $archived->admin_id,
            ]);
            $archived->delete();
            return redirect()->route('admin.players', ['tab' => 'archived'])
                             ->with('success', 'تمت استعادة اللاعب');
        }
        if ($request->ajax() && $request->has('fetch_id')) {
            $player = Player::findOrFail($request->fetch_id);
            return response()->json($player);
        }
        $tab  = $request->get('tab', 'view');
        $name = $request->get('name');
        $playersQuery = Player::latest();
        if ($tab === 'view' && $name) {
            $playersQuery->where('name','like',"%{$name}%");
        }
        $players = $playersQuery->paginate(15);
        $archivedQuery = ArchivedPlayer::latest();
        if ($tab === 'archived' && $name) {
            $archivedQuery->where('name','like',"%{$name}%");
        }
        $archived = $archivedQuery->get();
        return view('admin.players.players', compact('players','archived'));
    }

    public function owners(Request $request)
    {
        $tab  = $request->get('tab', 'addOwner');
        $name = $request->get('name');
        if ($request->isMethod('post')
            && ! $request->hasAny(['_method','archive_id','restore_id','delete_id'])
        ) {
            $request->validate([
                'name'          => 'required|string|max:100',
                'email'         => 'required|email|unique:owners,email|max:255',
                'phone_number'  => 'required|string|unique:owners,phone_number|max:15',
                'address'       => 'nullable|string|max:255',
                'password'      => 'required|string|min:6',
            ]);
            Owner::create([
                'name'         => $request->name,
                'email'        => $request->email,
                'phone_number' => $request->phone_number,
                'address'      => $request->address,
                'password'     => Hash::make($request->password),
                'admin_id'     => Auth::id(),
            ]);
            return redirect()->route('admin.owners', ['tab' => 'viewOwners'])
                             ->with('success', 'تمت إضافة صاحب الملعب');
        }
        if ($request->isMethod('post') && $request->has('archive_id')) {
            $owner = Owner::findOrFail($request->archive_id);
            ArchivedOwner::create([
                'owner_id'     => $owner->id,
                'name'         => $owner->name,
                'email'        => $owner->email,
                'phone_number' => $owner->phone_number,
                'address'      => $owner->address,
                'admin_id'     => $owner->admin_id,
                'password'     => $owner->password,
            ]);
            $owner->delete();
            return redirect()->route('admin.owners', ['tab' => 'viewOwners'])
                             ->with('success', 'تمت أرشفة صاحب الملعب');
        }
        if ($request->isMethod('delete') && $request->has('delete_id')) {
            Owner::destroy($request->delete_id);
            return redirect()->route('admin.owners', ['tab' => 'viewOwners'])
                             ->with('success', 'تم حذف صاحب الملعب نهائيًا');
        }
        if ($request->isMethod('post') && $request->has('restore_id')) {
            $arch = ArchivedOwner::findOrFail($request->restore_id);
            Owner::create([
                'name'         => $arch->name,
                'email'        => $arch->email,
                'phone_number' => $arch->phone_number,
                'address'      => $arch->address,
                'admin_id'     => $arch->admin_id,
                'password'     => $arch->password,
            ]);
            $arch->delete();
            return redirect()->route('admin.owners', ['tab' => 'archivedOwners'])
                             ->with('success', 'تمت استعادة صاحب الملعب');
        }
        if ($request->ajax() && $request->has('name')) {
            $viewList = Owner::latest()
                ->when($name, fn($q) => $q->where('name','like',"%{$name}%"))
                ->get()
                ->map(fn($o) => $o->only('id','name','email','phone_number','address'));
            $archList = ArchivedOwner::latest()
                ->when($name, fn($q) => $q->where('name','like',"%{$name}%"))
                ->get()
                ->map(fn($o) => $o->only('id','name','email','phone_number','address'));
            return response()->json([
                'viewOwners'     => $viewList,
                'archivedOwners' => $archList,
            ]);
        }
        $owners = Owner::latest()
            ->when($tab==='viewOwners' && $name, fn($q)=> $q->where('name','like',"%{$name}%"))
            ->paginate(15);
        $archived = ArchivedOwner::latest()
            ->when($tab==='archivedOwners' && $name, fn($q)=> $q->where('name','like',"%{$name}%"))
            ->get();
        return view('admin.owners.owners', compact('owners','archived'));
    }

    public function stadiums(Request $request)
    {
        // تفعيل/تعطيل
        if ($request->isMethod('post') && $request->hasAny(['activate_id','deactivate_id'])) {
            $id     = $request->activate_id ?? $request->deactivate_id;
            $status = $request->has('activate_id') ? 1 : 0;
            if ($s = Stadium::find($id)) {
                $s->update([
                    'status'            => $status,
                    'admin_id'          => Auth::id(),
                    'status_changed_by' => 'admin',
                ]);

                // NEW: عند التعطيل → استرجاع وإشعار المؤكدين
                if ((int)$status === 0) {
                    $this->refundConfirmedReservationsForStadium($s->id, 'admin');
                }
            }
            return $request->ajax()
                ? response()->json([], 204)
                : redirect()->route('admin.stadiums')
                            ->with('success','تم تحديث حالة الملعب');
        }

        // تحديث
        if ($request->isMethod('post') && $request->has('edit_id')) {
            $stadium = Stadium::findOrFail($request->edit_id);

            $request->validate([
                'name'         => 'required|string|max:100',
                'price'        => 'required|numeric|min:0',
                'address'      => 'required|string|max:255',
                'description'  => 'nullable|string',
                'stad_pic'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'status'       => 'required|boolean',
                'location_url' => 'nullable|url|max:255',
            ]);

            $data = $request->only(['name','price','address','description','status','location_url']);

            if ($file = $request->file('stad_pic')) {
                if ($stadium->stad_pic && Storage::disk('public')->exists($stadium->stad_pic)) {
                    Storage::disk('public')->delete($stadium->stad_pic);
                }
                $data['stad_pic'] = $file->store('stad_pics', 'public');
            }

            if ((int)$stadium->status !== (int)$data['status']) {
                $data['status_changed_by'] = 'admin';
            }
            $data['admin_id'] = Auth::id();

            $stadium->update($data);

            // NEW: لو التحديث جعل الحالة معطّلة
            if ((int)$data['status'] === 0) {
                $this->refundConfirmedReservationsForStadium($stadium->id, 'admin');
            }

            return $request->ajax()
                ? response()->json([], 204)
                : redirect()->route('admin.stadiums')->with('success','تم تحديث بيانات الملعب');
        }

        // إنشاء جديد
        if ($request->isMethod('post') && ! $request->hasAny(['_method','activate_id','deactivate_id','edit_id'])) {
            $request->validate([
                'owner_id'   => 'required|exists:owners,id',
                'name'       => 'required|string|max:100',
                'price'      => 'required|numeric|min:0',
                'address'    => 'required|string|max:255',
                'description'=> 'nullable|string',
                'stad_pic'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $path = $request->file('stad_pic')?->store('stad_pics','public');
            Stadium::create(array_filter([
                'owner_id'          => $request->owner_id,
                'name'              => $request->name,
                'price'             => $request->price,
                'address'           => $request->address,
                'description'       => $request->description,
                'stad_pic'          => $path,
                'status'            => 1,
                'admin_id'          => Auth::id(),
                'status_changed_by' => 'admin',
            ]));
            return redirect()->route('admin.stadiums')
                             ->with('success','تمت إضافة الملعب');
        }

        // AJAX: جلب ملعب واحد
        if ($request->ajax() && $request->has('fetch_id')) {
            $s = Stadium::with('owner')->findOrFail($request->fetch_id);
            return response()->json([
                'id'           => $s->id,
                'owner_id'     => $s->owner_id,
                'name'         => $s->name,
                'price'        => $s->price,
                'address'      => $s->address,
                'description'  => $s->description,
                'status'       => (int) $s->status,
                'location_url' => $s->location_url,
                'stad_pic_url' => $s->stad_pic ? asset('storage/'.$s->stad_pic) : null,
            ]);
        }

        // AJAX: بحث قائمة الملاعب
        if ($request->ajax()) {
            $q = $request->query('q');
            $list = Stadium::with('owner')->latest()
                ->when($q, fn($qb)=> $qb->where('name','like',"%{$q}%"))
                ->get()
                ->map(fn($s)=>[
                    'id'         => $s->id,
                    'name'       => $s->name,
                    'owner'      => optional($s->owner)->name,
                    'address'    => $s->address,
                    'description'=> $s->description,
                    'status'     => $s->status? 'نشط':'معطّل',
                ]);
            return response()->json($list);
        }

        // عرض الصفحة
        $stadiums = Stadium::with('owner')->latest()->paginate(15);
        return view('admin.stadiums.stadiums', compact('stadiums'));
    }

    public function services(Request $request)
    {
        // تفعيل/تعطيل
        if ($request->isMethod('post') && $request->hasAny(['activate_id','deactivate_id'])) {
            $id      = $request->activate_id ?? $request->deactivate_id;
            $service = Service::find($id);
            if ($service) {
                $service->service_status = $request->has('activate_id') ? 'مفعل' : 'غير مفعل';
                $service->admin_id       = Auth::id();
                $service->save();
            }
            return redirect()->route('admin.services',['tab'=>'viewServices'])
                             ->with('success','تم تحديث حالة الخدمة');
        }

        // إنشاء جديد
        if ($request->isMethod('post') && ! $request->hasAny(['_method','activate_id','deactivate_id'])) {
            $request->validate([
                's_id'         =>'required|exists:stadiums,id',
                'service_type' =>'required|string|max:50',
            ]);
            Service::create([
                's_id'           => $request->s_id,
                'service_type'   => $request->service_type,
                'service_status' => 'مفعل',
                'admin_id'       => Auth::id(),
            ]);
            return redirect()->route('admin.services',['tab'=>'addService'])
                             ->with('success','تمت إضافة الخدمة');
        }

        if ($request->isMethod('delete') && $request->has('delete_id')) {
            Service::destroy($request->delete_id);
            return redirect()->route('admin.services',['tab'=>'viewServices'])
                             ->with('success','تم حذف الخدمة');
        }

        $stadiums = Stadium::latest()->get();
        $services = Service::with('stadium')->latest()->paginate(15);
        return view('admin.services.services', compact('stadiums','services'));
    }

    public function notifications(Request $request)
    {
        if ($request->isMethod('get')) {
            $stadiums      = Stadium::with('owner')->get();
            $notifications = Notification::with('stadium', 'owner')
                ->latest()->take(20)->get();

            return view('admin.notifications.notifications',
                compact('stadiums', 'notifications'));
        }

        $data = $request->validate([
            'title'         => 'required|string|max:80',
            'body'          => 'required|string|max:300',
            'target'        => 'required|in:admins,one_owner,players',
            'owner_stadium' => 'nullable|required_if:target,one_owner|exists:stadiums,id',
        ]);

        $admin_id = Auth::id();

        if ($data['target'] === 'one_owner' && isset($data['owner_stadium'])) {
            $stadium = Stadium::with('owner')->find($data['owner_stadium']);
            Notification::create([
                'title'      => $data['title'],
                'body'       => $data['body'],
                'target'     => 'one_owner',
                'stadium_id' => $stadium->id,
                'owner_id'   => $stadium->owner_id,
                'admin_id'   => $admin_id,
            ]);
        }
        elseif ($data['target'] === 'admins') {
            $owners = \App\Models\Owner::all();
            foreach ($owners as $owner) {
                Notification::create([
                    'title'      => $data['title'],
                    'body'       => $data['body'],
                    'target'     => 'one_owner',
                    'owner_id'   => $owner->id,
                    'stadium_id' => null,
                    'admin_id'   => $admin_id,
                ]);
            }
        }
        elseif ($data['target'] === 'players') {

            DB::beginTransaction();
            try {
                $notification = Notification::create([
                    'title'    => $data['title'],
                    'body'     => $data['body'],
                    'target'   => 'players',
                    'admin_id' => $admin_id,
                ]);
                $playerIds = Player::pluck('id');
                $playerNotificationRecords = [];
                $now = now();
                foreach ($playerIds as $playerId) {
                    $playerNotificationRecords[] = [
                        'player_id'       => $playerId,
                        'notification_id' => $notification->id,
                        'is_read'         => false,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];
                }
                if (!empty($playerNotificationRecords)) {
                    DB::table('player_notifications')->insert($playerNotificationRecords);
                }

                $tokens = \App\Models\DeviceToken::pluck('token')->unique()->filter()->all();
                if (!empty($tokens)) {
                    app(\App\Services\FcmService::class)->send(
                        $data['title'],
                        $data['body'],
                        $tokens,
                        [
                            'type' => 'admin_announcement',
                            'notification_id' => (string)$notification->id,
                        ]
                    );
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error('Failed to send player notification: ' . $e->getMessage());
                return back()->with('error', 'An error occurred while sending the notification.');
            }
        }
        return back()->with('success', 'تم إرسال الإشعار بنجاح!');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name'     =>'required|string|max:100',
            'email'    =>"required|email|max:255|unique:users,email,{$user->id}",
            'password' =>'nullable|string|min:6',
            'pic'      =>'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $user->name  = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        if ($file = $request->file('pic')) {
            if ($user->pic && Storage::disk('public')->exists($user->pic)) {
                Storage::disk('public')->delete($user->pic);
            }
            $user->pic = $file->store('profile_pics','public');
        }
        $user->save();
        return redirect()->route('admin.profile')
                         ->with('success','تم تحديث الملف الشخصي');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile.profile', compact('user'));
    }

    public function settings(Request $request)
    {
        $settingKeys = ['about_text', 'site_name', 'email', 'phone', 'address'];
        if ($request->isMethod('post')) {
            $request->validate([
                'about_text' => 'required|string|max:600',
                'site_name'  => 'required|string|max:255',
                'email'      => 'required|email|max:255',
                'phone'      => 'required|string|max:25',
                'address'    => 'required|string|max:255',
            ]);
            $admin_id = auth()->id();
            foreach ($settingKeys as $key) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->$key, 'admin_id' => $admin_id]
                );
            }
            return back()->with('success', 'تم تحديث إعدادات النظام');
        }
        $settings = Setting::whereIn('key', $settingKeys)->pluck('value', 'key');
        return view('admin.settings.settings', compact('settings'));
    }

    public function homepage()
    {
        $keys = ['site_name', 'email', 'phone', 'address', 'about_text'];
        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key');
        return view('homepage.home', compact('settings'));
    }

    public function support(Request $request)
    {
        $query = Support::with(['reporter', 'target']);

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('reporter')) {
            $reporter = $request->reporter;
            $query->whereHasMorph('reporter', [Player::class, Owner::class], function ($morphQuery, $type) use ($reporter) {
                $morphQuery->where('name', 'LIKE', "%$reporter%");
                if ($type === Owner::class) {
                    $morphQuery->orWhere('email', 'LIKE', "%$reporter%");
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $supports = $query->latest()->paginate(15);
        $supports->appends($request->all());

        return view('admin.support.support', compact('supports'));
    }

    /**
     * Update the specified resource in storage and notify both sides.
     */
    public function updateSupport(Request $request, $id)
    {
        $support = Support::findOrFail($id);

        $request->validate([
            'message' => 'required|string|max:5000',
            'status'  => 'required|in:pending,reviewed,resolved,rejected',
        ]);

        // Update then reload relations
        $support->update($request->only('message', 'status'));
        $support->refresh()->load(['reporter', 'target']);

        $statusMap = [
            'pending'  => 'جديدة',
            'reviewed' => 'قيد المراجعة',
            'resolved' => 'تم الحل',
            'rejected' => 'مرفوضة',
        ];

        $title = 'الحالة';
        $body  = "تم تحديث حالة الشكوى إلى: {$statusMap[$support->status]}\n"
               . "الوصف: " . (string) $support->message;

        $adminId = Auth::id();

        // Collect recipients
        $ownerIds  = [];
        $playerIds = [];

        if ($support->reporter instanceof Owner) {
            $ownerIds[] = $support->reporter->id;
        } elseif ($support->reporter instanceof Player) {
            $playerIds[] = $support->reporter->id;
        }

        if ($support->target) {
            if ($support->target instanceof Owner) {
                $ownerIds[] = $support->target->id;
            } elseif ($support->target instanceof Player) {
                $playerIds[] = $support->target->id;
            }
        }

        $ownerIds  = array_values(array_unique($ownerIds));
        $playerIds = array_values(array_unique($playerIds));

        DB::beginTransaction();
        try {
            // Notify owners (individual notifications like "one_owner")
            foreach ($ownerIds as $oid) {
                $ownerNotification = Notification::create([
                    'title'      => $title,
                    'body'       => $body,
                    'target'     => 'one_owner',
                    'owner_id'   => $oid,
                    'stadium_id' => null,
                    'admin_id'   => $adminId,
                ]);

                // Send owner-targeted FCM if possible
                $ownerTokens = $this->getOwnerTokens([$oid]);
                if (!empty($ownerTokens)) {
                    app(\App\Services\FcmService::class)->send(
                        $title,
                        $body,
                        $ownerTokens,
                        [
                            'type'             => 'support_status_update',
                            'support_id'       => (string)$support->id,
                            'status_key'       => $support->status,
                            'status_label'     => $statusMap[$support->status],
                            'notification_id'  => (string)$ownerNotification->id,
                            'recipient_type'   => 'owner',
                            'recipient_ids'    => implode(',', $ownerIds),
                        ]
                    );
                }
            }

            // Notify players (single notification with pivot to targeted players)
            if (!empty($playerIds)) {
                $playerNotification = Notification::create([
                    'title'    => $title,
                    'body'     => $body,
                    'target'   => 'players',
                    'admin_id' => $adminId,
                ]);

                $now   = now();
                $pivot = [];
                foreach ($playerIds as $pid) {
                    $pivot[] = [
                        'player_id'       => $pid,
                        'notification_id' => $playerNotification->id,
                        'is_read'         => false,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];
                }
                if ($pivot) {
                    DB::table('player_notifications')->insert($pivot);
                }

                // Send player-targeted FCM if possible
                $playerTokens = $this->getPlayerTokens($playerIds);
                if (!empty($playerTokens)) {
                    app(\App\Services\FcmService::class)->send(
                        $title,
                        $body,
                        $playerTokens,
                        [
                            'type'             => 'support_status_update',
                            'support_id'       => (string)$support->id,
                            'status_key'       => $support->status,
                            'status_label'     => $statusMap[$support->status],
                            'notification_id'  => (string)$playerNotification->id,
                            'recipient_type'   => 'player',
                            'recipient_ids'    => implode(',', $playerIds),
                        ]
                    );
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to send status notifications for support #'.$support->id.' : '.$e->getMessage());
            return redirect()
                ->route('admin.support')
                ->with('success', 'تم تحديث الشكوى بنجاح.')
                ->with('warning', 'تعذّر إرسال الإشعارات. راجع السجلات.');
        }

        return redirect()->route('admin.support')->with('success', 'تم تحديث الشكوى وإرسال الإشعارات للطرفين بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroySupport($id)
    {
        Support::findOrFail($id)->delete();
        return redirect()->route('admin.support')->with('success', 'تم حذف الشكوى بنجاح.');
    }

    /* =======================
     * Helpers for FCM tokens
     * ======================= */

    protected function getOwnerTokens(array $ownerIds): array
    {
        if (empty($ownerIds)) return [];

        // owner_id direct column
        if (Schema::hasColumn('device_tokens', 'owner_id')) {
            return \App\Models\DeviceToken::whereIn('owner_id', $ownerIds)
                ->pluck('token')->filter()->unique()->values()->all();
        }

        // polymorphic: userable_type / userable_id
        if (Schema::hasColumn('device_tokens', 'userable_type') && Schema::hasColumn('device_tokens', 'userable_id')) {
            return \App\Models\DeviceToken::where('userable_type', Owner::class)
                ->whereIn('userable_id', $ownerIds)
                ->pluck('token')->filter()->unique()->values()->all();
        }

        // notifiable_type / notifiable_id
        if (Schema::hasColumn('device_tokens', 'notifiable_type') && Schema::hasColumn('device_tokens', 'notifiable_id')) {
            return \App\Models\DeviceToken::where('notifiable_type', Owner::class)
                ->whereIn('notifiable_id', $ownerIds)
                ->pluck('token')->filter()->unique()->values()->all();
        }

        // Fallback: return all tokens (least preferred)
        return \App\Models\DeviceToken::pluck('token')->filter()->unique()->values()->all();
    }

    protected function getPlayerTokens(array $playerIds): array
    {
        if (empty($playerIds)) return [];

        // player_id direct column
        if (Schema::hasColumn('device_tokens', 'player_id')) {
            return \App\Models\DeviceToken::whereIn('player_id', $playerIds)
                ->pluck('token')->filter()->unique()->values()->all();
        }

        // polymorphic: userable_type / userable_id
        if (Schema::hasColumn('device_tokens', 'userable_type') && Schema::hasColumn('device_tokens', 'userable_id')) {
            return \App\Models\DeviceToken::where('userable_type', Player::class)
                ->whereIn('userable_id', $playerIds)
                ->pluck('token')->filter()->unique()->values()->all();
        }

        // notifiable_type / notifiable_id
        if (Schema::hasColumn('device_tokens', 'notifiable_type') && Schema::hasColumn('device_tokens', 'notifiable_id')) {
            return \App\Models\DeviceToken::where('notifiable_type', Player::class)
                ->whereIn('notifiable_id', $playerIds)
                ->pluck('token')->filter()->unique()->values()->all();
        }

        // Fallback: return all tokens (least preferred)
        return \App\Models\DeviceToken::pluck('token')->filter()->unique()->values()->all();
    }

    public function reports()
    {
        // just render the Blade; data is fetched via AJAX
        return view('admin.reports.reports');
    }

    public function reportsData(Request $request)
    {
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', 0); // 0 = all months in the year

        // Base reservation query (finished/playing only)
        $base = DB::table('player_reservations')->whereIn('status', ['finished', 'playing']);

        // Date range filters
        if ($month > 0) {
            $base->whereYear('created_at', $year)->whereMonth('created_at', $month);
        } else {
            $base->whereYear('created_at', $year);
        }

        // Totals (players & stadiums => overall counts; bookings/revenue => filtered)
        $playersCount   = Player::count();
        $stadiumsCount  = Stadium::count();
        $bookingsCount  = (int) (clone $base)->count();
        $revenueTotal   = (float) (clone $base)->sum('res_price');

        // Revenue by month for the selected year (12 points)
        $revByMonth = DB::table('player_reservations')
            ->selectRaw('MONTH(created_at) as m, SUM(res_price) as rev')
            ->whereIn('status', ['finished', 'playing'])
            ->whereYear('created_at', $year)
            ->groupBy('m')
            ->pluck('rev', 'm');

        $revenueMonthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $revenueMonthly[] = (float) ($revByMonth[$m] ?? 0);
        }

        // Top clients (players) by revenue within the selected period
        $topClientsRaw = (clone $base)
            ->select('player_id', DB::raw('SUM(res_price) as revenue'), DB::raw('COUNT(*) as bookings'))
            ->whereNotNull('player_id')
            ->groupBy('player_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $playerNames = Player::whereIn('id', $topClientsRaw->pluck('player_id'))->pluck('name', 'id');

        $topClients = $topClientsRaw->map(function ($r) use ($playerNames) {
            return [
                'player_id' => $r->player_id,
                'name'      => $playerNames[$r->player_id] ?? ('لاعب #' . $r->player_id),
                'revenue'   => (float) $r->revenue,
                'bookings'  => (int) $r->bookings,
            ];
        })->values();

        // Yearly table (12 rows: players joined, bookings, revenue per month)
        $yearly = [];
        for ($m = 1; $m <= 12; $m++) {
            $playersNew = Player::whereYear('created_at', $year)->whereMonth('created_at', $m)->count();
            $b = DB::table('player_reservations')
                ->whereIn('status', ['finished', 'playing'])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $m);
            $bookingsM = (int) $b->count();
            $revenueM  = (float) $b->sum('res_price');

            $yearly[] = [
                'year'      => $year,
                'month'     => $m,
                'players'   => $playersNew,
                'bookings'  => $bookingsM,
                'revenue'   => $revenueM,
            ];
        }

        return response()->json([
            'totals' => [
                'players'  => $playersCount,
                'stadiums' => $stadiumsCount,
                'bookings' => $bookingsCount,
                'revenue'  => $revenueTotal,
            ],
            'chart' => [
                'year'    => $year,
                'labels'  => ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'],
                'series'  => $revenueMonthly,
            ],
            'top_clients' => $topClients,
            'yearly'      => $yearly,
        ]);
    }

    /* ==========================================================
     * NEW: إلغاء + استرداد + إشعار للمؤكدين عند تعطيل الملعب
     * ========================================================== */
    private function refundConfirmedReservationsForStadium(int $stadiumId, string $actor = 'admin'): int
    {
        $now = now();

        $items = PlayerReservation::with([
                'stadium',
                'player.deviceTokens',
                'invitedPlayers' => function ($q) {
                    $q->wherePivot('status', 'accepted');
                },
                'invitedPlayers.deviceTokens',
            ])
            ->where('s_id', $stadiumId)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($now) {
                $q->whereDate('date', '>', $now->toDateString())
                  ->orWhere(function ($qq) use ($now) {
                      $qq->whereDate('date', $now->toDateString())
                         ->where('time', '>', $now->format('H:i:s'));
                  });
            })
            ->get();

        if ($items->isEmpty()) return 0;

        $wallet = app(WalletService::class);
        $fcm    = app(FcmService::class);

        foreach ($items as $r) {
            DB::transaction(function () use ($wallet, $fcm, $r, $actor) {
                // 1) استرجاع كل المدفوعات
                $wallet->refundEntireReservation($r);

                // 2) نص الإشعار
                $who = $actor === 'admin' ? 'الإدارة' : 'مالك الملعب';
                $title = 'إلغاء الحجز واسترداد المبلغ';
                $body  = 'تم إلغاء مباراتك في ملعب ' . (optional($r->stadium)->name ?? 'غير محدد')
                    . " بسبب تعطيل الملعب من قِبل {$who}. "
                    . 'تم استرداد أي مبالغ مدفوعة إلى محافظكم.';

                // 3) المستلمون: منشئ الحجز + المقبولون
                $audience = collect([$r->player])->filter()->merge($r->invitedPlayers);

                // 4) إرسال FCM
                $tokens = $audience
                    ->flatMap(fn($p) => $p?->deviceTokens?->pluck('token') ?? collect())
                    ->unique()->filter()->values()->all();

                if (!empty($tokens)) {
                    $fcm->send($title, $body, $tokens, [
                        'type'       => 'booking_cancelled_refunded',
                        'booking_id' => (string) $r->id,
                        'stadium_id' => (string) ($r->stadium->id ?? 0),
                    ]);
                }

                // 5) تنظيف الدعوات وتحديث الحالة
                $r->invitedPlayers()->detach();
                $r->status = 'cancelled';
                $r->save();
            });
        }

        return $items->count();
    }
}
