<?php

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function createAdvertiserOgImage(User $user, string $path): MediaAsset
{
    Storage::disk('public')->put($path, 'image');

    return MediaAsset::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => $path,
        'original_name' => 'share.webp',
        'mime_type' => 'image/webp',
        'size' => 5,
        'kind' => 'image',
    ]);
}

it('keeps the current OG image when storing its replacement fails', function (): void {
    Storage::fake('public');
    config(['media.disk' => 'public']);
    $user = User::factory()->create();
    $currentImage = createAdvertiserOgImage($user, 'media/current-share.webp');
    $user->ogImage()->associate($currentImage);
    $user->save();

    $this->mock(MediaService::class)
        ->shouldReceive('store')
        ->once()
        ->andThrow(new RuntimeException('Storage indisponivel.'));

    $this->actingAs($user)
        ->post('/perfil-anunciante', [
            '_method' => 'PATCH',
            'og_image' => UploadedFile::fake()->image('replacement.jpg'),
        ])
        ->assertStatus(500);

    expect($user->fresh()->og_media_asset_id)->toBe($currentImage->id)
        ->and(MediaAsset::query()->whereKey($currentImage->id)->exists())->toBeTrue();
    Storage::disk('public')->assertExists($currentImage->path);
});

it('replaces the current OG image only after storing the new image', function (): void {
    Storage::fake('public');
    config(['media.disk' => 'public']);
    $user = User::factory()->create();
    $currentImage = createAdvertiserOgImage($user, 'media/current-share.webp');
    $replacementImage = createAdvertiserOgImage($user, 'media/replacement-share.webp');
    $user->ogImage()->associate($currentImage);
    $user->save();

    $media = $this->mock(MediaService::class);
    $media->shouldReceive('store')->once()->andReturn($replacementImage);
    $media->shouldReceive('delete')->once()->with(Mockery::on(
        fn (MediaAsset $image): bool => $image->is($currentImage),
    ));

    $this->actingAs($user)
        ->post('/perfil-anunciante', [
            '_method' => 'PATCH',
            'og_image' => UploadedFile::fake()->image('replacement.jpg'),
        ])
        ->assertRedirect();

    expect($user->fresh()->og_media_asset_id)->toBe($replacementImage->id);
});

it('dissociates the OG image before deleting it', function (): void {
    Storage::fake('public');
    config(['media.disk' => 'public']);
    $user = User::factory()->create();
    $currentImage = createAdvertiserOgImage($user, 'media/current-share.webp');
    $user->ogImage()->associate($currentImage);
    $user->save();

    $this->actingAs($user)
        ->post('/perfil-anunciante', [
            '_method' => 'PATCH',
            'remove_og_image' => true,
        ])
        ->assertRedirect();

    expect($user->fresh()->og_media_asset_id)->toBeNull()
        ->and(MediaAsset::query()->whereKey($currentImage->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing($currentImage->path);
});
