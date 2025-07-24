# Local Development Workflow for VoicePad Admin Panel

You are a powerful agentic AI coding assistant, an expert PHP developer specialized in Laravel (version >= 12), FilamentPHP (version >= 3.3), and Livewire 3, currently building an admin panel for VoicePad application.

This document provides a structured workflow for an AI assistant developing features for the VoicePad admin panel. It emphasizes breaking down problems into smaller, testable components while maintaining code quality standards for both backend (Laravel/Filament) and frontend (Livewire 3) development.

## Table of Contents
1. [Environment Setup Verification](#environment-setup-verification)
2. [Problem Analysis Framework](#problem-analysis-framework)
3. [Backend Development Workflow Steps](#backend-development-workflow-steps)
4. [Frontend Development Workflow (Livewire 3)](#frontend-development-workflow-livewire-3)
5. [Code Quality Checkpoints](#code-quality-checkpoints)
6. [Testing Strategy](#testing-strategy)
7. [Common Patterns & Solutions](#common-patterns--solutions)
8. [Livewire 3 Best Practices](#livewire-3-best-practices)
9. [One-Shot Examples](#one-shot-examples)
10. [Troubleshooting Guide](#troubleshooting-guide)

## Environment Setup Verification

Anytime you want to run artisan command, you MUST use the Docker container to ensure the environment is consistent and dependencies are correctly loaded.
### Example:
for `php artisan migrate` command, use:
```bash
docker compose exec -it app php artisan migrate
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
- **Backend Layer**: What Filament resources/pages are needed?
- **Frontend Layer**: What Livewire components are needed?
- **Validation**: What input validation is required?
- **Authorization**: What permissions/policies are needed?

### 3. Implementation Steps
1. Create/modify database migrations
2. Create/update Eloquent models
3. Create service classes for business logic
4. Create Filament resources/components
5. Create Livewire components for dynamic UI
6. Add validation rules
7. Implement authorization
8. Write tests (both backend and frontend)
```

### 2. Frontend vs Backend Decision Framework

**When to use Livewire components:**
- Dynamic user interactions (real-time search, filtering)
- Form handling with real-time validation
- Interactive widgets and dashboards
- Modal dialogs and dynamic content
- File uploads with progress indicators
- Live data updates without page refresh

**When to use Filament:**
- Admin panel CRUD operations
- Data tables and listings
- Form builders for admin
- Role-based access control
- System configuration interfaces

## Backend Development Workflow Steps

### Step 1: Create Database Structure

```bash
# Create migration
docker compose exec -it app php artisan make:migration create_voice_recordings_table

# After writing migration code, run quality checks
docker compose exec -it app ./vendor/bin/pint database/migrations/
docker compose exec -it app ./vendor/bin/phpstan analyse database/migrations/

# Run migration
docker compose exec -it app php artisan migrate

# If migration needs adjustment
docker compose exec -it app php artisan migrate:rollback
# Fix the migration file
... adjust migration code ...

# Run migration again
docker compose exec -it app php artisan migrate
```

### Step 2: Create Model

```bash
# Create model
docker compose exec -it app php artisan make:model VoiceRecording

# Implement model code...

# Run quality checks on model
docker compose exec -it app ./vendor/bin/pint app/Models/VoiceRecording.php
docker compose exec -it app ./vendor/bin/phpstan analyse app/Models/VoiceRecording.php

# Generate IDE helpers for the new model
docker compose exec -it app php artisan ide-helper:generate
docker compose exec -it app php artisan ide-helper:models --write
docker compose exec -it app php artisan ide-helper:meta
```

**Model Template with Quality Hints**:
```php
<?php

declare(strict_types=1);

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

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
docker compose exec -it app ./vendor/bin/pint app/Services/ app/Actions/
docker compose exec -it app ./vendor/bin/phpstan analyse app/Services/ app/Actions/
```

### Step 4: Create Filament Resource

```bash
# Create Filament resource
docker compose exec -it app php artisan make:filament-resource VoiceRecording --generate

# Check the generated files
docker compose exec -it app ./vendor/bin/pint app/Filament/Resources/VoiceRecordingResource.php
docker compose exec -it app ./vendor/bin/pint app/Filament/Resources/VoiceRecordingResource/Pages/
docker compose exec -it app ./vendor/bin/phpstan analyse app/Filament/Resources/
```

## Frontend Development Workflow (Livewire 3)

### Step 1: Create Livewire Component

```bash
# Create a basic Livewire component
docker compose exec -it app php artisan make:livewire VoiceRecordingManager

# Create with test file
docker compose exec -it app php artisan make:livewire VoiceRecordingUploader --test

# Create inline component (for simple components)
docker compose exec -it app php artisan make:livewire SimpleCounter --inline

# Create form object (for complex forms)
docker compose exec -it app php artisan make:livewire-form VoiceRecordingForm

# Quality check after creating
docker compose exec -it app ./vendor/bin/pint app/Livewire/
docker compose exec -it app ./vendor/bin/phpstan analyse app/Livewire/
```

**Basic Livewire 3 Component Template**:
```php
<?php

declare(strict_types=1);

namespace App\\Livewire;

use Livewire\\Attributes\\Computed;
use Livewire\\Attributes\\On;
use Livewire\\Attributes\\Reactive;
use Livewire\\Attributes\\Validate;
use Livewire\\Component;
use Livewire\\WithPagination;

class VoiceRecordingManager extends Component
{
    use WithPagination;

    #[Validate('required|string|min:3')]
    public string $search = '';

    #[Validate('required|string|max:255')]
    public string $title = '';

    public bool $showModal = false;

    /**
     * Mount the component with initial data.
     */
    public function mount(?string $initialSearch = null): void
    {
        $this->search = $initialSearch ?? '';
    }

    /**
     * Computed property for recordings (cached per request).
     */
    #[Computed]
    public function recordings()
    {
        return VoiceRecording::query()
            ->where('title', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);
    }

    /**
     * Handle search action.
     */
    public function search(): void
    {
        $this->resetPage();
        $this->dispatch('recordings-filtered');
    }

    /**
     * Listen for external events.
     */
    #[On('recording-created')]
    public function refreshRecordings(): void
    {
        $this->resetPage();
        unset($this->recordings); // Clear computed property cache
    }

    /**
     * Create new recording.
     */
    public function create(): void
    {
        $this->validate();

        VoiceRecording::create([
            'title' => $this->title,
            'user_id' => auth()->id(),
        ]);

        $this->reset('title', 'showModal');
        $this->dispatch('recording-created');

        $this->js(\"alert('Recording created successfully!')\");
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.voice-recording-manager');
    }
}
```

### Step 2: Create Blade Template

**Livewire 3 Blade Template** (`resources/views/livewire/voice-recording-manager.blade.php`):
```html
<div>
    {{-- Search Section --}}
    <div class=\"mb-6\">
        <div class=\"flex gap-4\">
            <input 
                type=\"text\" 
                wire:model.live.debounce.300ms=\"search\"
                placeholder=\"Search recordings...\"
                class=\"flex-1 rounded-md border-gray-300\"
            >
            
            <button 
                wire:click=\"$set('showModal', true)\"
                class=\"bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600\"
            >
                Add Recording
            </button>
        </div>
    </div>

    {{-- Recordings List --}}
    <div class=\"space-y-4\">
        @foreach($this->recordings as $recording)
            <div class=\"p-4 border rounded-lg\">
                <h3 class=\"font-semibold\">{{ $recording->title }}</h3>
                <p class=\"text-gray-600\">{{ $recording->created_at->diffForHumans() }}</p>
                
                <div class=\"mt-2 flex gap-2\">
                    <button 
                        wire:click=\"edit({{ $recording->id }})\"
                        wire:loading.attr=\"disabled\"
                        wire:target=\"edit({{ $recording->id }})\"
                        class=\"text-blue-600 hover:text-blue-800\"
                    >
                        <span wire:loading.remove wire:target=\"edit({{ $recording->id }})\">Edit</span>
                        <span wire:loading wire:target=\"edit({{ $recording->id }})\">Editing...</span>
                    </button>
                    
                    <button 
                        wire:click=\"delete({{ $recording->id }})\"
                        wire:confirm=\"Are you sure you want to delete this recording?\"
                        class=\"text-red-600 hover:text-red-800\"
                    >
                        Delete
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class=\"mt-6\">
        {{ $this->recordings->links() }}
    </div>

    {{-- Create Modal --}}
    @if($showModal)
        <div 
            class=\"fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center\"
            wire:click.self=\"$set('showModal', false)\"
        >
            <div class=\"bg-white p-6 rounded-lg max-w-md w-full\">
                <h2 class=\"text-xl font-semibold mb-4\">Create Recording</h2>
                
                <form wire:submit=\"create\">
                    <div class=\"mb-4\">
                        <label class=\"block text-sm font-medium mb-2\">Title</label>
                        <input 
                            type=\"text\" 
                            wire:model=\"title\"
                            class=\"w-full rounded-md border-gray-300\"
                            required
                        >
                        @error('title')
                            <p class=\"text-red-500 text-sm mt-1\">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class=\"flex gap-2\">
                        <button 
                            type=\"submit\"
                            wire:loading.attr=\"disabled\"
                            wire:target=\"create\"
                            class=\"bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 disabled:opacity-50\"
                        >
                            <span wire:loading.remove wire:target=\"create\">Create</span>
                            <span wire:loading wire:target=\"create\">Creating...</span>
                        </button>
                        
                        <button 
                            type=\"button\"
                            wire:click=\"$set('showModal', false)\"
                            class=\"bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400\"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Loading Overlay --}}
    <div 
        wire:loading.delay.longer 
        wire:target=\"search\"
        class=\"fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center\"
    >
        <div class=\"bg-white p-4 rounded-lg\">
            <div class=\"animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600\"></div>
            <p class=\"mt-2\">Searching...</p>
        </div>
    </div>
</div>
```

### Step 3: Advanced Livewire Components

**Form Object Example** (`app/Livewire/Forms/VoiceRecordingForm.php`):
```php
<?php

declare(strict_types=1);

namespace App\\Livewire\\Forms;

use App\\Models\\VoiceRecording;
use Livewire\\Attributes\\Validate;
use Livewire\\Form;

class VoiceRecordingForm extends Form
{
    public ?VoiceRecording $recording = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('required|in:draft,published,archived')]
    public string $status = 'draft';

    /**
     * Fill the form with recording data.
     */
    public function setRecording(VoiceRecording $recording): void
    {
        $this->recording = $recording;
        $this->title = $recording->title;
        $this->description = $recording->description ?? '';
        $this->status = $recording->status;
    }

    /**
     * Store a new recording.
     */
    public function store(): VoiceRecording
    {
        $this->validate();

        return VoiceRecording::create([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Update the existing recording.
     */
    public function update(): void
    {
        $this->validate();

        $this->recording->update([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
        ]);
    }
}
```

**File Upload Component** (`app/Livewire/VoiceRecordingUploader.php`):
```php
<?php

declare(strict_types=1);

namespace App\\Livewire;

use Livewire\\Attributes\\Validate;
use Livewire\\Component;
use Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile;
use Livewire\\WithFileUploads;

class VoiceRecordingUploader extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:mp3,wav,ogg|max:10240')] // 10MB
    public ?TemporaryUploadedFile $audio = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    public bool $isUploading = false;
    public int $uploadProgress = 0;

    /**
     * Handle file upload.
     */
    public function save(): void
    {
        $this->validate();

        $this->isUploading = true;

        // Store the file
        $path = $this->audio->store('voice-recordings', 'public');

        // Create the recording
        VoiceRecording::create([
            'title' => $this->title,
            'file_path' => $path,
            'user_id' => auth()->id(),
            'duration' => $this->getAudioDuration($path),
        ]);

        $this->reset();
        $this->isUploading = false;

        $this->dispatch('recording-uploaded');
        $this->js(\"alert('Recording uploaded successfully!')\");
    }

    /**
     * Get audio file duration (placeholder implementation).
     */
    private function getAudioDuration(string $path): int
    {
        // In a real implementation, you'd use a library like getID3
        // to extract the audio duration
        return 0;
    }

    public function render()
    {
        return view('livewire.voice-recording-uploader');
    }
}
```

### Step 4: JavaScript Integration

**Using @script for JavaScript Integration**:
```php
// In your Livewire component
public function render()
{
    return view('livewire.voice-recording-player');
}
```

```html
<!-- In the Blade template -->
<div>
    <audio id=\"audio-player\" controls>
        <source src=\"{{ $recording->file_url }}\" type=\"audio/mpeg\">
    </audio>
    
    @script
    <script>
        const player = document.getElementById('audio-player');
        
        player.addEventListener('timeupdate', () => {
            const currentTime = Math.floor(player.currentTime);
            $wire.updatePlaybackPosition(currentTime);
        });
        
        player.addEventListener('ended', () => {
            $wire.handlePlaybackEnd();
        });
    </script>
    @endscript
</div>
```

**Using @assets for External Libraries**:
```html
@assets
<script src=\"https://cdnjs.cloudflare.com/ajax/libs/wavesurfer.js/6.6.3/wavesurfer.min.js\"></script>
<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/wavesurfer.js/6.6.3/wavesurfer.min.css\">
@endassets

@script
<script>
    const wavesurfer = WaveSurfer.create({
        container: '#waveform',
        waveColor: '#4F46E5',
        progressColor: '#7C3AED'
    });
    
    wavesurfer.load($wire.audioUrl);
    
    wavesurfer.on('ready', () => {
        $wire.waveformReady();
    });
</script>
@endscript
```

## Code Quality Checkpoints

### For Backend Code

```bash
# Format specific file
docker compose exec -it app ./vendor/bin/pint path/to/file.php

# Check formatting without fixing
docker compose exec -it app ./vendor/bin/pint --test

# Format specific directory
docker compose exec -it app ./vendor/bin/pint app/Services/

# Run PHPStan on specific file
docker compose exec -it app ./vendor/bin/phpstan analyse path/to/file.php

# Run PHPStan with baseline (ignore existing errors)
docker compose exec -it app ./vendor/bin/phpstan analyse --generate-baseline
docker compose exec -it app ./vendor/bin/phpstan analyse

# Clear PHPStan cache if needed
docker compose exec -it app ./vendor/bin/phpstan clear-result-cache
```

### For Livewire Components

```bash
# Format Livewire components
docker compose exec -it app ./vendor/bin/pint app/Livewire/

# Analyze Livewire components
docker compose exec -it app ./vendor/bin/phpstan analyse app/Livewire/

# Clear Livewire caches
docker compose exec -it app php artisan livewire:clear-cached-components

# Validate Blade templates
docker compose exec -it app php artisan view:clear
docker compose exec -it app php artisan view:cache
```

## Testing Strategy

### 1. Backend Testing (Services/Models)

```bash
# Create test
docker compose exec -it app php artisan make:test Services/VoiceRecordingServiceTest --unit

# Run specific test
docker compose exec -it app php artisan test --filter VoiceRecordingServiceTest

# Run with coverage
docker compose exec -it app php artisan test --coverage --min=80
```

### 2. Livewire Component Testing

```bash
# Create Livewire test
docker compose exec -it app php artisan make:test Livewire/VoiceRecordingManagerTest

# Create test with component
docker compose exec -it app php artisan make:livewire VoicePlayer --test

# Run Livewire tests
docker compose exec -it app php artisan test --filter Livewire
```

**Livewire Test Template**:
```php
<?php

declare(strict_types=1);

namespace Tests\\Feature\\Livewire;

use App\\Livewire\\VoiceRecordingManager;
use App\\Models\\User;
use App\\Models\\VoiceRecording;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Livewire\\Livewire;
use Tests\\TestCase;

class VoiceRecordingManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_successfully(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/voice-recordings')
            ->assertSeeLivewire(VoiceRecordingManager::class);
    }

    public function test_can_search_recordings(): void
    {
        $user = User::factory()->create();
        VoiceRecording::factory()->create([
            'title' => 'Test Recording',
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(VoiceRecordingManager::class)
            ->set('search', 'Test')
            ->assertSee('Test Recording');
    }

    public function test_can_create_new_recording(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(VoiceRecordingManager::class)
            ->set('title', 'New Recording')
            ->call('create')
            ->assertSet('title', '')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('voice_recordings', [
            'title' => 'New Recording',
            'user_id' => $user->id,
        ]);
    }

    public function test_validates_title_when_creating(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(VoiceRecordingManager::class)
            ->set('title', '')
            ->call('create')
            ->assertHasErrors(['title' => 'required']);
    }

    public function test_dispatches_event_when_recording_created(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(VoiceRecordingManager::class)
            ->set('title', 'New Recording')
            ->call('create')
            ->assertDispatched('recording-created');
    }

    public function test_handles_external_events(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(VoiceRecordingManager::class)
            ->dispatch('recording-created')
            ->assertMethodWasCalled('refreshRecordings');
    }
}
```

### 3. JavaScript Testing

```bash
# Run JavaScript tests (if using Jest/Vitest)
docker compose exec -it node npm run test

# Run browser tests (if using Laravel Dusk)
docker compose exec -it app php artisan dusk
```

## Common Patterns & Solutions

### Pattern 1: Real-time Search with Debouncing

```php
class SearchComponent extends Component
{
    #[Validate('string|max:255')]
    public string $query = '';

    #[Computed]
    public function results()
    {
        if (strlen($this->query) < 3) {
            return collect();
        }

        return VoiceRecording::search($this->query)
            ->take(10)
            ->get();
    }

    public function updatedQuery(): void
    {
        // This will be called on every query update
        // Debouncing is handled by wire:model.live.debounce.300ms
    }

    public function render()
    {
        return view('livewire.search-component');
    }
}
```

```html
<div>
    <input 
        type=\"text\" 
        wire:model.live.debounce.300ms=\"query\"
        placeholder=\"Search...\" 
        class=\"w-full\"
    >
    
    <div wire:loading wire:target=\"query\" class=\"text-gray-500\">
        Searching...
    </div>

    @if($this->results->isNotEmpty())
        <ul class=\"mt-4 space-y-2\">
            @foreach($this->results as $result)
                <li class=\"p-2 border rounded\">{{ $result->title }}</li>
            @endforeach
        </ul>
    @endif
</div>
```

### Pattern 2: Modal Management

```php
class ModalManagerComponent extends Component
{
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editingId = null;

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->reset('title'); // Reset form fields
    }

    public function openEditModal(int $id): void
    {
        $this->editingId = $id;
        $this->showEditModal = true;
        
        // Load data for editing
        $recording = VoiceRecording::find($id);
        $this->title = $recording->title;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset('editingId', 'title');
    }

    // Use JavaScript for better UX
    public function closeModalWithJs(): void
    {
        $this->js(\"document.getElementById('modal').style.display = 'none'\");
    }

    public function render()
    {
        return view('livewire.modal-manager');
    }
}
```

### Pattern 3: File Upload with Progress

```php
class FileUploadComponent extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:mp3,wav|max:10240')]
    public ?TemporaryUploadedFile $file = null;

    public bool $isUploading = false;
    public string $uploadMessage = '';

    public function updatedFile(): void
    {
        $this->validate(['file']);
        $this->uploadMessage = 'File selected: ' . $this->file->getClientOriginalName();
    }

    public function save(): void
    {
        $this->validate();
        $this->isUploading = true;
        $this->uploadMessage = 'Uploading...';

        try {
            $path = $this->file->store('uploads', 'public');
            
            VoiceRecording::create([
                'title' => $this->file->getClientOriginalName(),
                'file_path' => $path,
                'user_id' => auth()->id(),
            ]);

            $this->uploadMessage = 'Upload completed!';
            $this->reset('file');
            
            $this->dispatch('file-uploaded');
        } catch (\\Exception $e) {
            $this->uploadMessage = 'Upload failed: ' . $e->getMessage();
        } finally {
            $this->isUploading = false;
        }
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
}
```

## Livewire 3 Best Practices

### Performance Optimization

1. **Use Computed Properties for Expensive Operations**:
```php
#[Computed]
public function expensiveData()
{
    return ExpensiveModel::with(['relations'])
        ->where('complex_condition', true)
        ->get();
}
```

2. **Avoid Using `.live` Unless Necessary**:
```html
<!-- Use debouncing for search -->
<input wire:model.live.debounce.300ms=\"search\">

<!-- Use .blur for form validation -->
<input wire:model.blur=\"email\">

<!-- Use .change for select elements -->
<select wire:model.change=\"category\">
```

3. **Use Lazy Loading for Non-Critical Components**:
```php
#[Lazy]
class ExpensiveWidget extends Component
{
    public function placeholder()
    {
        return view('livewire.placeholders.widget');
    }
}
```

### Security Best Practices

1. **Use Locked Properties for Sensitive Data**:
```php
#[Locked]
public int $userId;

#[Locked] 
public string $role;
```

2. **Validate All Inputs**:
```php
#[Validate('required|email')]
public string $email = '';

#[Validate('required|min:8')]
public string $password = '';
```

3. **Authorize Actions**:
```php
public function delete(VoiceRecording $recording): void
{
    $this->authorize('delete', $recording);
    
    $recording->delete();
}
```

### UX Enhancement

1. **Use Loading States Effectively**:
```html
<button 
    wire:click=\"save\" 
    wire:loading.attr=\"disabled\"
    wire:target=\"save\"
>
    <span wire:loading.remove wire:target=\"save\">Save</span>
    <span wire:loading wire:target=\"save\">Saving...</span>
</button>
```

2. **Implement Optimistic UI Updates**:
```php
public function toggleFavorite(int $id): void
{
    // Update UI immediately
    $this->js(\"document.getElementById('heart-{$id}').classList.toggle('text-red-500')\");
    
    // Then update the server
    $recording = VoiceRecording::find($id);
    $recording->update(['is_favorite' => !$recording->is_favorite]);
}
```

3. **Use Transitions for Smooth UX**:
```html
<div wire:transition.opacity.duration.300ms>
    Content that appears/disappears
</div>
```

## One-Shot Examples

### Example 1: Voice Recording Dashboard Widget

**Task**: Create a dashboard widget showing voice recording statistics with real-time updates.

```php
<?php

namespace App\\Livewire\\Widgets;

use App\\Models\\VoiceRecording;
use Livewire\\Attributes\\Computed;
use Livewire\\Attributes\\On;
use Livewire\\Component;

class VoiceRecordingStats extends Component
{
    public string $period = '7d'; // 7d, 30d, 90d

    #[Computed]
    public function stats()
    {
        $days = match($this->period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 7,
        };

        return [
            'total' => VoiceRecording::count(),
            'recent' => VoiceRecording::where('created_at', '>=', now()->subDays($days))->count(),
            'duration' => VoiceRecording::sum('duration'),
            'avg_duration' => VoiceRecording::avg('duration'),
        ];
    }

    #[On('recording-created')]
    #[On('recording-deleted')]
    public function refreshStats(): void
    {
        unset($this->stats);
    }

    public function render()
    {
        return view('livewire.widgets.voice-recording-stats');
    }
}
```

```html
<div class=\"bg-white rounded-lg shadow p-6\">
    <div class=\"flex justify-between items-center mb-4\">
        <h3 class=\"text-lg font-semibold\">Voice Recordings</h3>
        <select wire:model.change=\"period\" class=\"text-sm\">
            <option value=\"7d\">Last 7 days</option>
            <option value=\"30d\">Last 30 days</option>
            <option value=\"90d\">Last 90 days</option>
        </select>
    </div>

    <div class=\"grid grid-cols-2 gap-4\" wire:loading.class=\"opacity-50\">
        <div>
            <p class=\"text-2xl font-bold\">{{ number_format($this->stats['total']) }}</p>
            <p class=\"text-gray-600\">Total Recordings</p>
        </div>
        <div>
            <p class=\"text-2xl font-bold text-green-600\">{{ number_format($this->stats['recent']) }}</p>
            <p class=\"text-gray-600\">Recent</p>
        </div>
        <div>
            <p class=\"text-2xl font-bold\">{{ gmdate('H:i:s', $this->stats['duration']) }}</p>
            <p class=\"text-gray-600\">Total Duration</p>
        </div>
        <div>
            <p class=\"text-2xl font-bold\">{{ gmdate('H:i:s', $this->stats['avg_duration']) }}</p>
            <p class=\"text-gray-600\">Avg Duration</p>
        </div>
    </div>
</div>
```

### Example 2: Advanced Search with Filters

**Task**: Create a search component with multiple filters and sorting.

```php
<?php

namespace App\\Livewire;

use App\\Models\\VoiceRecording;
use Livewire\\Attributes\\Computed;
use Livewire\\Attributes\\Url;
use Livewire\\Attributes\\Validate;
use Livewire\\Component;
use Livewire\\WithPagination;

class AdvancedSearch extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    #[Validate('string|max:255')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public bool $showFilters = false;

    #[Computed]
    public function recordings()
    {
        return VoiceRecording::query()
            ->when($this->search, fn($q) => $q->where('title', 'like', \"%{$this->search}%\"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(12);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.advanced-search');
    }
}
```

### Example 3: Real-time Audio Player

**Task**: Create an audio player component with waveform visualization.

```php
<?php

namespace App\\Livewire;

use App\\Models\\VoiceRecording;
use Livewire\\Attributes\\On;
use Livewire\\Component;

class AudioPlayer extends Component
{
    public VoiceRecording $recording;
    public bool $isPlaying = false;
    public int $currentTime = 0;
    public bool $isLoaded = false;

    public function mount(VoiceRecording $recording): void
    {
        $this->recording = $recording;
    }

    public function play(): void
    {
        $this->isPlaying = true;
        $this->js(\"document.getElementById('audio-{$this->recording->id}').play()\");
    }

    public function pause(): void
    {
        $this->isPlaying = false;
        $this->js(\"document.getElementById('audio-{$this->recording->id}').pause()\");
    }

    public function seek(int $time): void
    {
        $this->currentTime = $time;
        $this->js(\"document.getElementById('audio-{$this->recording->id}').currentTime = {$time}\");
    }

    #[On('audio-time-update')]
    public function updateTime(int $time): void
    {
        $this->currentTime = $time;
    }

    #[On('audio-loaded')]
    public function audioLoaded(): void
    {
        $this->isLoaded = true;
    }

    #[On('audio-ended')]
    public function audioEnded(): void
    {
        $this->isPlaying = false;
        $this->currentTime = 0;
    }

    public function render()
    {
        return view('livewire.audio-player');
    }
}
```

```html
<div class=\"bg-white rounded-lg shadow p-4\">
    <div class=\"flex items-center space-x-4\">
        <div class=\"flex-shrink-0\">
            @if($isLoaded)
                <button 
                    wire:click=\"{{ $isPlaying ? 'pause' : 'play' }}\"
                    class=\"w-12 h-12 rounded-full bg-blue-500 text-white flex items-center justify-center hover:bg-blue-600\"
                >
                    @if($isPlaying)
                        <svg class=\"w-6 h-6\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                            <path d=\"M5.5 3.5A1.5 1.5 0 017 2h6a1.5 1.5 0 011.5 1.5v13a1.5 1.5 0 01-1.5 1.5H7A1.5 1.5 0 015.5 16.5v-13z\"/>
                        </svg>
                    @else
                        <svg class=\"w-6 h-6\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                            <path d=\"M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z\"/>
                        </svg>
                    @endif
                </button>
            @else
                <div class=\"w-12 h-12 rounded-full bg-gray-300 animate-pulse\"></div>
            @endif
        </div>

        <div class=\"flex-1\">
            <h4 class=\"font-semibold\">{{ $recording->title }}</h4>
            <div class=\"text-sm text-gray-600\">
                {{ gmdate('i:s', $currentTime) }} / {{ gmdate('i:s', $recording->duration) }}
            </div>
            
            <div class=\"mt-2 bg-gray-200 rounded-full h-2 cursor-pointer\" wire:click=\"seek($event.offsetX / $event.target.offsetWidth * {{ $recording->duration }})\">
                <div 
                    class=\"bg-blue-500 h-2 rounded-full transition-all duration-100\"
                    style=\"width: {{ $recording->duration > 0 ? ($currentTime / $recording->duration) * 100 : 0 }}%\"
                ></div>
            </div>
        </div>
    </div>

    <audio 
        id=\"audio-{{ $recording->id }}\"
        src=\"{{ $recording->file_url }}\"
        preload=\"metadata\"
        class=\"hidden\"
    ></audio>

    @script
    <script>
        const audio = document.getElementById('audio-{{ $recording->id }}');
        
        audio.addEventListener('loadedmetadata', () => {
            $wire.dispatch('audio-loaded');
        });
        
        audio.addEventListener('timeupdate', () => {
            $wire.dispatch('audio-time-update', Math.floor(audio.currentTime));
        });
        
        audio.addEventListener('ended', () => {
            $wire.dispatch('audio-ended');
        });
    </script>
    @endscript
</div>
```

## Troubleshooting Guide

### Common Livewire 3 Issues

1. **Component Not Updating**:
    - Check if component has single root element
    - Verify wire:model bindings are correct
    - Use `$wire.refresh()` to force update
    - Check for JavaScript errors in browser console

2. **Events Not Firing**:
    - Ensure event names match between dispatch and listener
    - Check if #[On] attribute is properly imported
    - Verify component is properly rendered

3. **Properties Not Persisting**:
    - Check #[Url] attributes for query parameters
    - Verify session is working properly
    - Use #[Session] for session persistence if needed

4. **File Uploads Failing**:
    - Check file size limits in php.ini
    - Verify storage disk configuration
    - Ensure WithFileUploads trait is used
    - Check file validation rules

### Performance Issues

1. **Slow Response Times**:
    - Use #[Computed] for expensive operations
    - Implement proper database indexing
    - Use eager loading for relationships
    - Consider caching computed properties

2. **Too Many Network Requests**:
    - Avoid using .live modifier unnecessarily
    - Use debouncing for search inputs
    - Batch multiple property updates
    - Use request bundling for multiple actions

### JavaScript Integration Problems

1. **$wire Not Available**:
    - Ensure script is within @script directive
    - Check if Livewire assets are loaded
    - Verify component is properly initialized

2. **Event Listeners Not Working**:
    - Use cleanup functions to remove listeners
    - Check for Alpine conflicts
    - Verify event names are correct

## Quick Reference Card

```bash
# 🚀 Start new Livewire feature
git checkout -b feature/voice-player-component
docker compose exec -it app php artisan make:livewire VoicePlayer --test

# 🔨 During development (run frequently)
docker compose exec -it app ./vendor/bin/pint app/Livewire/         # Format code
docker compose exec -it app ./vendor/bin/phpstan analyse app/Livewire/  # Check types
docker compose exec -it app php artisan test --filter Livewire     # Run tests

# 🧪 Test Livewire components
docker compose exec -it app php artisan test tests/Feature/Livewire/VoicePlayerTest.php

# 📦 Before committing
docker compose exec -it app ./vendor/bin/pint
docker compose exec -it app ./vendor/bin/phpstan analyse
docker compose exec -it app php artisan test
git add .
git commit -m \"feat: add voice player component\"

# 🐛 Debug Livewire helpers
docker compose exec -it app php artisan livewire:check            # Check Livewire setup
docker compose exec -it app php artisan livewire:list             # List all components
docker compose exec -it app php artisan route:list | grep livewire # Show Livewire routes

# 🧹 Clean up Livewire
docker compose exec -it app php artisan livewire:clear-cached-components
docker compose exec -it app php artisan view:clear
docker compose exec -it app php artisan optimize:clear
```

## Development Mindset for AI Agents

### Backend-First Approach
1. **Start with data modeling**: Define your Eloquent models and relationships first
2. **Build services layer**: Create reusable business logic in service classes
3. **Add Filament resources**: Create admin interfaces for data management
4. **Test the foundation**: Ensure backend logic works before adding frontend

### Frontend Enhancement
1. **Identify interactive needs**: Determine what requires real-time updates
2. **Create focused components**: Each Livewire component should have a single responsibility
3. **Progressive enhancement**: Start with basic functionality, add advanced features incrementally
4. **Test user interactions**: Verify all user flows work correctly

### Key Principles
1. **Always validate assumptions**: Test each step before moving forward
2. **Small, incremental changes**: Build one feature at a time
3. **Quality over speed**: Write maintainable, tested code
4. **Follow conventions**: Use Laravel/Livewire patterns consistently
5. **Optimize last**: Get it working, then make it fast
6. **Test comprehensively**: Both unit and integration tests are valuable

Remember: **Every component you create should pass both Pint and PHPStan checks, have corresponding tests, and follow Livewire 3 best practices.**

