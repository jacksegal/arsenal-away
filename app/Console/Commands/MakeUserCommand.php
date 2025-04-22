<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class MakeUserCommand extends Command
{
    protected $signature = 'make:user {name?} {email?} {password?} {--phone=}';
    protected $description = 'Create a new user (interactive if no arguments provided)';

    public function handle()
    {
        $this->info('Creating a new user...');
        $this->newLine();

        // Get name
        $name = $this->argument('name');
        if (empty($name)) {
            $name = $this->ask('What is the user\'s full name?');
            while (empty($name)) {
                $this->error('Name is required');
                $name = $this->ask('What is the user\'s full name?');
            }
        }

        // Get email
        $email = $this->argument('email');
        if (empty($email)) {
            $email = $this->ask('What is the user\'s email address?');
            while (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Please enter a valid email address');
                $email = $this->ask('What is the user\'s email address?');
            }
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address provided');
            return;
        }

        // Check if email already exists
        if (User::where('email', $email)->exists()) {
            if (!$this->confirm('A user with this email already exists. Would you like to update their details instead?')) {
                $this->error('Operation cancelled');
                return;
            }
        }

        // Get password
        $password = $this->argument('password');
        if (empty($password)) {
            $password = $this->secret('What is the user\'s password?');
            while (empty($password)) {
                $this->error('Password is required');
                $password = $this->secret('What is the user\'s password?');
            }
        }

        // Get phone number
        $phone = $this->option('phone');
        if (empty($phone)) {
            $phone = $this->ask('What is the user\'s phone number? (optional)');
        }

        try {
            if (User::where('email', $email)->exists()) {
                // Update existing user
                $user = User::where('email', $email)->first();
                $user->update([
                    'name' => $name,
                    'password' => Hash::make($password),
                    'phone_number' => $phone,
                ]);
                $this->info('User updated successfully!');
            } else {
                // Create new user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'phone_number' => $phone,
                ]);
                $this->info('User created successfully!');
            }

            $this->newLine();
            $this->table(
                ['Name', 'Email', 'Phone'],
                [[$user->name, $user->email, $user->phone_number ?? 'N/A']]
            );
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
} 