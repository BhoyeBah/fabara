<?php
namespace App\Filament\Pages;

use App\Support\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Paramètres';
    protected static ?string $title = 'Paramètres de l’entreprise';
    protected static ?int $navigationSort = 60;
    protected static ?string $navigationGroup = 'Administration';
    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->role?->value === 'admin';
    }

    public function mount(): void
    {
        $this->form->fill(Setting::all());
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\TextInput::make('company_name')->label('Nom de l’entreprise')->required(),
            Forms\Components\TextInput::make('phone')->label('Téléphone'),
            Forms\Components\TextInput::make('email')->label('Email de contact (affiché dans la politique de confidentialité)')->email(),
            Forms\Components\TextInput::make('address')->label('Adresse'),
            Forms\Components\TextInput::make('whatsapp')->label('WhatsApp (avec indicatif, sans + ni espaces)')->helperText('Utilisé par les boutons « Commander » et « Devis » du site.'),
            Forms\Components\TextInput::make('currency')->label('Devise affichée après les montants')->helperText('Ex : GNF. Vide = aucune.'),
            Forms\Components\TextInput::make('tax_rate')->label('TVA en %')->numeric()->minValue(0)->helperText('0 = pas de TVA. Les prix saisis sont considérés TTC.'),
            Forms\Components\Textarea::make('invoice_footer')->label('Message en bas des factures'),
        ])->columns(2);
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            Setting::put($key, $value);
        }
        Notification::make()->title('Paramètres enregistrés.')->success()->send();
    }
}
