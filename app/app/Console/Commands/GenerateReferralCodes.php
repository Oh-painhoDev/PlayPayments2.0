<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateReferralCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'referrals:generate-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate referral codes for users that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNull('referral_code')
            ->orWhere('referral_code', '')
            ->get();
        
        $this->info("Found {$users->count()} users without referral codes.");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            $user->referral_code = User::generateUniqueReferralCode();
            $user->save();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Successfully generated referral codes for {$users->count()} users.");
        
        return Command::SUCCESS;
    }
}
