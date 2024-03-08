<?php

    namespace App\Http\Controllers\Monite;

    use App\Models\Address;
    use App\Models\Business;
    use App\Models\BusinessBankAccount;
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


    class MoniteEntityBankAccountController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RedirectsActions;


        public function create($jwt, BusinessBankAccount $bankAccount, $entity_id)
        {


            $body = [
                "iban" => $bankAccount->iban,
                "bic" => $bankAccount->bic,
                "bank_name" => $bankAccount->bank_name,
                "display_name" => $bankAccount->display_name,
                "is_default" => $bankAccount->is_default,
                "account_holder_name" => $bankAccount->account_holder_name,
                "account_number" => $bankAccount->account_number,
                "routing_number" => $bankAccount->routing_number,
                "sort_code" => $bankAccount->sort_code,
                "currency" => $bankAccount->currency,
                "country" => $bankAccount->country,
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->post(env('MONITE_URL').'/v1/bank_accounts');

            if ($response->status() == 201) {

                return $response->json()['id'];
            }

            return false;

        }

        public function update($jwt, BusinessBankAccount $bankAccount, $entity_id)
        {

            $body = [
                "iban" => $bankAccount->iban,
                "bic" => $bankAccount->bic,
                "bank_name" => $bankAccount->bank_name,
                "display_name" => $bankAccount->display_name,
                //"is_default" => $bankAccount->is_default,
                "account_holder_name" => $bankAccount->account_holder_name,
                "account_number" => $bankAccount->account_number,
                "routing_number" => $bankAccount->routing_number,
                "sort_code" => $bankAccount->sort_code,
                "currency" => $bankAccount->currency,
                "country" => $bankAccount->country,
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->patch(env('MONITE_URL').'/v1/bank_accounts/'.$bankAccount->monite->external_id);

            if ($response->status() == 200) {

                return true;
            }

            return false;

        }

        public function delete($jwt, BusinessVatId $vatId, $entity_id)
        {


            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->delete(env('MONITE_URL').'/v1/entities/'.$entity_id.'/vat_ids/'.$vatId->external_id);

            if ($response->status() == 204) {

                return true;
            }

            return false;

        }


    }
