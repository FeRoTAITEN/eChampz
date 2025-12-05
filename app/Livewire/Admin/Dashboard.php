<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('admin.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public int $totalUsers = 0;
    public int $totalGamers = 0;
    public int $totalRecruiters = 0;
    public int $totalAdmins = 0;
    public int $verifiedUsers = 0;
    public int $unverifiedUsers = 0;

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->totalUsers = User::count();
        $this->totalGamers = User::where('role', 'gamer')->count();
        $this->totalRecruiters = User::where('role', 'recruiter')->count();
        $this->totalAdmins = Admin::count();
        $this->verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $this->unverifiedUsers = User::whereNull('email_verified_at')->count();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}

