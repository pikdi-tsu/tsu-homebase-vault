<?php

namespace App\Filament\Resources\UserMahasiswaResource\Pages;

use App\Filament\Actions\ImportMahasiswaAction;
use App\Models\BackupUsersDosenTendik;
use App\Models\BackupUsersMahasiswa;
use App\Services\DefaultPasswordService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use App\Filament\Resources\UserMahasiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ListUserMahasiswa extends ListRecords
{
    protected static string $resource = UserMahasiswaResource::class;

//    protected $listeners = ['openCreateModalForMahasiswa' => 'openCreateModal'];
//
//    public function openCreateModal(): void
//    {
//        $this->mountAction('create');
//    }

    protected function getHeaderActions(): array
    {
        return [
            ImportMahasiswaAction::make()
                ->successNotificationTitle('Import User Dosen/Tendik berhasil ditambahkan'),

            CreateAction::make()
                ->label('input Manual Mahasiswa')
                ->icon('heroicon-m-pencil-square')
                ->color('secondary')
                ->modalHeading('Tambah User Mahasiswa')
                ->schema(CreateUserMahasiswa::getCreateFormSchema())
                ->modalSubmitActionLabel('Simpan')
                ->modalCancelActionLabel('Batal')
                ->createAnotherAction(fn (Action $action) => $action->label('Simpan & Tambah Lagi'))
                ->using(function (array $data, Form $form): Model {
                    $data['password'] = (new DefaultPasswordService())->getDefaultHashedPassword();
                    $data['created_by'] = Auth::user()->username;

                    $rolesIds = $data['roles'] ?? [];

                    $newUser = $this->getModel()::create($data);
                    $newUser->assignRole($rolesIds);

                    return $newUser;
                })
                ->successNotificationTitle('User Mahasiswa berhasil ditambahkan'),
        ];
    }
}
