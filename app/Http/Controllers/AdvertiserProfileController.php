<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdvertiserProfileRequest;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;

class AdvertiserProfileController extends Controller
{
    public function update(UpdateAdvertiserProfileRequest $request, MediaService $media): RedirectResponse
    {
        $user = $request->user();
        $currentImage = $user->ogImage;

        if ($request->hasFile('og_image')) {
            $image = $media->store($request->file('og_image'), $user, "Imagem de compartilhamento de {$user->name}");
            $user->ogImage()->associate($image);
            $user->save();

            if ($currentImage) {
                $media->delete($currentImage);
            }
        } elseif ($request->boolean('remove_og_image')) {
            $user->ogImage()->dissociate();
            $user->save();

            if ($currentImage) {
                $media->delete($currentImage);
            }
        }

        return back()->with('success', 'Imagem de compartilhamento atualizada.');
    }
}
