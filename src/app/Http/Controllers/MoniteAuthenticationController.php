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


    class MoniteAuthenticationController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RedirectsActions;


        public function auth()
        {
//"client_id": "fa55ba43-4ff0-444d-bf70-8b37f479b757",
//"client_secret": "0abccf5e-8567-4295-aee1-586299baf8f8"
            $response = Http::withBody('{
    "grant_type": "client_credentials",
    "client_id": "fa55ba43-4ff0-444d-bf70-8b37f479b757",
    "client_secret": "cccb74e1-e5c9-4e88-a974-360ba512cab3"
}')->withHeader("x-monite-version", "2023-06-04")->post('https://api.sandbox.monite.com/v1/auth/token');

            if ($response->status() == 200) {
                return $response->json();
            }

            return false;

        }

    }
