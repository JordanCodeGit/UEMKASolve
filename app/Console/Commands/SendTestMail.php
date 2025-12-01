<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailMail;
use App\Models\User;

class SendTestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:test-mail {email?} {--user_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test verification email to a given address (for local testing)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? config('mail.from.address') ?? 'hello@example.com';
        $userId = (int) $this->option('user_id');

        $this->info('Sending test mail to: ' . $email . ' (user_id=' . $userId . ')');

        // Prefer an explicit id, otherwise try to find by email, or create a temporary user
        $user = User::find($userId);
        if (! $user) {
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            $this->warn('User not found by id or email. Creating temporary user with the test email.');
            $user = User::create([
                'name' => 'Test User',
                'email' => $email,
                'password' => bcrypt('password123'),
            ]);
            $this->info('Created temporary user id=' . $user->getKey());
        }

        try {
            Mail::to($email)->send(new VerifyEmailMail($user));
            $this->info('Mail send attempted; check logs and inbox.');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Error sending mail: ' . $e->getMessage());
            return 1;
        }
    }
}
