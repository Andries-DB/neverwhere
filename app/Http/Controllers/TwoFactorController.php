<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Toon 2FA setup pagina
     */
    public function setup()
    {
        $user = Auth::user();

        if (!$user->google2fa_secret) {
            $secret = $this->google2fa->generateSecretKey();
            $user->update(['google2fa_secret' => $secret]);
        }

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        return Inertia::render('TwoFactor/TwoFactorSetup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $user->google2fa_secret,
            'is_enabled' => $user->google2fa_enabled,
            'last_verified' => $user->two_factor_verified_at?->diffForHumans(),
        ]);
    }

    /**
     * Activeer 2FA
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->code);

        if ($valid) {
            $user->update([
                'google2fa_enabled' => true,
                'two_factor_verified_at' => now(),
            ]);

            return redirect()->back()->with('success', '2FA is succesvol geactiveerd!');
        }

        return redirect()->back()->withErrors(['code' => 'Ongeldige code. Probeer opnieuw.']);
    }

    /**
     * Deactiveer 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->code);

        if ($valid) {
            $user->update([
                'google2fa_enabled' => false,
                'google2fa_secret' => null,
                'two_factor_verified_at' => null,
            ]);

            return redirect()->route('two-factor.setup');
        }

        return redirect()->back()->withErrors(['code' => 'Ongeldige code. Probeer opnieuw.']);
    }

    /**
     * Toon 2FA verificatie pagina
     */
    public function verify()
    {
        if (!Auth::user()->needsTwoFactorVerification()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('TwoFactor/TwoFactorVerify');
    }

    /**
     * Verifieer 2FA code
     */
    public function confirmVerification(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->code);

        if ($valid) {
            $user->markTwoFactorAsVerified();
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->back()->withErrors(['code' => 'Ongeldige code. Probeer opnieuw.']);
    }
}
