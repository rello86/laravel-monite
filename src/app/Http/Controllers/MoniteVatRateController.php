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
    use PrinsFrank\Standards\Country\CountryAlpha2;
    use PrinsFrank\Standards\Country\Groups\EU;


    class MoniteVatRateController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RedirectsActions;


        /*public function get($jwt, Business $business)
        {

            $response = Http::withToken($jwt['access_token'])
                ->withHeader("x-monite-version", "2023-06-04")
                ->withHeader("x-monite-entity-id", $business->monite->external_id)
                ->withQueryParameters(
                    [
                        'entity_vat_id_id' => $business->vatIds()->first()->monite->external_id,
                        //'product_type' => $product->type
                    ]
                )
                ->get(env('MONITE_URL').'/v1/vat_rates');

            if ($response->status() == 200) {
                return $response->json();
            }

            return false;

        }*/

        public function getVatRates($jwt, Business $business, $businessVatId)
        {


            $response = Http::withToken($jwt['access_token'])
                ->withHeader("x-monite-version", "2023-06-04")
                ->withHeader("x-monite-entity-id", $business->monite->external_id)
                ->withQueryParameters(
                    [
                        'entity_vat_id_id' => $businessVatId,
                        //'product_type' => $product->type
                    ]
                )
                ->get(env('MONITE_URL').'/v1/vat_rates');

            if ($response->status() == 200) {
                return $response->json();
            }

            return false;

        }

        public function getCounterpart($jwt, Counterpart $counterpart, $entityId)
        {


            $response = Http::withToken($jwt['access_token'])
                ->withHeader("x-monite-version", "2023-06-04")
                ->withHeader("x-monite-entity-id", $entityId)
                ->withQueryParameters(
                    [
                        'counterpart_id' => $counterpart->monite->external_id,
                        //'product_type' => $product->type
                    ]
                )
                ->get(env('MONITE_URL').'/v1/vat_rates');

            if ($response->status() == 200) {
                return $response->json();
            }

            return false;

        }


    }
