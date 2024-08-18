<?php

namespace Ars\Otp\Commands;

use Ars\Otp\Repositories\OtpRepository;
use Illuminate\Console\Command;

class OtpCodeClearExpiredCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:clear-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge all expired Otp codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = app(OtpRepository::class)->purgeExpiredCodes();
        $this->components->info(sprintf('[%s] Expired codes purged.', $count));
    }
}
