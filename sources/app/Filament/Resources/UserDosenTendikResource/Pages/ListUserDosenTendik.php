<?php

namespace App\Filament\Resources\UserDosenTendikResource\Pages;

use App\Filament\Actions\ImportDosenTendikAction;
use App\Models\BackupUsersDosenTendik;
use App\Models\MasterGroup;
use App\Services\DefaultPasswordService;
use App\Traits\HasAccentCreateAction;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use App\Filament\Resources\UserDosenTendikResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Form;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ListUserDosenTendik extends ListRecords
{
    protected static string $resource = UserDosenTendikResource::class;

//    protected $listeners = ['openCreateModalForDosenTendik' => 'openCreateModal'];
//
//    public function openCreateModal(): void
//    {
//        $this->mountAction('create');
//    }

    protected function getHeaderActions(): array
    {
        return [
            ImportDosenTendikAction::make()
                ->successNotificationTitle('Import User Dosen/Tendik berhasil ditambahkan'),

            CreateAction::make()
                ->label('Input Manual Dosen/Tendik')
                ->color('secondary')
                ->icon('heroicon-m-pencil-square')
                ->modalHeading('Input Dosen/Tendik Manual')
                ->modalWidth('2xl')
                ->modalSubmitActionLabel('Simpan')
                ->createAnotherAction(fn ($action) => $action->label('Simpan & Tambah Lagi'))
                ->schema(CreateUserDosenTendik::getCreateFormSchema())
                ->using(function (array $data, $form): Model {
                    $data['password'] = (new DefaultPasswordService())->getDefaultHashedPassword();
                    $data['created_by'] = Auth::user()->username;

                    $rolesIds = $data['roles'] ?? [];

                    $newUser = $this->getModel()::create($data);
                    $newUser->assignRole($rolesIds);

                    return $newUser;
                })
                ->successNotificationTitle('User Dosen/Tendik berhasil ditambahkan'),
        ];
    }
}
