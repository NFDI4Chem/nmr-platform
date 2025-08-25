# Developer Guide

Welcome to the NMR Platform developer documentation. This guide covers everything you need to know to contribute to the platform or extend its functionality.

## Architecture Overview

The NMR Platform is built using modern web technologies with a focus on scalability, maintainability, and extensibility.

### Technology Stack

**Backend:**
- **Framework**: Laravel 12 (PHP 8.2+)
- **Admin Interface**: Filament v3
- **Database**: MySQL 8.0+ / PostgreSQL 13+
- **Queue System**: Redis / Database queues
- **Search**: Laravel Scout with Meilisearch
- **File Storage**: Local / S3 / GCS

**Frontend:**
- **Main UI**: Filament (Livewire + Alpine.js)
- **Spectrum Viewer**: Vue.js 3 + D3.js
- **Build Tool**: Vite
- **Styling**: Tailwind CSS

**Infrastructure:**
- **Containerization**: Docker & Docker Compose
- **Web Server**: Nginx / Apache
- **Process Management**: Supervisor
- **Monitoring**: Laravel Horizon, Pulse

### Application Structure

```
nmr-platform/
├── app/
│   ├── Actions/           # Application actions
│   ├── Enums/            # Enumeration classes
│   ├── Filament/         # Filament admin resources
│   ├── Http/             # Controllers and middleware
│   ├── Models/           # Eloquent models
│   ├── Policies/         # Authorization policies
│   ├── Services/         # Business logic services
│   └── View/             # View composers
├── config/               # Configuration files
├── database/
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   └── seeders/         # Database seeders
├── docs/                # VitePress documentation
├── resources/
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript/Vue components
│   └── views/           # Blade templates
├── routes/              # Route definitions
├── storage/             # Storage directories
└── tests/               # Test suites
```

## Design Patterns

### Service Layer Pattern

Business logic is encapsulated in service classes:

```php
<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\Project;

class DatasetService
{
    public function createDataset(Project $project, array $data): Dataset
    {
        // Validation and business logic
        $dataset = new Dataset($data);
        $dataset->project()->associate($project);
        $dataset->save();
        
        // Trigger processing job
        ProcessDatasetJob::dispatch($dataset);
        
        return $dataset;
    }
}
```

### Repository Pattern

Data access is abstracted through repositories:

```php
<?php

namespace App\Repositories;

interface DatasetRepositoryInterface
{
    public function findByProject(Project $project): Collection;
    public function findByExperimentType(string $type): Collection;
    public function search(array $criteria): Collection;
}
```

### Action Pattern

Complex operations are implemented as actions:

```php
<?php

namespace App\Actions;

use App\Models\Dataset;

class ProcessNMRSpectrum
{
    public function execute(Dataset $dataset): array
    {
        // Peak detection
        $peaks = $this->detectPeaks($dataset);
        
        // Integration
        $integrations = $this->calculateIntegrations($dataset);
        
        // Metadata extraction
        $metadata = $this->extractMetadata($dataset);
        
        return compact('peaks', 'integrations', 'metadata');
    }
}
```

## Key Components

### Models

The platform uses Eloquent models with relationships:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dataset extends Model
{
    protected $fillable = [
        'name',
        'description',
        'experiment_type',
        'nucleus',
        'frequency',
        'solvent',
        'temperature',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'processed_at' => 'datetime'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(DatasetFile::class);
    }

    public function peaks(): HasMany
    {
        return $this->hasMany(Peak::class);
    }
}
```

### Filament Resources

Admin interface is built with Filament resources:

```php
<?php

namespace App\Filament\Resources;

use App\Models\Dataset;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class DatasetResource extends Resource
{
    protected static ?string $model = Dataset::class;
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('experiment_type')
                    ->options([
                        '1H_NMR' => '1H NMR',
                        '13C_NMR' => '13C NMR',
                        '2D_NMR' => '2D NMR',
                    ])
                    ->required(),
                // Additional form fields...
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('experiment_type'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('experiment_type'),
            ]);
    }
}
```

### Jobs and Queues

Background processing is handled by queued jobs:

```php
<?php

