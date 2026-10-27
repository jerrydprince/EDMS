<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\OAuthIntegration;
use Illuminate\Support\Facades\Auth;

class IntegrationController extends Controller
{
    public function getActiveIntegrations()
    {
        $integrations = OAuthIntegration::where('user_id', Auth::id())
                        ->pluck('provider')
                        ->toArray();
        return response()->json($integrations);
    }

    public function getCustomIntegrations()
    {
        return response()->json(\App\Models\CustomIntegration::all());
    }

    public function addCustomIntegration(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'api_key' => 'nullable|string',
        ]);

        $integration = \App\Models\CustomIntegration::create($request->all());
        
        return response()->json($integration);
    }

    public function redirect($provider)
    {
        $driver = $provider === 'microsoft' ? 'graph' : ($provider === 'pdf-adobe' ? 'adobe' : $provider);
        return Socialite::driver($driver)->redirect();
    }

    public function callback($provider)
    {
        try {
            $driver = $provider === 'microsoft' ? 'graph' : ($provider === 'pdf-adobe' ? 'adobe' : $provider);
            $socialUser = Socialite::driver($driver)->user();

            OAuthIntegration::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'provider' => $provider,
                ],
                [
                    'provider_id' => $socialUser->getId(),
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
                ]
            );

            return redirect('/settings?tab=Integrations')->with('success', ucfirst($provider) . ' connected successfully!');
        } catch (\Exception $e) {
            return redirect('/settings?tab=Integrations')->with('error', 'Failed to connect to ' . ucfirst($provider));
        }
    }
}
