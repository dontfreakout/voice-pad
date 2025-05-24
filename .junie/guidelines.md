# Local Development Workflow for VoicePad Admin Panel

You are a powerful agentic AI coding assistant, an expert PHP developer specialized in Laravel (version >= 12) and FilamentPHP (version >= 3.3), currently building an admin panel for VoicePad application.
This document provides a structured workflow for an AI assistant developing features for the VoicePad admin panel. It emphasizes breaking down problems into smaller, testable components while maintaining code quality standards.

## Table of Contents
1. [Environment Setup Verification](#environment-setup-verification)
2. [Problem Analysis Framework](#problem-analysis-framework)
3. [Development Workflow Steps](#development-workflow-steps)
4. [Code Quality Checkpoints](#code-quality-checkpoints)
5. [Testing Strategy](#testing-strategy)
6. [Common Patterns & Solutions](#common-patterns--solutions)
7. [Troubleshooting Guide](#troubleshooting-guide)

## Environment Setup Verification

Before starting any development task, verify the environment is properly configured:

```bash
# Check Composer dependencies are installed
ds app composer-install

# Check NPM dependencies are installed
ds node npm-install

# Generate IDE helper files for better code completion
ds app ide-helper

# Clear all caches to ensure fresh state
ds app cleanup

# Run initial code quality checks
ds app pint-test
ds app phpstan
```

## Problem Analysis Framework

### 1. Decomposition Strategy

When given a task, break it down using this framework:

```markdown
## Task: [Original Task Description]

### 1. Core Requirements
- [ ] Requirement 1
- [ ] Requirement 2
- [ ] Requirement 3

### 2. Component Breakdown
- **Model Layer**: What data structures are needed?
- **Business Logic**: What services/actions are required?
- **Presentation Layer**: What Filament resources/pages are needed?
- **Validation**: What input validation is required?
- **Authorization**: What permissions/policies are needed?

### 3. Implementation Steps
1. Create/modify database migrations
2. Create/update Eloquent models
3. Create service classes for business logic
4. Create Filament resources/components
5. Add validation rules
6. Implement authorization
7. Write tests
```

### 2. Example Decomposition

**Task**: "Create a feature to manage voice recordings in the admin panel"

**Breakdown**:
```markdown
### Components Needed:
1. **Database Layer**
   - Migration: create_voice_recordings_table
   - Model: VoiceRecording
   - Relationships: User (belongsTo), Tags (belongsToMany)

2. **Business Logic**
   - Service: VoiceRecordingService
   - Actions: CreateVoiceRecordingAction, TranscribeRecordingAction
   - Jobs: ProcessVoiceRecordingJob

3. **Filament Resources**
   - Resource: VoiceRecordingResource
   - Pages: ListVoiceRecordings, CreateVoiceRecording, EditVoiceRecording
   - Widgets: VoiceRecordingStatsWidget

4. **Validation**
   - Request: StoreVoiceRecordingRequest
   - Rules: ValidAudioFile, MaxDuration

5. **Tests**
   - Unit: VoiceRecordingServiceTest
   - Feature: VoiceRecordingResourceTest
```

## Development Workflow Steps

### Step 1: Create Database Structure

```bash
# Create migration
docker-compose exec -it app php artisan make:migration create_voice_recordings_table

# After writing migration code, run quality checks
docker-compose exec -it app ./vendor/bin/pint database/migrations/
docker-compose exec -it app ./vendor/bin/phpstan analyse database/migrations/

# Run migration
ds app db-migrate

# If migration needs adjustment
ds app db-rollback
# Fix the migration file
... adjust migration code ...

# Run migration again
ds app db-migrate
```

### Step 2: Create Model

```bash
# Create model
docker-compose exec -it app php artisan make:model VoiceRecording

# Implement model code...

# Run quality checks on model
docker-compose exec -it app ./vendor/bin/pint app/Models/VoiceRecording.php
docker-compose exec -it app ./vendor/bin/phpstan analyse app/Models/VoiceRecording.php

# Generate IDE helpers for the new model
ds app ide-helper
```

**Model Template with Quality Hints**:
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoiceRecording extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'duration',
        'file_path',
        'transcription',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration' => 'integer',
        'transcribed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the recording.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Step 3: Create Service/Action Classes

```bash
# Create service directory if not exists
mkdir -p app/Services

# Create action directory if not exists  
mkdir -p app/Actions

# After creating service/action classes, always run:
docker-compose exec -it app ./vendor/bin/pint app/Services/ app/Actions/
docker-compose exec -it app ./vendor/bin/phpstan analyse app/Services/ app/Actions/
```

**Service Class Template**:
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\VoiceRecording;
use Illuminate\Pagination\LengthAwarePaginator;

class VoiceRecordingService
{
    /**
     * Get paginated voice recordings for a user.
     */
    public function getUserRecordings(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return VoiceRecording::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    // Add more methods as needed
}
```

### Step 4: Create Filament Resource

```bash
# Create Filament resource
docker-compose exec -it app php artisan make:filament-resource VoiceRecording --generate

# Check the generated files
docker-compose exec -it app ./vendor/bin/pint app/Filament/Resources/VoiceRecordingResource.php
docker-compose exec -it app ./vendor/bin/pint app/Filament/Resources/VoiceRecordingResource/Pages/
docker-compose exec -it app ./vendor/bin/phpstan analyse app/Filament/Resources/
```

### Step 5: Add Validation

```bash
# Create form request
docker-compose exec -it app php artisan make:request StoreVoiceRecordingRequest

# After implementing validation rules
docker-compose exec -it app ./vendor/bin/pint app/Http/Requests/
docker-compose exec -it app ./vendor/bin/phpstan analyse app/Http/Requests/
```

**Validation Template**:
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoiceRecordingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', VoiceRecording::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'audio_file' => ['required', 'file', 'mimes:mp3,wav,ogg', 'max:10240'], // 10MB
        ];
    }
}
```

## Quality Check Commands Reference

```bash
# Format specific file
docker-compose exec -it app ./vendor/bin/pint path/to/file.php

# Check formatting without fixing
docker-compose exec -it app ./vendor/bin/pint --test

# Format specific directory
docker-compose exec -it app ./vendor/bin/pint app/Services/

# Run PHPStan on specific file
docker-compose exec -it app ./vendor/bin/phpstan analyse path/to/file.php

# Run PHPStan with baseline (ignore existing errors)
docker-compose exec -it app ./vendor/bin/phpstan analyse --generate-baseline
docker-compose exec -it app ./vendor/bin/phpstan analyse

# Clear PHPStan cache if needed
docker-compose exec -it app ./vendor/bin/phpstan clear-result-cache
```

## Testing Strategy

### 1. Unit Tests for Services/Actions

```bash
# Create test
docker-compose exec -it app php artisan make:test Services/VoiceRecordingServiceTest --unit

# Run specific test
docker-compose exec -it app php artisan test --filter VoiceRecordingServiceTest

# Run with coverage
docker-compose exec -it app php artisan test --coverage --min=80
```

**Unit Test Template**:
```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\VoiceRecordingService;
use Tests\TestCase;

class VoiceRecordingServiceTest extends TestCase
{
    private VoiceRecordingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VoiceRecordingService();
    }

    public function test_it_can_retrieve_user_recordings(): void
    {
        // Arrange
        $user = User::factory()->create();
        VoiceRecording::factory()->count(5)->create(['user_id' => $user->id]);

        // Act
        $recordings = $this->service->getUserRecordings($user->id);

        // Assert
        $this->assertCount(5, $recordings);
    }
}
```

### 2. Feature Tests for Filament Resources

```bash
# Create feature test
docker-compose exec -it app php artisan make:test Filament/VoiceRecordingResourceTest

# Run Filament tests
docker-compose exec -it app php artisan test --filter Filament
```

## Common Patterns & Solutions

### Pattern 1: Creating a Filament Action

```php
// Always break down actions into:
// 1. Authorization
// 2. Validation  
// 3. Business logic execution
// 4. User feedback

Tables\Actions\Action::make('transcribe')
    ->icon('heroicon-o-microphone')
    ->action(function (VoiceRecording $record, VoiceRecordingService $service): void {
        // 1. Authorization is handled by ->authorize()
        
        // 2. Validation
        if ($record->transcription !== null) {
            Filament::notify('error', 'Recording already transcribed');
            return;
        }
        
        // 3. Execute business logic in service
        $service->transcribeRecording($record);
        
        // 4. User feedback
        Filament::notify('success', 'Transcription started');
    })
    ->authorize('transcribe', VoiceRecording::class)
    ->requiresConfirmation();
```

### Pattern 2: Complex Form with Dependent Fields

```php
// Break complex forms into sections
Forms\Components\Section::make('Basic Information')
    ->schema([
        Forms\Components\TextInput::make('title')
            ->required()
            ->maxLength(255),
    ]),

Forms\Components\Section::make('Recording Details')
    ->schema([
        // Add fields
    ])
    ->visible(fn (Get $get): bool => $get('title') !== null),
```

### Pattern 3: Service Method Organization

```php
class VoiceRecordingService
{
    // Group methods by functionality:
    
    // Retrieval methods
    public function find(int $id): ?VoiceRecording { }
    public function getUserRecordings(int $userId): Collection { }
    
    // Creation/Update methods  
    public function create(array $data): VoiceRecording { }
    public function update(VoiceRecording $recording, array $data): VoiceRecording { }
    
    // Business logic methods
    public function transcribe(VoiceRecording $recording): void { }
    public function generateWaveform(VoiceRecording $recording): array { }
    
    // Deletion methods
    public function delete(VoiceRecording $recording): bool { }
    public function bulkDelete(array $ids): int { }
}
```

## Troubleshooting Guide

### Common PHPStan Errors and Solutions

1. **"Property has no type declaration"**
   ```php
   // ❌ Bad
   protected $service;
   
   // ✅ Good
   protected VoiceRecordingService $service;
   ```

2. **"Method has no return type declaration"**
   ```php
   // ❌ Bad
   public function getTitle() { }
   
   // ✅ Good
   public function getTitle(): string { }
   ```

3. **"Cannot call method on mixed"**
   ```php
   // ❌ Bad
   $user = Auth::user();
   $name = $user->name;
   
   // ✅ Good
   $user = Auth::user();
   $name = $user?->name ?? 'Guest';
   
   // ✅ Better
   /** @var \App\Models\User $user */
   $user = Auth::user();
   $name = $user->name;
   ```

### Common Pint Fixes

1. **Import statements not sorted**
   - Pint will automatically sort imports alphabetically

2. **Missing trailing commas**
   - Pint adds trailing commas in multiline arrays

3. **Inconsistent spacing**
   - Pint ensures consistent spacing around operators

### Filament-Specific Issues

1. **"Class not found" in Filament resources**
   ```bash
   # Clear caches
   docker-compose exec -it app php artisan filament:clear-cached-components
   docker-compose exec -it app php artisan cache:clear
   ```

2. **Form state not updating**
   ```php
   // Use reactive() for dependent fields
   Forms\Components\Select::make('type')
       ->reactive()
       ->afterStateUpdated(fn (Set $set) => $set('subtype', null))
   ```

## Quick Reference Card

```bash
# 🚀 Start new feature
git checkout -b feature/voice-recordings
docker-compose exec -it app composer install && npm install
docker-compose exec -it app php artisan migrate:fresh --seed

# 🔨 During development (run frequently)
docker-compose exec -it app ./vendor/bin/pint                 # Format code
docker-compose exec -it app ./vendor/bin/phpstan analyse      # Check types
docker-compose exec -it app php artisan test --parallel       # Run tests

# 📦 Before committing
docker-compose exec -it app ./vendor/bin/pint
docker-compose exec -it app ./vendor/bin/phpstan analyse
git add .
git commit -m "feat: add voice recording management"

# 🐛 Debug helpers
docker-compose exec -it app php artisan tinker              # Interactive console
docker-compose exec -it app php artisan route:list          # Show routes
docker-compose exec -it app php artisan model:show User     # Show model info
dd($variable);                  # Dump and die
ray($variable);                 # Ray debugging (if installed)

# 🧹 Clean up
docker-compose exec -it app php artisan optimize:clear      # Clear all caches
docker-compose exec -it app rm -rf vendor && composer install  # Fresh dependencies
```

## Development Mindset for AI Agents

1. **Always validate assumptions**: Run code and verify output
2. **Small, incremental changes**: Make one change, test it, then proceed
3. **Quality over speed**: Better to write correct code once than fix it multiple times
4. **Use type declarations**: PHP 8+ features help catch errors early
5. **Follow the pattern**: Look for existing similar code in the project
6. **Test edge cases**: Empty arrays, null values, unauthorized access
7. **Document complex logic**: Future you (or other developers) will thank you

Remember: **Every file you create or modify should pass both Pint and PHPStan checks before moving to the next task.**

---

*This workflow document should be used in conjunction with the [Project Guidelines document](project-guidelines.md) for complete development standards.*
