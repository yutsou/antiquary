<?php

namespace App\Console\Commands;

use App\Models\Lot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--static : Generate static sitemap.xml file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Generating sitemap...');

        // 獲取所有已發布的拍賣品
        $lots = Lot::whereIn('status', [20, 21, 61])
                  ->latest()
                  ->get();

        // 生成 sitemap 內容
        $content = view('sitemap', compact('lots'))->render();

        if ($this->option('static')) {
            // 保存到 public 目錄（靜態文件）
            $path = public_path('sitemap.xml');
            file_put_contents($path, $content);
            $this->info('Static sitemap generated successfully at: ' . $path);
        } else {
            // 只顯示統計信息，不生成靜態文件
            $this->info('Sitemap content generated (dynamic mode)');
        }

        $this->info('Total lots included: ' . $lots->count());
        $this->info('Sitemap will be served dynamically via SitemapController');

        return Command::SUCCESS;
    }
}
