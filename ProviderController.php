<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use App\Models\Stadium;
use App\Models\OwnerReservation;
use App\Models\PlayerReservation;
use App\Models\ArchivedPlayerReservation;
use App\Models\Review;
use App\Models\Player;
use App\Models\Notification;
use App\Models\Support;
use App\Services\WalletService;
use App\Services\FcmService;

class ProviderController extends Controller
{
    private const ADMIN_COMMISSION = 0.10;

    private function getOwnerNotificationsQuery()
    {
        $owner = Auth::guard('owner')->user();
        if (!$owner) {
            return Notification::query()->whereRaw('1 = 0');
        }

        return Notification::where(function ($q) use ($owner) {
            $q->where(function ($query) use ($owner) {
                $query->where('target', 'one_owner')
                      ->where('owner_id', $owner->id);
            })
            ->orWhere('target', 'admins');
        });
    }

    private function getOwnerDataWithNotifications(): array
    {
        $owner = Auth::guard('owner')->user();
        $unreadNotifications = $this->getOwnerNotificationsQuery()
            ->where('read', 0)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $allNotifications = $this->getOwnerNotificationsQuery()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return compact('owner', 'unreadNotifications', 'allNotifications');
    }

    public function unreadCountJson()
    {
        $unreadCount = $this->getOwnerNotificationsQuery()->where('read', 0)->count();
        return response()->json(['unread_count' => $unreadCount]);
    }

    public function markNotificationsRead(Request $request)
    {
        $this->getOwnerNotificationsQuery()->where('read', 0)->update(['read' => 1]);
        return response()->json(['success' => true]);
    }

    public function dashboard()
    {
        $data  = $this->getOwnerDataWithNotifications();
        $owner = $data['owner'];
        abort_unless($owner, 403);

        $stadiumIds   = Stadium::where('owner_id', $owner->id)->pluck('id');
        $startOfMonth = now()->startOfMonth()->toDateString();
        $today        = now()->toDateString();

        $bookingsMonthCount =
              PlayerReservation::whereIn('s_id', $stadiumIds)->where('date', '>=', $startOfMonth)->count()
            + OwnerReservation ::whereIn('s_id', $stadiumIds)->where('date', '>=', $startOfMonth)->count();

        $bookingsToday =
              PlayerReservation::whereIn('s_id', $stadiumIds)->where('date', $today)->count()
            + OwnerReservation ::whereIn('s_id', $stadiumIds)->where('date', $today)->count();

        $playerRev = (float) PlayerReservation::whereIn('s_id', $stadiumIds)
            ->whereIn('status', ['finished', 'playing'])
            ->where('date', '>=', $startOfMonth)
            ->sum('res_price');

        $ownerRev = (float) OwnerReservation::whereIn('s_id', $stadiumIds)
            ->whereIn('status', ['finished', 'playing'])
            ->where('date', '>=', $startOfMonth)
            ->sum('res_price');

        $monthRevenue = $playerRev + $ownerRev;
        $adminDue     = round($monthRevenue * self::ADMIN_COMMISSION, 2);

        $avgRating    = round((float) (Review::whereIn('s_id', $stadiumIds)->avg('rating_number') ?? 0), 1);
        $reviewsCount = (int) Review::whereIn('s_id', $stadiumIds)->count();

        $closestSlots = $this->computeClosestSlots($stadiumIds);

        return view(
            'provider.dashboard2',
            array_merge(
                $data,
                compact(
                    'bookingsMonthCount',
                    'bookingsToday',
                    'monthRevenue',
                    'adminDue',
                    'avgRating',
                    'reviewsCount',
                    'closestSlots'
                )
            )
        );
    }

