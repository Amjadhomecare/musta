<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


class SuperAdminTest extends TestCase
{  
  public function test_add_new_user()
    {      
        $data = [
            'name' => 'amjad',
            'group' => 'accounting',
            'password' => '12345678',
        ];

        $response = $this->postJson('cle/testing', $data);

    
        $response->assertStatus(200);

 
        $response->assertJson(['message' => 'success']);


        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'group' => 'Test Group',
            // Use a closure to check if the password is hashed correctly
            'password' => function ($password) {
                return Hash::check('password', $password);
            },
        ]);
    }

}
