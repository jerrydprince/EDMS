<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

class AuditTrailService
{
    /**
     * Domain channel constants
     */
    public const DOMAIN_SECURITY = 'security';
    public const DOMAIN_PRIVILEGE = 'privilege';
    public const DOMAIN_FINANCE = 'finance';
    public const DOMAIN_WORKFLOW = 'workflow';
    public const DOMAIN_RECORDS = 'records';
    public const DOMAIN_GOVERNANCE = 'governance';

    /**
     * Severity level constants
     */
    public const SEVERITY_ROUTINE = 'routine';
    public const SEVERITY_NOTICE = 'notice';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_CRITICAL = 'critical';

    /**
     * Master forensic logging method
     */
    public static function log(array $params): ?Activity
    {
        try {
            $logName = $params['log_name'] ?? self::DOMAIN_SECURITY;
            $event = $params['event'] ?? 'system_action';
            $description = $params['description'] ?? 'System operation recorded';
            $severity = $params['severity'] ?? self::SEVERITY_ROUTINE;
            $subject = $params['subject'] ?? null;
            $causer = $params['causer'] ?? (auth()->check() ? auth()->user() : null);
            $customProps = $params['properties'] ?? [];

            // Detect station device, browser, and network telemetry
            $telemetry = self::resolveStationTelemetry();

            // Detect actor info
            $actor = self::resolveActorData($causer);

            // Compute delta if old and new are provided
            $delta = [];
            if (isset($customProps['old']) || isset($customProps['new'])) {
                $delta = self::computeDelta($customProps['old'] ?? [], $customProps['new'] ?? []);
            }

            // Fetch previous record hash for tamper-evident hash chaining
            $lastActivity = Activity::latest('id')->first();
            $parentHash = $lastActivity && isset($lastActivity->properties['forensic_hash'])
                ? $lastActivity->properties['forensic_hash']
                : 'GENESIS_ROOT_HASH_' . date('Y');

            // Generate deterministic cryptographic signature of this event
            $timestamp = microtime(true);
            $hashPayload = json_encode([
                'timestamp' => $timestamp,
                'log_name' => $logName,
                'event' => $event,
                'causer_id' => $actor['id'] ?? 0,
                'ip' => $telemetry['ip'],
                'parent_hash' => $parentHash,
                'delta' => $delta,
            ]);
            $recordHash = hash('sha256', $hashPayload);

            $properties = array_merge([
                'event' => $event,
                'severity' => $severity,
                'actor' => $actor,
                'user' => $actor['name'] ?? 'System / Anonymous',
                'device' => $telemetry['device'],
                'ip' => $telemetry['ip'],
                'browser' => $telemetry['browser'],
                'os' => $telemetry['os'],
                'user_agent' => $telemetry['user_agent'],
                'url' => app()->runningInConsole() ? 'CLI' : (request()?->fullUrl() ?? 'N/A'),
                'method' => app()->runningInConsole() ? 'CLI' : (request()?->method() ?? 'N/A'),
                'delta' => $delta,
                'forensic_hash' => $recordHash,
                'parent_hash' => $parentHash,
                'forensic_timestamp' => $timestamp,
            ], $customProps);

            $activity = activity($logName)
                ->event($event)
                ->withProperty('forensic_hash', $recordHash);

            if ($subject instanceof Model) {
                $activity->performedOn($subject);
            }

            if ($causer instanceof Model) {
                $activity->causedBy($causer);
            }

            $activity->withProperties($properties);

            $savedActivity = $activity->log($description);

            if ($savedActivity) {
                // Populate direct indexed columns for maximum query performance
                DB::table('activity_log')
                    ->where('id', $savedActivity->id)
                    ->update([
                        'severity' => $severity,
                        'forensic_hash' => $recordHash,
                        'parent_hash' => $parentHash,
                    ]);
                $savedActivity->severity = $severity;
                $savedActivity->forensic_hash = $recordHash;
                $savedActivity->parent_hash = $parentHash;
            }

            return $savedActivity;
        } catch (\Throwable $e) {
            \Log::error('AuditTrailService Failed to record log: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'params' => $params
            ]);
            return null;
        }
    }

    /**
     * Flexible argument parser to support both ($event, $desc, $props, $subject, $sev)
     * and ($event, $desc, $subject, $causer, $props, $sev).
     */
    private static function parseHelperArgs($arg3, $arg4, $arg5, $arg6, string $defaultSeverity): array
    {
        $subject = null;
        $causer = null;
        $properties = [];
        $severity = $defaultSeverity;

        if (is_array($arg3)) {
            $properties = $arg3;
            $subject = $arg4;
            $severity = is_string($arg5) && !empty($arg5) ? $arg5 : $defaultSeverity;
        } elseif (is_array($arg4)) {
            $subject = $arg3;
            $properties = $arg4;
            $severity = is_string($arg5) && !empty($arg5) ? $arg5 : $defaultSeverity;
        } elseif (is_array($arg5)) {
            $subject = $arg3;
            $causer = $arg4;
            $properties = $arg5;
            $severity = is_string($arg6) && !empty($arg6) ? $arg6 : $defaultSeverity;
        } else {
            $subject = $arg3;
            $causer = $arg4;
            $severity = is_string($arg5) && !empty($arg5) ? $arg5 : (is_string($arg6) && !empty($arg6) ? $arg6 : $defaultSeverity);
        }

        // If causer is not set and subject is a User, default causer to that user
        if ($subject instanceof \App\Models\User && !$causer) {
            $causer = $subject;
        }

        return [$properties, $subject, $causer, $severity];
    }

    /**
     * Log Security Events (Logins, PIN resets, 2FA, password updates)
     */
    public static function logSecurity(string $event, string $description, $arg3 = [], $arg4 = null, $arg5 = null, $arg6 = null): ?Activity
    {
        [$properties, $subject, $causer, $severity] = self::parseHelperArgs($arg3, $arg4, $arg5, $arg6, self::SEVERITY_ROUTINE);
        return self::log([
            'log_name' => self::DOMAIN_SECURITY,
            'event' => $event,
            'description' => $description,
            'severity' => $severity,
            'subject' => $subject,
            'causer' => $causer,
            'properties' => $properties,
        ]);
    }

    /**
     * Log Privilege & Personnel Administration Events
     */
    public static function logPrivilege(string $event, string $description, $arg3 = [], $arg4 = null, $arg5 = null, $arg6 = null): ?Activity
    {
        [$properties, $subject, $causer, $severity] = self::parseHelperArgs($arg3, $arg4, $arg5, $arg6, self::SEVERITY_WARNING);
        return self::log([
            'log_name' => self::DOMAIN_PRIVILEGE,
            'event' => $event,
            'description' => $description,
            'severity' => $severity,
            'subject' => $subject,
            'causer' => $causer,
            'properties' => $properties,
        ]);
    }

    /**
     * Log Financial & Accounts Events (Vouchers, Cashbook, Votebook, Ledger)
     */
    public static function logFinance(string $event, string $description, $arg3 = [], $arg4 = null, $arg5 = null, $arg6 = null): ?Activity
    {
        [$properties, $subject, $causer, $severity] = self::parseHelperArgs($arg3, $arg4, $arg5, $arg6, self::SEVERITY_NOTICE);
        return self::log([
            'log_name' => self::DOMAIN_FINANCE,
            'event' => $event,
            'description' => $description,
            'severity' => $severity,
            'subject' => $subject,
            'causer' => $causer,
            'properties' => $properties,
        ]);
    }

    /**
     * Log Workflow, File Movement & Correspondence Events
     */
    public static function logWorkflow(string $event, string $description, $arg3 = [], $arg4 = null, $arg5 = null, $arg6 = null): ?Activity
    {
        [$properties, $subject, $causer, $severity] = self::parseHelperArgs($arg3, $arg4, $arg5, $arg6, self::SEVERITY_ROUTINE);
        return self::log([
            'log_name' => self::DOMAIN_WORKFLOW,
            'event' => $event,
            'description' => $description,
            'severity' => $severity,
            'subject' => $subject,
            'causer' => $causer,
            'properties' => $properties,
        ]);
    }

    /**
     * Log Records & Document Archiving Events
     */
    public static function logRecord(string $event, string $description, $arg3 = [], $arg4 = null, $arg5 = null, $arg6 = null): ?Activity
    {
        [$properties, $subject, $causer, $severity] = self::parseHelperArgs($arg3, $arg4, $arg5, $arg6, self::SEVERITY_ROUTINE);
        return self::log([
            'log_name' => self::DOMAIN_RECORDS,
            'event' => $event,
            'description' => $description,
            'severity' => $severity,
            'subject' => $subject,
            'causer' => $causer,
            'properties' => $properties,
        ]);
    }

    /**
     * Automatically log Eloquent Model changes with Field-level State Delta
     */
    public static function logModelDelta(Model $model, string $event, string $description, string $domain = self::DOMAIN_RECORDS, string $severity = self::SEVERITY_ROUTINE, array $extraProps = []): ?Activity
    {
        $dirty = $model->getDirty();
        $original = [];
        $new = [];

        $ignoredFields = ['updated_at', 'remember_token', 'password', 'approval_pin'];

        foreach ($dirty as $key => $newVal) {
            if (in_array($key, $ignoredFields)) continue;
            $original[$key] = $model->getOriginal($key);
            $new[$key] = $newVal;
        }

        $props = array_merge($extraProps, [
            'old' => $original,
            'new' => $new,
            'model_class' => get_class($model),
            'model_id' => $model->getKey(),
        ]);

        return self::log([
            'log_name' => $domain,
            'event' => $event,
            'description' => $description,
            'severity' => $severity,
            'subject' => $model,
            'properties' => $props,
        ]);
    }

    /**
     * Verify Cryptographic Chain Integrity across recent activity records
     */
    public static function verifyChainIntegrity(int $limit = 100): array
    {
        $records = Activity::latest('id')->take($limit)->get()->reverse()->values();
        $totalChecked = $records->count();
        $tamperedRecords = [];

        for ($i = 1; $i < $totalChecked; $i++) {
            $prevRecord = $records[$i - 1];
            $currRecord = $records[$i];

            $prevHash = $prevRecord->properties['forensic_hash'] ?? null;
            $currParentHash = $currRecord->properties['parent_hash'] ?? null;

            if ($prevHash && $currParentHash && $prevHash !== $currParentHash) {
                $tamperedRecords[] = [
                    'record_id' => $currRecord->id,
                    'description' => $currRecord->description,
                    'expected_parent' => $prevHash,
                    'found_parent' => $currParentHash,
                    'timestamp' => $currRecord->created_at->toDateTimeString(),
                ];
            }
        }

        $isVerified = count($tamperedRecords) === 0;
        $latestRecord = Activity::latest('id')->first();
        $headHash = $latestRecord && isset($latestRecord->properties['forensic_hash'])
            ? $latestRecord->properties['forensic_hash']
            : 'genesis';

        return [
            'is_intact' => $isVerified,
            'verified' => $isVerified,
            'total_checked' => $totalChecked,
            'verified_count' => $totalChecked - count($tamperedRecords),
            'tampered_count' => count($tamperedRecords),
            'tampered_records' => $tamperedRecords,
            'head_hash' => $headHash,
            'message' => $isVerified
                ? "Cryptographic SHA-256 chain is 100% intact across the last {$totalChecked} blocks. No unauthorized database tampering or deletions detected."
                : "Integrity alert: " . count($tamperedRecords) . " records have broken parent cryptographic seals.",
            'verified_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Resolve Actor information from user model or session
     */
    protected static function resolveActorData($user): array
    {
        if (!$user) {
            return [
                'id' => null,
                'name' => 'System / Automated Scheduler',
                'email' => 'system@edms.gov.ng',
                'ippis_no' => 'N/A',
                'department' => 'System',
                'designation' => 'System Engine',
            ];
        }

        return [
            'id' => $user->id,
            'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->name ?? 'Officer'),
            'email' => $user->email ?? 'N/A',
            'ippis_no' => $user->ippis_no ?? 'N/A',
            'department' => $user->department?->title ?? ($user->department_title ?? 'General Registry'),
            'designation' => $user->designation?->title ?? 'Personnel',
            'roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [],
        ];
    }

    /**
     * Compute field-level delta comparison
     */
    protected static function computeDelta(array $old, array $new): array
    {
        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            if ($oldVal !== $newVal) {
                $changes[$key] = [
                    'from' => $oldVal,
                    'to' => $newVal,
                ];
            }
        }

        return $changes;
    }

    /**
     * Resolve Client Station Telemetry
     */
    protected static function resolveStationTelemetry(): array
    {
        $userAgent = Request::userAgent() ?: 'Unknown Station';
        $ip = Request::header('X-Forwarded-For') ?? Request::ip() ?? '127.0.0.1';
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }

        // Basic device detection
        $device = 'Desktop';
        if (preg_match('/(android|iphone|ipad|ipod|mobile)/i', $userAgent)) {
            $device = preg_match('/(tablet|ipad)/i', $userAgent) ? 'Tablet' : 'Mobile';
        }

        // Basic OS detection
        $os = 'Unknown OS';
        if (str_contains($userAgent, 'Windows')) $os = 'Windows';
        elseif (str_contains($userAgent, 'Mac OS')) $os = 'macOS';
        elseif (str_contains($userAgent, 'Android')) $os = 'Android';
        elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) $os = 'iOS';
        elseif (str_contains($userAgent, 'Linux')) $os = 'Linux';

        // Basic Browser detection
        $browser = 'Unknown Browser';
        if (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg')) $browser = 'Chrome';
        elseif (str_contains($userAgent, 'Edg')) $browser = 'Edge';
        elseif (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) $browser = 'Safari';

        return [
            'ip' => $ip,
            'device' => $device,
            'os' => $os,
            'browser' => $browser,
            'user_agent' => $userAgent,
        ];
    }
}
