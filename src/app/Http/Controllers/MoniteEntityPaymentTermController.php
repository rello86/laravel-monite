<?php

    namespace App\Http\Controllers\Monite;

    use App\Models\BusinessPaymentTerm;
    use App\Models\MeasureUnit;
    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    use Illuminate\Foundation\Bus\DispatchesJobs;
    use Illuminate\Foundation\Validation\ValidatesRequests;
    use Illuminate\Routing\Controller as BaseController;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Http;
    use Laravel\Jetstream\RedirectsActions;


    class MoniteEntityPaymentTermController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RedirectsActions;


        public function create($jwt, BusinessPaymentTerm $businessPaymentTerm, $entity_id)
        {


            $body = collect([
                "name" => $businessPaymentTerm->name,
                "description" => $businessPaymentTerm->description,
                "term_final" => [
                    "number_of_days" => $businessPaymentTerm->term_final_days,
                ],
            ]);


            if ($businessPaymentTerm->term_first_days) {
                $body->concat(["term_1" => [
                    "number_of_days" => $businessPaymentTerm->term_first_days,
                    "discount" => $businessPaymentTerm->term_first_discount,
                ]]);
            }
            if ($businessPaymentTerm->term_second_days) {
                $body->concat(["term_2" => [
                    "number_of_days" => $businessPaymentTerm->term_second_days,
                    "discount" => $businessPaymentTerm->term_second_discount,
                ]]);
            }

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->post(env('MONITE_URL') . '/v1/payment_terms');

            if ($response->status() == 201) {
                return $response->json()['id'];
            }

            return false;

        }

        public function update($jwt, BusinessPaymentTerm $businessPaymentTerm, $entity_id)
        {

            $body = collect([
                "name" => $businessPaymentTerm->name,
                "description" => $businessPaymentTerm->description,
                "term_final" => [
                    "number_of_days" => $businessPaymentTerm->term_final_days,
                ],
            ]);


            if ($businessPaymentTerm->term_first_days) {
                $body->concat(["term_1" => [
                    "number_of_days" => $businessPaymentTerm->term_first_days,
                    "discount" => $businessPaymentTerm->term_first_discount,
                ]]);
            }
            if ($businessPaymentTerm->term_second_days) {
                $body->concat(["term_2" => [
                    "number_of_days" => $businessPaymentTerm->term_second_days,
                    "discount" => $businessPaymentTerm->term_second_discount,
                ]]);
            }

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->patch(env('MONITE_URL') . '/v1/payment_terms/' . $businessPaymentTerm->monite->external_id);

            if ($response->status() == 200) {
                return true;
            }

            return false;

        }

        public function delete($jwt, BusinessPaymentTerm $businessPaymentTerm, $entity_id)
        {


            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->delete(env('MONITE_URL') . '/v1/payment_terms/' . $businessPaymentTerm->monite->external_id);

            if ($response->status() == 204) {
                return true;
            }

            return false;

        }


    }