namespace App\Jobs;

use App\Models\Dataset;
use App\Services\NMRProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDatasetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Dataset $dataset
    ) {}

    public function handle(NMRProcessingService $processingService): void
    {
        $processingService->processDataset($this->dataset);
    }
}
```

## Development Environment Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- Docker (recommended)
- Git

### Local Setup

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/NFDI4Chem/nmr-platform.git
   cd nmr-platform
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**:
   ```bash
   # Using Docker
   docker-compose up -d mysql
   
   # Or configure local database in .env
   php artisan migrate --seed
   ```

5. **Build Assets**:
   ```bash
   npm run dev
   ```

6. **Start Development Server**:
   ```bash
   php artisan serve
   ```

### Docker Development

```bash
# Start all services
docker-compose -f docker-compose.dev.yml up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Run migrations
docker-compose exec app php artisan migrate --seed

# Build assets
docker-compose exec app npm run dev
```

## Testing

### Test Structure

```
tests/
├── Feature/           # Feature tests
│   ├── Api/          # API endpoint tests
│   ├── Auth/         # Authentication tests
│   └── Admin/        # Admin interface tests
├── Unit/             # Unit tests
│   ├── Models/       # Model tests
│   ├── Services/     # Service tests
│   └── Actions/      # Action tests
└── TestCase.php      # Base test class
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test tests/Feature/Api/DatasetTest.php
```

### Writing Tests

**Feature Test Example:**
```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Project;
use Tests\TestCase;

class DatasetTest extends TestCase
{
    public function test_user_can_create_dataset(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        
        $response = $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/datasets", [
                'name' => 'Test Dataset',
                'experiment_type' => '1H_NMR',
                'nucleus' => '1H',
            ]);
            
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'experiment_type',
                ]
            ]);
    }
}
```

**Unit Test Example:**
```php
<?php

namespace Tests\Unit\Services;

use App\Services\NMRProcessingService;
use App\Models\Dataset;
use Tests\TestCase;

class NMRProcessingServiceTest extends TestCase
{
    public function test_can_detect_peaks(): void
    {
        $service = new NMRProcessingService();
        $dataset = Dataset::factory()->create();
        
        $peaks = $service->detectPeaks($dataset);
        
        $this->assertIsArray($peaks);
        $this->assertNotEmpty($peaks);
    }
}
```

## Contributing Guidelines

### Code Style

The project follows PSR-12 coding standards:

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

### Git Workflow

1. **Fork and Clone**:
   ```bash
   git clone https://github.com/your-username/nmr-platform.git
   ```

2. **Create Feature Branch**:
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Changes**:
   - Write tests first (TDD approach)
   - Implement feature
   - Ensure tests pass

4. **Commit and Push**:
   ```bash
   git add .
   git commit -m "feat: add your feature description"
   git push origin feature/your-feature-name
   ```

5. **Create Pull Request**:
   - Use descriptive title and description
   - Reference related issues
   - Ensure CI passes

### Commit Message Format

Follow conventional commits:

```
type(scope): description

[optional body]

[optional footer]
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Test changes
- `chore`: Build/config changes

## API Development

### Creating New Endpoints

1. **Create Controller**:
   ```bash
   php artisan make:controller Api/V1/DatasetController --api
   ```

2. **Define Routes**:
   ```php
   // routes/api.php
   Route::apiResource('datasets', DatasetController::class);
   ```

3. **Implement Controller**:
   ```php
   <?php
   
   namespace App\Http\Controllers\Api\V1;
   
   use App\Http\Controllers\Controller;
   use App\Http\Resources\DatasetResource;
   use App\Models\Dataset;
   
   class DatasetController extends Controller
   {
       public function index()
       {
           return DatasetResource::collection(
               Dataset::paginate()
           );
       }
   }
   ```

4. **Create API Resource**:
   ```bash
   php artisan make:resource DatasetResource
   ```

### API Documentation

Document APIs using OpenAPI annotations:

