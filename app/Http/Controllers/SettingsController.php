<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\PasswordPolicy;
use App\Models\UserSession;
use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    /**
     * Render the unified Settings Hub with all required enterprise telemetry.
     */
    public function settings(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->loadMissing(['department', 'division', 'unit', 'section', 'designation']);
            try {
                $user->email_password = $user->email_password ? Crypt::decrypt($user->email_password) : null;
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                $user->email_password = null;
            }
        }

        $organisation = Organisation::first();
        $passwordPolicy = PasswordPolicy::first();

        // System Diagnostic & Telemetry Info
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default'),
            'environment' => app()->environment(),
            'server_os' => PHP_OS . ' (' . php_uname('m') . ')',
            'storage_writable' => is_writable(storage_path()),
            'max_upload_size' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
        ];

        return inertia('dashboard/settings/Settings', [
            'user' => $user,
            'organisation' => $organisation,
            'passwordPolicy' => $passwordPolicy,
            'systemInfo' => $systemInfo,
        ]);
    }

    /**
     * Get or render Organisation settings.
     */
    public function organisation()
    {
        return $this->settings(request());
    }

    public function getOrganisation()
    {
        $organisation = Organisation::first();
        return response()->json($organisation);
    }

    /**
     * Save Organisation identity, branding, and legal metadata.
     */
    public function saveOrganisation(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:40',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'rc_number' => 'nullable|string|max:100',
            'tin_number' => 'nullable|string|max:100',
            'motto' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:30',
            'secondary_color' => 'nullable|string|max:30',
            'timezone' => 'nullable|string|max:50',
            'currency_symbol' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:10',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            'stamp_file' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
        ]);

        $destinationPath = public_path('organisation');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $logoPath = null;
        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $imageName = 'logo_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $imageName);
            $logoPath = 'organisation/' . $imageName;
        }

        $stampPath = null;
        if ($request->hasFile('stamp_file')) {
            $stampImage = $request->file('stamp_file');
            $stampName = 'stamp_' . time() . '.' . $stampImage->getClientOriginalExtension();
            $stampImage->move($destinationPath, $stampName);
            $stampPath = 'organisation/' . $stampName;
        }

        $org = Organisation::first() ?? new Organisation();
        $org->company_name = $request->company_name;
        $org->email = $request->email;
        $org->phone = $request->phone;
        $org->address = $request->address;
        $org->website = $request->website;
        $org->rc_number = $request->rc_number;
        $org->tin_number = $request->tin_number;
        $org->motto = $request->motto;
        $org->primary_color = $request->primary_color ?? '#10B981';
        $org->secondary_color = $request->secondary_color ?? '#0F172A';
        $org->timezone = $request->timezone ?? 'Africa/Lagos';
        $org->currency_symbol = $request->currency_symbol ?? '₦';
        $org->currency_code = $request->currency_code ?? 'NGN';

        if ($logoPath) {
            $org->logo = $logoPath;
        }
        if ($stampPath) {
            $org->stamp_path = $stampPath;
        }

        $org->save();

        // Forensic Audit Log
        if (function_exists('activity')) {
            activity('organisation_settings')
                ->causedBy(auth()->user())
                ->performedOn($org)
                ->withProperties(['company_name' => $org->company_name, 'primary_color' => $org->primary_color])
                ->log('Updated corporate organisation metadata and branding settings');
        }

        return redirect()->back()->with('success', 'Organisation and branding settings updated successfully.');
    }

    /**
     * Save user workspace preferences (theme, font_size, density, default_landing_page).
     */
    public function savePreferences(Request $request)
    {
        $request->validate([
            'theme' => 'nullable|string|in:light,dark,system',
            'font_size' => 'nullable|integer|min:12|max:26',
            'density' => 'nullable|string|in:comfortable,compact,spacious',
            'default_landing_page' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        if ($request->has('theme')) {
            $user->theme = $request->theme;
        }
        if ($request->has('font_size')) {
            $user->font_size = (string)$request->font_size;
        }
        if ($request->has('density')) {
            $user->density = $request->density;
        }
        if ($request->has('default_landing_page')) {
            $user->default_landing_page = $request->default_landing_page;
        }
        $user->save();

        if (function_exists('activity')) {
            activity('user_preferences')
                ->causedBy($user)
                ->withProperties(['theme' => $user->theme, 'font_size' => $user->font_size, 'density' => $user->density])
                ->log('User updated workspace display preferences');
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Preferences saved successfully.']);
        }

        return redirect()->back()->with('success', 'Preferences updated successfully.');
    }

    /**
     * Backwards-compatible endpoint for AJAX theme toggle.
     */
    public function updatePreferences(Request $request)
    {
        return $this->savePreferences($request);
    }

    /**
     * Save User Notification Channel Configuration.
     */
    public function saveNotificationSettings(Request $request)
    {
        $request->validate([
            'notification_settings' => 'required|array',
        ]);

        $user = auth()->user();
        $currentSettings = is_array($user->notification_settings) ? $user->notification_settings : [];
        $newSettings = array_merge($currentSettings, $request->notification_settings);

        $user->notification_settings = $newSettings;
        $user->save();

        if (function_exists('activity')) {
            activity('notification_settings')
                ->causedBy($user)
                ->withProperties(['settings_count' => count($newSettings)])
                ->log('User updated notification alert channel preferences');
        }

        return redirect()->back()->with('success', 'Notification preferences updated successfully.');
    }

    /**
     * Update user regional localisation preferences.
     */
    public function updateLocalisation(Request $request)
    {
        $request->validate([
            'language' => 'required|string|max:50',
            'time_format' => 'required|string|max:30',
            'date_format' => 'required|string|max:30',
            'timezone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
        ]);

        $user = auth()->user();
        $user->language = $request->language;
        $user->time_format = $request->time_format;
        $user->date_format = $request->date_format;
        if ($request->filled('timezone')) {
            $user->timezone = $request->timezone;
        }
        if ($request->filled('currency')) {
            $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('admin') || $user->hasRole('super-admin');
            if ($isSuperAdmin) {
                $user->currency = $request->currency;
            }
        }
        $user->save();

        if (function_exists('activity')) {
            activity('localisation_settings')
                ->causedBy($user)
                ->withProperties(['language' => $user->language, 'timezone' => $user->timezone])
                ->log('User updated localisation and timezone preferences');
        }

        return redirect()->back()->with('success', 'Localisation preferences updated successfully.');
    }

    /**
     * Save digital approval signature drawn on interactive canvas.
     */
    public function saveDigitalSignaturePad(Request $request)
    {
        $request->validate([
            'signature_data' => 'required|string',
        ]);

        $user = auth()->user();
        $data = $request->signature_data;

        // Strip data:image/png;base64,
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]);
            $data = base64_decode($data);

            if ($data === false) {
                return response()->json(['error' => 'Base64 decode failed'], 422);
            }
        } else {
            return response()->json(['error' => 'Invalid data URI'], 422);
        }

        $dir = public_path('signatures');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $fileName = 'sig_user_' . $user->id . '_' . time() . '.' . $type;
        file_put_contents($dir . '/' . $fileName, $data);

        $user->digital_signature_path = 'signatures/' . $fileName;
        $user->save();

        if (function_exists('activity')) {
            activity('digital_signature')
                ->causedBy($user)
                ->log('User created and registered new drawn digital signature');
        }

        return response()->json([
            'success' => true,
            'path' => $user->digital_signature_path,
            'message' => 'Digital signature registered successfully.'
        ]);
    }

    /**
     * Update user telephone number.
     */
    public function updateProfilePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:30',
        ]);

        $user = auth()->user();
        $user->phone = $request->phone;
        $user->save();

        return redirect()->back()->with('success', 'Phone number updated successfully.');
    }

    /**
     * Save Password Security Policies.
     */
    public function passwordPolicies(Request $request)
    {
        $request->validate([
            'number_of_characters' => 'required|integer|min:6|max:64',
        ]);

        $pwd = PasswordPolicy::first() ?? new PasswordPolicy();
        $pwd->number_of_characters = $request->number_of_characters;
        $pwd->uppercase = $request->boolean('uppercase');
        $pwd->lowercase = $request->boolean('lowercase');
        $pwd->special_character = $request->boolean('special_character');
        $pwd->number = $request->boolean('number');
        $pwd->password_request_notification = $request->boolean('password_request_notification');
        $pwd->password_change_notification = $request->boolean('password_change_notification');
        $pwd->prevent_password_reusing = $request->boolean('prevent_password_reusing');
        $pwd->save();

        if (function_exists('activity')) {
            activity('password_policy')
                ->causedBy(auth()->user())
                ->performedOn($pwd)
                ->log('Updated enterprise password security policies');
        }

        return redirect()->back()->with('success', 'Password policies updated successfully.');
    }

    public function getPasswordPolicies()
    {
        $pwd = PasswordPolicy::first();
        return response()->json($pwd);
    }

    /**
     * Active User Sessions Management.
     */
    public function getUserSession()
    {
        $sessions = DB::table('sessions')->where('user_id', auth()->user()->id)->get()->map(function ($item) {
            $payload = @unserialize(base64_decode($item->payload));
            $browser = is_array($payload) && isset($payload['browser']) ? $payload['browser'] : 'Chrome';
            $location = is_array($payload) && isset($payload['location']) ? $payload['location'] : 'Local Session';
            return [
                'id' => $item->id,
                'browser' => $browser,
                'location' => $location,
                'ip_address' => $item->ip_address,
                'last_activity' => \Carbon\Carbon::createFromTimestamp($item->last_activity)->diffForHumans(),
                'is_current' => $item->id === session()->getId(),
            ];
        });

        return response()->json($sessions);
    }

    public function deleteUserSession($id)
    {
        DB::table('sessions')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }

    public function logOutAllOtherDevices()
    {
        $currentId = session()->getId();
        DB::table('sessions')
            ->where('user_id', auth()->user()->id)
            ->where('id', '!=', $currentId)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function enableTwoFactorAuthentication()
    {
        $user = auth()->user();
        if (method_exists($user, 'enableTwoFactorAuthentication')) {
            $user->enableTwoFactorAuthentication();
        } else {
            $user->is_two_factor_enabled = true;
            $user->save();
        }
        return redirect()->back()->with('success', 'Two factor authentication initialized.');
    }

    public function disableTwoFactorAuthentication($id)
    {
        $user = User::find($id) ?? auth()->user();
        $user->is_two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return redirect()->back()->with('success', 'Two factor authentication disabled.');
    }

    public function twoFA()
    {
        return inertia('dashboard/2fa');
    }

    public function updateUser2FA()
    {
        $user = auth()->user();
        $user->is_two_factor_enabled = true;
        $user->save();
        $intendedUrl = session()->get('url.intended', redirect()->back());
        $email = Crypt::encrypt(auth()->user()->email);
        return response()->json(['url' => $intendedUrl, 'email' => $email]);
    }

    /**
     * System Configurations (SMTP, IMAP, Custom Overrides).
     */
    public function getSystemConfigurations(Request $request)
    {
        $group = $request->query('group');
        if ($group) {
            $configs = SystemConfiguration::where('group', $group)->get();
        } else {
            $configs = SystemConfiguration::all();
        }
        return response()->json($configs);
    }

    public function saveSystemConfigurations(Request $request)
    {
        $configs = $request->input('configs', []);
        foreach ($configs as $config) {
            SystemConfiguration::updateOrCreate(
                ['key' => $config['key']],
                ['value' => $config['value'], 'group' => $config['group'] ?? 'general']
            );
        }

        if (function_exists('activity')) {
            activity('system_configurations')
                ->causedBy(auth()->user())
                ->withProperties(['configs_count' => count($configs)])
                ->log('Updated system environment configurations');
        }

        return redirect()->back()->with('success', 'System configurations updated successfully.');
    }
}
