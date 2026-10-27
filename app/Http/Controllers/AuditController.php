<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Services\AuditTrailService;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditController extends Controller
{
    /**
     * Display a forensic listing of audit logs with executive KPIs and filters.
     */
    public function index(Request $request)
    {
        $domain = $request->input('domain', '');
        $severity = $request->input('severity', '');
        $event = $request->input('event', '');
        $userSearch = $request->input('user', '');
        $keyword = $request->input('keyword', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        // Support backward compatibility if search array was passed
        if ($request->has('search') && is_array($request->search)) {
            $s = $request->search;
            $event = $s['event'] ?? $event;
            $userSearch = $s['user'] ?? $userSearch;
            if (!empty($s['date'])) {
                $dateFrom = $s['date'];
                $dateTo = $s['date'];
            }
        }

        $query = Activity::query();

        // 1. Domain Filter (log_name)
        if (!empty($domain) && $domain !== 'all') {
            $query->where('log_name', $domain);
        }

        // 2. Severity Filter (Uses direct indexed column with fallback)
        if (!empty($severity) && $severity !== 'all') {
            $query->where(function ($q) use ($severity) {
                $q->where('severity', $severity)
                  ->orWhere('properties->severity', $severity);
            });
        }

        // 3. Event Code Filter (Uses direct indexed event column)
        if (!empty($event)) {
            $query->where(function ($q) use ($event) {
                $q->where('event', $event)
                  ->orWhere('properties->event', 'LIKE', "%{$event}%");
            });
        }

        // 4. Actor Search (causer or properties->actor->name or properties->user)
        if (!empty($userSearch)) {
            $query->where(function ($q) use ($userSearch) {
                $q->where('properties->actor->name', 'LIKE', "%{$userSearch}%")
                  ->orWhere('properties->actor->email', 'LIKE', "%{$userSearch}%")
                  ->orWhere('properties->user', 'LIKE', "%{$userSearch}%")
                  ->orWhereHasMorph('causer', [\App\Models\User::class], function ($uq) use ($userSearch) {
                      $uq->where('first_name', 'LIKE', "%{$userSearch}%")
                         ->orWhere('last_name', 'LIKE', "%{$userSearch}%")
                         ->orWhere('email', 'LIKE', "%{$userSearch}%");
                  });
            });
        }

        // 5. Keyword Search (Indexed hash, description, or IP)
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('forensic_hash', $keyword)
                  ->orWhere('description', 'LIKE', "%{$keyword}%")
                  ->orWhere('properties->telemetry->ip', 'LIKE', "%{$keyword}%")
                  ->orWhere('properties->ip', 'LIKE', "%{$keyword}%");
            });
        }

        // 6. Date Range Filters (Indexed created_at)
        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        // Paginate latest logs with eager loaded causer
        $activities = $query->with('causer')->latest('id')->paginate(25)->withQueryString();

        // Cached Executive Telemetry KPIs (60 seconds TTL for instant dashboard loading)
        $telemetry = \Illuminate\Support\Facades\Cache::remember('audit_telemetry_kpis_' . date('Ymd_H'), 60, function () {
            $today = Carbon::today();
            $totalToday = Activity::whereDate('created_at', $today)->count();
            
            $securityWarnings = Activity::whereDate('created_at', $today)
                ->where(function ($q) {
                    $q->whereIn('severity', ['warning', 'critical'])
                      ->orWhere('log_name', 'security')
                      ->orWhere('properties->severity', 'warning')
                      ->orWhere('properties->severity', 'critical');
                })->count();

            $financialOperations = Activity::where('log_name', 'finance')
                ->whereDate('created_at', $today)
                ->count();
            
            $activeActors = Activity::where('created_at', '>=', Carbon::now()->subHours(24))
                ->whereNotNull('causer_id')
                ->distinct('causer_id')
                ->count('causer_id');

            // Quick integrity audit of the last 50 hashed entries
            $integrityCheck = AuditTrailService::verifyChainIntegrity(50);

            return [
                'total_today' => $totalToday,
                'security_warnings' => $securityWarnings,
                'financial_operations' => $financialOperations,
                'active_actors' => $activeActors,
                'chain_integrity' => $integrityCheck
            ];
        });

        $filters = [
            'domain' => $domain,
            'severity' => $severity,
            'event' => $event,
            'user' => $userSearch,
            'keyword' => $keyword,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return inertia('dashboard/AuditLogs', [
            'activities' => $activities,
            'telemetry' => $telemetry,
            'filters' => $filters,
            'search' => $filters // backward compatibility
        ]);
    }

    /**
     * Real-time cryptographic chain integrity verification endpoint.
     */
    public function verifyIntegrity(Request $request)
    {
        $limit = (int) $request->input('limit', 100);
        $result = AuditTrailService::verifyChainIntegrity($limit);

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    /**
     * Export evidentiary audit trail in CSV format.
     */
    public function exportCsv(Request $request)
    {
        $domain = $request->input('domain', '');
        $severity = $request->input('severity', '');
        $event = $request->input('event', '');
        $userSearch = $request->input('user', '');
        $keyword = $request->input('keyword', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        $query = Activity::query();

        if (!empty($domain) && $domain !== 'all') {
            $query->where('log_name', $domain);
        }
        if (!empty($severity) && $severity !== 'all') {
            $query->where('properties->severity', $severity);
        }
        if (!empty($event)) {
            $query->where(function ($q) use ($event) {
                $q->where('event', $event)
                  ->orWhere('properties->event', 'LIKE', "%{$event}%");
            });
        }
        if (!empty($userSearch)) {
            $query->where(function ($q) use ($userSearch) {
                $q->where('properties->actor->name', 'LIKE', "%{$userSearch}%")
                  ->orWhere('properties->user', 'LIKE', "%{$userSearch}%");
            });
        }
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('description', 'LIKE', "%{$keyword}%")
                  ->orWhere('properties->telemetry->ip', 'LIKE', "%{$keyword}%")
                  ->orWhere('properties->forensic_hash', 'LIKE', "%{$keyword}%");
            });
        }
        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $filename = 'audit_trail_evidence_' . date('Y-m-d_His') . '.csv';

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($handle, [
                'Log ID',
                'Timestamp (UTC)',
                'Domain',
                'Severity',
                'Event',
                'Description',
                'Actor Name',
                'Actor Email',
                'Actor Role',
                'Station IP',
                'OS',
                'Browser',
                'Device',
                'Forensic Hash (SHA-256)',
                'Parent Hash'
            ]);

            $query->latest('id')->chunk(200, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    $props = $log->properties ? $log->properties->toArray() : [];
                    $actor = $props['actor'] ?? [];
                    $telemetry = $props['telemetry'] ?? [];

                    $actorName = $actor['name'] ?? ($props['user'] ?? 'System/Automated');
                    $actorEmail = $actor['email'] ?? '';
                    $actorRole = $actor['role'] ?? '';

                    $ip = $telemetry['ip'] ?? ($props['ip'] ?? 'N/A');
                    $os = $telemetry['os'] ?? 'N/A';
                    $browser = $telemetry['browser'] ?? 'N/A';
                    $device = $telemetry['device'] ?? ($props['device'] ?? 'N/A');

                    fputcsv($handle, [
                        $log->id,
                        $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '',
                        $log->log_name ?? 'general',
                        $props['severity'] ?? 'routine',
                        $log->event ?? ($props['event'] ?? 'general_event'),
                        $log->description ?? '',
                        $actorName,
                        $actorEmail,
                        $actorRole,
                        $ip,
                        $os,
                        $browser,
                        $device,
                        $props['forensic_hash'] ?? 'legacy_unhashed',
                        $props['parent_hash'] ?? 'genesis'
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
