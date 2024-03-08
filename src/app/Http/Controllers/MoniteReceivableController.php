<?php

    namespace App\Http\Controllers\Monite;

    use App\Models\Address;
    use App\Models\Business;
    use App\Models\BusinessBankAccount;
    use App\Models\BusinessPaymentTerm;
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


    class MoniteReceivableController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests, RedirectsActions;


        public function create($jwt, Receivable $receivable, $entity_id)
        {

            $lineItems = new Collection();
            $isQuote = $receivable->type === 'quote';

            foreach ($receivable->lineItems as $line_item) {
                $lineItems->add([
                    "quantity" => $line_item['quantity'],
                    "product_id" => ProductPrice::find($line_item['product_price_id'])->monite->external_id,
                    "vat_rate_id" => $line_item['vat_rate_id'],
                    /*"discount" => [
                        "type" => null,
                        "amount" => null,
                    ],*/
                ]);
            }


            if ($isQuote) {
                $body = [
                    //"entity_vat_id" => BusinessVatId::find($receivable->business_vat_id)->monite->external_id,
                    "type" => $receivable->type,
                    "currency" => $receivable->currency,
                    "expiry_date" => $receivable->expiry_date,
                    "quote_accept_page_url" => $receivable->quote->quote_accept_page_url,
                    "counterpart_id" => $receivable->counterpart->monite->external_id,
                    "commercial_condition_description" => $receivable->commercial_condition_description,
                    "vat_exempt" => $receivable->vat_exempt,
                    "vat_exemption_rationale" => $receivable->vat_exemption_rationale,
                    "withholding_tax_rate" => $receivable->withholding_tax_rate,
                    "memo" => $receivable->memo,
                    /*"discount" => [
                        "type" => $receivable->discount_type,
                        "amount" => $receivable->discount_amount,
                    ],*/
                    "line_items" => $lineItems
                ];
            } else {
                $body = [
                    //"entity_vat_id" => $receivable->business_vat_id ? BusinessVatId::find($receivable->business_vat_id)->monite->external_id : null,
                    "type" => $receivable->type,
                    "currency" => $receivable->currency,
                    "fulfillment_date" => $receivable->fulfillment_date,
                    "payment_page_url" => $receivable->payment_page_url,
                    "counterpart_id" => $receivable->counterpart->monite->external_id,
                    "commercial_condition_description" => $receivable->commercial_condition_description,
                    "entity_bank_account_id" => $receivable->invoice->business_bank_account_id ? BusinessBankAccount::find($receivable->invoice->business_bank_account_id)->monite->external_id : null,
                    "payment_terms_id" => $receivable->invoice->business_payment_term_id ? BusinessPaymentTerm::find($receivable->invoice->business_payment_term_id)->monite->external_id : null,
                    "vat_exempt" => $receivable->vat_exempt,
                    "vat_exemption_rationale" => $receivable->vat_exemption_rationale,
                    "withholding_tax_rate" => $receivable->withholding_tax_rate,
                    "memo" => $receivable->memo,
                    /*"discount" => [
                        "type" => $receivable->discount_type,
                        "amount" => $receivable->discount_amount,
                    ],*/
                    "line_items" => $lineItems
                ];
            }

            if ($receivable->discount_type && $receivable->discount_amount) {
                $body["discount"] = [
                    "type" => $receivable->discount_type,
                    "amount" => $receivable->discount_amount,
                ];
            }

            if ($receivable->counterpart_shipping_address_id) {
                $address = Address::find($receivable->counterpart_shipping_address_id);
                $body["counterpart_shipping_address"] = [
                    "country" => $address->country,
                    "city" => $address->city,
                    "postal_code" => $address->postal_code,
                    "state" => $address->state,
                    "line1" => $address->street_name . ' ' . $address->street_number,
                ];
            }

            if ($receivable->counterpart_billing_address_id) {
                $address = Address::find($receivable->counterpart_billing_address_id);
                $body["counterpart_billing_address"] = [
                    "country" => $address->country,
                    "city" => $address->city,
                    "postal_code" => $address->postal_code,
                    "state" => $address->state,
                    "line1" => $address->street_name . ' ' . $address->street_number,
                ];
            }

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->post(env('MONITE_URL') . '/v1/receivables');


            if ($response->status() == 201) {

                /*$receivable->quote->status = $response->json()['status'];
                $receivable->external_id = $response->json()['id'];
                $receivable->subtotal = $response->json()['subtotal'];
                $receivable->total_amount = $response->json()['total_amount'];
                $receivable->total_vat_amount = $response->json()['total_vat_amount'];*/

                $receivable->document_id = $response->json()['document_id'];

                if ($isQuote) {

                } else {
                    /*$receivable->payment_page_url = $response->json()['payment_page_url'];*/
                }

                $receivable->save();
                return $response->json()['id'];
            }

            return false;

        }

        public function get($jwt, Receivable $receivable, $entity_id)
        {
            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->get(env('MONITE_URL') . '/v1/receivables/' . $receivable->external_id);
            if ($response->status() == 200) {
                return $response->json();
            }
            return false;
        }

        public function update($jwt, Receivable $receivable, $entity_id)
        {

            $lineItems = new Collection();
            $isQuote = $receivable->type === 'quote';

            foreach ($receivable->lineItems as $line_item) {
                $lineItems->add([
                    "quantity" => $line_item['quantity'],
                    "product_id" => ProductPrice::find($line_item['product_price_id'])->monite->external_id,
                    "vat_rate_id" => $line_item['vat_rate_id'],
                    /*"discount" => [
                        "type" => null,
                        "amount" => null,
                    ],*/
                ]);
            }

            if ($isQuote) {
                $body = [
                    "quote" => [
                        "expiry_date" => $receivable->expiry_date,
                        "quote_accept_page_url" => $receivable->quote->quote_accept_page_url,
                        "counterpart_id" => $receivable->counterpart->monite->external_id,
                        //"commercial_condition_description" => $receivable->commercial_condition_description,
                        "vat_exempt" => $receivable->vat_exempt,
                        "vat_exemption_rationale" => $receivable->vat_exemption_rationale,
                        "withholding_tax_rate" => $receivable->withholding_tax_rate,
                        "memo" => $receivable->memo,
                        /*"discount" => [
                            "type" => $receivable->discount_type,
                            "amount" => $receivable->discount_amount,
                        ],*/
                        //"line_items" => $lineItems
                    ],

                ];
            } else {
                $body = [
                    "quote" => null,
                    "invoice" => [
                        "entity_vat_id_id" => $receivable->business_vat_id ? BusinessVatId::find($receivable->business_vat_id)->monite->external_id : null,
                        /*"currency" => $receivable->currency,*/
                        "fulfillment_date" => $receivable->fulfillment_date,
                        "payment_page_url" => $receivable->payment_page_url,
                        "counterpart_id" => $receivable->counterpart->monite->external_id,
                        "commercial_condition_description" => $receivable->commercial_condition_description,
                        "entity_bank_account_id" => $receivable->invoice->business_bank_account_id ? BusinessBankAccount::find($receivable->invoice->business_bank_account_id)->monite->external_id : null,
                        "payment_terms_id" => $receivable->invoice->business_payment_term_id ? BusinessPaymentTerm::find($receivable->invoice->business_payment_term_id)->monite->external_id : null,
                        "vat_exempt" => $receivable->vat_exempt,
                        "vat_exemption_rationale" => $receivable->vat_exemption_rationale,
                        "withholding_tax_rate" => $receivable->withholding_tax_rate,
                        "memo" => $receivable->memo,
                        /*"discount" => [
                            "type" => $receivable->discount_type,
                            "amount" => $receivable->discount_amount,
                        ],*/
                        //"line_items" => $lineItems
                    ],
                ];
            }

            if ($receivable->discount_type && $receivable->discount_amount) {
                $body[$receivable->type]["discount"] = [
                    "type" => $receivable->discount_type,
                    "amount" => $receivable->discount_amount,
                ];
            }

            if ($receivable->counterpart_shipping_address_id && !$isQuote) {
                $address = Address::find($receivable->counterpart_shipping_address_id);
                $body[$receivable->type]["counterpart_shipping_address"] = [
                    "country" => $address->country,
                    "city" => $address->city,
                    "postal_code" => $address->postal_code,
                    "state" => $address->state,
                    "line1" => $address->street_name . ' ' . $address->street_number,
                ];
            }

            if ($receivable->counterpart_billing_address_id && !$isQuote) {
                $address = Address::find($receivable->counterpart_billing_address_id);
                $body[$receivable->type]["counterpart_billing_address"] = [
                    "country" => $address->country,
                    "city" => $address->city,
                    "postal_code" => $address->postal_code,
                    "state" => $address->state,
                    "line1" => $address->street_name . ' ' . $address->street_number,
                ];
            }

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->patch(env('MONITE_URL') . '/v1/receivables/' . $receivable->monite->external_id);


            if ($response->status() == 200) {
                if($this->updateLineItems($jwt, $receivable, $entity_id)){
                    return true;
                }
            }

            return false;

        }

        public function updateLineItems($jwt, Receivable $receivable, $entity_id)
        {

            $lineItems = new Collection();
            $isQuote = $receivable->type === 'quote';

            foreach ($receivable->lineItems as $line_item) {
                $lineItems->add([
                    "quantity" => $line_item['quantity'],
                    "product_id" => ProductPrice::find($line_item['product_price_id'])->monite->external_id,
                    "vat_rate_id" => $line_item['vat_rate_id'],
                    /*"discount" => [
                        "type" => null,
                        "amount" => null,
                    ],*/
                ]);
            }


            $body = [
                "data" => $lineItems
            ];


            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->put(env('MONITE_URL') . '/v1/receivables/' . $receivable->monite->external_id . '/line_items');


            if ($response->status() == 200) {
                return true;
            }

            return false;

        }

        /**
         * Only for Quotes to Counterparts
         * @param $jwt
         * @param Receivable $receivable
         * @param $entity_id
         * @return bool
         */
        public function accept($jwt, Receivable $receivable, $entity_id)
        {
            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->post(env('MONITE_URL') . '/v1/receivables/' . $receivable->monite->external_id . '/accept');
            if ($response->status() == 200) {
                return true;
            }
            return false;
        }

        /**
         * Only for Quotes to Counterparts
         * @param $jwt
         * @param Receivable $receivable
         * @param $entity_id
         * @return bool
         */
        public function decline($jwt, Receivable $receivable, $entity_id, $comment)
        {
            $body = [
                "comment" => $comment,
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->post(env('MONITE_URL') . '/v1/receivables/' . $receivable->monite->external_id . '/decline');
            if ($response->status() == 200) {
                return true;
            }
            return false;
        }

        /**
         * @param $jwt
         * @param Receivable $receivable
         * @param $entity_id
         * @return bool
         */
        public function issue($jwt, Receivable $receivable, $entity_id)
        {

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->post(env('MONITE_URL') . '/v1/receivables/' . $receivable->monite->external_id . '/issue');
            if ($response->status() == 200) {
                return $response->json();
            }
            return false;
        }

        /**
         * @param $jwt
         * @param Receivable $receivable
         * @param $entity_id
         * @return bool
         */
        public function delete($jwt, Receivable $receivable, $entity_id)
        {

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->delete(env('MONITE_URL') . '/v1/receivables/' . $receivable->monite->external_id);
            if ($response->status() == 204) {
                return true;
            }
            return false;
        }

        /**
         * Only for Invoices
         * @param $jwt
         * @param Receivable $receivable
         * @param $entity_id
         * @return bool
         */
        public function cancel($jwt, Receivable $receivable, $entity_id)
        {

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->post(env('MONITE_URL') . '/v1/receivables/' . $receivable->monite->external_id . '/cancel');
            if ($response->status() == 204) {
                return true;
            }
            return false;
        }

        /**
         * @param $jwt
         * @param Receivable $receivable
         * @param $entity_id
         * @return bool
         */
        public function convert($jwt, Receivable $receivable, $entity_id, $type = 'invoice')
        {

            $body = [
                "based_on" => $receivable->monite->external_id,
                "type" => $type,
            ];

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->withBody(json_encode($body))->post(env('MONITE_URL') . '/v1/receivables');
            if ($response->status() == 201) {
                return $response->json();
            }
            return false;
        }

        /**
         * Only for Invoices
         * @param $jwt
         * @param Receivable $receivable
         * @param $entity_id
         * @return bool
         */
        public function pdfLink($jwt, Receivable $receivable, $entity_id)
        {

            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->get(env('MONITE_URL') . '/v1/receivables/' . $receivable->monite->external_id . '/pdf_link');
            if ($response->status() == 200) {
                return $response->json()['file_url'];
            }
            return false;
        }

        /**
         * Only for Quotes to Counterparts
         * @param $jwt
         * @param Receivable $receivable
         * @param $entity_id
         * @return bool
         */
        public function mark_as_paid($jwt, Receivable $receivable, $entity_id)
        {
            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->post(env('MONITE_URL') . '/v1/receivables/' . $receivable->monite->external_id . '/mark_as_paid');
            if ($response->status() == 200) {
                return true;
            }
            return false;
        }

        /**
         * Only for Quotes to Counterparts
         * @param $jwt
         * @param Receivable $receivable
         * @param $entity_id
         * @return bool
         */
        public function mark_as_uncollectible($jwt, Receivable $receivable, $entity_id)
        {
            $response = Http::withToken($jwt['access_token'])->withHeader("x-monite-version", "2023-06-04")->withHeader("x-monite-entity-id", $entity_id)->post(env('MONITE_URL') . '/v1/receivables/' . $receivable->monite->external_id . '/mark_as_uncollectible');
            if ($response->status() == 200) {
                return true;
            }
            return false;
        }
    }
