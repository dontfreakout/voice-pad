<?php

declare(strict_types=1);

namespace App\Filament\Resources\SoundResource\Pages;

use App\Filament\Resources\SoundResource;
use App\Models\Category;
use App\Services\SoundService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BulkUploadSounds extends Page
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static string $resource = SoundResource::class;

    protected static string $view = 'filament.resources.sound-resource.pages.bulk-upload-sounds';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $title = 'Bulk Upload Sounds';

    public function mount(): void
    {
        // @phpstan-ignore-next-line
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Upload Details')
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::query()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        FileUpload::make('audio_files')
                            ->label('Sound Files')
                            ->multiple()
                            ->maxParallelUploads(4)
                            ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/mp4', 'audio/wav', 'audio/x-wav'])
                            ->maxSize(10240) // 10MB
                            ->storeFiles(false) // We will handle storage manually
                            ->helperText('Select multiple sound files to upload. The sound name will be derived from the filename.')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(SoundService $soundService): void
    {
        // @phpstan-ignore-next-line
        $data = $this->form->getState();
        $categoryId = $data['category_id'];
        $uploadedFiles = $data['audio_files'] ?? [];

        if (empty($uploadedFiles)) {
            Notification::make()
                ->title('No sound files to upload')
                ->warning()
                ->send();

            return;
        }

        DB::beginTransaction();

        try {
            $successfulUploads = 0;
            foreach ($uploadedFiles as $file) {
                /** @var TemporaryUploadedFile|UploadedFile $file */
                $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $name = Str::of($name)->ucfirst()->ucsplit()->implode(' ');

                $soundService->createSoundFromFile(
                    $file,
                    $name,
                    (int) $categoryId,
                    null // No individual description field in this bulk form
                );
                $successfulUploads++;
            }

            DB::commit();

            Notification::make()
                ->title('Successfully uploaded ' . $successfulUploads . ' sound(s)')
                ->success()
                ->send();

            // $this->form->fill(); // Reset form
            $this->redirect(SoundResource::getUrl());

        } catch (Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error uploading sounds')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * @return mixed[]
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Upload Sounds')
                ->submit('save'),
        ];
    }
}
