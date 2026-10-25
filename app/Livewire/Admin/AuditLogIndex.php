<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogIndex extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.admin.audit-log', [
            'logs' => AuditLog::with('user.role')->latest()->paginate(20),
        ])->layout('layouts.app');
    }
}