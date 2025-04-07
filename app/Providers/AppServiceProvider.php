<?php

namespace App\Providers;

use App\Services\ApiService;
use App\Services\DataProcessor;
use App\Services\Parsers\OnuDataParser;
use App\Services\Parsers\OnuStatsParser;
use App\Services\Parsers\ParserInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ApiService::class, fn() => new ApiService());
        $this->app->bind(ParserInterface::class . '.onu_data', OnuDataParser::class);
        $this->app->bind(ParserInterface::class . '.onu_stats', OnuStatsParser::class);
        $this->app->singleton(DataProcessor::class, function ($app) {
            return new DataProcessor(
                $app->make(ApiService::class),
                $app->make(ParserInterface::class . '.onu_data'),
                $app->make(ParserInterface::class . '.onu_stats')
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
