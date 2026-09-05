@extends('layouts.app', ['breadcrumbs' => [
    ['href'=> route('dashboard'), 'text'=> 'Dashboard'],
    ['href'=> route('fees.index'), 'text'=> 'Fees', 'active'],
]])

@section('title',  __('Fees'))

@section('page_heading',   __('Fees'))

@section('content', )
    <div class="my-3">
        <a href="{{route('fee-structures.index')}}" class="april-btn april-btn-primary inline-flex items-center gap-2">
            <i class="fas fa-sack-dollar" aria-hidden="true"></i>
            Fee Structures per Grade per Term
        </a>
    </div>
    @livewire('list-fees-table')
@endsection