    private function computeClosestSlots($stadiumIds): array
    {
        $rows     = [];
        $now      = now();
        $today    = $now->toDateString();
        $tomorrow = $now->copy()->addDay()->toDateString();

        $stadiums = Stadium::whereIn('id', $stadiumIds)
            ->where('status', 1)
            ->get(['id', 'name']);

        foreach ($stadiums as $s) {
            $freeToday = $this->freeSlotsFor($s, $today);
            foreach ($freeToday as $from) {
                $rows[] = [
                    'date'         => $today,
                    'from'         => $from,
                    'to'           => sprintf('%02d:00', ((int) substr($from, 0, 2) + 1) % 24),
                    'stadium_id'   => $s->id,
                    'stadium_name' => $s->name,
                ];
            }

            if ($freeToday->isEmpty()) {
                $freeTomorrow = $this->freeSlotsFor($s, $tomorrow);
                foreach ($freeTomorrow as $from) {
                    $rows[] = [
                        'date'         => $tomorrow,
                        'from'         => $from,
                        'to'           => sprintf('%02d:00', ((int) substr($from, 0, 2) + 1) % 24),
                        'stadium_id'   => $s->id,
                        'stadium_name' => $s->name,
                    ];
                }
            }
        }

        usort($rows, fn ($a, $b) => strcmp($a['date'].' '.$a['from'], $b['date'].' '.$b['from']));
        return $rows;
    }

    /**
     * Evening-only free slots: 17:00 → 00:00 (1h steps, last start 23:00).
     * Handles overlaps across midnight (e.g., 23:00 → 00:00 next day).
     */
    private function freeSlotsFor(Stadium $stadium, string $date)
    {
        $selectedDate = Carbon::parse($date)->startOfDay();

        $startHour   = 17;
        $startMinute = 0;
        $endHour     = 0;   // midnight
        $endMinute   = 0;

        $startTime = $selectedDate->copy()->setTime($startHour, $startMinute);
        $endTime   = $selectedDate->copy()->setTime($endHour, $endMinute);

        $crossesMidnight = false;
        if ($endTime->lessThanOrEqualTo($startTime)) {
            $endTime->addDay();
            $crossesMidnight = true;
        }

        $period     = \Carbon\CarbonPeriod::create($startTime, '1 hour', $endTime->copy()->subHour());
        $slotTuples = collect($period)->map(fn (Carbon $from) => [$from, $from->copy()->addHour()]);

        if ($selectedDate->isSameDay(now())) {
            $slotTuples = $slotTuples->filter(fn ($slot) => $slot[0]->greaterThanOrEqualTo(now()))->values();
        }

        $datesToFetch = [$selectedDate->toDateString()];
        if ($crossesMidnight) $datesToFetch[] = $selectedDate->copy()->addDay()->toDateString();

        $playerReservations = PlayerReservation::where('s_id', $stadium->id)->whereIn('date', $datesToFetch)->get();
        $ownerReservations  = OwnerReservation ::where('s_id', $stadium->id)->whereIn('date', $datesToFetch)->get();

        $buildInterval = function ($dateField, $timeField, $timeToField) {
            $dateStr = $dateField instanceof Carbon ? $dateField->format('Y-m-d') : (string)$dateField;
            $start   = Carbon::parse($dateStr.' '.$timeField);
            $end     = !empty($timeToField) ? Carbon::parse($dateStr.' '.$timeToField) : $start->copy()->addHour();
            if ($end->lessThanOrEqualTo($start)) { $end->addDay(); } // 23:00 → 00:00
            return [$start, $end];
        };

        $overlaps = fn (Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd) =>
            $aStart->lt($bEnd) && $bStart->lt($aEnd);

        $playerBlocking    = ['confirmed', 'playing', 'pending'];
        $playerNonBlocking = ['cancelled', 'canceled', 'payment_failed', 'finished'];
        $ownerNonBlocking  = ['cancelled', 'canceled', 'void', 'payment_failed'];

        $available = collect();

        foreach ($slotTuples as [$slotStart, $slotEnd]) {
            if ($slotStart->toDateString() !== $selectedDate->toDateString()) continue;

            $blocked = false;

            foreach ($ownerReservations as $o) {
                [$resStart, $resEnd] = $buildInterval($o->date, $o->time, $o->time_to ?? null);
                if (!$overlaps($resStart, $resEnd, $slotStart, $slotEnd)) continue;
                $oStatus = isset($o->status) ? strtolower((string)$o->status) : null;
                if ($oStatus !== null && in_array($oStatus, $ownerNonBlocking, true)) continue;
                $blocked = true; break;
            }
            if ($blocked) continue;

            foreach ($playerReservations as $p) {
                [$resStart, $resEnd] = $buildInterval($p->date, $p->time, $p->time_to ?? null);
                if (!$overlaps($resStart, $resEnd, $slotStart, $slotEnd)) continue;
                $pStatus = strtolower((string)$p->status);
                if (in_array($pStatus, $playerBlocking, true)) { $blocked = true; break; }
                if (in_array($pStatus, $playerNonBlocking, true)) { continue; }
            }
            if ($blocked) continue;

            $available->push($slotStart->format('H:i'));
        }

        return $available->values();
    }

