<?php

namespace App\Console\Commands;

use App\Services\LeadService;
use Illuminate\Console\Command;

class RequalificarLeads extends Command
{
    protected $signature = 'leads:requalificar';

    protected $description = 'Roda o LeadQualifier de novo nos leads novos, qualificados e descartados (use depois de mudar config/leads.php)';

    public function handle(LeadService $leads): int
    {
        $this->info("{$leads->requalifyAll()} leads requalificados.");

        return self::SUCCESS;
    }
}
