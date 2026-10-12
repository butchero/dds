<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Notifications\ContactMessageNotification;
use App\Support\Breadcrumbs;
use App\Support\ContactChallenge;
use App\Support\ContactInquiry;
use App\Support\ContactOffice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        ContactChallenge::issue();

        return view('contact', [
            'breadcrumbs' => Breadcrumbs::forPage(__('Contact')),
            'office' => ContactOffice::present(),
            'departments' => ContactInquiry::departments(),
        ]);
    }

    public function challenge(): Response
    {
        $svg = ContactChallenge::svg();

        abort_if($svg === null, 404);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (filled($request->input('company_url'))) {
            return redirect()->route('contact')->with('status', __('Mesajul a fost trimis. Vă contactăm în curând.'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'regex:/^[^\r\n]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[^\r\n]+$/'],
            'department' => ['required', Rule::in(array_keys(ContactInquiry::departments()))],
            'message' => ['required', 'string', 'max:5000'],
            'verification_code' => ['required', 'string', 'max:10'],
        ], [
            'name.regex' => __('Numele nu poate conține rânduri noi.'),
            'phone.regex' => __('Telefonul nu poate conține rânduri noi.'),
        ], [
            'name' => 'numele',
            'email' => 'e-mailul',
            'phone' => 'telefonul',
            'department' => 'departamentul',
            'message' => 'mesajul',
            'verification_code' => 'codul de verificare',
        ]);

        if (! ContactChallenge::matches($data['verification_code'])) {
            throw ValidationException::withMessages([
                'verification_code' => __('Codul de verificare nu este corect.'),
            ]);
        }

        $companyEmail = SiteSetting::get('company_email');

        if (! filled($companyEmail)) {
            throw ValidationException::withMessages([
                'message' => __('Mesajul nu a putut fi trimis. Te rugăm să ne suni sau să ne scrii pe e-mail.'),
            ]);
        }

        Notification::route('mail', $companyEmail)->notify(new ContactMessageNotification(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'],
            department: $data['department'],
            message: $data['message'],
        ));

        return redirect()->route('contact')->with('status', __('Mesajul a fost trimis. Vă contactăm în curând.'));
    }
}
