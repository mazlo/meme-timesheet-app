<?php

use Illuminate\Database\Migrations\Migration;

class AddColorToColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table( 'columns', function( $table )
		{
			$table->string( 'color' )->length( '7' )->nullable()->after( 'position' );
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table( 'columns', function( $table )
		{
			$table->dropColumn( 'color' );
		});
	}

}