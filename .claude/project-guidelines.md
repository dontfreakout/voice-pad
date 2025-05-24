# VoicePad Admin Panel - Project Guidelines

This document outlines the coding conventions, code organization, and quality standards for the VoicePad admin panel
built with Laravel 12+ and FilamentPHP 3.3+.

## Table of Contents

1. [Coding Conventions](#coding-conventions)
2. [Code Organization](#code-organization)
3. [Code Quality Tools](#code-quality-tools)
4. [Development Workflow](#development-workflow)
5. [Best Practices](#best-practices)

## Coding Conventions

### PHP Coding Standards

We follow PSR-2 coding standard and the PSR-4 autoloading standard as adopted by Laravel. Additionally, we adhere to
Laravel's opinionated coding style enforced by Laravel Pint.

#### General Rules

1. **Files**
    - Use only `<?php` tags (no short tags)
    - Use strict types declaration at the top of each file: `declare(strict_types=1);`
    - Files MUST use only UTF-8 without BOM
    - Files SHOULD either declare symbols OR cause side-effects, not both
    - PHP files MUST end with a single blank line
    - Code SHOULD use PHP 8+ features, such as typed properties, union types, and named arguments

2. **Indentation & Spacing**
    - Code MUST use an indent of 4 spaces for each indent level, and MUST NOT use tabs for indenting
    - Lines SHOULD be 80 characters or less; the soft limit is 120 characters
    - There MUST be one blank line after namespace and use declarations

3. **Classes & Methods**
    - Class names MUST be declared in `StudlyCaps`
    - Method names MUST be declared in `camelCase`
    - Constants MUST be declared in all uppercase with underscore separators
    - Properties and methods MUST declare visibility (`public`, `protected`, `private`)

### Laravel-Specific Conventions

Following naming conventions that align with the PHP-FIG's PSR standards, as well as those organically adopted by the
Laravel community:

#### Naming Conventions

| Type         | Convention                 | Example                                |
|--------------|----------------------------|----------------------------------------|
| Controller   | Singular, PascalCase       | `UserController`                       |
| Model        | Singular, PascalCase       | `User`                                 |
| Migration    | snake_case                 | `2024_01_01_000000_create_users_table` |
| Table        | Plural, snake_case         | `users`                                |
| Column       | snake_case                 | `email_verified_at`                    |
| Route        | Plural, kebab-case         | `/users/{user}`                        |
| View         | kebab-case                 | `users.index`                          |
| Config       | snake_case                 | `config('app.timezone')`               |
| Form Request | PascalCase + Request       | `StoreUserRequest`                     |
| Resource     | Singular + Resource        | `UserResource`                         |
| Event        | PascalCase + past tense    | `UserRegistered`                       |
| Listener     | PascalCase + present tense | `SendEmailVerification`                |
| Command      | PascalCase + imperative    | `ProcessPayment`                       |
| Job          | PascalCase + imperative    | `SendNewsletterEmail`                  |

#### Eloquent Relationships

```php
// HasOne or BelongsTo: singular
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

// HasMany, BelongsToMany, HasManyThrough: plural
public function comments(): HasMany
{
    return $this->hasMany(Comment::class);
}
```

### FilamentPHP Conventions

1. **Resources**
    - Place in `app/Filament/Resources/`
    - Name as `{Model}Resource` (e.g., `UserResource`)
    - Use static methods for configuration

2. **Custom Pages**
    - Place in `app/Filament/Pages/`
    - Extend appropriate Filament base classes

3. **Widgets**
    - Place in `app/Filament/Widgets/`
    - Follow Filament's widget naming patterns

## Code Organization

### Directory Structure

```
app/
├── Actions/              # Single-purpose action classes
├── Console/
│   └── Commands/        # Artisan commands
├── Contracts/           # Interfaces
├── DTOs/               # Data Transfer Objects
├── Enums/              # PHP Enums
├── Events/             # Event classes
├── Exceptions/         # Custom exceptions
├── Filament/           # FilamentPHP components
│   ├── Pages/          # Custom Filament pages
│   ├── Resources/      # Filament resources
│   └── Widgets/        # Dashboard widgets
├── Http/
│   ├── Controllers/    # API/Web controllers
│   ├── Middleware/     # HTTP middleware
│   └── Requests/       # Form requests
├── Jobs/               # Queued jobs
├── Listeners/          # Event listeners
├── Mail/               # Mailable classes
├── Models/             # Eloquent models
├── Notifications/      # Notification classes
├── Observers/          # Model observers
├── Policies/           # Authorization policies
├── Providers/          # Service providers
├── Repositories/       # Repository pattern (if used)
├── Rules/              # Custom validation rules
├── Services/           # Business logic services
├── Traits/             # Reusable traits
└── ViewModels/         # View models (if used)
```

### Design Principles

1. **Single Responsibility Principle**
    - A class should have only one responsibility
    - Extract business logic to Action or Service classes
    - Keep controllers thin

2. **Repository Pattern** (Optional)
    - Use when you need to abstract database queries
    - Useful for complex queries or when switching databases

3. **Service Layer**
    - Place complex business logic in service classes
    - Keep models focused on relationships and attributes

4. **Data Transfer Objects (DTOs)**
    - Use for complex data structures
    - Type-safe data passing between layers

### Example Service Class

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\DTOs\UserData;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function createUser(UserData $userData): User
    {
        return DB::transaction(function () use ($userData) {
            $user = User::create($userData->toArray());
            
            // Additional logic here
            
            return $user;
        });
    }
}
```

## Best Practices

### General Laravel Best Practices

1. **Use Eloquent Over Raw Queries**
    - Eloquent allows you to write readable and maintainable code
    - Leverage scopes, accessors, mutators

2. **Follow Laravel Conventions**
    - As long as you follow certain conventions, you do not need to add additional configuration
    - Use resource controllers
    - Follow RESTful routing

3. **Validation**
    - Use Form Request classes for validation
    - Keep validation rules DRY with custom Rule classes

4. **Security**
    - Always use parameterized queries (Eloquent does this automatically)
    - Validate and sanitize all user input
    - Use Laravel's built-in authentication
    - Keep `.env` file secure
    - Use HTTPS in production

### FilamentPHP Best Practices

1. **Resource Organization**
    - Group related resources using navigation groups
    - Use resource sub-navigation for complex resources

2. **Performance**
    - Use eager loading in resource queries
    - Implement table column searching efficiently
    - Cache expensive computations

3. **Custom Fields**
    - Create reusable custom field classes
    - Document custom field usage

4. **Testing**
    - Test Filament resources using Livewire testing utilities
    - Test custom actions and bulk actions

### Code Examples

#### Clean Controller Example

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly CreateUserAction $createUserAction,
    ) {
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->createUserAction->execute($request->validated());

        return response()->json([
            'data' => new UserResource($user),
        ], 201);
    }
}
```

#### Filament Resource Example

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
```
