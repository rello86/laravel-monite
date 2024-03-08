<?php

    namespace LaravelMonite\app\Traits;

    use App\Models\Monite;
    use Illuminate\Database\Eloquent\Relations\MorphOne;

    trait HasMonite
    {

        /**
         * Get all of the business's addresses.
         */
        public function monite(): MorphOne
        {
            return $this->morphOne(Monite::class, 'moniteable');
        }

    }
