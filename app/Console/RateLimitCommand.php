<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use App\Security\RateLimiter;
use App\Security\RateLimitCleaner;

/**
 * Console command pro správu Rate Limiting systému
 * 
 * Použití:
 * php bin/console app:rate-limit:cleanup
 * php bin/console app:rate-limit:stats  
 * php bin/console app:rate-limit:clear --ip=192.168.1.1
 */
class RateLimitCommand extends Command
{
    /** @var RateLimiter */
    private $rateLimiter;

    /** @var RateLimitCleaner */
    private $cleaner;

    public function __construct(
        RateLimiter $rateLimiter,
        RateLimitCleaner $cleaner
    ) {
        $this->rateLimiter = $rateLimiter;
        $this->cleaner = $cleaner;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:rate-limit')
            ->setDescription('Správa Rate Limiting systému')
            ->addOption(
                'action',
                'a',
                InputOption::VALUE_REQUIRED,
                'Akce: cleanup, stats, clear, optimize',
                'stats'
            )
            ->addOption(
                'ip',
                null,
                InputOption::VALUE_OPTIONAL,
                'IP adresa pro vymazání blokování'
            )
            ->addOption(
                'days',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Počet dní pro čištění starých záznamů',
                7
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Potvrdit akci bez dotazu'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getOption('action');

        switch ($action) {
            case 'cleanup':
                return $this->executeCleanup($input, $output);
            
            case 'stats':
                return $this->executeStats($input, $output);
            
            case 'clear':
                return $this->executeClear($input, $output);
            
            case 'optimize':
                return $this->executeOptimize($input, $output);
            
            default:
                $output->writeln('<error>Neznámá akce. Použijte: cleanup, stats, clear, optimize</error>');
                return Command::FAILURE;
        }
    }

    /**
     * Vyčištění starých záznamů
     */
    private function executeCleanup(InputInterface $input, OutputInterface $output): int
    {
        $days = (int) $input->getOption('days');
        $force = $input->getOption('force');

        $output->writeln('<info>Rate Limit Cleanup</info>');
        $output->writeln("Mažu záznamy starší než {$days} dní...");

        if (!$force) {
            $output->writeln('<question>Pokračovat? (y/N)</question>');
            $handle = fopen('php://stdin', 'r');
            $line = fgets($handle);
            fclose($handle);
            
            if (trim(strtolower($line)) !== 'y') {
                $output->writeln('<comment>Zrušeno.</comment>');
                return Command::SUCCESS;
            }
        }

        $result = $this->cleaner->cleanOldRecords($days);

        $output->writeln('<info>Cleanup dokončen:</info>');
        $output->writeln("- Rate limit záznamy smazány: {$result['rate_limits_deleted']}");
        $output->writeln("- Expirované bloky smazány: {$result['expired_blocks_deleted']}");
        $output->writeln("- Staré bloky smazány: {$result['old_blocks_deleted']}");

        if (!empty($result['errors'])) {
            $output->writeln('<error>Chyby:</error>');
            foreach ($result['errors'] as $error) {
                $output->writeln("- {$error}");
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Zobrazení statistik
     */
    private function executeStats(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Rate Limiting Statistiky</info>');

        // Obecné statistiky
        $stats = $this->rateLimiter->getStatistics();
        
        $table = new Table($output);
        $table->setHeaders(['Metrika', 'Hodnota']);
        $table->addRows([
            ['Aktuálně zablokované IP', $stats['currently_blocked_ips']],
            ['Pokusy za 24h', $stats['attempts_last_24h']],
            ['Neúspěšné pokusy za 24h', $stats['failed_attempts_last_24h']],
            ['Úspěšnost (%)', $stats['success_rate'] . '%'],
        ]);
        $table->render();

        // Top IP adresy
        if (!empty($stats['top_ips'])) {
            $output->writeln('<info>Top IP adresy (neúspěšné pokusy za 24h):</info>');
            $topTable = new Table($output);
            $topTable->setHeaders(['IP Adresa', 'Počet pokusů']);
            
            foreach ($stats['top_ips'] as $ip) {
                $topTable->addRow([$ip->ip_address, $ip->attempt_count]);
            }
            $topTable->render();
        }

        // Statistiky tabulek
        $tableStats = $this->cleaner->getTableStats();
        if (!isset($tableStats['error'])) {
            $output->writeln('<info>Velikost tabulek:</info>');
            $sizeTable = new Table($output);
            $sizeTable->setHeaders(['Tabulka', 'Počet záznamů', 'Velikost (MB)']);
            
            $sizeTable->addRows([
                [
                    'rate_limits', 
                    $tableStats['rate_limits_count'],
                    $tableStats['table_sizes_mb']['rate_limits'] ?? 'N/A'
                ],
                [
                    'rate_limit_blocks', 
                    $tableStats['blocks_count'],
                    $tableStats['table_sizes_mb']['rate_limit_blocks'] ?? 'N/A'
                ],
            ]);
            $sizeTable->render();

            if ($tableStats['last_cleanup']) {
                $output->writeln("Poslední cleanup: {$tableStats['last_cleanup']->format('Y-m-d H:i:s')}");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Vymazání blokování pro IP adresu
     */
    private function executeClear(InputInterface $input, OutputInterface $output): int
    {
        $ip = $input->getOption('ip');
        $force = $input->getOption('force');

        if (!$ip) {
            $output->writeln('<error>Musíte zadat IP adresu pomocí --ip</error>');
            return Command::FAILURE;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $output->writeln('<error>Neplatná IP adresa</error>');
            return Command::FAILURE;
        }

        $output->writeln("<info>Mažu rate limit blokování pro IP: {$ip}</info>");

        if (!$force) {
            $output->writeln('<question>Pokračovat? (y/N)</question>');
            $handle = fopen('php://stdin', 'r');
            $line = fgets($handle);
            fclose($handle);
            
            if (trim(strtolower($line)) !== 'y') {
                $output->writeln('<comment>Zrušeno.</comment>');
                return Command::SUCCESS;
            }
        }

        if ($this->rateLimiter->clearBlocking($ip, 'Vymazáno přes console command')) {
            $output->writeln('<info>Rate limiting vymazán úspěšně.</info>');
        } else {
            $output->writeln('<error>Chyba při mazání rate limitingu.</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Optimalizace databázových tabulek
     */
    private function executeOptimize(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Optimalizace rate limiting tabulek...</info>');

        if ($this->cleaner->optimizeTables()) {
            $output->writeln('<info>Tabulky optimalizovány úspěšně.</info>');
        } else {
            $output->writeln('<error>Chyba při optimalizaci tabulek.</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}