@extends('layouts.app', ['breadcrumbs' => [
    ['href'=> route('dashboard'), 'text'=> 'Dashboard'],
    ['href'=> route('fees.index'), 'text'=> 'Fees'],
    ['href'=> route('fee-structures.index'), 'text'=> 'Fee Structures', 'active'],
]])

@section('title', __('Fee Structures'))

@section('page_heading',  __('Fee Structures per Grade per Term'))

@section('content' )
    @livewire('manage-fee-structures')
@endsection
