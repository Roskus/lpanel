<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterWebsiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('website', function (Blueprint $table) {
            $table->addColumn('boolean', 'http')->default(true);
            $table->addColumn('boolean', 'https')->default(false);
            $table->addColumn('boolean', 'http2https')->default(false);
            $table->addColumn('boolean', 'lets_encrypt')->default(false);
            $table->addColumn('json', 'alias')->nullable()->default(null);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('website', function (Blueprint $table) {
            $table->removeColumn('http');
            $table->removeColumn('https');
            $table->removeColumn('http2https');
            $table->removeColumn('lets_encrypt');
            $table->dropSoftDeletes();
        });
    }
}
