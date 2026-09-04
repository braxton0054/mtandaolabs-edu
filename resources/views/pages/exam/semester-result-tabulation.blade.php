@extends('layouts.app', ['breadcrumbs' => [
    ['href'=> route('dashboard'), 'text'=> 'Dashboard'],
    ['href'=> route('exams.index'), 'text'=> 'exams'],
    ['href'=> route('exams.semester-result-tabulation'), 'text'=> 'Term Result tabulation', 'active'],
]])

@section('title',    __('Term result tabulation'))

@section('page_heading',  __('Term result tabulation'))

@section('content', )
@livewire('semester-result-tabulation')
@endsection