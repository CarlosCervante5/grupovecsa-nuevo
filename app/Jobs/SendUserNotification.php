<?php

namespace App\Jobs;

use App\Models\User;
use App\Helpers\ApiResponseHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class SendUserNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;

    protected $user;
    protected $notification;

    /**
     * Create a new job instance.
     */
    public function __construct( User $user, $notification )
    {
        $this->user = $user;
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   

        $this->user->notify($this->notification);

        ApiResponseHelper::apiSuccess(200, 'Notificación de creación de cuenta enviada correctamente al usuario', ['email' => $this->user->email_1]);

    }

    public function failed(\Exception $e)
    {
        ApiResponseHelper::apiError('Error en el job para notificar al usuario '. $this->user->email_1, $e->getMessage(), 500, 'SEND_NOTIFICATION_ERROR');

    }
}
