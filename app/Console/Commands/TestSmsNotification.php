<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TestSms;
use Illuminate\Console\Command;

class TestSmsNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:sms {phone_number?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test SMS notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phoneNumber = $this->argument('phone_number');

        if (!$phoneNumber) {
            $phoneNumber = $this->ask('Please enter the phone number to send the test SMS to (format: +1234567890)');
        }

        // Create a temporary user with the provided phone number
        $user = new User();
        $user->phone_number = $phoneNumber;

        try {
            $user->notify(new TestSms());
            $this->info('Test SMS notification sent successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to send test SMS: ' . $e->getMessage());
        }
    }
}
