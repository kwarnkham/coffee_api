<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemTest extends TestCase
{

    public function test_create_an_item(): void
    {
        $itemData = Item::factory()->make()->toArray();
        $this->actingAs($this->admin)->postJson(route('items.store'), $itemData)->assertCreated();
        $this->assertDatabaseCount('items', 1);
        $this->assertDatabaseHas('items', $itemData);
    }
}
