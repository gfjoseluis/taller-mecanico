<?php

namespace App\Livewire;

use App\Models\Partner;
use Livewire\Component;
use Livewire\WithPagination;

class PartnerTable extends Component
{
    use WithPagination;
    public function render() { return view('livewire.partner-table', ['items' => Partner::orderBy('nombre')->paginate(15)]); }
}
