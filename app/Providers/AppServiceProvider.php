<?php

namespace App\Providers;

use App\Console\Commands\ProcessWeekFantasyCycle;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Blade::directive('uiDate', function (string $expression): string {
            return "<?php echo e({$expression} ? \\Illuminate\\Support\\Carbon::parse({$expression})->setTimezone(config('app.timezone'))->format('M j, Y · H:i') : '—'); ?>";
        });

        Blade::directive('uiDateInput', function (string $expression): string {
            return "<?php echo e({$expression} ? \\Illuminate\\Support\\Carbon::parse({$expression})->setTimezone(config('app.timezone'))->format('Y-m-d\\TH:i') : ''); ?>";
        });

        Gate::define('viewApiDocs', function ($user = null) {
            return app()->environment(['local', 'testing']);
        });

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                $openApi->secure(
                    SecurityScheme::http('bearer')->as('bearerAuth')
                );
            });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessWeekFantasyCycle::class,
            ]);
        }
    }
}
