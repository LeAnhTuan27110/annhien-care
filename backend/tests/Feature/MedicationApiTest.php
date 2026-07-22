<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\FamilyLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MedicationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('patient');
        Role::findOrCreate('family_member');
    }

    public function test_patient_can_submit_medication_and_only_receives_pending_status(): void
    {
        $patient = $this->patient();
        Sanctum::actingAs($patient);

        $this->postJson('/api/medications', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.verification_status', 'pending')
            ->assertJsonMissing(['drug_name' => 'Paracetamol']);
    }

    public function test_full_family_link_can_submit_for_patient_but_view_only_link_cannot(): void
    {
        $patient = $this->patient();
        $familyMember = $this->familyMember($patient, 'full');
        Sanctum::actingAs($familyMember);

        $this->postJson('/api/medications', [...$this->payload(), 'patient_id' => $patient->id])->assertCreated();
        $this->assertDatabaseHas('medications', ['patient_id' => $patient->id, 'created_by' => $familyMember->id]);

        $viewOnlyMember = $this->familyMember($patient, 'view_only');
        Sanctum::actingAs($viewOnlyMember);
        $this->postJson('/api/medications', [...$this->payload(), 'patient_id' => $patient->id])->assertForbidden();
    }

    private function patient(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('patient');

        return $user;
    }

    private function familyMember(User $patient, string $permissionLevel): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('family_member');
        FamilyLink::query()->create([
            'patient_id' => $patient->id, 'family_user_id' => $user->id, 'relationship_type' => 'child',
            'permission_level' => $permissionLevel, 'status' => 'active', 'consented_at' => now(), 'created_by' => $patient->id,
        ]);

        return $user;
    }

    private function payload(): array
    {
        return ['drug_name' => 'Paracetamol', 'dosage' => '500 mg', 'frequency' => '2 times/day', 'start_date' => '2026-07-01'];
    }
}
