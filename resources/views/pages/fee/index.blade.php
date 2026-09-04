@extends('layouts.app', ['breadcrumbs' => [
    ['href'=> route('dashboard'), 'text'=> 'Dashboard'],
    ['href'=> route('fees.index'), 'text'=> 'Fees', 'active'],
]])

@section('title',  __('Fees'))

@section('page_heading',   __('Fees'))

@section('content', )
    <div class="my-2">
        <a href="{{route('fee-structures.index')}}" class="btn btn-primary">Fee Structures per Grade per Term</a>
    </div>
    @livewire('list-fees-table')
@endsection