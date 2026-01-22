<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Availability;
use App\Models\AvailabilityWeekDay;
use App\Models\AvailabilityWeekRange;
use App\Models\AvailabilityDayBlockedSlot;
use App\Models\AvailabilityDateOverride;
use App\Models\AvailabilityOverrideRange;
use App\Models\AvailabilityDateBlockedSlot;

class AvailabilityController extends Controller
{
    public function show(Request $request)
    {
        $uid = $request->user()->id;

        $availability = Availability::firstOrCreate(
            ['user_id' => $uid],
            ['timezone' => 'Europe/Paris']
        );

        // S'assure d'avoir 7 jours
        for ($weekday = 1; $weekday <= 7; $weekday++) {
            AvailabilityWeekDay::firstOrCreate([
                'availability_id' => $availability->id,
                'weekday' => $weekday,
            ], [
                'enabled' => false,
                'slot_duration_min' => 30,
            ]);
        }

        $availability->load([
            'weekDays.ranges',
            'weekDays.blockedSlots',
            'dateOverrides.ranges',
            'dateOverrides.blockedSlots',
        ]);

        return response()->json([
            'availability' => $this->payload($availability),
        ]);
    }

    public function update(Request $request)
    {
        $uid = $request->user()->id;

        $data = $request->validate([
            'timezone' => ['nullable', 'string', 'max:64'],

            'days' => ['required', 'array'],
            'days.*.day' => ['required', 'integer', 'min:1', 'max:7'],
            'days.*.enabled' => ['required', 'boolean'],
            'days.*.slotDurationMin' => ['required', 'integer', 'min:5', 'max:250'],

            'days.*.ranges' => ['nullable', 'array'],
            'days.*.ranges.*.start' => ['required_with:days.*.ranges', 'date_format:H:i'],
            'days.*.ranges.*.end' => ['required_with:days.*.ranges', 'date_format:H:i'],

            'days.*.blockedSlotIds' => ['nullable', 'array'],
            'days.*.blockedSlotIds.*' => ['string', 'regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/'],

            'overridesByDate' => ['nullable', 'array'],
            'overridesByDate.*.isOff' => ['sometimes', 'boolean'],
            'overridesByDate.*.ranges' => ['nullable', 'array'],
            'overridesByDate.*.ranges.*.start' => ['required_with:overridesByDate.*.ranges', 'date_format:H:i'],
            'overridesByDate.*.ranges.*.end' => ['required_with:overridesByDate.*.ranges', 'date_format:H:i'],

            'blockedSlotsByDate' => ['nullable', 'array'],
            'blockedSlotsByDate.*' => ['array'],
            'blockedSlotsByDate.*.*' => ['string', 'regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/'],
        ]);

        $availability = Availability::firstOrCreate(
            ['user_id' => $uid],
            ['timezone' => 'Europe/Paris']
        );

        DB::transaction(function () use ($availability, $data) {
            // timezone
            if (array_key_exists('timezone', $data) && $data['timezone']) {
                $availability->timezone = $data['timezone'];
                $availability->save();
            }

            // --- WEEK DAYS ---
            $daysPayload = $data['days'];

            foreach ($daysPayload as $d) {
                $weekday = (int) $d['day'];

                $weekDay = AvailabilityWeekDay::updateOrCreate(
                    [
                        'availability_id' => $availability->id,
                        'weekday' => $weekday,
                    ],
                    [
                        'enabled' => (bool) $d['enabled'],
                        'slot_duration_min' => (int) $d['slotDurationMin'],
                    ]
                );

                // RANGES: replace all
                AvailabilityWeekRange::where('week_day_id', $weekDay->id)->delete();
                foreach (($d['ranges'] ?? []) as $r) {
                    AvailabilityWeekRange::create([
                        'week_day_id' => $weekDay->id,
                        'start_time' => $r['start'],
                        'end_time' => $r['end'],
                    ]);
                }

                // BLOCKED SLOTS (weekly): replace all
                AvailabilityDayBlockedSlot::where('week_day_id', $weekDay->id)->delete();
                foreach (($d['blockedSlotIds'] ?? []) as $slotId) {
                    [$start, $end] = explode('-', $slotId);
                    AvailabilityDayBlockedSlot::create([
                        'week_day_id' => $weekDay->id,
                        'start_time' => $start,
                        'end_time' => $end,
                    ]);
                }
            }

            // --- OVERRIDES BY DATE ---
            $overridesByDate = $data['overridesByDate'] ?? [];
            $blockedByDate = $data['blockedSlotsByDate'] ?? [];

            // Strategy: sync full set
            $incomingDates = array_keys($overridesByDate);
            // supprime overrides absents
            AvailabilityDateOverride::where('availability_id', $availability->id)
                ->when(count($incomingDates) > 0, fn($q) => $q->whereNotIn('date', $incomingDates))
                ->when(count($incomingDates) === 0, fn($q) => $q) // delete all
                ->delete();

            foreach ($overridesByDate as $date => $ov) {
                $override = AvailabilityDateOverride::updateOrCreate(
                    [
                        'availability_id' => $availability->id,
                        'date' => $date,
                    ],
                    [
                        'is_off' => (bool)($ov['isOff'] ?? false),
                    ]
                );

                // ranges override: replace all
                if (array_key_exists('ranges', $ov)) {
                    AvailabilityOverrideRange::where('override_id', $override->id)->delete();

                    foreach (($ov['ranges'] ?? []) as $r) {
                        AvailabilityOverrideRange::create([
                            'override_id' => $override->id,
                            'start_time' => $r['start'],
                            'end_time' => $r['end'],
                        ]);
                    }
                }

                // blocked slots for date: replace all
                if (array_key_exists($date, $blockedByDate)) {
                    AvailabilityDateBlockedSlot::where('override_id', $override->id)->delete();

                    foreach (($blockedByDate[$date] ?? []) as $slotId) {
                        [$start, $end] = explode('-', $slotId);
                        AvailabilityDateBlockedSlot::create([
                            'override_id' => $override->id,
                            'start_time' => $start,
                            'end_time' => $end,
                        ]);
                    }
                }
            }
        });

        $availability->refresh();
        $availability->load([
            'weekDays.ranges',
            'weekDays.blockedSlots',
            'dateOverrides.ranges',
            'dateOverrides.blockedSlots',
        ]);

        return response()->json([
            'availability' => $this->payload($availability),
        ]);
    }

