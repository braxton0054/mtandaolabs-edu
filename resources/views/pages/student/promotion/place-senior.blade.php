@extends('layouts.app', ['breadcrumbs' => [
    ['href'=> route('dashboard'), 'text'=> 'Dashboard'],
    ['href'=> route('students.index'), 'text'=> 'Students'],
    ['href'=> route('students.place-senior'), 'text'=> 'Senior Placement', 'active'],
]])

@section('title', __('Senior Placement'))

@section('page_heading',  __('Place Learners into Senior School'))

@section('content' )
    @livewire('place-senior-learners')
@endsection
