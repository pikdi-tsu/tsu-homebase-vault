<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserDosenTendikResource;
use App\Filament\Resources\UserDosenTendikResource\Pages\CreateUserDosenTendik;
use App\Models\BackupUsersDosenTendik;
use App\Models\UserDosenTendik;
use App\Services\DefaultPasswordService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Components\Form;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShortcutButtonsWidget extends Widget implements HasActions
{
    use InteractsWithActions;

    protected string $view = 'filament.widgets.shortcut-buttons-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -2;
}
