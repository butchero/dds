<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\BillingProfile;
use App\Models\Equipment;
use App\Support\Counties;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountDataController extends Controller
{
    public function storeAddress(Request $request): RedirectResponse
    {
        $request->user()->addresses()->create($this->addressData($request));

        return back()->with('status', 'Adresa a fost adăugată.');
    }

    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->update($this->addressData($request));

        return back()->with('status', 'Adresa a fost salvată.');
    }

    public function deleteAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        try {
            $address->delete();
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Adresa a fost ștearsă.');
    }

    public function storeEquipment(Request $request): RedirectResponse
    {
        $request->user()->equipment()->create($this->equipmentData($request));

        return back()->with('status', 'Echipamentul a fost adăugat.');
    }

    public function updateEquipment(Request $request, Equipment $equipment): RedirectResponse
    {
        abort_unless($equipment->user_id === $request->user()->id, 403);
        $equipment->update($this->equipmentData($request));

        return back()->with('status', 'Echipamentul a fost salvat.');
    }

    public function deleteEquipment(Request $request, Equipment $equipment): RedirectResponse
    {
        abort_unless($equipment->user_id === $request->user()->id, 403);
        $equipment->delete();

        return back()->with('status', 'Echipamentul a fost șters.');
    }

    public function storeBilling(Request $request): RedirectResponse
    {
        $request->user()->billingProfiles()->create($this->billingData($request));

        return back()->with('status', 'Profilul de facturare a fost adăugat.');
    }

    public function updateBilling(Request $request, BillingProfile $billingProfile): RedirectResponse
    {
        abort_unless($billingProfile->user_id === $request->user()->id, 403);
        $billingProfile->update($this->billingData($request));

        return back()->with('status', 'Profilul de facturare a fost salvat.');
    }

    public function deleteBilling(Request $request, BillingProfile $billingProfile): RedirectResponse
    {
        abort_unless($billingProfile->user_id === $request->user()->id, 403);

        try {
            $billingProfile->delete();
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Profilul de facturare a fost șters.');
    }

    /**
     * @return array<string, mixed>
     */
    private function addressData(Request $request): array
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'county' => ['required', Rule::in(array_keys(Counties::options()))],
            'city' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'string', 'max:50'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'details' => ['nullable', 'string', 'max:255'],
        ]);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function equipmentData(Request $request): array
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'address_id' => ['required', 'integer'],
            'interval_months' => ['required', Rule::in([12, 24])],
            'last_revision_on' => ['nullable', 'date'],
            'notify_email' => ['nullable', 'boolean'],
            'notify_sms' => ['nullable', 'boolean'],
        ]);

        if (! $user->addresses()->whereKey($data['address_id'])->exists()) {
            throw ValidationException::withMessages([
                'address_id' => 'Alege una dintre adresele tale.',
            ]);
        }

        return [
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'address_id' => $data['address_id'],
            'interval_months' => $data['interval_months'],
            'last_revision_on' => $data['last_revision_on'] ?? null,
            'notify_email' => $request->boolean('notify_email') && $user->hasVerifiedEmail(),
            'notify_sms' => $request->boolean('notify_sms') && filled($user->phone),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function billingData(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['individual', 'company'])],
            'copy_address_id' => ['nullable', 'integer'],
            'first_name' => ['required_if:type,individual', 'nullable', 'string', 'max:255'],
            'last_name' => ['required_if:type,individual', 'nullable', 'string', 'max:255'],
            'company_name' => ['required_if:type,company', 'nullable', 'string', 'max:255'],
            'cui' => ['required_if:type,company', 'nullable', 'string', 'max:20'],
            'registration_number' => ['required_if:type,company', 'nullable', 'string', 'max:50'],
            'is_vat_payer' => ['nullable', 'boolean'],
            'county' => ['required_without:copy_address_id', 'nullable', Rule::in(array_keys(Counties::options()))],
            'city' => ['required_without:copy_address_id', 'nullable', 'string', 'max:255'],
            'street' => ['required_without:copy_address_id', 'nullable', 'string', 'max:255'],
            'street_number' => ['required_without:copy_address_id', 'nullable', 'string', 'max:50'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'details' => ['nullable', 'string', 'max:255'],
            'bank' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:34'],
        ]);

        if (filled($data['copy_address_id'] ?? null)) {
            $source = $request->user()->addresses()->whereKey($data['copy_address_id'])->first();

            if (! $source) {
                throw ValidationException::withMessages([
                    'copy_address_id' => 'Alege una dintre adresele tale.',
                ]);
            }

            $data['county'] = $source->county;
            $data['city'] = $source->city;
            $data['street'] = $source->street;
            $data['street_number'] = $source->street_number;
            $data['postal_code'] = $source->postal_code;
            $data['details'] = $source->details;
        }

        unset($data['copy_address_id']);
        $data['is_vat_payer'] = $request->boolean('is_vat_payer');

        if ($data['type'] === 'individual') {
            $data['company_name'] = null;
            $data['cui'] = null;
            $data['registration_number'] = null;
            $data['is_vat_payer'] = false;
            $data['bank'] = null;
            $data['iban'] = null;
        } else {
            $data['first_name'] = null;
            $data['last_name'] = null;
        }

        return $data;
    }
}