```php
/**
 * @OA\Get(
 *     path="/api/v1/datasets",
 *     summary="List datasets",
 *     tags={"Datasets"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/DatasetCollection")
 *     )
 * )
 */
public function index()
{
    // Implementation
}
```

## Database Design

### Migrations

Create and modify database structure:

```bash
php artisan make:migration create_datasets_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('experiment_type');
            $table->string('nucleus');
            $table->decimal('frequency', 8, 2)->nullable();
            $table->string('solvent')->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['experiment_type', 'nucleus']);
            $table->index('processed_at');
        });
    }
};
```

### Model Relationships

Design efficient database relationships:

```php
// One-to-Many
public function datasets(): HasMany
{
    return $this->hasMany(Dataset::class);
}

// Many-to-Many with Pivot
public function collaborators(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'project_collaborators')
        ->withPivot('role', 'permissions')
        ->withTimestamps();
}

// Polymorphic
public function comments(): MorphMany
{
    return $this->morphMany(Comment::class, 'commentable');
}
```

## Performance Optimization

### Database Optimization

1. **Eager Loading**:
   ```php
   $datasets = Dataset::with(['project', 'files', 'peaks'])->get();
   ```

2. **Query Optimization**:
   ```php
   $datasets = Dataset::select(['id', 'name', 'experiment_type'])
       ->where('experiment_type', '1H_NMR')
       ->orderBy('created_at', 'desc')
       ->paginate(20);
   ```

3. **Database Indexes**:
   ```php
   $table->index(['experiment_type', 'nucleus']);
   $table->index('created_at');
   ```

### Caching Strategy

1. **Model Caching**:
   ```php
   $project = Cache::remember("project.{$id}", 3600, function () use ($id) {
       return Project::with('datasets')->find($id);
   });
   ```

2. **Query Result Caching**:
   ```php
   $popularDatasets = Cache::remember('popular_datasets', 1800, function () {
       return Dataset::withCount('downloads')
           ->orderBy('downloads_count', 'desc')
           ->take(10)
           ->get();
   });
   ```

## Security Considerations

### Authentication & Authorization

1. **API Authentication**:
   ```php
   // routes/api.php
   Route::middleware('auth:sanctum')->group(function () {
       Route::apiResource('datasets', DatasetController::class);
   });
   ```

2. **Authorization Policies**:
   ```php
   <?php
   
   namespace App\Policies;
   
   class DatasetPolicy
   {
       public function view(User $user, Dataset $dataset): bool
       {
           return $user->can('view', $dataset->project);
       }
       
       public function update(User $user, Dataset $dataset): bool
       {
           return $user->can('edit', $dataset->project);
       }
   }
   ```

### Data Validation

1. **Form Requests**:
   ```php
   <?php
   
   namespace App\Http\Requests;
   
   use Illuminate\Foundation\Http\FormRequest;
   
   class StoreDatasetRequest extends FormRequest
   {
       public function rules(): array
       {
           return [
               'name' => 'required|string|max:255',
               'experiment_type' => 'required|in:1H_NMR,13C_NMR,2D_NMR',
               'files.*' => 'required|file|mimes:fid,ser,jdx|max:102400',
           ];
       }
   }
   ```

2. **Sanitization**:
   ```php
   $data = $request->validated();
   $data['name'] = strip_tags($data['name']);
   $data['description'] = clean($data['description']);
   ```

## Deployment

### Production Checklist

- [ ] Environment configuration optimized for production
- [ ] Database migrations and seeders ready
- [ ] Static assets built and optimized
- [ ] Queue workers configured
- [ ] Logging and monitoring set up
- [ ] SSL certificates installed
- [ ] Backup strategy implemented
- [ ] Security headers configured

### Docker Deployment

See [Deployment Guide](/developer/deployment) for detailed instructions.

## Additional Resources

- [Database Schema Documentation](/developer/database)
- [Testing Guide](/developer/testing)
- [Deployment Guide](/developer/deployment)
- [API Reference](/api/)
- [Contributing Guidelines](https://github.com/NFDI4Chem/nmr-platform/blob/main/CONTRIBUTING.md)
