<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Media\CloudinaryImageStorageService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadProfileImage
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $user;
    protected $original_filename;

    public function __construct(string $path, User $user, string $original_filename)
    {
        $this->path = $path;
        $this->user = $user;
        $this->original_filename = $original_filename;
    }

    public function handle(CloudinaryImageStorageService $storage): void
    {
        $this->validateInputs();

        Log::info('Uploading image for user profile:', [
            'user_uuid' => $this->user->uuid,
            'path' => $this->path,
        ]);

        try {
            $result = $storage->storeFromTempRelativePath(
                trim((string) config('filesystems.profile_folder_base', 'default_folder')),
                $this->user->uuid,
                $this->path,
                $this->user->uuid
            );

            $profile = $this->user->getRoleProfile();
            $profile['profile']->picture = $result['url'];
            $profile['profile']->save();
        } catch (\Throwable $e) {
            Log::error('UploadProfileImage failed', [
                'user_uuid' => $this->user->uuid,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'user' => $this->user,
            'original_filename' => $this->original_filename,
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                throw new Exception("{$field} is required");
            }
        }
    }
}