    private function payload(Availability $availability): array
    {
        $days = $availability->weekDays
            ->sortBy('weekday')
            ->values()
            ->map(function ($d) {
                $ranges = ($d->ranges ?? collect())->map(fn($r) => [
                    'id' => (string)$r->id,
                    'start' => $r->start_time,
                    'end' => $r->end_time,
                ])->values();

                $blockedIds = ($d->blockedSlots ?? collect())->map(fn($b) => "{$b->start_time}-{$b->end_time}")->values();

                return [
                    'day' => (int)$d->weekday,
                    'enabled' => (bool)$d->enabled,
                    'slotDurationMin' => (int)$d->slot_duration_min,
                    'ranges' => $ranges,
                    'blockedSlotIds' => $blockedIds,
                ];
            });

        $overridesByDate = [];
        $blockedSlotsByDate = [];

        foreach ($availability->dateOverrides as $ov) {
            $dateKey = $ov->date instanceof \Carbon\Carbon
                ? $ov->date->format('Y-m-d')
                : (string) $ov->date;

            $overridesByDate[$dateKey] = [
                'isOff' => (bool) $ov->is_off,
                'ranges' => ($ov->ranges ?? collect())->map(fn($r) => [
                    'id' => (string) $r->id,
                    'start' => $r->start_time,
                    'end' => $r->end_time,
                ])->values(),
            ];

            $blockedSlotsByDate[$dateKey] = ($ov->blockedSlots ?? collect())
                ->map(fn($b) => "{$b->start_time}-{$b->end_time}")
                ->values()
                ->all();
        }

        return [
            'timezone' => $availability->timezone,
            'days' => $days,
            'overridesByDate' => $overridesByDate,
            'blockedSlotsByDay' => (object)[], // optionnel (tu as déjà blocked dans days)
            'blockedSlotsByDate' => $blockedSlotsByDate,
        ];
    }
}
