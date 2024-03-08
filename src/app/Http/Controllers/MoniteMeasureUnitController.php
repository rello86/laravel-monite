<?php

    namespace App\Http\Controllers\Monite;

    use App\Models\MeasureUnit;
    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    use Illuminate\Foundation\Bus\DispatchesJobs;
    use Illuminate\Foundation\Validation\ValidatesRequests;
    use Illuminate\Routing\Controller as BaseController;
    use Illuminate\Support\Facades\Http;
    use Laravel\Jetstream\RedirectsActions;


    class MoniteMeasureUnitController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RedirectsActions;


        public function create($jwt, MeasureUnit $measureUnit, $entity_id)
        {


            $body = [
                "name" => $measureUnit->name,
                "description" => $measureUnit->description,
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->post(env('MONITE_URL').'/v1/measure_units');

            if ($response->status() == 201) {
                return $response->json()['id'];
            }

            return false;

        }

        public function update($jwt, MeasureUnit $measureUnit, $entity_id)
        {

            $body = [
                "name" => $measureUnit->name,
                "description" => $measureUnit->description,
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->patch(env('MONITE_URL').'/v1/measure_units/'.$measureUnit->monite->external_id);

            if ($response->status() == 200) {
                return true;
            }

            return false;

        }

        public function delete($jwt, MeasureUnit $measureUnit, $entity_id)
        {


            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->delete(env('MONITE_URL').'/v1/measure_units/'.$measureUnit->external_id);

            if ($response->status() == 204) {
                return true;
            }

            return false;

        }


    }