    public function stadiums(Request $request)
    {
        $data = $this->getOwnerDataWithNotifications();
        $owner = $data['owner'];
        abort_unless($owner, 403);

        $query = Stadium::where('owner_id', $owner->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active' ? 1 : 0);
        }

        $stadiums = $query->latest('id')->get()->map(fn ($stadium) => [
            'id' => $stadium->id, 'name' => $stadium->name, 'location' => $stadium->address,
            'type' => '—', 'description' => $stadium->description, 'price' => $stadium->price,
            'status' => $stadium->status ? 'active' : 'inactive', 'reason' => null,
            'status_changed_by' => $stadium->status_changed_by,
        ]);

        $data['stadiums'] = $stadiums;
        return view('provider.Stadiums.stadiums', $data);
    }

    public function toggle(Stadium $stadium): RedirectResponse
    {
        $owner = Auth::guard('owner')->user();
        abort_unless($stadium->owner_id === $owner->id, 403);

        if ($stadium->status == 0 && $stadium->status_changed_by === 'admin') {
            return back()->with('error', 'لا يمكنك تفعيل هذا الملعب. تم تعطيله من قبل الإدارة.');
        }

        $targetStatus = $stadium->status ? 0 : 1;

        $stadium->update([
            'status'            => $targetStatus,
            'status_changed_by' => 'provider'
        ]);

        if ($targetStatus === 0) {
            $this->refundConfirmedReservationsForStadium($stadium->id, 'provider');
        }

        return back()->with('success', $targetStatus ? 'تم التفعيل بنجاح.' : 'تم التعطيل بنجاح.');
    }

    public function confirmReservation(int $id): RedirectResponse
    {
        $reservation = PlayerReservation::findOrFail($id);
        if ($reservation->status === 'pending' && $reservation->tor === 'مبدئي') {
            $reservation->status = 'confirmed';
            $reservation->tor    = 'كامل';
            $reservation->save();
            return back()->with('success', 'تم تأكيد الحجز.');
        }
        return back()->with('error', 'لا يمكن تأكيد هذا الحجز.');
    }

