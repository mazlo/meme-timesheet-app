<?php

use Illuminate\Database\Migrations\Migration;

class AddRememberTokenToUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table( 'Users', function( $table )
		{
			$table->string( 'remember_token' )->length( '100' )->after( 'email' );
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table( 'Users', function( $table )
		{
			$table->dropColumn( 'remember_token' );
		});
	}

}