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
     * Toon 2FA setup pagina - alleen als 2FA nog niet is ingesteld
     */
    public function setup()
    {
        $user = Auth::user();

        // Redirect als 2FA al is ingesteld
        if ($user->google2fa_enabled) {
            return redirect()->route('two-factor.manage');
        }

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
            'isRequired' => true, // Altijd verplicht
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
     * Deactiveer 2FA - alleen mogelijk via management pagina
     */
    public function disable(Request $request)
    {
        $user = Auth::user();

        // Controleer of gebruiker toegang heeft tot management
        if (!$user->google2fa_enabled || $user->needsTwoFactorVerification()) {
            return redirect()->route('two-factor.verify');
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->code);

        if ($valid) {
            $user->update([
                'google2fa_enabled' => false,
                'google2fa_secret' => null,
                'two_factor_verified_at' => null,
            ]);

            // Na uitschakelen moet gebruiker opnieuw instellen
            return redirect()->route('two-factor.setup')->with('success', '2FA is gedeactiveerd. Je moet het opnieuw instellen.');
        }

        return redirect()->back()->withErrors(['code' => 'Ongeldige code. Probeer opnieuw.']);
    }

    /**
     * Toon 2FA verificatie pagina - alleen als verificatie nodig is
     */
    public function verify()
    {
        $user = Auth::user();

        // Redirect als 2FA niet is ingesteld
        if (!$user->google2fa_enabled) {
            return redirect()->route('two-factor.setup');
        }

        // Redirect als verificatie niet nodig is
        if (!$user->needsTwoFactorVerification()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('TwoFactor/TwoFactorVerify');
    }

    /**
     * Toon 2FA management pagina - alleen na volledige verificatie
     */
    public function manage()
    {
        $user = Auth::user();

        // Redirect als 2FA niet is ingesteld
        if (!$user->google2fa_enabled) {
            return redirect()->route('two-factor.setup');
        }

        // Redirect als verificatie nodig is
        if ($user->needsTwoFactorVerification()) {
            return redirect()->route('two-factor.verify');
        }

        return Inertia::render('TwoFactor/TwoFactorManage', [
            'lastVerified' => $user->two_factor_verified_at?->diffForHumans(),
        ]);
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
