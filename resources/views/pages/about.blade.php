@extends('layouts.app')

@section('title', $site->get('about_about_hero.page_title'))

@section('content')
@foreach($site->pageSections('about') as $sectionKey)
    @include('pages.sections.'.$sectionKey)
@endforeach

@endsection
