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


    class MoniteEntityVatIdController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RedirectsActions;


        public function create($jwt, BusinessVatId $vatId, $entity_id)
        {


            $body = [
                "country" => $vatId->country,
                "type" => $vatId->type,
                "value" => $vatId->value,
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withBody(json_encode($body))->post(env('MONITE_URL').'/v1/entities/'.$entity_id.'/vat_ids');

            if ($response->status() == 201) {

                return $response->json()['id'];
            }

            return false;

        }

        public function update($jwt, BusinessVatId $vatId, $entity_id)
        {

            $body = [
                "country" => $vatId->country,
                "type" => $vatId->type,
                "value" => $vatId->value,
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withBody(json_encode($body))->patch(env('MONITE_URL').'/v1/entities/'.$entity_id.'/vat_ids/'.$vatId->monite->external_id);

            if ($response->status() == 200) {

                return true;
            }

            return false;

        }

        public function delete($jwt, BusinessVatId $vatId, $entity_id)
        {


            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->delete(env('MONITE_URL').'/v1/entities/'.$entity_id.'/vat_ids/'.$vatId->monite->external_id);

            if ($response->status() == 204) {

                return true;
            }

            return false;

        }


    }