    public function bookings(Request $request)
    {
        $data = $this->getOwnerDataWithNotifications();
        $owner = $data['owner'];
        abort_unless($owner, 403);

        $statusFilter = $request->query('status'); // confirmed|pending|playing|finished|cancelled

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'player_name' => 'required|string|max:100',
                'stadium_id'  => 'required|exists:stadiums,id',
                'date'        => 'required|date|after_or_equal:today',
                'from'        => 'required|date_format:H:i',
                'to'          => [
                    'required','date_format:H:i',
                    function ($attribute, $value, $fail) use ($request) {
                        $from = $request->input('from');
                        if (!$from) return;
                        // allow 23:00 -> 00:00, else require to > from
                        if (!($from === '23:00' && $value === '00:00')) {
                            if (Carbon::createFromFormat('H:i', $value) <= Carbon::createFromFormat('H:i', $from)) {
                                return $fail('لا بد أن يكون وقت الانتهاء بعد وقت البداية بساعة.');
                            }
                        }
                    },
                ],
            ]);

            if ($validated['date'] == now()->toDateString()) {
                $endCheck   = Carbon::parse($validated['date'].' '.$validated['to']);
                $startCheck = Carbon::parse($validated['date'].' '.$validated['from']);
                if ($endCheck->lessThanOrEqualTo($startCheck)) { $endCheck->addDay(); }
                if ($endCheck->lessThanOrEqualTo(now())) {
                    return back()->with('error', 'لا يمكن حجز وقت قد انتهى بالفعل.')->withInput();
                }
            }

            $stadium = Stadium::where('id', $validated['stadium_id'])
                ->where('owner_id', $owner->id)->firstOrFail();
            if ($stadium->status == 0) {
                return back()->with('error', 'لا يمكن الحجز في هذا الملعب لأنه معطل.');
            }

            // overlap detection with midnight handling
            $candidateStart = Carbon::parse($validated['date'].' '.$validated['from']);
            $candidateEnd   = Carbon::parse($validated['date'].' '.$validated['to']);
            if ($candidateEnd->lessThanOrEqualTo($candidateStart)) { $candidateEnd->addDay(); }

            $datesToCheck = [$candidateStart->toDateString(), $candidateStart->copy()->addDay()->toDateString()];

            $playerReservations = PlayerReservation::where('s_id', $stadium->id)
                ->whereIn('date', $datesToCheck)
                ->get();
            $ownerReservations  = OwnerReservation::where('s_id', $stadium->id)
                ->whereIn('date', $datesToCheck)
                ->get();

            $buildInterval = function ($dateField, $timeField, $timeToField) {
                $dateStr = $dateField instanceof Carbon ? $dateField->format('Y-m-d') : (string)$dateField;
                $start   = Carbon::parse($dateStr.' '.$timeField);
                $end     = !empty($timeToField) ? Carbon::parse($dateStr.' '.$timeToField) : $start->copy()->addHour();
                if ($end->lessThanOrEqualTo($start)) { $end->addDay(); }
                return [$start, $end];
            };

            $overlaps = fn (Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd) =>
                $aStart->lt($bEnd) && $bStart->lt($aEnd);

            $isBusy = false;

            foreach ($playerReservations as $r) {
                [$rs, $re] = $buildInterval($r->date, $r->time, $r->time_to ?? null);
                $status = strtolower((string)$r->status);
                if (in_array($status, ['cancelled','canceled','payment_failed','finished'], true)) continue;
                if ($overlaps($rs, $re, $candidateStart, $candidateEnd)) { $isBusy = true; break; }
            }
            if (!$isBusy) {
                foreach ($ownerReservations as $r) {
                    [$rs, $re] = $buildInterval($r->date, $r->time, $r->time_to ?? null);
                    $status = isset($r->status) ? strtolower((string)$r->status) : 'confirmed';
                    if (in_array($status, ['cancelled','canceled','void','payment_failed'], true)) continue;
                    if ($overlaps($rs, $re, $candidateStart, $candidateEnd)) { $isBusy = true; break; }
                }
            }

            if ($isBusy) return back()->with('error', 'هذا الوقت محجوز بالفعل.')->withInput();

            OwnerReservation::create([
                's_id'        => $stadium->id,
                'owner_id'    => $owner->id,
                'player_name' => $validated['player_name'],
                'res_price'   => $stadium->price,
                'date'        => $validated['date'],
                'time'        => $validated['from'],
                'time_to'     => $validated['to'],
                'status'      => 'confirmed',
                'payment_type'=> 'cash',
                'tor'         => 'كامل'
            ]);
            return back()->with('success', 'تم إضافة الحجز بنجاح.');
        }

        $search = $request->query('player');

        $ownerId = $owner->id;
        $activePlayers = PlayerReservation::with(['stadium', 'player'])
            ->whereHas('stadium', fn ($q) => $q->where('owner_id', $ownerId))
            ->when($search, fn ($q) => $q->whereHas('player', fn ($qq) => $qq->where('name', 'like', "%{$search}%")))
            ->when($statusFilter, function ($q) use ($statusFilter) {
                if ($statusFilter === 'cancelled') {
                    $q->whereIn('status', ['cancelled', 'canceled']);
                } else {
                    $q->where('status', $statusFilter);
                }
            })
            ->get()
            ->filter(function ($r) {
                $startDateTime = Carbon::parse($r->date)->setTimeFromTimeString($r->time);
                if ($startDateTime->isPast()) {
                    ArchivedPlayerReservation::create([
                        's_id'          => $r->s_id,
                        'player_id'     => $r->player_id,
                        'player_name'   => $r->player->name ?? null,
                        'res_price'     => $r->res_price,
                        'date'          => Carbon::parse($r->date)->toDateString(),
                        'time'          => $r->time,
                        'time_to'       => $r->time_to,
                        'status'        => $r->status,
                        'payment_type'  => $r->payment_type,
                        'tor'           => $r->tor,
                        'archived_at'   => now(),
                    ]);
                    $r->delete();
                    return false;
                }
                return true;
            })
            ->map(function ($r) {
                $status = strtolower((string)$r->status);
                if ($status === 'canceled') $status = 'cancelled';
                return [
                    'id'     => $r->id,
                    'player' => $r->player->name ?? '',
                    'stadium'=> $r->stadium->name ?? '',
                    'date'   => $r->date,
                    'from'   => $r->time,
                    'to'     => $r->time_to,
                    'tor'    => $r->tor,
                    'status' => $status,
                    'type'   => 'player'
                ];
            });

        $ownerRes = OwnerReservation::with('stadium')
            ->where('owner_id', $owner->id)
            ->when($statusFilter, function ($q) use ($statusFilter) {
                if ($statusFilter === 'cancelled') {
                    $q->whereIn('status', ['cancelled', 'canceled']);
                } else {
                    $q->where('status', $statusFilter);
                }
            })
            ->get()
            ->filter(function ($r) {
                $startDateTime = Carbon::parse($r->date)->setTimeFromTimeString($r->time);
                if ($startDateTime->isPast()) {
                    ArchivedPlayerReservation::create([
                        's_id'         => $r->s_id,
                        'player_id'    => null,
                        'player_name'  => $r->player_name,
                        'res_price'    => $r->res_price,
                        'date'         => Carbon::parse($r->date)->toDateString(),
                        'time'         => $r->time,
                        'time_to'      => $r->time_to,
                        'status'       => $r->status,
                        'payment_type' => $r->payment_type,
                        'tor'          => $r->tor,
                        'archived_at'  => now(),
                    ]);
                    $r->delete();
                    return false;
                }
                return true;
            })
            ->map(function ($r) {
                $status = strtolower((string)($r->status ?? 'confirmed'));
                if ($status === 'canceled') $status = 'cancelled';
                return [
                    'id'     => $r->id,
                    'player' => $r->player_name,
                    'stadium'=> $r->stadium->name ?? '',
                    'date'   => $r->date,
                    'from'   => $r->time,
                    'to'     => $r->time_to,
                    'tor'    => $r->tor,
                    'status' => $status,
                    'type'   => 'owner'
                ];
            });

        $reservations = collect($activePlayers)->concat($ownerRes)->sortByDesc('id')->values();

        $archived = ArchivedPlayerReservation::with(['stadium', 'player'])
            ->whereHas('stadium', fn ($q) => $q->where('owner_id', $owner->id))
            ->latest('archived_at')->get()
            ->map(fn ($r) => [
                'id'          => $r->id,
                'player_id'   => $r->player_id,
                'player'      => $r->player->name ?? $r->player_name ?? 'غير مسجل',
                'stadium'     => $r->stadium->name ?? 'ملعب محذوف',
                'archived_at' => $r->archived_at
            ]);

        $stadiums = Stadium::where('owner_id', $owner->id)->where('status', 1)->select('id', 'name')->get();
        $viewData = compact('reservations', 'stadiums', 'archived');

        return view('provider.bookings.bookings', array_merge($data, $viewData));
    }

    public function archive(int $id): RedirectResponse
    {
        $owner = Auth::guard('owner')->user();
        abort_unless($owner, 403);

        $pr = PlayerReservation::with(['stadium', 'player'])
            ->where('id', $id)
            ->whereHas('stadium', fn ($q) => $q->where('owner_id', $owner->id))
            ->first();

        if ($pr) {
            ArchivedPlayerReservation::create([
                's_id'         => $pr->s_id,
                'player_id'    => $pr->player_id,
                'player_name'  => $pr->player->name ?? null,
                'res_price'    => $pr->res_price,
                'date'         => Carbon::parse($pr->date)->toDateString(),
                'time'         => $pr->time,
                'time_to'      => $pr->time_to,
                'status'       => $pr->status,
                'payment_type' => $pr->payment_type,
                'tor'          => $pr->tor,
                'archived_at'  => now(),
            ]);
            $pr->delete();
            return back()->with('success', 'تم أرشفة حجز اللاعب.');
        }

        $or = OwnerReservation::with('stadium')
            ->where('id', $id)->where('owner_id', $owner->id)->firstOrFail();

        ArchivedPlayerReservation::create([
            's_id'         => $or->s_id,
            'player_id'    => null,
            'player_name'  => $or->player_name,
            'res_price'    => $or->res_price,
            'date'         => Carbon::parse($or->date)->toDateString(),
            'time'         => $or->time,
            'time_to'      => $or->time_to,
            'status'       => $or->status,
            'payment_type' => $or->payment_type,
            'tor'          => $or->tor,
            'archived_at'  => now(),
        ]);
        $or->delete();
        return back()->with('success', 'تم أرشفة حجز المالك.');
    }

    public function storeComplaint(Request $request): RedirectResponse
    {
        $owner = Auth::guard('owner')->user();
        abort_unless($owner, 403);

        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'message'   => 'required|string|max:1000',
        ]);

        Support::create([
            'reporter_type'         => 'App\Models\Owner',
            'reporter_id'           => $owner->id,
            'target_type'           => 'App\Models\Player',
            'target_id'             => $validated['player_id'],
            'message'               => $validated['message'],
            'status'                => 'pending',
            'player_reservation_id' => null,
        ]);

        return back()->with('success', 'تم إرسال الشكوى بنجاح.');
    }

    public function availableSlots(Request $request)
    {
        $owner = Auth::guard('owner')->user();
        abort_unless($owner, 403);
        $request->validate(['stadium_id' => 'required|exists:stadiums,id', 'date' => 'required|date']);

        $stadium = Stadium::where('id', $request->stadium_id)->where('owner_id', $owner->id)->firstOrFail();
        if ($stadium->status == 0) return response()->json([], 200);

        $date = Carbon::parse($request->date)->toDateString();
        $free = $this->freeSlotsFor($stadium, $date);

        return response()->json($free->values());
    }

    public function reportsIndex()
    {
        return $this->reports();
    }

    public function reviews()
    {
        $data  = $this->getOwnerDataWithNotifications();
        $owner = $data['owner'];
        $stadiumIds = Stadium::where('owner_id', $owner->id)->pluck('id');

        $reviews = Review::with(['stadium', 'player'])
            ->whereIn('s_id', $stadiumIds)
            ->orderBy('timestamp', 'desc')
            ->get();

        $avg = $reviews->count() ? round($reviews->avg('rating_number'), 1) : 0;

        $viewData = compact('reviews', 'avg');
        return view('provider.reviews.reviews', array_merge($data, $viewData));
    }

    public function reports()
    {
        $data  = $this->getOwnerDataWithNotifications();
        $owner = $data['owner'];

        $reports = Support::where('reporter_type', 'App\Models\Owner')
            ->where('reporter_id', $owner->id)
            ->with('target')
            ->latest('created_at')
            ->get();

        $viewData = compact('reports');
        return view('provider.reports.reports', array_merge($data, $viewData));
    }

    public function profile()
    {
        $data = $this->getOwnerDataWithNotifications();
        return view('provider.profile.profile', $data);
    }

    public function updateProfile(Request $request)
    {
        $owner = Auth::guard('owner')->user();
        $rules = [
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:100', Rule::unique('owners', 'email')->ignore($owner->id)],
            'pic'      => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:6',
        ];
        $validated = $request->validate($rules);
        if ($request->hasFile('pic')) {
            if ($owner->pic && Storage::disk('public')->exists($owner->pic)) {
                Storage::disk('public')->delete($owner->pic);
            }
            $validated['pic'] = $request->file('pic')->store('provider_pics', 'public');
        } else {
            unset($validated['pic']);
        }
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $owner->update($validated);
        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح');
    }

    private function refundConfirmedReservationsForStadium(int $stadiumId, string $actor = 'provider'): int
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
                $wallet->refundEntireReservation($r);

                $who = $actor === 'admin' ? 'الإدارة' : 'مالك الملعب';
                $title = 'إلغاء الحجز واسترداد المبلغ';
                $body  = 'تم إلغاء مباراتك في ملعب ' . (optional($r->stadium)->name ?? 'غير محدد')
                    . " بسبب تعطيل الملعب من قِبل {$who}. "
                    . 'تم استرداد أي مبالغ مدفوعة إلى محافظكم.';

                $audience = collect([$r->player])->filter()->merge($r->invitedPlayers);

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

                $r->invitedPlayers()->detach();
                $r->status = 'cancelled';
                $r->save();
            });
        }

        return $items->count();
    }
}
