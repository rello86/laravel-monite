<?php

    namespace App\Http\Controllers\Monite;

    use App\Models\Address;
    use App\Models\Business;
    use App\Models\BusinessServiceProvider;
    use App\Models\Counterpart;
    use App\Models\MeasureUnit;
    use App\Models\MoniteBusiness;
    use App\Models\MoniteCounterpart;
    use App\Models\Product;
    use App\Models\Receivable;
    use App\Models\ServiceProvider;
    use App\Models\ServiceProviderBusiness;
    use App\Models\BusinessVatId;
    use Exception;
    use formance\stack\Models\Operations\AddScopeToClientRequest;
    use formance\stack\SDK;
    use GuzzleHttp\Client;
    use GuzzleHttp\Psr7\Request;
    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    use Illuminate\Foundation\Bus\DispatchesJobs;
    use Illuminate\Foundation\Validation\ValidatesRequests;
    use Illuminate\Routing\Controller as BaseController;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Http;
    use Inertia\Inertia;
    use Laravel\Jetstream\RedirectsActions;


    class MoniteCounterpartController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RedirectsActions;

        public function create($jwt, Counterpart $counterpart, $entity_id)
        {


            $body = [
                "organization" => [
                    "legal_name" => $counterpart->business->legal_name,
                    "is_vendor" => $counterpart->is_vendor,
                    "is_customer" => $counterpart->is_customer,
                    "phone" => $counterpart->phone_prefix . $counterpart->phone_number,
                    "email" => $counterpart->email,
                    "registered_address" => [
                        "country" => $counterpart->address->country,
                        "city" => $counterpart->address->city,
                        "postal_code" => $counterpart->address->postal_code,
                        "state" => $counterpart->address->state,
                        "line1" => $counterpart->address->street_name . " " . $counterpart->address->street_number
                    ]
                ],
                "reminders_enabled" => $counterpart->reminders_enabled,
                "tax_id" => $counterpart->tax_id,
                "type" => "organization",
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->post(env('MONITE_URL') . '/v1/counterparts');

            if ($response->status() == 201) {

                return $response->json()['id'];
            }

            return false;

        }

        public function update($jwt, Counterpart $counterpart, $entity_id)
        {


            $body = [
                "organization" => [
                    "legal_name" => $counterpart->business->legal_name,
                    "is_vendor" => $counterpart->is_vendor,
                    "is_customer" => $counterpart->is_customer,
                    "phone" => $counterpart->phone_prefix . $counterpart->phone_number,
                    "email" => $counterpart->email,
                    /*"registered_address" => [
                        "country" => $counterpart->address->country,
                        "city" => $counterpart->address->city,
                        "postal_code" => $counterpart->address->postal_code,
                        "state" => $counterpart->address->state,
                        "line1" => $counterpart->address->street_name . " " . $counterpart->address->street_number
                    ]*/
                ],
                "reminders_enabled" => $counterpart->reminders_enabled,
                "tax_id" => $counterpart->tax_id,
                "type" => "organization",
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->patch(env('MONITE_URL') . '/v1/counterparts/' . $counterpart->monite->external_id);

            if ($response->status() == 200) {
                return true;
            }

            return false;

        }

        public function delete($jwt, Counterpart $counterpart, $entity_id)
        {


            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->delete(env('MONITE_URL') . '/v1/counterparts/' . $counterpart->monite->external_id);

            if ($response->status() == 204) {

                return true;
            }

            return false;

        }

        public function getAddresses($jwt, Counterpart $counterpart, $entity_id)
        {

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->get(env('MONITE_URL') . '/v1/counterparts/' . $counterpart->monite->external_id . '/addresses');

            if ($response->status() == 200) {

                return $response->json();
            }

            return false;

        }

        public function createAddress($jwt, Address $address, $entity_id)
        {

            $body = [
                "country" => $address->country,
                "city" => $address->city,
                "postal_code" => $address->postal_code,
                "state" => $address->state,
                "line1" => $address->street_name . " " . $address->street_number
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->post(env('MONITE_URL') . '/v1/counterparts/' . $address->addressable->monite->external_id . '/addresses');

            if ($response->status() == 201) {

                return $response->json()['id'];
            }

            return false;

        }

        public function updateAddress($jwt, Address $address, $entity_id)
        {

            $body = [
                "country" => $address->country,
                "city" => $address->city,
                "postal_code" => $address->postal_code,
                "state" => $address->state,
                "line1" => $address->street_name . " " . $address->street_number
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->patch(env('MONITE_URL') . '/v1/counterparts/' . $address->addressable->monite->external_id . '/addresses/' . $address->monite->external_id);

            if ($response->status() == 200) {
                return true;
            }

            return false;

        }

    }
