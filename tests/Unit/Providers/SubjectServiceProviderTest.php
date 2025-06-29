<?php
namespace Tests\Unit\Providers;

use App\Console\Validation\ScoreValidationService;
use App\Contracts\ScoringServiceInterface;
use App\Contracts\SubjectServiceInterface;
use App\Providers\SubjectServiceProvider;
use App\Services\Subjects\ScoringService;
use App\Services\Subjects\SubjectService;
use Illuminate\Foundation\Application;
use Tests\TestCase;

class SubjectServiceProviderTest extends TestCase
{
        public function test_provider_registers_subject_service_interface()
    {
        // Test with real Laravel app instance
        $subjectService = app(SubjectServiceInterface::class);
        $this->assertInstanceOf(SubjectService::class, $subjectService);
    }

    public function test_provider_registers_scoring_service_interface()
    {
        $app = new Application();
        $provider = new SubjectServiceProvider($app);

        $provider->register();

        $this->assertTrue($app->bound(ScoringServiceInterface::class));

        $instance = $app->make(ScoringServiceInterface::class);
        $this->assertInstanceOf(ScoringService::class, $instance);
    }

        public function test_provider_registers_services_as_singletons()
    {
        // Test singleton behavior with real app
        $instance1 = app(SubjectService::class);
        $instance2 = app(SubjectService::class);
        $this->assertSame($instance1, $instance2);

        $scoringInstance1 = app(ScoringService::class);
        $scoringInstance2 = app(ScoringService::class);
        $this->assertSame($scoringInstance1, $scoringInstance2);
    }

        public function test_provider_registers_validation_service()
    {
        $validationService = app(ScoreValidationService::class);
        $this->assertInstanceOf(ScoreValidationService::class, $validationService);
    }

        public function test_validation_service_dependencies_injected()
    {
        $validationService = app(ScoreValidationService::class);

        // Test that dependencies are properly injected
        $this->assertInstanceOf(ScoreValidationService::class, $validationService);

        // Test validation functionality works (indicates DI is working)
        $result = $validationService->validateSearchParams(['sbd' => '12345678']);
        $this->assertTrue($result['valid']);
    }

    public function test_provider_boot_method_exists()
    {
        $app = new Application();
        $provider = new SubjectServiceProvider($app);

        $this->assertTrue(method_exists($provider, 'boot'));

        // Should not throw exception
        $provider->boot();
        $this->assertTrue(true);
    }

    public function test_services_are_properly_bound_in_real_app()
    {
        // Test with real Laravel app instance
        $subjectService = app(SubjectServiceInterface::class);
        $scoringService = app(ScoringServiceInterface::class);
        $validationService = app(ScoreValidationService::class);

        $this->assertInstanceOf(SubjectService::class, $subjectService);
        $this->assertInstanceOf(ScoringService::class, $scoringService);
        $this->assertInstanceOf(ScoreValidationService::class, $validationService);
    }

    public function test_services_functionality_through_container()
    {
        $subjectService = app(SubjectServiceInterface::class);
        $scoringService = app(ScoringServiceInterface::class);

        // Test subject service functionality
        $this->assertTrue($subjectService->validateSubjectCode('toan'));
        $this->assertFalse($subjectService->validateSubjectCode('invalid'));

        // Test scoring service functionality
        $this->assertTrue($scoringService->validateScore(8.5));
        $this->assertFalse($scoringService->validateScore(15.0));
    }
}
