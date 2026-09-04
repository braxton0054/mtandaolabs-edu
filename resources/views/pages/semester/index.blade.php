@extends('layouts.app', ['breadcrumbs' => [
    ['href'=> route('dashboard'), 'text'=> 'Dashboard'],
    ['href'=> route('semesters.index'), 'text'=> 'Semesters', 'active'],
]])

@section('title', __('Terms'))

@section('page_heading',  __('Terms'))

@section('content', ) 
    @livewire('set-semester')

    <div class="my-2">
        <form action="{{route('semesters.reset-calendar')}}" method="POST" class="inline">
            @csrf
            <x-button label="Restore official MoE dates" theme="secondary" icon="fas fa-calendar" type="submit"/>
        </form>
        <small class="text-secondary">Each school sets its own opening and closing dates; this restores the official baseline.</small>
    </div>

    @livewire('list-semesters-table')
@endsection