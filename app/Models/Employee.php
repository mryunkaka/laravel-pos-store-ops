<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    public const EXPERIENCE_OPTIONS = [
        '0 Pengalaman',
        '1 Tahun',
        '2 Tahun',
        '3 Tahun',
        '4 Tahun',
        '5 Tahun',
        '5+ Tahun',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'experience',
        'photo',
        'salary',
        'vacation',
        'city',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
    ];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? false, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        });
    }

    public function advanceSalaries(): HasMany
    {
        return $this->hasMany(AdvanceSalary::class);
    }
}
