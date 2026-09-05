@extends('layouts.app', ['breadcrumbs' => [
    ['href'=> route('dashboard'), 'text'=> 'Dashboard'],
    ['href'=> route('students.index'), 'text'=> 'Students'],
    ['href'=> route('students.promote'), 'text'=> 'Promote Students', 'active'],
]])

@section('title', __('Promote Students'))

@section('page_heading',  __('Promote Students'))

@section('content' )
    <div class="my-3">
        <a href="{{route('students.place-senior')}}" class="april-btn april-btn-primary inline-flex items-center gap-2">
            <i class="fas fa-graduation-cap" aria-hidden="true"></i>
            Senior School Placement (Grade 9 &rarr; Grade 10)
        </a>
    </div>
    @livewire('promote-students')
@endsection