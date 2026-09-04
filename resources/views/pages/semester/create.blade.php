@extends('layouts.app', ['breadcrumbs' => [
    ['href'=> route('dashboard'), 'text'=> 'Dashboard'],
    ['href'=> route('semesters.index'), 'text'=> 'Semesters'],
    ['href'=> route('semesters.create'), 'text'=> 'Create' , 'active'],
]])

@section('title', __('Create term'))

@section('page_heading',  __('Create term'))

@section('content' )
    @livewire('create-semester-form')
@endsection