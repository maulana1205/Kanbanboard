<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'division',
        'team',              // ✅ sesuai tabel
        'Function',          // ✅ match dengan kolom DB (huruf besar)
        'detail_task',
        'priority',
        'start_date',
        'finish_date',
        'sla',
        'sla_status',
        'status',
        'task_progress',     // ✅ sesuai tabel
        'uom',
        'qty',
        'vendor',
        'fdt_id',
        'created_by',
        'assigned_to',
        'remark',
    ];

    protected $appends = [
        'over_sla_days',
        'task_creator_name',
        'task_function',
        'sla_status_computed', // 🔹 supaya selalu ada walau field kosong
    ];

    // Relasi ke user pembuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke user yang ditugaskan
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Hitung over SLA (berapa hari lewat deadline)
    public function getOverSlaDaysAttribute()
    {
        if ($this->sla && $this->start_date) {
            $deadline = Carbon::parse($this->start_date)->addDays($this->sla);

            if ($this->finish_date) {
                return Carbon::parse($this->finish_date)->greaterThan($deadline)
                    ? Carbon::parse($this->finish_date)->diffInDays($deadline)
                    : 0;
            } else {
                return Carbon::now()->greaterThan($deadline)
                    ? Carbon::now()->diffInDays($deadline)
                    : 0;
            }
        }
        return 0;
    }

    // Tambahkan nama creator ke response API
    public function getTaskCreatorNameAttribute()
    {
        return $this->creator ? $this->creator->name : null;
    }

    // Ambil task function (karena field di DB huruf besar)
    public function getTaskFunctionAttribute()
    {
        return $this->Function;
    }

    // Hitung status SLA dinamis (On SLA / Over SLA / N/A)
    public function getSlaStatusComputedAttribute()
    {
        if (!$this->sla || !$this->start_date) {
            return 'N/A';
        }

        $deadline = Carbon::parse($this->start_date)->addDays($this->sla);

        // Jika sudah selesai → cek finish_date
        if ($this->finish_date) {
            return Carbon::parse($this->finish_date)->greaterThan($deadline)
                ? 'Over SLA'
                : 'On SLA';
        }

        // Jika belum selesai → cek sekarang
        return now()->greaterThan($deadline)
            ? 'Over SLA'
            : 'On SLA';
    }
}
