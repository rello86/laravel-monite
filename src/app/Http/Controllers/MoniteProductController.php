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
    use App\Models\ProductPrice;
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


    class MoniteProductController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RedirectsActions;


        public function create($jwt, Product $product, ProductPrice $productPrice, $entity_id)
        {


            $body = [
                "name" => $product->name,
                "type" => $product->type,
                "description" => $product->description,
                "price" => [
                    "value" => $productPrice->price,
                    "currency" => $productPrice->currency,
                ],
                "measure_unit_id" => $product->measureUnit->monite->external_id,
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->post(env('MONITE_URL').'/v1/products');

            if ($response->status() == 201) {
                return $response->json()['id'];
            }

            return false;

        }

        public function update($jwt, Product $product, ProductPrice $productPrice, $entity_id)
        {


            $body = [
                "name" => $product->name,
                "type" => $product->type,
                "description" => $product->description,
                "price" => [
                    "value" => $productPrice->price,
                    "currency" => $productPrice->currency,
                ],
                "measure_unit_id" => $product->measureUnit->monite->external_id,
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->patch(env('MONITE_URL').'/v1/products/'.$productPrice->monite->external_id);

            if ($response->status() == 200) {
                return true;
            }

            return false;

        }


        public function delete($jwt, ProductPrice $productPrice, $entity_id)
        {

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->delete(env('MONITE_URL').'/v1/products/'.$productPrice->monite->external_id);

            if ($response->status() == 204) {

                return true;
            }

            return false;

        }

    }
