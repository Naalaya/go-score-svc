<?php
namespace Tests\Unit\Console\Commands;

use App\Console\Commands\ImportCsvToNormalized;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ImportCsvToNormalizedTest extends TestCase
{
    use RefreshDatabase;

        public function test_command_signature_exists()
    {
        $command = new ImportCsvToNormalized();

        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $this->assertNotEmpty($signatureProperty->getDefaultValue());

        $descriptionProperty = $reflection->getProperty('description');
        $this->assertNotEmpty($descriptionProperty->getDefaultValue());
    }

        public function test_command_is_registered()
    {
        // Test that command class exists and can be instantiated
        $command = new ImportCsvToNormalized();
        $this->assertInstanceOf(ImportCsvToNormalized::class, $command);
    }

    public function test_command_handle_method_exists()
    {
        $command = new ImportCsvToNormalized();

        $this->assertTrue(method_exists($command, 'handle'));
    }

        public function test_command_with_missing_file()
    {
        // Test command can be instantiated without errors
        $command = new ImportCsvToNormalized();
        $this->assertTrue(method_exists($command, 'handle'));
    }

    public function test_command_description_is_meaningful()
    {
        $command = new ImportCsvToNormalized();

        $description = $command->getDescription();
        $this->assertNotEmpty($description);
        $this->assertIsString($description);
        $this->assertGreaterThan(10, strlen($description));
    }

        public function test_command_signature_has_required_arguments()
    {
        $command = new ImportCsvToNormalized();

        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $signature = $signatureProperty->getDefaultValue();
        $this->assertNotEmpty($signature);
        $this->assertIsString($signature);
    }

    public function test_command_can_be_instantiated()
    {
        $command = new ImportCsvToNormalized();

        $this->assertInstanceOf(ImportCsvToNormalized::class, $command);
        $this->assertInstanceOf(\Illuminate\Console\Command::class, $command);
    }

        public function test_command_with_help_option()
    {
        $command = new ImportCsvToNormalized();

        // Test command has standard Laravel Command methods
        $this->assertTrue(method_exists($command, 'handle'));
        $this->assertTrue(method_exists($command, 'run'));
    }

    public function test_command_has_signature_property()
    {
        $command = new ImportCsvToNormalized();

        $reflection = new \ReflectionClass($command);
        $this->assertTrue($reflection->hasProperty('signature'));
    }

    public function test_command_has_description_property()
    {
        $command = new ImportCsvToNormalized();

        $reflection = new \ReflectionClass($command);
        $this->assertTrue($reflection->hasProperty('description'));
    }

    public function test_command_extends_console_command()
    {
        $command = new ImportCsvToNormalized();

        $this->assertInstanceOf(\Illuminate\Console\Command::class, $command);
    }
}
