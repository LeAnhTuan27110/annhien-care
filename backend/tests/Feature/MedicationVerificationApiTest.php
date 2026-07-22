<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\CaregiverProfile;
use App\Domains\Auth\Models\DoctorProfile;
use App\Domains\Auth\Models\FamilyLink;
use App\Domains\Health\Models\Medication;
use App\Domains\Health\Models\VerificationSignature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MedicationVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('patient');
        Role::findOrCreate('doctor');
        Role::findOrCreate('nurse');
        Role::findOrCreate('family_member');
        Role::findOrCreate('caregiver');
    }

    public function test_patient_can_submit_a_medication_but_only_receives_its_pending_status(): void
    {
        $patient = $this->patient();
        Sanctum::actingAs($patient);

        $response = $this->postJson('/api/medications', $this->medicationPayload());

        $response->assertCreated()
            ->assertJsonPath('data.verification_status', 'pending')
            ->assertJsonMissing(['drug_name' => 'Paracetamol']);

        $this->assertDatabaseHas('medications', [
            'patient_id' => $patient->id,
            'created_by' => $patient->id,
            'drug_name' => 'Paracetamol',
            'source' => 'manual',
            'verification_status' => 'pending',
        ]);
    }

    public function test_doctor_can_verify_pending_medication_and_patient_sees_verified_content(): void
    {
        $patient = $this->patient();
        $medication = Medication::query()->create([
            ...$this->medicationPayload(),
            'patient_id' => $patient->id,
            'created_by' => $patient->id,
            'source' => 'manual',
            'verification_status' => 'pending',
        ]);
        $doctor = $this->doctor();

        Sanctum::actingAs($doctor);
        $this->postJson("/api/doctor/medications/{$medication->id}/verify")
            ->assertOk()
            ->assertJsonPath('data.verification_status', 'verified');

        $this->assertDatabaseHas('medications', [
            'id' => $medication->id,
            'verification_status' => 'verified',
            'verified_by' => $doctor->id,
        ]);
        $this->assertDatabaseHas('verification_signatures', [
            'verifiable_type' => Medication::class,
            'verifiable_id' => $medication->id,
            'verified_by' => $doctor->id,
            'status' => 'verified',
        ]);
        $this->assertNotNull(VerificationSignature::query()->firstOrFail()->signature_hash);

        Sanctum::actingAs($patient);
        $this->getJson("/api/medications/{$medication->id}/status")
            ->assertOk()
            ->assertJsonPath('data.verification_status', 'verified')
            ->assertJsonPath('data.drug_name', 'Paracetamol');
    }

    public function test_patient_cannot_view_another_patients_medication_status(): void
    {
        $medication = Medication::query()->create([
            ...$this->medicationPayload(),
            'patient_id' => $this->patient()->id,
            'created_by' => $this->patient()->id,
            'source' => 'manual',
        ]);

        Sanctum::actingAs($this->patient());
        $this->getJson("/api/medications/{$medication->id}/status")->assertForbidden();
    }

    public function test_unverified_doctor_cannot_verify_a_medication(): void
    {
        $medication = $this->pendingMedication();
        Sanctum::actingAs($this->doctor(licenseVerified: false));

        $this->postJson("/api/doctor/medications/{$medication->id}/verify")->assertForbidden();

        $this->assertDatabaseHas('medications', [
            'id' => $medication->id,
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseCount('verification_signatures', 0);
    }

    public function test_verified_nurse_can_verify_a_pending_medication(): void
    {
        $medication = $this->pendingMedication();
        Sanctum::actingAs($this->nurse());

        $this->postJson("/api/doctor/medications/{$medication->id}/verify")
            ->assertOk()
            ->assertJsonPath('data.verification_status', 'verified');
    }

    public function test_family_member_and_caregiver_cannot_verify_a_medication(): void
    {
        foreach (['family_member', 'caregiver'] as $role) {
            $medication = $this->pendingMedication();
            $user = User::factory()->create(['status' => 'active']);
            $user->assignRole($role);
            Sanctum::actingAs($user);

            $this->postJson("/api/doctor/medications/{$medication->id}/verify")->assertForbidden();
        }
    }

    public function test_family_member_with_full_active_link_can_submit_for_patient_and_view_after_verification(): void
    {
        $patient = $this->patient();
        $familyMember = $this->familyMember($patient, 'full');
        Sanctum::actingAs($familyMember);

        $response = $this->postJson('/api/medications', [
            ...$this->medicationPayload(),
            'patient_id' => $patient->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.verification_status', 'pending')
            ->assertJsonMissing(['drug_name' => 'Paracetamol']);

        $medication = Medication::query()->firstOrFail();
        $this->assertSame($patient->id, $medication->patient_id);
        $this->assertSame($familyMember->id, $medication->created_by);

        Sanctum::actingAs($this->doctor());
        $this->postJson("/api/doctor/medications/{$medication->id}/verify")->assertOk();

        Sanctum::actingAs($familyMember);
        $this->getJson("/api/medications/{$medication->id}/status")
            ->assertOk()
            ->assertJsonPath('data.drug_name', 'Paracetamol');
    }

    public function test_view_only_family_member_cannot_submit_for_patient(): void
    {
        $patient = $this->patient();
        $familyMember = $this->familyMember($patient, 'view_only');
        Sanctum::actingAs($familyMember);

        $this->postJson('/api/medications', [
            ...$this->medicationPayload(),
            'patient_id' => $patient->id,
        ])->assertForbidden();
    }

    public function test_anonymous_users_cannot_access_medication_endpoints(): void
    {
        $medication = $this->pendingMedication();

        $this->postJson('/api/medications', $this->medicationPayload())->assertUnauthorized();
        $this->postJson("/api/doctor/medications/{$medication->id}/verify")->assertUnauthorized();
        $this->getJson("/api/medications/{$medication->id}/status")->assertUnauthorized();
    }

    public function test_medication_submission_requires_its_mandatory_fields(): void
    {
        Sanctum::actingAs($this->patient());

        $this->postJson('/api/medications', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['drug_name', 'dosage', 'frequency', 'start_date']);
    }

    public function test_verified_medication_cannot_be_verified_twice(): void
    {
        $medication = $this->pendingMedication();
        $doctor = $this->doctor();
        Sanctum::actingAs($doctor);

        $this->postJson("/api/doctor/medications/{$medication->id}/verify")->assertOk();
        $this->postJson("/api/doctor/medications/{$medication->id}/verify")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['medication']);

        $this->assertDatabaseCount('verification_signatures', 1);
    }

    public function test_verified_clinician_can_reject_pending_medication_with_reason(): void
    {
        $medication = $this->pendingMedication();
        Sanctum::actingAs($this->doctor());

        $this->postJson("/api/doctor/medications/{$medication->id}/reject", [
            'rejection_reason' => 'Thông tin đơn thuốc cần được bổ sung.',
        ])
            ->assertOk()
            ->assertJsonPath('data.verification_status', 'rejected');

        $this->assertDatabaseHas('medications', [
            'id' => $medication->id,
            'verification_status' => 'rejected',
            'rejection_reason' => 'Thông tin đơn thuốc cần được bổ sung.',
        ]);
        $this->assertDatabaseHas('verification_signatures', [
            'verifiable_type' => Medication::class,
            'verifiable_id' => $medication->id,
            'status' => 'rejected',
        ]);
    }

    private function patient(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('patient');

        return $user;
    }

    private function doctor(bool $licenseVerified = true): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('doctor');
        DoctorProfile::query()->create([
            'user_id' => $user->id,
            'license_number' => 'VN-TEST-001',
            'specialty' => 'General medicine',
            'license_verified_status' => $licenseVerified ? 'verified' : 'pending',
            'license_verified_at' => $licenseVerified ? now() : null,
        ]);

        return $user;
    }

    private function nurse(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('nurse');
        CaregiverProfile::query()->create([
            'user_id' => $user->id,
            'caregiver_type' => 'nurse',
            'license_number' => 'VN-NURSE-001',
            'license_verified_status' => 'verified',
            'license_verified_at' => now(),
        ]);

        return $user;
    }

    private function familyMember(User $patient, string $permissionLevel): User
    {
        $familyMember = User::factory()->create(['status' => 'active']);
        $familyMember->assignRole('family_member');
        FamilyLink::query()->create([
            'patient_id' => $patient->id,
            'family_user_id' => $familyMember->id,
            'relationship_type' => 'child',
            'permission_level' => $permissionLevel,
            'status' => 'active',
            'consented_at' => now(),
            'created_by' => $patient->id,
        ]);

        return $familyMember;
    }

    private function pendingMedication(): Medication
    {
        $patient = $this->patient();

        return Medication::query()->create([
            ...$this->medicationPayload(),
            'patient_id' => $patient->id,
            'created_by' => $patient->id,
            'source' => 'manual',
            'verification_status' => 'pending',
        ]);
    }

    private function medicationPayload(): array
    {
        return [
            'drug_name' => 'Paracetamol',
            'dosage' => '500 mg',
            'frequency' => '2 times/day',
            'route' => 'oral',
            'start_date' => '2026-07-01',
            'instructions' => 'After meals',
        ];
    }
}
