<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class BackupUsersMahasiswa extends Model
{
    use HasApiTokens;

    use Notifiable;
    use HasRoles;

    protected $table = 'backup_users_mahasiswa';

    protected $fillable = [
        "periode_masuk",
        "program_studi",
        "nim",
        "nama",
        "sistem_kuliah",
        "jalur_penerimaan",
        "gelombang_daftar",
        "transfer_tidak",
        "universitas_asal",
        "nim_asal",
        "ipk_asal",
        "kurikulum",
        "agama",
        "kewarganegaraan",
        "status_mahasiswa",
        "alamat",
        "telepon",
        "hp",
        "tempat_lahir",
        "tgl_lahir",
        "kodepos",
        "jenkel",
        "golongan_darah",
        "status_nikah",
        "email",
        "nik_ktp",
        "no_kk",
        "rt",
        "rw",
        "dusun",
        "desa_kelurahan",
        "kecamatan",
        "kota",
        "propinsi",
        "tgl_daftar",
        "nama_ayah",
        "alamat_ayah",
        "telp_ayah",
        "tgl_lahir_ayah",
        "pendidikan_ayah",
        "pekerjaan_ayah",
        "penghasilan_ayah",
        "nama_ibu",
        "alamat_ibu",
        "telp_ibu",
        "tgl_lahir_ibu",
        "pendidikan_ibu",
        "pekerjaan_ibu",
        "penghasilan_ibu",
        "nama_wali",
        "alamat_wali",
        "telp_wali",
        "tgl_wali",
        "pendidikan_wali",
        "pekerjaan_wali",
        "penghasilan_wali",
    ];

    protected function namaLengkapDanNim(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->nim} - {$this->nama}",
        );
    }
}
