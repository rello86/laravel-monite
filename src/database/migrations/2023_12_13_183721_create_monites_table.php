<?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up()
        {
            Schema::create('monites', function (Blueprint $table) {
                $table->uuid('id');
                $table->string('external_id')->index();
                $table->uuidMorphs('moniteable');
                $table->timestampsTz(6);
                $table->softDeletes();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down()
        {
            Schema::dropIfExists('monites');
        }
    };
