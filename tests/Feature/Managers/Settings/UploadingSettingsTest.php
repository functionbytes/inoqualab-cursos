<?php

namespace Tests\Feature\Managers\Settings;

use App\Models\Setting\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UploadingSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function managerUser(): User
    {
        return User::factory()->manager()->create();
    }

    private function userWithoutPermissions(): User
    {
        $user = User::factory()->manager()->create();
        $user->syncRoles([]);
        $user->syncPermissions([]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'max_file_size' => 20480,
            'max_files_per_upload' => 5,
            'allowed_file_types' => ['jpg', 'pdf'],
            'allowed_image_types' => ['jpg', 'png'],
            'allowed_document_types' => ['pdf', 'doc'],
            'storage_driver' => 's3',
            's3_bucket' => 'mi-bucket',
            's3_region' => 'us-east-1',
            'enable_virus_scan' => '1',
        ], $overrides);
    }

    public function test_manager_can_view_uploading_settings(): void
    {
        $manager = $this->managerUser();

        $response = $this->actingAs($manager)->get(route('manager.settings.system.uploading'));

        $response->assertOk();
        $response->assertSee('Límites de carga');
    }

    public function test_manager_can_update_uploading_settings(): void
    {
        $manager = $this->managerUser();

        $response = $this->actingAs($manager)
            ->post(route('manager.settings.system.uploading.update'), $this->validPayload());

        $response->assertRedirect(route('manager.settings.system.uploading'));

        $this->assertSame('20480', Setting::where('key', 'uploading_max_file_size')->value('value'));
        $this->assertSame('5', Setting::where('key', 'uploading_max_files_per_upload')->value('value'));
        $this->assertSame(json_encode(['jpg', 'pdf']), Setting::where('key', 'uploading_allowed_file_types')->value('value'));
        $this->assertSame('s3', Setting::where('key', 'uploading_storage_driver')->value('value'));
        $this->assertSame('mi-bucket', Setting::where('key', 'uploading_s3_bucket')->value('value'));
        $this->assertSame('1', Setting::where('key', 'uploading_enable_virus_scan')->value('value'));
    }

    public function test_update_requires_at_least_one_allowed_file_type(): void
    {
        $manager = $this->managerUser();

        $response = $this->actingAs($manager)
            ->post(route('manager.settings.system.uploading.update'), $this->validPayload(['allowed_file_types' => []]));

        $response->assertSessionHasErrors('allowed_file_types');
    }

    public function test_update_rejects_invalid_storage_driver(): void
    {
        $manager = $this->managerUser();

        $response = $this->actingAs($manager)
            ->post(route('manager.settings.system.uploading.update'), $this->validPayload(['storage_driver' => 'dropbox']));

        $response->assertSessionHasErrors('storage_driver');
    }

    public function test_update_rejects_max_file_size_above_server_ceiling(): void
    {
        $manager = $this->managerUser();

        $response = $this->actingAs($manager)
            ->post(route('manager.settings.system.uploading.update'), $this->validPayload(['max_file_size' => 999999]));

        $response->assertSessionHasErrors('max_file_size');
    }

    public function test_user_without_permission_cannot_view_uploading_settings(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->get(route('manager.settings.system.uploading'))
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_update_uploading_settings(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->post(route('manager.settings.system.uploading.update'), $this->validPayload())
            ->assertForbidden();
    }
}
