<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSignUpWithCorrectData()
    {
        $this->json('post', 'api/signUp', [
            'email'     => $this->faker->email,
            'password'  => '123456',
            'firstname' => 'José',
            'lastname'  => 'da Silva Sauro'
        ])->seeJson([
            'message'   => 'user_registered'
        ])->assertResponseStatus(200);
    }
}
