<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NodeFeatureTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_user_cant_see_node_list_page() {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/nodes');
        $response->assertRedirect();
    }

    public function test_guest_cant_see_node_list_page() {
        $response = $this->get('/nodes');
        $response->assertRedirect();
    }

    public function test_admin_can_see_nodes_list_page() {
        /** @var User */
        $user = User::factory()->create(['admin' => true]);
        $this->actingAs($user);
        $response = $this->get('/nodes');
        $response->assertStatus(200);
    }

    public function test_admin_can_add_new_node() {
        /** @var User */
        $user = User::factory()->create(['admin' => true]);
        $this->actingAs($user);
        $response = $this->post('/nodes', ['title' => 'NodeTitle']);
        $this->assertDatabaseHas('nodes', ['title' => 'NodeTitle']);
        $response->assertSessionHas('success');
    }

    public function test_user_cant_add_new_node() {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post('/nodes', ['title' => 'NodeTitle']);
        $this->assertDatabaseMissing('nodes', ['title' => 'NodeTitle']);
        $response->assertSessionMissing('success');
    }

    public function test_guest_cant_add_new_node() {
        $response = $this->post('/nodes', ['title' => 'NodeTitle']);
        $this->assertDatabaseMissing('nodes', ['title' => 'NodeTitle']);
        $response->assertSessionMissing('success');
    }

    public function test_admin_can_update_node() {
        /** @var User */
        $user = User::factory()->create(['admin' => true]);
        $this->actingAs($user);

        $node = Node::create(['title' => 'test']);
        $this->assertDatabaseHas('nodes', ['title' => 'test']);

        $response = $this->put("/nodes/$node->id", ['title' => 'changed']);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('nodes', ['title' => 'changed']);
        $this->assertDatabaseMissing('nodes', ['title' => 'test']);
    }

    public function test_user_cant_update_node() {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $node = Node::create(['title' => 'test']);
        $this->assertDatabaseHas('nodes', ['title' => 'test']);

        $response = $this->put("/nodes/$node->id", ['title' => 'changed']);
        $response->assertRedirect();

        $this->assertDatabaseMissing('nodes', ['title' => 'changed']);
        $this->assertDatabaseHas('nodes', ['title' => 'test']);
    }

    public function test_guest_cant_update_node() {
        $node = Node::create(['title' => 'test']);
        $this->assertDatabaseHas('nodes', ['title' => 'test']);

        $response = $this->put("/nodes/$node->id", ['title' => 'changed']);
        $response->assertRedirect();

        $this->assertDatabaseMissing('nodes', ['title' => 'changed']);
        $this->assertDatabaseHas('nodes', ['title' => 'test']);
    }

    public function test_admin_can_move_nodes_to_other_branches() {
        /** @var User */
        $user = User::factory()->create(['admin' => true]);
        $this->actingAs($user);

        $parentNode = Node::create(['title' => 'parent']);
        $childNode = Node::create(['title' => 'child', 'node_id' => $parentNode->id]);
        $childOfChildNode = Node::create(['title' => 'childofchild',  'node_id' => $childNode->id]);
        $this->assertDatabaseHas('nodes', ['title' => 'parent']);
        $this->assertDatabaseHas('nodes', ['title' => 'child']);
        $this->assertDatabaseHas('nodes', ['title' => 'childofchild']);

        $response = $this->put("/nodes/$childNode->id", ['title' => $childNode->title, 'node_id' => null]);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('nodes', ['path' => $childNode->id]);
        $this->assertDatabaseHas('nodes', ['path' => "$childNode->id.$childOfChildNode->id"]);
    }

    public function test_user_cant_move_nodes_to_other_branches() {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $parentNode = Node::create(['title' => 'parent']);
        $childNode = Node::create(['title' => 'child', 'node_id' => $parentNode->id]);
        $childOfChildNode = Node::create(['title' => 'childofchild',  'node_id' => $childNode->id]);
        $this->assertDatabaseHas('nodes', ['title' => 'parent']);
        $this->assertDatabaseHas('nodes', ['title' => 'child']);
        $this->assertDatabaseHas('nodes', ['title' => 'childofchild']);

        $response = $this->put("/nodes/$childNode->id", ['title' => $childNode->title, 'node_id' => null]);
        $response->assertRedirect();

        $this->assertDatabaseMissing('nodes', ['path' => $childNode->id]);
        $this->assertDatabaseMissing('nodes', ['path' => "$childNode->id.$childOfChildNode->id"]);
    }

    public function test_guest_cant_move_nodes_to_other_branches() {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $parentNode = Node::create(['title' => 'parent']);
        $childNode = Node::create(['title' => 'child', 'node_id' => $parentNode->id]);
        $childOfChildNode = Node::create(['title' => 'childofchild',  'node_id' => $childNode->id]);
        $this->assertDatabaseHas('nodes', ['title' => 'parent']);
        $this->assertDatabaseHas('nodes', ['title' => 'child']);
        $this->assertDatabaseHas('nodes', ['title' => 'childofchild']);

        $response = $this->put("/nodes/$childNode->id", ['title' => $childNode->title, 'node_id' => null]);
        $response->assertRedirect();

        $this->assertDatabaseMissing('nodes', ['path' => $childNode->id]);
        $this->assertDatabaseMissing('nodes', ['path' => "$childNode->id.$childOfChildNode->id"]);
    }

    public function test_admin_can_delete_node_with_descendants() {
        /** @var User */
        $user = User::factory()->create(['admin' => true]);
        $this->actingAs($user);

        $parentNode = Node::create(['title' => 'parent']);
        $childNode = Node::create(['title' => 'child', 'node_id' => $parentNode->id]);
        $childOfChildNode = Node::create(['title' => 'childofchild',  'node_id' => $childNode->id]);
        $this->assertDatabaseHas('nodes', ['title' => 'parent']);
        $this->assertDatabaseHas('nodes', ['title' => 'child']);
        $this->assertDatabaseHas('nodes', ['title' => 'childofchild']);

        $response = $this->delete("/nodes/$childNode->id");
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('nodes', ['id' => $childNode->id]);
        $this->assertDatabaseMissing('nodes', ['id' => $childOfChildNode->id]);
    }
    public function test_user_cant_delete_node_with_descendants() {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $parentNode = Node::create(['title' => 'parent']);
        $childNode = Node::create(['title' => 'child', 'node_id' => $parentNode->id]);
        $childOfChildNode = Node::create(['title' => 'childofchild',  'node_id' => $childNode->id]);
        $this->assertDatabaseHas('nodes', ['title' => 'parent']);
        $this->assertDatabaseHas('nodes', ['title' => 'child']);
        $this->assertDatabaseHas('nodes', ['title' => 'childofchild']);

        $response = $this->delete("/nodes/$childNode->id");
        $response->assertRedirect();

        $this->assertDatabaseHas('nodes', ['id' => $childNode->id]);
        $this->assertDatabaseHas('nodes', ['id' => $childOfChildNode->id]);
    }
    public function test_guest_cant_delete_node_with_descendants() {
        $parentNode = Node::create(['title' => 'parent']);
        $childNode = Node::create(['title' => 'child', 'node_id' => $parentNode->id]);
        $childOfChildNode = Node::create(['title' => 'childofchild',  'node_id' => $childNode->id]);
        $this->assertDatabaseHas('nodes', ['title' => 'parent']);
        $this->assertDatabaseHas('nodes', ['title' => 'child']);
        $this->assertDatabaseHas('nodes', ['title' => 'childofchild']);

        $response = $this->delete("/nodes/$childNode->id");
        $response->assertRedirect();

        $this->assertDatabaseHas('nodes', ['id' => $childNode->id]);
        $this->assertDatabaseHas('nodes', ['id' => $childOfChildNode->id]);
    }

    public function test_admin_can_delete_node_without_descendants() {
        /** @var User */
        $user = User::factory()->create(['admin' => true]);
        $this->actingAs($user);

        $node = Node::create(['title' => 'parent']);
        $this->assertDatabaseHas('nodes', ['title' => 'parent']);

        $response = $this->delete("/nodes/$node->id");
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('nodes', ['id' => $node->id]);
    }

    public function test_user_cant_delete_node_without_descendants() {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $node = Node::create(['title' => 'parent']);
        $this->assertDatabaseHas('nodes', ['title' => 'parent']);

        $response = $this->delete("/nodes/$node->id");
        $response->assertRedirect();

        $this->assertDatabaseHas('nodes', ['id' => $node->id]);
    }

    public function test_guest_cant_delete_node_without_descendants() {
        $node = Node::create(['title' => 'parent']);
        $this->assertDatabaseHas('nodes', ['title' => 'parent']);

        $response = $this->delete("/nodes/$node->id");
        $response->assertRedirect();

        $this->assertDatabaseHas('nodes', ['id' => $node->id]);
    }

    public function test_admin_cant_set_node_title_to_null() {
        /** @var User */
        $user = User::factory()->create(['admin' => true]);
        $this->actingAs($user);

        $node = Node::create(['title' => 'test']);
        $this->assertDatabaseHas('nodes', ['title' => 'test']);

        $response = $this->put("/nodes/$node->id", ['title' => null]);
        $response->assertSessionHasErrors();

        $this->assertDatabaseMissing('nodes', ['title' => 'changed']);
        $this->assertDatabaseHas('nodes', ['title' => 'test']);
    }

    public function test_admin_cant_set_node_id_to_that_doesnt_exists_in_database() {
         /** @var User */
         $user = User::factory()->create(['admin' => true]);
         $this->actingAs($user);

         $node = Node::create(['title' => 'test']);
         $this->assertDatabaseHas('nodes', ['title' => 'test']);

         $response = $this->put("/nodes/$node->id", ['title' => $node->title, 'node_id' => 999]);
         $response->assertSessionHasErrors();

         $this->assertDatabaseMissing('nodes', ['node_id' => 999]);
         $this->assertDatabaseHas('nodes', ['node_id' => null]);
    }

    public function test_admin_cant_add_node_without_title() {
        /** @var User */
        $user = User::factory()->create(['admin' => true]);
        $this->actingAs($user);

        $response = $this->post('/nodes', ['title' => null]);
        $response->assertSessionHasErrors();
    }

    public function test_admin_cant_add_node_with_node_id_that_doesnt_exists() {
        /** @var User */
        $user = User::factory()->create(['admin' => true]);
        $this->actingAs($user);

        $response = $this->post('/nodes', ['title' => 'test', 'node_id' => 999]);
        $response->assertSessionHasErrors();
    }


}
