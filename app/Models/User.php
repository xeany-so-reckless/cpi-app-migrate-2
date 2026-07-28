<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
 
class User extends Authenticatable
{
    use Notifiable;
 
    protected $fillable = [
        'employee_code',
        'name',
        'password',
    ];
 
    protected $hidden = [
        'password',
        'remember_token',
    ];
 
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
 
    /**
     * Login memakai employee_code (mis. "TLY01"), bukan email.
     */
    public function getAuthIdentifierName(): string
    {
        return 'employee_code';
    }
 
    /**
     * Relasi many-to-many: 1 user bisa punya banyak role sekaligus
     * (misal: sekaligus 'foreman' di Tally Pro DAN role lain di modul lain).
     * Menggantikan kolom `role` tunggal yang lama.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
 
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }
 
    /**
     * True kalau user punya SALAH SATU dari role yang disebutkan.
     * Dipakai di middleware EnsureUserHasRole.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }
}
