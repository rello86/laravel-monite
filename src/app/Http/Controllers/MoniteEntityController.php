<?php

    namespace App\Http\Controllers\Monite;

    use App\Models\Business;
    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    use Illuminate\Foundation\Bus\DispatchesJobs;
    use Illuminate\Foundation\Validation\ValidatesRequests;
    use Illuminate\Routing\Controller as BaseController;
    use Illuminate\Support\Facades\Http;
    use Laravel\Jetstream\RedirectsActions;
    use PrinsFrank\Standards\Country\CountryAlpha2;
    use PrinsFrank\Standards\Country\Groups\EU;


    class MoniteEntityController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RedirectsActions;


        public function create($jwt, Business $business)
        {
            $setBusinessStructure = CountryAlpha2::from($business->address->country)->isMemberOf(EU::class) || $business->address->country == 'US';
            $body = [
                "organization" => [
                    "legal_name" => $business->legal_name,
                    "legal_entity_id" => $business->legal_entity_id,
                    "business_structure" => $setBusinessStructure ? $business->business_structure : null,
                ],
                "tax_id" => $business->tax_id,
                "email" => $business->email,
                "type" => "organization",
                "address" => [
                    "country" => $business->address->country,
                    "state" => $business->address->state,
                    "city" => $business->address->city,
                    "postal_code" => $business->address->postal_code,
                    "line1" => $business->address->street_name . ', ' . $business->address->street_number
                ]
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withBody(json_encode($body))->post(env('MONITE_URL').'/v1/entities');


            if ($response->status() == 200) {
                return $response->json()['id'];
            }
            return false;

        }

        public function get($jwt, $entityId)
        {


            $response = Http::withToken($jwt)->withQueryParameters(['entity_id' => $entityId])->get(env('MONITE_URL').'/v1/entities/');

            if ($response->status() == 200) {
                return $response->json();
            }

            return false;

        }

        public function update($jwt, Business $business)
        {
            $setBusinessStructure = CountryAlpha2::from($business->address->country)->isMemberOf(EU::class) || $business->address->country == 'US';
            $body = [
                "organization" => [
                    "legal_name" => $business->legal_name,
                    "legal_entity_id" => $business->legal_entity_id,
                    "business_structure" => $setBusinessStructure ? $business->business_structure : null,
                ],
                "tax_id" => $business->tax_id,
                "email" => $business->email,
                "address" => [
                    "country" => $business->address->country,
                    "state" => $business->address->state,
                    "city" => $business->address->city,
                    "postal_code" => $business->address->postal_code,
                    "line1" => $business->address->street_name . ', ' . $business->address->street_number
                ]
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withBody(json_encode($body))->patch(env('MONITE_URL').'/v1/entities/'.$business->monite->external_id);


            if ($response->status() == 200) {
                return true;
            }
            return false;

        }



    }
