<?php

namespace Tests\Feature;

use App\Models\Turf;
use App\Models\User;
use App\Models\TurfOverride;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class GroundOverrideTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $turf;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an owner user
        $this->owner = User::factory()->create([
            'role' => 'owner'
        ]);

        // Authenticate as owner
        Sanctum::actingAs($this->owner);

        // Create a turf for this owner
        $this->turf = Turf::create([
            'owner_id' => $this->owner->id,
            'name' => 'Owner Turf',
            'sport_type' => ['football'],
            'city' => 'Test City',
            'address' => 'Test Address',
        ]);
    }

    /** @test */
    public function owner_can_list_overrides()
    {
        $override = $this->turf->overrides()->create([
            'date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'price' => 500,
            'is_blocked' => false,
        ]);

        $response = $this->getJson("/api/owner/grounds/{$this->turf->id}/overrides");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id' => $override->id,
                'price' => 500
            ]);
    }

    /** @test */
    public function owner_can_set_override()
    {
        $data = [
            'date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'price' => 600,
            'is_blocked' => false,
            'sport_type' => 'football',
        ];

        $response = $this->postJson("/api/owner/grounds/{$this->turf->id}/overrides", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Override saved successfully']);

        $this->assertDatabaseHas('turf_overrides', [
            'turf_id' => $this->turf->id,
            'date' => $data['date'],
            'price' => 600
        ]);
    }

    /** @test */
    public function owner_can_update_existing_override()
    {
        $override = $this->turf->overrides()->create([
            'date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'price' => 600,
            'is_blocked' => false,
        ]);

        $data = [
            'date' => $override->date->format('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'price' => 750, // Updated price
            'is_blocked' => true, // Block it now
        ];

        $response = $this->postJson("/api/owner/grounds/{$this->turf->id}/overrides", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('turf_overrides', [
            'id' => $override->id,
            'price' => 750,
            'is_blocked' => 1
        ]);
    }

    /** @test */
    public function owner_can_remove_override()
    {
        $override = $this->turf->overrides()->create([
            'date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '18:00',
            'end_time' => '19:00',
            'price' => 500,
        ]);

        $response = $this->deleteJson("/api/owner/grounds/{$this->turf->id}/overrides/{$override->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Override removed successfully']);

        $this->assertDatabaseMissing('turf_overrides', ['id' => $override->id]);
    }

    /** @test */
    public function owner_cannot_manage_another_owners_ground_overrides()
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $otherTurf = Turf::create([
            'owner_id' => $otherOwner->id,
            'name' => 'Other Turf',
            'sport_type' => ['football'],
            'city' => 'Other City',
            'address' => 'Other Address',
        ]);

        $response = $this->getJson("/api/owner/grounds/{$otherTurf->id}/overrides");
        $response->assertStatus(404); // Should not find ground belong to another owner

        $response = $this->postJson("/api/owner/grounds/{$otherTurf->id}/overrides", [
            'date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'price' => 500,
        ]);
        $response->assertStatus(404);
    }
}
