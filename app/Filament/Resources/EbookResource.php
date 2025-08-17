<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EbookResource\Pages;
use App\Models\Ebook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\Repeater;

class EbookResource extends Resource
{
    protected static ?string $model = Ebook::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'E-books';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('E-book Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(200)
                            ->placeholder('Enter e-book title'),

                        Textarea::make('description')
                            ->rows(4)
                            ->placeholder('Enter e-book description'),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->placeholder('0.00')
                                    ->default(0),

                                Select::make('price_type')
                                    ->options([
                                        'free' => 'Free',
                                        'paid' => 'Paid',
                                    ])
                                    ->default('free')
                                    ->required(),
                            ]),
                    ])->columns(1),

                Section::make('Files & Media')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('3:4')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('400')
                            ->directory('ebooks/covers')
                            ->placeholder('Upload cover image (3:4 ratio recommended)'),

                        FileUpload::make('ebook_file')
                            ->acceptedFileTypes(['application/pdf', 'application/epub+zip'])
                            ->maxSize(51200) // 50MB
                            ->directory('ebooks/files')
                            ->placeholder('Upload e-book file (PDF/EPUB)')
                            ->required(),
                    ])->columns(2),

                Section::make('Audiobook Files (Optional)')
                    ->schema([
                        Repeater::make('audiobook_files')
                            ->relationship('audiobookFiles')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->placeholder('Chapter/Part name'),

                                FileUpload::make('file_url')
                                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/aac', 'audio/wav'])
                                    ->maxSize(102400) // 100MB
                                    ->directory('ebooks/audiobooks')
                                    ->placeholder('Upload audio file')
                                    ->required(),

                                TextInput::make('order_number')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->collapsed(),
                    ])->collapsible(),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),

                        Toggle::make('is_featured')
                            ->label('Featured E-book')
                            ->default(false),

                        TextInput::make('created_by')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(auth()->id()),
                    ])->columns(3)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->circular()
                    ->size(50),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),

                TextColumn::make('description')
                    ->limit(80)
                    ->toggleable(),

                BadgeColumn::make('price_type')
                    ->colors([
                        'success' => 'free',
                        'warning' => 'paid',
                    ]),

                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),

                IconColumn::make('has_audiobook')
                    ->boolean()
                    ->trueIcon('heroicon-o-musical-note')
                    ->falseIcon('heroicon-o-x-circle'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('price_type')
                    ->options([
                        'free' => 'Free',
                        'paid' => 'Paid',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active Status'),

                TernaryFilter::make('is_featured')
                    ->label('Featured'),

                TernaryFilter::make('has_audiobook')
                    ->label('Has Audiobook'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Informasi E-book')
                    ->schema([
                        InfolistGrid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Judul')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),

                                TextEntry::make('price')
                                    ->label('Harga')
                                    ->money('IDR')
                                    ->formatStateUsing(fn ($state) => $state == 0 ? 'Gratis' : 'Rp ' . number_format($state, 0, ',', '.')),
                            ]),

                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->markdown()
                            ->columnSpan(2),

                        InfolistGrid::make(2)
                            ->schema([
                                ImageEntry::make('cover_image')
                                    ->label('Cover Image')
                                    ->circular()
                                    ->size(100),

                                TextEntry::make('file_url')
                                    ->label('File E-book')
                                    ->url(fn ($record) => $record->full_file_url)
                                    ->openUrlInNewTab(),
                            ]),
                    ])->columns(2),

                InfolistSection::make('Informasi Sistem')
                    ->schema([
                        InfolistGrid::make(3)
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('Dibuat Oleh')
                                    ->icon('heroicon-m-user'),

                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime()
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime()
                                    ->icon('heroicon-m-clock'),
                            ]),
                    ])->columns(3)->collapsible(),
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
            'index' => Pages\ListEbooks::route('/'),
            'create' => Pages\CreateEbook::route('/create'),
            'edit' => Pages\EditEbook::route('/{record}/edit'),
            'view' => Pages\ViewEbook::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['creator', 'audiobookFiles']);
    }
}
